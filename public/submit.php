<?php
declare(strict_types=1);

error_reporting(0);
ini_set('display_errors', '0');
ob_start();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'errors' => ['form' => 'Neplatný požadavek.']], JSON_UNESCAPED_UNICODE);
    exit;
}

$config = require __DIR__ . '/../src/config.php';
require __DIR__ . '/../src/bootstrap.php';
require __DIR__ . '/../src/SubmissionHandler.php';

$handler = new SubmissionHandler($config, __DIR__ . '/../uploads');
$result = $handler->handle($_POST, $_FILES);

if (empty($result['ok'])) {
    http_response_code(422);
}

ob_clean();
echo json_encode($result, JSON_UNESCAPED_UNICODE);
