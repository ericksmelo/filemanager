<?php
require_once __DIR__ . '/../includes/auth.php';
requireAuth();

$message = '';
$msgType = 'success';

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $target = UPLOAD_DIR . basename($_POST['file'] ?? '');
    if (file_exists($target) && is_file($target)) {
        unlink($target);
        $message = 'Arquivo excluído com sucesso.';
    } else {
        $message = 'Arquivo não encontrado.';
        $msgType = 'danger';
    }
}

// Handle upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'upload') {
    $maxBytes = MAX_FILE_MB * 1024 * 1024;
    $uploads  = $_FILES['files'] ?? null;

    if (!$uploads || empty($uploads['name'][0])) {
        $message = 'Nenhum arquivo selecionado.';
        $msgType = 'warning';
    } else {
        $uploaded = 0;
        $errors   = [];
        $count    = count($uploads['name']);

        for ($i = 0; $i < $count; $i++) {
            if ($uploads['error'][$i] !== UPLOAD_ERR_OK) {
                $errors[] = htmlspecialchars($uploads['name'][$i]) . ': erro no upload.';
                continue;
            }
            if ($uploads['size'][$i] > $maxBytes) {
                $errors[] = htmlspecialchars($uploads['name'][$i]) . ": excede " . MAX_FILE_MB . " MB.";
                continue;
            }
            $original  = $uploads['name'][$i];
            $safe      = basename($original);
            $dest      = UPLOAD_DIR . $safe;
            // Avoid overwriting: append suffix if needed
            if (file_exists($dest)) {
                $info = pathinfo($safe);
                $safe = $info['filename'] . '_' . time() . '.' . ($info['extension'] ?? '');
                $dest = UPLOAD_DIR . $safe;
            }
            if (move_uploaded_file($uploads['tmp_name'][$i], $dest)) {
                $uploaded++;
            } else {
                $errors[] = htmlspecialchars($original) . ': falha ao mover arquivo.';
            }
        }

        if ($errors) {
            $message = implode('<br>', $errors);
            $msgType = $uploaded > 0 ? 'warning' : 'danger';
        } else {
            $message = "$uploaded arquivo(s) enviado(s) com sucesso.";
        }
    }
}

$files = listFiles();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — Gerenciar Arquivos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; }
        .top-bar { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); }
        .drop-zone {
            border: 2px dashed #6c757d;
            border-radius: 12px;
            transition: border-color .2s, background .2s;
            cursor: pointer;
        }
        .drop-zone.dragover, .drop-zone:hover {
            border-color: #0d6efd;
            background: #f0f5ff;
        }
        .file-row:hover { background: #f8f9fa; }
    </style>
</head>
<body>

<!-- Top bar -->
<div class="top-bar py-3 mb-4">
    <div class="container d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <span style="font-size:1.6rem">⚙️</span>
            <h1 class="h5 mb-0 text-white fw-bold">Painel Admin</h1>
        </div>
        <div class="d-flex gap-2">
            <a href="/" class="btn btn-outline-light btn-sm">🌐 Ver Site</a>
            <a href="/logout" class="btn btn-danger btn-sm">Sair</a>
        </div>
    </div>
</div>

<div class="container pb-5">

    <?php if ($message): ?>
        <div class="alert alert-<?= $msgType ?> alert-dismissible fade show" role="alert">
            <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Upload card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold border-bottom">
            ⬆️ Enviar Arquivos
        </div>
        <div class="card-body">
            <form method="post" enctype="multipart/form-data" id="upload-form">
                <input type="hidden" name="action" value="upload">
                <div class="drop-zone p-5 text-center mb-3" id="drop-zone"
                     onclick="document.getElementById('file-input').click()">
                    <div style="font-size:2.5rem">📂</div>
                    <p class="mb-1 fw-semibold">Clique ou arraste arquivos aqui</p>
                    <p class="text-muted small mb-0">Máximo <?= MAX_FILE_MB ?> MB por arquivo</p>
                    <div id="selected-files" class="mt-2 small text-primary"></div>
                </div>
                <input type="file" name="files[]" id="file-input" multiple class="d-none">
                <button type="submit" class="btn btn-primary px-4">
                    ⬆️ Enviar
                </button>
            </form>
        </div>
    </div>

    <!-- File list -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white fw-semibold border-bottom d-flex justify-content-between align-items-center">
            <span>📋 Arquivos (<?= count($files) ?>)</span>
        </div>
        <?php if (empty($files)): ?>
            <div class="card-body text-center text-muted py-5">
                Nenhum arquivo enviado ainda.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width:40px"></th>
                            <th>Nome</th>
                            <th>Tamanho</th>
                            <th>Data</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($files as $file): ?>
                        <tr class="file-row">
                            <td class="text-center"><?= fileIcon($file['name']) ?></td>
                            <td>
                                <span class="fw-semibold" title="<?= htmlspecialchars($file['name']) ?>">
                                    <?= htmlspecialchars($file['name']) ?>
                                </span>
                            </td>
                            <td class="text-muted small"><?= formatSize($file['size']) ?></td>
                            <td class="text-muted small"><?= date('d/m/Y H:i', $file['mtime']) ?></td>
                            <td class="text-end">
                                <a href="/download?file=<?= rawurlencode($file['name']) ?>"
                                   class="btn btn-outline-primary btn-sm me-1">⬇ Baixar</a>
                                <form method="post" class="d-inline"
                                      onsubmit="return confirm('Excluir <?= addslashes(htmlspecialchars($file['name'])) ?>?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="file" value="<?= htmlspecialchars($file['name']) ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm">🗑 Excluir</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const dropZone  = document.getElementById('drop-zone');
const fileInput = document.getElementById('file-input');
const label     = document.getElementById('selected-files');

function updateLabel(files) {
    label.textContent = files.length
        ? `${files.length} arquivo(s) selecionado(s)`
        : '';
}

fileInput.addEventListener('change', () => updateLabel(fileInput.files));

dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('dragover'); });
dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
dropZone.addEventListener('drop', e => {
    e.preventDefault();
    dropZone.classList.remove('dragover');
    const dt = new DataTransfer();
    for (const f of e.dataTransfer.files) dt.items.add(f);
    fileInput.files = dt.files;
    updateLabel(fileInput.files);
});
</script>
</body>
</html>
