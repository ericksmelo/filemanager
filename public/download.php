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

$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
$extMime = [
    'apk'  => 'application/vnd.android.package-archive',
    'ipa'  => 'application/octet-stream',
    'exe'  => 'application/octet-stream',
    'dmg'  => 'application/octet-stream',
    'zip'  => 'application/zip',
    'pdf'  => 'application/pdf',
    'mp4'  => 'video/mp4',
    'mp3'  => 'audio/mpeg',
];
$mime = $extMime[$ext] ?? mime_content_type($filepath) ?: 'application/octet-stream';

header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . addslashes($filename) . '"');
header('Content-Length: ' . filesize($filepath));
header('Cache-Control: no-store');

readfile($filepath);
exit;
