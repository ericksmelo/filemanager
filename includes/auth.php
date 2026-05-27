<?php
require_once __DIR__ . '/config.php';

function startSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function isLoggedIn(): bool
{
    startSession();
    return ($_SESSION['admin'] ?? false) === true;
}

function requireAuth(): void
{
    if (!isLoggedIn()) {
        header('Location: /login');
        exit;
    }
}

function tryLogin(string $user, string $pass): bool
{
    if ($user === ADMIN_USER && $pass === ADMIN_PASS) {
        startSession();
        session_regenerate_id(true);
        $_SESSION['admin'] = true;
        return true;
    }
    return false;
}

function doLogout(): void
{
    startSession();
    $_SESSION = [];
    session_destroy();
}
