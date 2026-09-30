<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Helpers\Uuid;
use App\Repositories\FileRepository;
use finfo;

final class FileService extends Service
{
    private FileRepository $repo;

    public function __construct(private readonly array $config)
    {
        $this->repo = new FileRepository($config['database']);
    }

    /**
     * @return array<string,mixed>
     */
    public function listing(
        string $userId,
        ?string $folderId = null,
        string $filter = 'todos',
        ?string $disciplineId = null,
        ?string $search = null
    ): array {
        $currentFolder = null;
        $ancestors = [];

        if ($folderId !== null && $folderId !== '' && $folderId !== 'root') {
            $currentFolder = $this->repo->ownedFolder($folderId, $userId);
            if ($currentFolder === null) {
                throw new NotFoundException('Pasta não encontrada.');
            }
            $ancestors = $this->repo->folderAncestors($folderId, $userId);
        }

        $isTrash = ($filter === 'lixeira');
        $isFavorites = ($filter === 'favoritos');

        if ($isTrash) {
            $files = $this->repo->trashFiles($userId, $search);
            $folders = [];
        } else {
            $files = $this->repo->files(
                $userId,
                $isFavorites ? null : $folderId,
                $isFavorites,
                $disciplineId,
                $search
            );
            $folders = $this->repo->folders(
                $userId,
                $isFavorites ? null : ($folderId ?: 'root'),
                $disciplineId
            );
        }

        return [
            'files' => $files,
            'folders' => $folders,
            'allFolders' => $this->repo->allFolders($userId),
            'disciplines' => $this->repo->disciplines($userId),
            'tags' => $this->repo->tags($userId),
            'stats' => $this->repo->stats($userId),
            'currentFolder' => $currentFolder,
            'ancestors' => $ancestors,
            'currentFilter' => $filter,
            'currentDiscipline' => $disciplineId,
            'currentSearch' => $search ?? '',
        ];
    }

    /**
     * @param array<string,mixed> $upload
     * @param list<string> $tags
     */
    public function upload(
        string $userId,
        array $upload,
        string $disciplineId,
        ?string $folderId,
        array $tags
    ): void {
        if ($disciplineId === '' || !$this->repo->ownedDiscipline($disciplineId, $userId)) {
            throw new ValidationException(['disciplina_id' => 'Selecione uma disciplina válida.']);
        }

        if ($folderId !== null && $folderId !== '') {
            $folder = $this->repo->ownedFolder($folderId, $userId);
            if ($folder === null) {
                throw new ValidationException(['pasta_id' => 'Pasta selecionada não encontrada.']);
            }
            if (!empty($folder['disciplina_id']) && $folder['disciplina_id'] !== $disciplineId) {
                throw new ValidationException(['pasta_id' => 'A pasta selecionada pertence a outra disciplina.']);
            }
        }

        $error = $upload['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($error !== UPLOAD_ERR_OK) {
            $msg = match ($error) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'O arquivo excede o limite máximo permitido.',
                UPLOAD_ERR_PARTIAL => 'O upload do arquivo foi feito apenas parcialmente.',
                UPLOAD_ERR_NO_FILE => 'Nenhum arquivo foi selecionado.',
                default => 'Falha no upload do arquivo.',
            };
            throw new ValidationException(['arquivo' => $msg]);
        }

        if (!isset($upload['tmp_name']) || !is_uploaded_file((string) $upload['tmp_name'])) {
            throw new ValidationException(['arquivo' => 'Tentativa de upload inválida.']);
        }

        $size = (int) ($upload['size'] ?? 0);
        $maxBytes = (int) ($this->config['uploads']['max_bytes'] ?? (20 * 1024 * 1024));
        if ($size <= 0 || $size > $maxBytes) {
            throw new ValidationException(['arquivo' => 'O arquivo deve ter tamanho entre 1 byte e 20 MB.']);
        }

        $rawName = basename((string) ($upload['name'] ?? ''));
        $original = preg_replace('/[\r\n\t]/', '', $rawName);
        if ($original === null || trim($original) === '') {
            throw new ValidationException(['arquivo' => 'Nome de arquivo inválido.']);
        }

        $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        $allowedTypes = $this->config['uploads']['allowed'] ?? [];

        if (!isset($allowedTypes[$ext])) {
            throw new ValidationException([
                'arquivo' => 'Extensão não permitida. Envie arquivos PDF, EPUB, DOCX, TXT, PNG ou JPG.',
            ]);
        }

