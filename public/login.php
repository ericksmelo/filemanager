<?php
require_once __DIR__ . '/../includes/auth.php';

if (isLoggedIn()) {
    header('Location: /admin');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['user'] ?? '');
    $pass = $_POST['pass'] ?? '';
    if (tryLogin($user, $pass)) {
        header('Location: /admin');
        exit;
    }
    $error = 'Usuário ou senha inválidos.';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card { max-width: 380px; width: 100%; border: none; border-radius: 16px; }
    </style>
</head>
<body>
    <div class="login-card card shadow-lg p-4">
        <div class="text-center mb-4">
            <div style="font-size:3rem">🔐</div>
            <h4 class="fw-bold mt-2">Painel Admin</h4>
            <p class="text-muted small mb-0">Acesso restrito</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2 small"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" autocomplete="off">
            <div class="mb-3">
                <label class="form-label fw-semibold">Usuário</label>
                <input type="text" name="user" class="form-control"
                       value="<?= htmlspecialchars($_POST['user'] ?? '') ?>"
                       autofocus required>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">Senha</label>
                <input type="password" name="pass" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 fw-semibold">
                Entrar
            </button>
        </form>

        <div class="text-center mt-3">
            <a href="/" class="text-muted small">← Voltar para downloads</a>
        </div>
    </div>
</body>
</html>
