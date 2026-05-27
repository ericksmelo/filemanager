<?php

function loadEnv(string $path): void
{
    if (!file_exists($path)) return;
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (!str_contains($line, '=')) continue;
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        $value = trim($value, '"\'');
        if (!isset($_ENV[$key]) && !getenv($key)) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

loadEnv(__DIR__ . '/../.env');

define('ADMIN_USER',    getenv('ADMIN_USER')    ?: 'admin');
define('ADMIN_PASS',    getenv('ADMIN_PASS')    ?: 'admin123');
define('APP_URL',       rtrim(getenv('APP_URL') ?: '', '/'));
define('UPLOAD_DIR',    __DIR__ . '/../uploads/');
define('MAX_FILE_MB',   (int)(getenv('MAX_FILE_MB') ?: 50));

function appUrl(): string
{
    if (APP_URL !== '') return APP_URL;
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return "$scheme://$host";
}

function formatSize(int $bytes): string
{
    if ($bytes < 1024) return "$bytes B";
    if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
    if ($bytes < 1073741824) return round($bytes / 1048576, 1) . ' MB';
    return round($bytes / 1073741824, 1) . ' GB';
}

function fileIcon(string $name): string
{
    return match (strtolower(pathinfo($name, PATHINFO_EXTENSION))) {
        'pdf'                          => '📄',
        'doc', 'docx'                  => '📝',
        'xls', 'xlsx'                  => '📊',
        'ppt', 'pptx'                  => '📋',
        'zip', 'rar', '7z', 'tar', 'gz' => '🗜️',
        'jpg', 'jpeg', 'png', 'gif',
        'webp', 'svg'                  => '🖼️',
        'mp4', 'avi', 'mov', 'mkv'     => '🎬',
        'mp3', 'wav', 'ogg', 'flac'    => '🎵',
        'txt'                          => '📃',
        default                        => '📁',
    };
}

function listFiles(): array
{
    $files = [];
    if (!is_dir(UPLOAD_DIR)) return $files;
    foreach (new DirectoryIterator(UPLOAD_DIR) as $f) {
        if ($f->isDot() || $f->isDir() || $f->getFilename() === '.gitkeep') continue;
        $files[] = [
            'name'  => $f->getFilename(),
            'size'  => $f->getSize(),
            'mtime' => $f->getMTime(),
        ];
    }
    usort($files, fn($a, $b) => $b['mtime'] - $a['mtime']);
    return $files;
}
