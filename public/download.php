<?php
require_once __DIR__ . '/../includes/config.php';

$requested = $_GET['file'] ?? '';
if ($requested === '') {
    http_response_code(400);
    exit('Arquivo não especificado.');
}

// Sanitize: strip directory traversal
$filename = basename($requested);
$filepath = UPLOAD_DIR . $filename;

if (!file_exists($filepath) || !is_file($filepath)) {
    http_response_code(404);
    exit('Arquivo não encontrado.');
}

$mime = mime_content_type($filepath) ?: 'application/octet-stream';

header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . addslashes($filename) . '"');
header('Content-Length: ' . filesize($filepath));
header('Cache-Control: no-store');

readfile($filepath);
exit;
