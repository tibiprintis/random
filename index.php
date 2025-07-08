<?php
$autoload = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoload)) {
    header('HTTP/1.1 500 Internal Server Error');
    echo 'Dependencies missing—run composer install.';
    exit;
}
require $autoload;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;

session_start();

$dataDir = __DIR__ . '/data';
if (!is_dir($dataDir) && !mkdir($dataDir, 0777, true)) {
    header('HTTP/1.1 500 Internal Server Error');
    echo 'Failed to create data directory.';
    exit;
}

$dbPath = $dataDir . '/app.db';
$pdo = new PDO('sqlite:' . $dbPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec('CREATE TABLE IF NOT EXISTS settings (id INTEGER PRIMARY KEY, key TEXT UNIQUE, value TEXT)');

function getSetting(PDO $pdo, string $key): ?string {
    $stmt = $pdo->prepare('SELECT value FROM settings WHERE key = ?');
    $stmt->execute([$key]);
    return $stmt->fetchColumn() ?: null;
}

function setSetting(PDO $pdo, string $key, string $value): void {
    $stmt = $pdo->prepare('INSERT INTO settings(key, value) VALUES (?, ?) ON CONFLICT(key) DO UPDATE SET value = excluded.value');
    $stmt->execute([$key, $value]);
}

// Initialize default password
if (!getSetting($pdo, 'password')) {
    setSetting($pdo, 'password', password_hash('Euro2369!', PASSWORD_DEFAULT));
}

$app = AppFactory::create();
$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// poor-man cron executed on each request
$app->add(function(Request $request, RequestHandlerInterface $handler) use ($pdo){
    $last = getSetting($pdo, 'cron_last') ?: 0;
    if (time() - (int)$last > 60) { // run every minute
        // place cron tasks here
        setSetting($pdo, 'cron_last', (string)time());
    }
    return $handler->handle($request);
});

$authMiddleware = function(Request $request, RequestHandlerInterface $handler) {
    if (empty($_SESSION['logged_in'])) {
        $response = new Slim\Psr7\Response();
        return $response->withHeader('Location', '/login')->withStatus(302);
    }
    return $handler->handle($request);
};

$app->get('/login', function(Request $request, Response $response) {
    $error = $request->getQueryParams()['error'] ?? '';
    $response->getBody()->write(renderLogin($error));
    return $response;
});

$app->post('/login', function(Request $request, Response $response) use ($pdo) {
    $data = (array)$request->getParsedBody();
    $password = $data['password'] ?? '';
    $hash = getSetting($pdo, 'password');
    if (password_verify($password, $hash)) {
        $_SESSION['logged_in'] = true;
        return $response->withHeader('Location', '/')->withStatus(302);
    }
    return $response->withHeader('Location', '/login?error=1')->withStatus(302);
});

$app->get('/logout', function(Request $request, Response $response) {
    session_destroy();
    return $response->withHeader('Location', '/login')->withStatus(302);
});

$app->get('/', function(Request $request, Response $response) {
    $response->getBody()->write(renderHome());
    return $response;
})->add($authMiddleware);

$app->get('/admin', function(Request $request, Response $response) {
    $response->getBody()->write(renderAdmin());
    return $response;
})->add($authMiddleware);

$app->post('/admin/password', function(Request $request, Response $response) use ($pdo) {
    $data = (array)$request->getParsedBody();
    if (!empty($data['new_password'])) {
        setSetting($pdo, 'password', password_hash($data['new_password'], PASSWORD_DEFAULT));
    }
    return $response->withHeader('Location', '/admin')->withStatus(302);
})->add($authMiddleware);

$app->run();

function baseTemplate(string $title, string $content): string {
    return <<<HTML
<!doctype html>
<html lang="en" class="h-full" x-data="{ dark: true }" x-bind:class="{ 'dark': dark }" x-init="dark = true">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<title>{$title}</title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@^3/dist/tailwind.min.css" rel="stylesheet"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<script src="https://unpkg.com/htmx.org@1.9.2"></script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/motion-one/10.15.1/motion.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-900 text-gray-100 h-full">
<div class="container mx-auto p-4">
{$content}
</div>
</body>
</html>
HTML;
}

function renderLogin(string $error): string {
    $msg = $error ? '<p class="text-red-500">Parola incorecta</p>' : '';
    $content = <<<HTML
<div class="max-w-sm mx-auto mt-20 animate__animated animate__fadeIn">
<h1 class="text-2xl mb-4">Autentificare</h1>
{$msg}
<form method="post" action="/login" class="space-y-4">
<input type="password" name="password" placeholder="Parola" class="w-full p-2 rounded bg-gray-800" required>
<button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 p-2 rounded">Login</button>
</form>
</div>
HTML;
    return baseTemplate('Login', $content);
}

function renderHome(): string {
    $content = <<<HTML
<h1 class="text-3xl mb-6">Dashboard</h1>
<div class="space-y-4">
    <div class="p-4 bg-gray-800 rounded shadow flex items-center justify-between">
        <div>
            <h2 class="text-xl">Administrare</h2>
            <p class="text-sm text-gray-400">Schimbare parola si alte setari</p>
        </div>
        <a href="/admin" class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded">Deschide</a>
    </div>
</div>
HTML;
    return baseTemplate('Acasa', $content);
}

function renderAdmin(): string {
    $content = <<<HTML
<h1 class="text-3xl mb-6">Panou Administrare</h1>
<form method="post" action="/admin/password" class="space-y-4 max-w-sm">
<label class="block">Parola noua
<input type="password" name="new_password" class="w-full p-2 rounded bg-gray-800" required></label>
<button type="submit" class="bg-green-600 hover:bg-green-700 px-4 py-2 rounded">Schimba Parola</button>
</form>
<a href="/" class="inline-block mt-4 text-blue-400">&larr; Inapoi</a>
HTML;
    return baseTemplate('Admin', $content);
}
?>
