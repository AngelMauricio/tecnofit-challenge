<?php
declare(strict_types=1);
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/vendor/autoload.php';
require_once BASE_PATH . '/src/config.php';

// Autoload
spl_autoload_register(function ($class) {
    $file = BASE_PATH . '/src/' . str_replace('\\', '/', str_replace('App\\', '', $class)) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// DB
try {
    $dsn = sprintf(
        "mysql:host=%s;port=%s;dbname=%s;charset=%s",
        $config['db']['host'],
        $config['db']['port'],
        $config['db']['database'],
        $config['db']['charset']
    );

    $pdo = new PDO($dsn, $config['db']['user'], $config['db']['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Router
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$uri = explode('/', trim($uri, '/'));
// 404
if (!isset($uri[0]) || $uri[0] !== 'api' || !isset($uri[1]) || !$uri[1]) {
    http_response_code(404);
    echo json_encode(['error' => 'Endpoint not found. All requests must be like /api/movement/1']);
    exit;
}

$resource = ucfirst($uri[1] ?? null);
$parameter = $uri[2] ?? null;
$method = $_SERVER['REQUEST_METHOD'];

$controllerClass = "App\\Controllers\\{$resource}Controller";
$repositoryClass = "App\\Repositories\\{$resource}Repository";

if (class_exists($controllerClass) && class_exists($repositoryClass)) {
    $repository = new $repositoryClass($pdo);
    $controller = new $controllerClass($repository);

    $action = strtolower($method);

    if (method_exists($controller, $action)) {
        header('Content-Type: application/json');
        try {
            echo json_encode($controller->$action($parameter));
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Internal Server Error', 'details' => $e->getMessage()]);
        }
        exit;
    }
}

http_response_code(404);
echo json_encode(['error' => 'Endpoint or Method not found']);