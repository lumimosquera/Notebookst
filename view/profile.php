<?php
// ═══════════════════════════════════════════════════════════════
// profile.php — self-contained, no double session_start
// ═══════════════════════════════════════════════════════════════

// 1. Session must start before ANY output and before encabezado.php
//    (encabezado also calls session_start — using session_status() avoids the duplicate warning)
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

// 2. DB connection — must exist before the POST handler below
require_once '../model/conexion.php';

$user_id = intval($_SESSION['user_id']);

// ── UPLOAD CONSTANTS ────────────────────────────────────────────
// __DIR__ is always the real filesystem path of THIS file (view/)
// regardless of how PHP/Apache sets the working directory.
define('UPLOAD_DIR_ABS', __DIR__ . '/../asset/img/user/');
define('UPLOAD_DIR_WEB', '../asset/img/user/');   // relative to view/ — matches DB format
define('UPLOAD_MAX_MB',  5);

// ── POST HANDLER (must run before any HTML output) ──────────────
$flash = null;   // ['msg' => '...', 'type' => 'success|warning|danger']

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre'])) {

    $nombre  = trim($_POST['nombre']  ?? '');
    $usuario = trim($_POST['usuario'] ?? '');

    if (empty($nombre) || empty($usuario)) {
        $flash = ['msg' => 'Nombre y usuario son obligatorios.', 'type' => 'warning'];

    } else {
        $img_url = null;   // will be set only if a new image was successfully uploaded

        // ── File upload block ────────────────────────────────────
        $file_sent = isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE;

        if ($file_sent) {
            $file  = $_FILES['imagen'];
            $error = (int) $file['error'];

            if ($error !== UPLOAD_ERR_OK) {
                // Translate PHP upload error code → human message
                $err_messages = [
                    UPLOAD_ERR_INI_SIZE   => 'El archivo supera el límite del servidor (php.ini: upload_max_filesize).',
                    UPLOAD_ERR_FORM_SIZE  => 'El archivo supera el límite del formulario (MAX_FILE_SIZE).',
                    UPLOAD_ERR_PARTIAL    => 'El archivo se subió de forma incompleta. Intenta de nuevo.',
                    UPLOAD_ERR_NO_TMP_DIR => 'Error interno: no hay carpeta temporal. Contacta al administrador.',
                    UPLOAD_ERR_CANT_WRITE => 'Error interno: no se puede escribir en disco. Contacta al administrador.',
                    UPLOAD_ERR_EXTENSION  => 'Una extensión de PHP bloqueó la subida.',
                ];
                $flash = ['msg' => $err_messages[$error] ?? "Error de subida PHP (código {$error}).", 'type' => 'danger'];

            } else {
                // Validate MIME type from the actual file bytes (not the filename/extension)
                $finfo        = new finfo(FILEINFO_MIME_TYPE);
                $mime         = $finfo->file($file['tmp_name']);
                $mime_to_ext  = [
                    'image/jpeg' => 'jpg',
                    'image/png'  => 'png',
                    'image/webp' => 'webp',
                    'image/gif'  => 'gif',
                ];

                if (!array_key_exists($mime, $mime_to_ext)) {
                    $flash = ['msg' => 'Formato no permitido. Sube una imagen JPG, PNG, WEBP o GIF.', 'type' => 'warning'];

                } elseif ($file['size'] > UPLOAD_MAX_MB * 1024 * 1024) {
                    $flash = ['msg' => 'La imagen no puede superar ' . UPLOAD_MAX_MB . ' MB.', 'type' => 'warning'];

                } else {
                    // Create the directory if it doesn't exist
                    // On Windows/XAMPP, is_writable() can return false even when Apache CAN write,
                    // so we skip that check entirely and let move_uploaded_file() be the real test.
                    if (!is_dir(UPLOAD_DIR_ABS)) {
                        mkdir(UPLOAD_DIR_ABS, 0777, true);
                    }

                    {
                        $ext      = $mime_to_ext[$mime];
                        $filename = 'avatar_' . $user_id . '_' . time() . '.' . $ext;
                        $dest_abs = UPLOAD_DIR_ABS . $filename;

                        if (move_uploaded_file($file['tmp_name'], $dest_abs)) {

                            // Delete the previous avatar (only files we generated, not legacy ones)
                            $prevStmt = $pdo->prepare("SELECT imagen FROM usuarios WHERE id_usuario = ?");
                            $prevStmt->execute([$user_id]);
                            $prevRow  = $prevStmt->fetch(PDO::FETCH_ASSOC);

                            if ($prevRow && !empty($prevRow['imagen'])) {
                                $prev_abs = realpath(UPLOAD_DIR_ABS . '/' . basename($prevRow['imagen']));
                                if ($prev_abs && file_exists($prev_abs)
                                    && strpos(basename($prev_abs), 'avatar_') === 0) {
                                    @unlink($prev_abs);
                                }
                            }

                            // Store path in the same format as the original app:
                            // ../asset/img/user/filename.ext  (relative to view/)
                            $img_url = UPLOAD_DIR_WEB . $filename;

                        } else {
                            // move_uploaded_file() failed — collect diagnostic info
                            $diag = '';
                            if (!is_dir(UPLOAD_DIR_ABS))      $diag = 'El directorio no existe: ' . UPLOAD_DIR_ABS;
                            elseif (!is_writable(UPLOAD_DIR_ABS)) $diag = 'Sin permiso de escritura en: ' . UPLOAD_DIR_ABS;
                            else                               $diag = 'Error desconocido. Revisa error_log de PHP/Apache.';

                            $flash = ['msg' => 'No se pudo guardar la imagen. ' . $diag, 'type' => 'danger'];
                        }
                    }
                }
            }
        }
        // ── End file upload block ────────────────────────────────

        // Only save to DB if no upload error occurred
        if ($flash === null) {
            if ($img_url !== null) {
                // New image uploaded — save name, username AND image
                $pdo->prepare("UPDATE usuarios SET nombre = ?, usuario = ?, imagen = ? WHERE id_usuario = ?")
                    ->execute([$nombre, $usuario, $img_url, $user_id]);
                // Immediately reflect in session so navbar/topbar updates without re-login
                $_SESSION['imagen_usuario'] = $img_url;
            } else {
                // No new image — save only name and username
                $pdo->prepare("UPDATE usuarios SET nombre = ?, usuario = ? WHERE id_usuario = ?")
                    ->execute([$nombre, $usuario, $user_id]);
            }

            $_SESSION['nombre_usuario'] = $nombre;

            // PRG — redirect to clear POST and show success toast
            header("Location: profile.php?msg=" . urlencode("Perfil actualizado correctamente.") . "&type=success");
            exit;
        }
    }
}
// ── END POST HANDLER ─────────────────────────────────────────────

