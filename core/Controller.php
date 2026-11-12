<?php
declare(strict_types=1);

namespace Core;

/** Base controller: rendering, redirects, flash messages, 404. */
abstract class Controller
{
    /** Name used in URLs, e.g. 'students' or 'teachers'. Set by each child. */
    protected string $resource = '';

    protected function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        ob_start();
        require __DIR__ . '/../views/' . $view . '.php';
        $content = ob_get_clean();
        require __DIR__ . '/../views/layout.php';
    }

    protected function redirect(string $action = 'index', array $params = []): never
    {
        $query = http_build_query(['resource' => $this->resource, 'action' => $action] + $params);
        header('Location: index.php?' . $query);
        exit;
    }

    protected function flash(string $message): void
    {
        $_SESSION['flash'] = $message;
    }

    protected function notFound(string $message = 'Record not found.'): never
    {
        http_response_code(404);
        $this->render('404', ['message' => $message]);
        exit;
    }

    protected function requireCsrf(): void
    {
        if (!Csrf::verify()) {
            http_response_code(419);
            $this->render('404', ['message' => 'Invalid or expired form. Please go back and try again.']);
            exit;
        }
    }
}