        // Validação estrita de tipo MIME direto do servidor
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $detectedMime = (string) $finfo->file((string) $upload['tmp_name']);
        $cleanMime = strtolower(explode(';', $detectedMime)[0]);

        $allowedMimesForExt = (array) $allowedTypes[$ext];
        if (!in_array($cleanMime, $allowedMimesForExt, true)) {
            throw new ValidationException([
                'arquivo' => 'O conteúdo do arquivo (' . $cleanMime . ') não corresponde à extensão informada (.' . $ext . ').',
            ]);
        }

        // Validações adicionais de integridade por tipo de arquivo
        if (in_array($ext, ['png', 'jpg', 'jpeg'], true)) {
            if (@getimagesize((string) $upload['tmp_name']) === false) {
                throw new ValidationException(['arquivo' => 'A imagem enviada está corrompida ou é inválida.']);
            }
        } elseif ($ext === 'pdf') {
            $handle = @fopen((string) $upload['tmp_name'], 'rb');
            $header = $handle !== false ? fread($handle, 5) : '';
            if ($handle !== false) {
                fclose($handle);
            }
            if ($header !== '%PDF-') {
                throw new ValidationException(['arquivo' => 'O arquivo PDF enviado é inválido ou está corrompido.']);
            }
        } elseif ($ext === 'txt') {
            $sample = @file_get_contents((string) $upload['tmp_name'], false, null, 0, 2048);
            if ($sample !== false && str_contains($sample, "\0")) {
                throw new ValidationException(['arquivo' => 'O arquivo TXT não deve conter conteúdo binário.']);
            }
        }

        // Validação de tags
        $userTags = array_column($this->repo->tags($userId), 'id');
        $validTags = [];
        foreach ($tags as $tagId) {
            if (is_string($tagId) && in_array($tagId, $userTags, true)) {
                $validTags[] = $tagId;
            }
        }

        $fileId = Uuid::v4();
        $savedFileName = $fileId . '.' . $ext;
        $storageRelative = 'uploads/' . $userId . '/' . $savedFileName;
        $storageDir = dirname(__DIR__, 2) . '/storage/uploads/' . $userId;

        if (!is_dir($storageDir) && !mkdir($storageDir, 0755, true) && !is_dir($storageDir)) {
            throw new \RuntimeException('Não foi possível inicializar o diretório de armazenamento do usuário.');
        }

        $destination = $storageDir . '/' . $savedFileName;
        if (!move_uploaded_file((string) $upload['tmp_name'], $destination)) {
            throw new \RuntimeException('Falha ao mover o arquivo enviado para o armazenamento seguro.');
        }

