<?php
require_once __DIR__ . '/../includes/config.php';
$files = listFiles();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Downloads</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f0f2f5; }
        .hero { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); }
        .file-card { transition: transform .15s, box-shadow .15s; border: none; }
        .file-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,.12) !important; }
        .icon-wrap { font-size: 2.2rem; line-height: 1; }
        .btn-download { background: #0d6efd; border: none; }
        .btn-download:hover { background: #0b5ed7; }
    </style>
</head>
<body>

<div class="hero py-4 mb-4">
    <div class="container d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <span style="font-size:1.8rem">📁</span>
            <h1 class="h4 mb-0 text-white fw-bold">Arquivos para Download</h1>
        </div>
        <small class="text-white-50"><?= count($files) ?> arquivo<?= count($files) !== 1 ? 's' : '' ?></small>
    </div>
</div>

<div class="container pb-5">
    <?php if (empty($files)): ?>
        <div class="text-center py-5">
            <div style="font-size:4rem">📭</div>
            <h5 class="text-muted mt-3">Nenhum arquivo disponível</h5>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($files as $file):
                $base   = appUrl();
                $dlUrl  = $base . '/download?file=' . rawurlencode($file['name']);
                $qrUrl  = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=' . rawurlencode($dlUrl);
                $modalId = 'qr' . md5($file['name']);
            ?>
            <div class="col-12 col-sm-6 col-lg-4">
                <div class="card file-card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-start gap-3">
                            <div class="icon-wrap"><?= fileIcon($file['name']) ?></div>
                            <div class="overflow-hidden flex-grow-1">
                                <p class="mb-1 fw-semibold text-truncate" title="<?= htmlspecialchars($file['name']) ?>">
                                    <?= htmlspecialchars($file['name']) ?>
                                </p>
                                <small class="text-muted">
                                    <?= formatSize($file['size']) ?>
                                    &middot;
                                    <?= date('d/m/Y H:i', $file['mtime']) ?>
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top d-flex gap-2">
                        <a href="<?= htmlspecialchars($dlUrl) ?>"
                           class="btn btn-download btn-sm text-white flex-grow-1">
                            ⬇&nbsp; Baixar
                        </a>
                        <button class="btn btn-outline-secondary btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#<?= $modalId ?>">
                            📷 QR
                        </button>
                    </div>
                </div>
            </div>

            <!-- QR Modal -->
            <div class="modal fade" id="<?= $modalId ?>" tabindex="-1">
                <div class="modal-dialog modal-sm modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header py-2">
                            <h6 class="modal-title">Escanear para baixar</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center py-3">
                            <img src="<?= htmlspecialchars($qrUrl) ?>"
                                 alt="QR Code"
                                 class="img-fluid rounded mb-3"
                                 style="width:200px;height:200px">
                            <p class="small text-muted mb-0">
                                <?= htmlspecialchars($file['name']) ?><br>
                                <span class="text-body-tertiary"><?= formatSize($file['size']) ?></span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
