<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\AuthenticationException;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Helpers\Csrf;
use App\Helpers\Flash;
use App\Middleware\AuthenticationMiddleware;
use App\Services\ScannerService;

final class ScannerController extends Controller
{
    public function __construct(private readonly array $config) {}
    public function index(): void { $this->auth(); $service = $this->service(); $this->view('scanner/index', ['title' => 'Scanner | PracticeDay', 'csrfToken' => Csrf::token(), 'userName' => $_SESSION['user_name'] ?? '', 'userAvatar' => $_SESSION['user_avatar'] ?? null, 'activePage' => 'scanner', 'scans' => $service->list($this->user()), 'disciplines' => $service->disciplines($this->user()), 'ocrAvailable' => $service->ocrAvailable(), 'feedback' => Flash::get('scanner')]); }
    public function create(): never { $this->auth(); try { Csrf::validate($_POST); $this->service()->create($this->user(), $_POST, $_FILES['paginas'] ?? []); Flash::set('scanner', ['success' => 'Digitalização salva. As páginas foram preparadas para OCR.']); } catch (ValidationException $e) { Flash::set('scanner', ['errors' => $e->errors()]); } catch (\Throwable $e) { Flash::set('scanner', ['errors' => ['geral' => $e->getMessage()]]); } $this->redirect('/scanner'); }
    public function delete(string $id): void
    {
        $this->auth();
        try {
            Csrf::validate($_POST);
            $this->service()->delete($this->user(), $id);
            Flash::set('scanner', ['success' => 'Digitalização excluída.']);
        } catch (NotFoundException $e) {
            Flash::set('scanner', ['errors' => ['geral' => $e->getMessage()]]);
        } catch (ValidationException $e) {
            Flash::set('scanner', ['errors' => $e->errors()]);
        } catch (\Throwable $e) {
            Flash::set('scanner', ['errors' => ['geral' => $e->getMessage()]]);
        }
        $this->redirect('/scanner');
    }
    public function image(string $id): never { $this->auth(); try { $page = $this->service()->page($this->user(), $id); $path = dirname(__DIR__, 2) . '/storage/' . $page['caminho_imagem']; if (!is_file($path)) throw new NotFoundException('Imagem indisponível.'); header('Content-Type: ' . ((new \finfo(FILEINFO_MIME_TYPE))->file($path) ?: 'image/jpeg')); header('Content-Length: ' . filesize($path)); readfile($path); } catch (NotFoundException) { http_response_code(404); } exit; }
    private function service(): ScannerService { return new ScannerService($this->config); }
    private function user(): string { return (string) ($_SESSION['user_id'] ?? ''); }
    private function auth(): void { try { (new AuthenticationMiddleware($this->config))->handle(); } catch (AuthenticationException) { $this->redirect('/login'); } }
}
