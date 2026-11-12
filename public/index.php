<?php
declare(strict_types=1);

session_start();

// Prefer Composer's PSR-4 autoloader; fall back to core/autoload.php if
// `composer dump-autoload` has not been run yet.
$composer = __DIR__ . '/../vendor/autoload.php';
require is_file($composer) ? $composer : __DIR__ . '/../core/autoload.php';

use App\Controllers\StudentController;
use App\Controllers\TeacherController;

ini_set('display_errors', '0'); // never show raw errors to users

set_exception_handler(function (Throwable $e): void {
    error_log($e->getMessage());
    http_response_code(500);
    echo '<!doctype html><meta charset="utf-8"><title>Error</title>'
       . '<p>Something went wrong. Please try again later.</p>';
});

// resource => controller class
$resources = [
    'students' => StudentController::class,
    'teachers' => TeacherController::class,
];

// action => allowed HTTP method
$routes = [
    'index'   => 'GET',
    'create'  => 'GET',
    'store'   => 'POST',
    'edit'    => 'GET',
    'update'  => 'POST',
    'destroy' => 'POST',
];

$resource = $_GET['resource'] ?? 'students';
$action   = $_GET['action'] ?? 'index';

if (!is_string($resource) || !isset($resources[$resource])
    || !is_string($action) || !isset($routes[$action])) {
    http_response_code(404);
    echo 'Page not found.';
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== $routes[$action]) {
    http_response_code(405);
    echo 'Method not allowed.';
    exit;
}

$controller = new $resources[$resource]();
$controller->$action();
