<?php
declare(strict_types=1);

namespace App\Services;

use App\Helpers\Database;
use PDO;

final class SearchService extends Service
{
    private PDO $db;

    public function __construct(private readonly array $config)
    {
        $this->db = Database::connect($config['database']);
    }

    /** @return array{files: list<array>, disciplines: list<array>, notes: list<array>, tasks: list<array>} */
    public function search(string $userId, string $query): array
    {
        $term = '%' . trim($query) . '%';
        if (trim($query) === '') {
            return ['files' => [], 'disciplines' => [], 'notes' => [], 'tasks' => []];
        }

        $files = $this->db->prepare(
            "SELECT a.id, a.nome_original, a.extensao, a.criado_em, d.nome AS disciplina_nome
             FROM arquivos a
             LEFT JOIN disciplinas d ON d.id = a.disciplina_id
             WHERE a.usuario_id = :u AND a.excluido = 0 AND (a.nome_original LIKE :t OR a.extensao LIKE :t2)
             ORDER BY a.criado_em DESC LIMIT 20"
        );
        $files->execute(['u' => $userId, 't' => $term, 't2' => $term]);
        $files = $files->fetchAll(PDO::FETCH_ASSOC);

        $disciplines = $this->db->prepare(
            "SELECT id, nome, cor FROM disciplinas
             WHERE usuario_id = :u AND nome LIKE :t
             ORDER BY nome ASC LIMIT 20"
        );
        $disciplines->execute(['u' => $userId, 't' => $term]);
        $disciplines = $disciplines->fetchAll(PDO::FETCH_ASSOC);

        $notes = $this->db->prepare(
            "SELECT id, titulo, conteudo, atualizado_em FROM notas
             WHERE usuario_id = :u AND (titulo LIKE :t OR conteudo LIKE :t2
                   OR EXISTS (SELECT 1 FROM blocos_nota b WHERE b.nota_id = notas.id AND b.conteudo LIKE :t3))
             ORDER BY atualizado_em DESC LIMIT 20"
        );
        $notes->execute(['u' => $userId, 't' => $term, 't2' => $term, 't3' => $term]);
        $notes = $notes->fetchAll(PDO::FETCH_ASSOC);

        $tasks = $this->db->prepare(
            "SELECT id, titulo, status, prioridade, data_vencimento FROM tarefas
             WHERE usuario_id = :u AND (titulo LIKE :t OR descricao LIKE :t2)
             ORDER BY criado_em DESC LIMIT 20"
        );
        $tasks->execute(['u' => $userId, 't' => $term, 't2' => $term]);
        $tasks = $tasks->fetchAll(PDO::FETCH_ASSOC);

        return ['files' => $files, 'disciplines' => $disciplines, 'notes' => $notes, 'tasks' => $tasks];
    }
}