        try {
            $this->repo->createFile([
                'id' => $fileId,
                'p' => ($folderId !== null && $folderId !== '') ? $folderId : null,
                'd' => $disciplineId,
                'u' => $userId,
                'saved' => $savedFileName,
                'original' => mb_substr($original, 0, 255),
                'ext' => $ext,
                'mime' => $cleanMime,
                'size' => $size,
                'path' => $storageRelative,
            ]);

            if ($validTags !== []) {
                $this->repo->attachTags($fileId, $validTags);
            }

            $sizeFormatted = number_format($size / 1024, 1, ',', '.') . ' KB';
            $this->repo->addHistory(
                Uuid::v4(),
                $fileId,
                $userId,
                'criado',
                'Upload inicial do arquivo (' . mb_substr($original, 0, 100) . ' · ' . $sizeFormatted . ')'
            );
        } catch (\Throwable $e) {
            if (file_exists($destination)) {
                @unlink($destination);
            }
            throw $e;
        }
    }

    /**
     * @return array<string,mixed>
     */
    public function file(string $userId, string $fileId): array
    {
        $file = $this->repo->file($fileId, $userId);
        if ($file === null) {
            throw new NotFoundException('Arquivo não encontrado ou acesso não autorizado.');
        }

        return $file;
    }

    public function rename(string $userId, string $fileId, string $newName): void
    {
        $file = $this->file($userId, $fileId);
        $clean = trim($newName);

        if ($clean === '' || mb_strlen($clean) > 255 || str_contains($clean, '/') || str_contains($clean, '\\')) {
            throw new ValidationException(['nome' => 'Informe um nome de arquivo válido sem barras (máx. 255 caracteres).']);
        }

        // Se o usuário não incluiu a extensão original, mantém a extensão
        $oldExt = (string) $file['extensao'];
        $newExt = strtolower(pathinfo($clean, PATHINFO_EXTENSION));
        if ($newExt !== $oldExt && $oldExt !== '') {
            $clean .= '.' . $oldExt;
        }

        if ($clean === $file['nome_original']) {
            return;
        }

        $this->repo->rename($fileId, $userId, $clean);
        $this->repo->addHistory(
            Uuid::v4(),
            $fileId,
            $userId,
            'renomeado',
            'Nome alterado de "' . $file['nome_original'] . '" para "' . $clean . '"'
        );
    }

    public function favorite(string $userId, string $fileId, bool $status): void
    {
        $this->file($userId, $fileId);
        $this->repo->favorite($fileId, $userId, $status);
    }

    public function moveFile(
        string $userId,
        string $fileId,
        ?string $targetFolderId,
        ?string $targetDisciplineId
    ): void {
        $file = $this->file($userId, $fileId);

        $destFolderName = 'Raiz';
        if ($targetFolderId !== null && $targetFolderId !== '' && $targetFolderId !== 'root') {
            $folder = $this->repo->ownedFolder($targetFolderId, $userId);
            if ($folder === null) {
                throw new ValidationException(['pasta' => 'Pasta de destino não encontrada.']);
            }
            $destFolderName = (string) $folder['nome'];
            if (empty($targetDisciplineId) && !empty($folder['disciplina_id'])) {
                $targetDisciplineId = $folder['disciplina_id'];
            }
        } else {
            $targetFolderId = null;
        }

        if ($targetDisciplineId !== null && $targetDisciplineId !== '') {
            if (!$this->repo->ownedDiscipline($targetDisciplineId, $userId)) {
                throw new ValidationException(['disciplina' => 'Disciplina de destino não encontrada.']);
            }
        } else {
            $targetDisciplineId = $file['disciplina_id'];
        }

        $this->repo->moveFile($fileId, $userId, $targetFolderId, $targetDisciplineId);
        $this->repo->addHistory(
            Uuid::v4(),
            $fileId,
            $userId,
            'movido',
            'Arquivo movido para a pasta: ' . $destFolderName
        );
    }

    /**
     * @param list<string> $tags
     */
    public function updateTags(string $userId, string $fileId, array $tags): void
    {
        $this->file($userId, $fileId);

        $userTags = array_column($this->repo->tags($userId), 'id');
        $validTags = [];
        foreach ($tags as $tagId) {
            if (is_string($tagId) && in_array($tagId, $userTags, true)) {
                $validTags[] = $tagId;
            }
        }

        $this->repo->syncTags($fileId, $validTags);
        $this->repo->addHistory(
            Uuid::v4(),
            $fileId,
            $userId,
            'modificado',
            'Tags do arquivo atualizadas (' . count($validTags) . ' tags selecionadas)'
        );
    }

    public function moveToTrash(string $userId, string $fileId): void
    {
        $this->file($userId, $fileId);
        $this->repo->moveToTrash($fileId, $userId);
    }

    public function restoreFromTrash(string $userId, string $fileId): void
    {
        $file = $this->repo->file($fileId, $userId, true);
        if ($file === null) {
            throw new NotFoundException('Arquivo não encontrado na lixeira.');
        }

        $this->repo->restoreFromTrash($fileId, $userId);
        $this->repo->addHistory(
            Uuid::v4(),
            $fileId,
            $userId,
            'restaurado',
            'Arquivo restaurado da lixeira para a área ativa'
        );
    }

    public function deletePermanently(string $userId, string $fileId): void
    {
        $file = $this->repo->file($fileId, $userId, true);
        if ($file === null) {
            throw new NotFoundException('Arquivo não encontrado.');
        }

        $diskPath = dirname(__DIR__, 2) . '/storage/' . $file['caminho_armazenamento'];
        if (file_exists($diskPath)) {
            @unlink($diskPath);
        }

        $this->repo->deletePermanently($fileId, $userId);
    }

    /**
     * @return list<array<string,mixed>>
     */
    public function fileHistory(string $userId, string $fileId): array
    {
        $file = $this->repo->file($fileId, $userId, true);
        if ($file === null) {
            throw new NotFoundException('Arquivo não encontrado.');
        }

        return $this->repo->history($fileId, $userId);
    }

    /**
     * @return array{file: array<string,mixed>, path: string}
     */
    public function download(string $userId, string $fileId): array
    {
        $file = $this->file($userId, $fileId);

        $baseUploads = realpath(dirname(__DIR__, 2) . '/storage/uploads');
        $filePath = realpath(dirname(__DIR__, 2) . '/storage/' . $file['caminho_armazenamento']);

        if ($baseUploads === false || $filePath === false) {
            throw new NotFoundException('Arquivo físico não encontrado no servidor.');
        }

        $normalizedBase = rtrim(str_replace('\\', '/', $baseUploads), '/') . '/' . $userId . '/';
        $normalizedFile = str_replace('\\', '/', $filePath);

        // Bloqueio rigoroso contra Path Traversal: o arquivo DEVE estar na pasta do usuário
        if (!str_starts_with($normalizedFile, $normalizedBase) && !str_starts_with($normalizedFile, rtrim(str_replace('\\', '/', $baseUploads), '/') . '/')) {
            throw new NotFoundException('Acesso negado ao arquivo solicitado.');
        }

        return [
            'file' => $file,
            'path' => $filePath,
        ];
    }

    public function createFolder(
        string $userId,
        string $name,
        ?string $disciplineId,
        ?string $parentId
    ): void {
        $clean = trim($name);
        if (mb_strlen($clean) < 1 || mb_strlen($clean) > 150) {
            throw new ValidationException(['nome' => 'O nome da pasta deve ter entre 1 e 150 caracteres.']);
        }

        if ($disciplineId !== null && $disciplineId !== '') {
            if (!$this->repo->ownedDiscipline($disciplineId, $userId)) {
                throw new ValidationException(['disciplina_id' => 'Disciplina não encontrada.']);
            }
        }

        if ($parentId !== null && $parentId !== '') {
            $parent = $this->repo->ownedFolder($parentId, $userId);
            if ($parent === null) {
                throw new ValidationException(['pasta_pai_id' => 'Pasta pai não encontrada.']);
            }
            if (empty($disciplineId) && !empty($parent['disciplina_id'])) {
                $disciplineId = (string) $parent['disciplina_id'];
            }
        }

        $this->repo->createFolder(
            Uuid::v4(),
            $disciplineId ?: null,
            $parentId ?: null,
            $clean,
            $userId
        );
    }

    public function renameFolder(string $userId, string $folderId, string $newName): void
    {
        $folder = $this->repo->ownedFolder($folderId, $userId);
        if ($folder === null) {
            throw new NotFoundException('Pasta não encontrada.');
        }

        $clean = trim($newName);
        if (mb_strlen($clean) < 1 || mb_strlen($clean) > 150) {
            throw new ValidationException(['nome' => 'Informe um nome entre 1 e 150 caracteres.']);
        }

        $this->repo->renameFolder($folderId, $clean);
    }

    public function moveFolder(string $userId, string $folderId, ?string $parentId): void
    {
        $folder = $this->repo->ownedFolder($folderId, $userId);
        if ($folder === null) {
            throw new NotFoundException('Pasta não encontrada.');
        }

        if ($parentId === '' || $parentId === null || $parentId === 'root') {
            $this->repo->moveFolder($folderId, null);
            return;
        }

        if ($parentId === $folderId) {
            throw new ValidationException(['pasta' => 'Não é possível mover uma pasta para dentro dela mesma.']);
        }

        $target = $this->repo->ownedFolder($parentId, $userId);
        if ($target === null) {
            throw new ValidationException(['pasta' => 'Pasta de destino não encontrada.']);
        }

        // Prevenir ciclos hierárquicos: verificar se $folderId é ancestral de $parentId
        $all = $this->repo->allFolders($userId);
        $parentsMap = [];
        foreach ($all as $item) {
            $parentsMap[(string) $item['id']] = $item['pasta_pai_id'] !== null ? (string) $item['pasta_pai_id'] : null;
        }

        $curr = $parentId;
        while ($curr !== null && $curr !== '') {
            if ($curr === $folderId) {
                throw new ValidationException(['pasta' => 'Não é possível mover uma pasta para dentro de suas subpastas.']);
            }
            $curr = $parentsMap[$curr] ?? null;
        }

        $this->repo->moveFolder($folderId, $parentId);
    }

    public function deleteFolder(string $userId, string $folderId): void
    {
        $folder = $this->repo->ownedFolder($folderId, $userId);
        if ($folder === null) {
            throw new NotFoundException('Pasta não encontrada.');
        }

        $this->repo->deleteFolder($folderId);
    }

    public function createTag(string $userId, string $name, string $color): void
    {
        $clean = trim($name);
        if (mb_strlen($clean) < 1 || mb_strlen($clean) > 80) {
            throw new ValidationException(['nome' => 'O nome da tag deve ter entre 1 e 80 caracteres.']);
        }

        $hexColor = preg_match('/^#[0-9a-fA-F]{6}$/', $color) ? $color : '#2563eb';
        $this->repo->createTag(Uuid::v4(), $userId, $clean, $hexColor);
    }
}