// ── LOAD FRESH USER DATA FOR THE VIEW ───────────────────────────
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
$stmt->execute([$user_id]);
$u = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$u) {
    // Corrupted session — force logout
    session_destroy();
    header('Location: ../index.php');
    exit;
}

// Stats counters
$stmtStats = $pdo->prepare("
    SELECT
        COUNT(DISTINCT m.id_materia)  AS materias,
        COUNT(t.id_tarea)             AS total,
        SUM(t.estado = 'completada')  AS completadas,
        SUM(t.estado = 'pendiente')   AS pendientes
    FROM materias m
    LEFT JOIN tareas t ON m.id_materia = t.id_materia
    WHERE m.id_usuario = ?
");
$stmtStats->execute([$user_id]);
$stats = $stmtStats->fetch(PDO::FETCH_ASSOC);

$pct        = ($stats['total'] > 0) ? round($stats['completadas'] / $stats['total'] * 100) : 0;
$since_fmt  = !empty($u['fecha_registro']) ? date('M Y', strtotime($u['fecha_registro'])) : '—';
$avatar_src = !empty($u['imagen']) ? htmlspecialchars($u['imagen']) : '../asset/img/N.png';

// Pass flash from GET (after PRG redirect) — but only if we don't already have one from POST
if ($flash === null && isset($_GET['msg'])) {
    $flash = [
        'msg'  => htmlspecialchars($_GET['msg']),
        'type' => in_array($_GET['type'] ?? '', ['success','warning','danger']) ? $_GET['type'] : 'success',
    ];
}

// encabezado.php calls session_start() internally — handled by the session_status() guard above
include_once 'encabezado.php';
?>

<div class="page-content">

    <!-- Breadcrumb -->
    <div class="breadcrumb-nbs">
        <a href="home.php">Dashboard</a>
        <span>›</span>
        <span>Perfil</span>
    </div>

    <!-- Page header -->
    <div class="page-header" style="margin-bottom:28px;">
        <div class="page-header-eyebrow">Cuenta</div>
        <h1 class="page-header-title">Mi perfil</h1>
    </div>

    <?php if ($flash): ?>
    <div style="padding:12px 16px;border-radius:var(--r-md);margin-bottom:20px;
                display:flex;align-items:center;gap:10px;font-size:13px;
                background:<?= $flash['type']==='success' ? 'rgba(74,240,200,.1)' : ($flash['type']==='warning' ? 'rgba(245,166,35,.1)' : 'rgba(252,92,125,.1)') ?>;
                border:1px solid <?= $flash['type']==='success' ? 'rgba(74,240,200,.3)' : ($flash['type']==='warning' ? 'rgba(245,166,35,.3)' : 'rgba(252,92,125,.3)') ?>;
                color:<?= $flash['type']==='success' ? 'var(--status-done)' : ($flash['type']==='warning' ? 'var(--status-pending)' : 'var(--status-cancelled)') ?>;"
         id="flashBanner">
        <i class="bi bi-<?= $flash['type']==='success' ? 'check-circle-fill' : ($flash['type']==='warning' ? 'exclamation-triangle-fill' : 'x-circle-fill') ?>"></i>
        <?= $flash['msg'] ?>
    </div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:300px 1fr;gap:20px;align-items:start;" id="profileGrid">

        <!-- ── LEFT: Avatar card ── -->
        <div>
            <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:var(--r-xl);overflow:hidden;">

                <!-- Cover -->
                <div style="height:80px;background:linear-gradient(135deg,#1a1e28 0%,#0d1520 50%,#0a1a14 100%);position:relative;">
                    <div style="position:absolute;inset:0;background:radial-gradient(circle at 60% 40%,rgba(74,240,200,.15) 0%,transparent 70%);"></div>
                </div>

                <!-- Avatar + camera button -->
                <div style="padding:0 20px 20px;text-align:center;margin-top:-40px;">
                    <div style="position:relative;display:inline-block;">
                        <img id="avatarPreview"
                             src="<?= $avatar_src ?>"
                             style="width:80px;height:80px;border-radius:50%;object-fit:cover;
                                    border:3px solid var(--bg-surface);background:var(--bg-overlay);"
                             alt="Avatar">
                        <label for="imgInput" title="Cambiar foto de perfil"
                               style="position:absolute;bottom:2px;right:2px;
                                      width:24px;height:24px;background:var(--accent);
                                      border-radius:50%;display:grid;place-items:center;
                                      cursor:pointer;border:2px solid var(--bg-surface);">
                            <i class="bi bi-camera-fill" style="font-size:10px;color:#0d0f14;"></i>
                        </label>
                    </div>

                    <div style="margin-top:10px;">
                        <div style="font-size:16px;font-weight:600;color:var(--text-primary);">
                            <?= htmlspecialchars($u['nombre']) ?>
                        </div>
                        <div style="font-family:var(--font-mono);font-size:11px;color:var(--text-tertiary);margin-top:2px;">
                            @<?= htmlspecialchars($u['usuario']) ?>
                        </div>
                        <div style="font-size:11px;color:var(--text-tertiary);margin-top:4px;">
                            <i class="bi bi-calendar3" style="font-size:10px;"></i> Desde <?= $since_fmt ?>
                        </div>
                    </div>
                </div>

                <!-- Stat pills -->
                <div style="padding:14px 16px;border-top:1px solid var(--border);
                            display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                    <?php foreach ([
                        ['value' => $stats['materias'],   'label' => 'Materias',    'color' => 'var(--accent)'],
                        ['value' => $stats['total'],      'label' => 'Actividades', 'color' => 'var(--text-primary)'],
                        ['value' => $stats['completadas'],'label' => 'Completadas', 'color' => 'var(--status-done)'],
                        ['value' => $stats['pendientes'], 'label' => 'Pendientes',  'color' => 'var(--status-pending)'],
                    ] as $s): ?>
                    <div style="background:var(--bg-elevated);border:1px solid var(--border);
                                border-radius:var(--r-md);padding:10px 12px;text-align:center;">
                        <div style="font-family:var(--font-mono);font-size:20px;font-weight:500;color:<?= $s['color'] ?>;">
                            <?= intval($s['value']) ?>
                        </div>
                        <div style="font-size:11px;color:var(--text-secondary);"><?= $s['label'] ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Progress bar -->
                <div style="padding:14px 16px 18px;border-top:1px solid var(--border);">
                    <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                        <span style="font-size:12px;color:var(--text-secondary);">Progreso general</span>
                        <span style="font-family:var(--font-mono);font-size:11px;color:var(--accent);"><?= $pct ?>%</span>
                    </div>
                    <div style="height:4px;background:var(--bg-overlay);border-radius:2px;overflow:hidden;">
                        <div style="width:<?= $pct ?>%;height:100%;background:var(--accent);
                                    border-radius:2px;transition:.8s cubic-bezier(.4,0,.2,1);"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── RIGHT: Edit form ── -->
        <div style="display:flex;flex-direction:column;gap:16px;">

            <!-- Account info form -->
            <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:var(--r-xl);overflow:hidden;">
                <div style="padding:18px 22px;border-bottom:1px solid var(--border);
                            display:flex;align-items:center;justify-content:space-between;">
                    <div>
                        <div style="font-family:var(--font-display);font-size:16px;color:var(--text-primary);">
                            Información de cuenta
                        </div>
                        <div style="font-size:12px;color:var(--text-tertiary);margin-top:2px;">
                            Actualiza tu nombre, usuario y foto de perfil
                        </div>
                    </div>
                    <i class="bi bi-person-circle" style="font-size:22px;color:var(--text-tertiary);"></i>
                </div>

                <!--
                    enctype="multipart/form-data" is REQUIRED for file uploads.
                    The file input (imgInput) lives inside this form.
                    The <label for="imgInput"> in the avatar card on the left also triggers
                    this same input because they share the same id — this is valid HTML.
                -->
                <form action="profile.php"
                      method="post"
                      enctype="multipart/form-data"
                      style="padding:22px;"
                      id="profileForm">

                    <!-- Hidden file input — triggered by the camera label in the avatar card -->
                    <input type="file"
                           id="imgInput"
                           name="imagen"
                           accept="image/jpeg,image/png,image/webp,image/gif"
                           style="display:none;"
                           onchange="handleAvatarPreview(this)">

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label-nbs">Nombre completo</label>
                            <input type="text"
                                   name="nombre"
                                   class="form-control-nbs"
                                   value="<?= htmlspecialchars($u['nombre']) ?>"
                                   required>
                        </div>
                        <div class="form-group">
                            <label class="form-label-nbs">Nombre de usuario</label>
                            <input type="text"
                                   name="usuario"
                                   class="form-control-nbs"
                                   value="<?= htmlspecialchars($u['usuario']) ?>"
                                   required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label-nbs">Correo electrónico</label>
                        <input type="email"
                               class="form-control-nbs"
                               value="<?= htmlspecialchars($u['correo']) ?>"
                               disabled
                               style="opacity:.5;cursor:not-allowed;">
                        <div style="font-size:11px;color:var(--text-tertiary);margin-top:4px;">
                            <i class="bi bi-lock-fill"></i> El correo no puede modificarse.
                        </div>
                    </div>

                    <!-- Selected file indicator (hidden until a file is chosen) -->
                    <div id="fileIndicator"
                         style="display:none;padding:8px 12px;background:var(--accent-dim);
                                border:1px solid rgba(74,240,200,.3);border-radius:var(--r-md);
                                font-size:12px;color:var(--accent);margin-bottom:14px;
                                display:none;align-items:center;gap:8px;">
                        <i class="bi bi-image-fill"></i>
                        <span id="fileIndicatorName"></span>
                        <button type="button"
                                onclick="clearAvatarSelection()"
                                style="margin-left:auto;background:none;border:none;
                                       color:var(--accent);cursor:pointer;font-size:14px;">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>

                    <div style="display:flex;justify-content:flex-end;margin-top:4px;">
                        <button type="submit" class="btn-primary-nbs">
                            <i class="bi bi-check2"></i> Guardar cambios
                        </button>
                    </div>
                </form>
            </div>

            <!-- Danger zone -->
            <div style="background:var(--bg-surface);border:1px solid rgba(252,92,125,.2);
                        border-radius:var(--r-xl);overflow:hidden;">
                <div style="padding:18px 22px;border-bottom:1px solid rgba(252,92,125,.15);
                            display:flex;align-items:center;justify-content:space-between;">
                    <div>
                        <div style="font-family:var(--font-display);font-size:16px;color:var(--status-cancelled);">
                            Zona de peligro
                        </div>
                        <div style="font-size:12px;color:var(--text-tertiary);margin-top:2px;">
                            Acciones irreversibles
                        </div>
                    </div>
                    <i class="bi bi-exclamation-triangle-fill"
                       style="font-size:20px;color:var(--status-cancelled);opacity:.6;"></i>
                </div>
                <div style="padding:18px 22px;display:flex;align-items:center;
                            justify-content:space-between;gap:20px;flex-wrap:wrap;">
                    <div>
                        <div style="font-size:13px;font-weight:500;color:var(--text-primary);">
                            Cerrar sesión
                        </div>
                        <div style="font-size:12px;color:var(--text-tertiary);">
                            Finaliza tu sesión actual en este dispositivo.
                        </div>
                    </div>
                    <a href="../model/logout.php"
                       style="display:inline-flex;align-items:center;gap:6px;
                              padding:7px 14px;border-radius:var(--r-md);
                              border:1px solid rgba(252,92,125,.35);
                              color:var(--status-cancelled);font-size:13px;
                              background:rgba(252,92,125,.08);text-decoration:none;
                              transition:background .15s;white-space:nowrap;"
                       onmouseover="this.style.background='rgba(252,92,125,.18)'"
                       onmouseout="this.style.background='rgba(252,92,125,.08)'">
                        <i class="bi bi-box-arrow-left"></i> Cerrar sesión
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
@media (max-width: 860px) {
    #profileGrid { display: flex !important; flex-direction: column !important; }
}
</style>

<script>
// Preview avatar before upload
function handleAvatarPreview(input) {
    if (!input.files || !input.files[0]) return;

    const file = input.files[0];
    const maxMB = <?= UPLOAD_MAX_MB ?>;

    if (file.size > maxMB * 1024 * 1024) {
        showToast(`La imagen no puede superar ${maxMB} MB.`, 'warning');
        input.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('avatarPreview').src = e.target.result;

        const indicator = document.getElementById('fileIndicator');
        document.getElementById('fileIndicatorName').textContent = file.name;
        indicator.style.display = 'flex';

        showToast('Imagen seleccionada. Pulsa "Guardar cambios" para aplicarla.', 'success');
    };
    reader.readAsDataURL(file);
}

// Clear avatar selection
function clearAvatarSelection() {
    const input = document.getElementById('imgInput');
    input.value = '';

    // Restore original avatar
    document.getElementById('avatarPreview').src = '<?= $avatar_src ?>';
    document.getElementById('fileIndicator').style.display = 'none';
}

// Auto-dismiss flash banner
(function() {
    const banner = document.getElementById('flashBanner');
    if (banner) setTimeout(() => {
        banner.style.transition = 'opacity .4s';
        banner.style.opacity = '0';
        setTimeout(() => banner.remove(), 400);
    }, 4000);
})();
</script>

<?php include_once 'footer.php'; ?>
