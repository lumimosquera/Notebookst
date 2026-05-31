<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../index.php'); exit; }

require '../model/conexion.php';
$user_id = intval($_SESSION['user_id']);

// ── POST: update profile ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre  = trim($_POST['nombre']  ?? '');
    $usuario = trim($_POST['usuario'] ?? '');

    if (empty($nombre) || empty($usuario)) {
        $toast = ['msg' => 'Nombre y usuario son obligatorios.', 'type' => 'warning'];
    } else {
        $img_url = null;

        if (isset($_FILES['imagen']['name']) && !empty($_FILES['imagen']['name'])) {
            $ext_map = ['jpg' => true, 'jpeg' => true, 'png' => true, 'webp' => true];
            $ext     = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));

            if (isset($ext_map[$ext]) && $_FILES['imagen']['error'] === 0) {
                $dir      = '../asset/img/user/';
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                $filename = uniqid('avatar_', true) . '.' . $ext;
                $path     = $dir . $filename;

                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $path)) {
                    // Obtener y eliminar foto anterior (con prepared statement seguro)
                    $stmt_old = $pdo->prepare("SELECT imagen FROM usuarios WHERE id_usuario = ?");
                    $stmt_old->execute([$user_id]);
                    $oldRow = $stmt_old->fetch();
                    
                    if ($oldRow && !empty($oldRow['imagen']) && file_exists($oldRow['imagen'])) {
                        @unlink($oldRow['imagen']);
                    }
                    $img_url = $path;
                } else {
                    $toast = ['msg' => 'No se pudo guardar la foto. Verifica permisos de carpeta.', 'type' => 'warning'];
                }
            } else {
                $toast = ['msg' => 'Formato no permitido. Usa JPG, PNG o WEBP.', 'type' => 'warning'];
            }
        }

        if (!isset($toast)) {
            try {
                if ($img_url) {
                    $stmt = $pdo->prepare("UPDATE usuarios SET nombre=?, usuario=?, imagen=? WHERE id_usuario=?");
                    $success = $stmt->execute([$nombre, $usuario, $img_url, $user_id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE usuarios SET nombre=?, usuario=? WHERE id_usuario=?");
                    $success = $stmt->execute([$nombre, $usuario, $user_id]);
                }
                
                if ($success && $stmt->rowCount() > 0) {
                    $_SESSION['nombre_usuario'] = $nombre;
                    header("Location: profile.php?msg=" . urlencode("Perfil actualizado.") . "&type=success");
                    exit;
                } else {
                    $toast = ['msg' => 'Error al guardar cambios. Intenta de nuevo.', 'type' => 'warning'];
                }
            } catch (Exception $e) {
                $toast = ['msg' => 'Error en la base de datos: ' . $e->getMessage(), 'type' => 'danger'];
            }
        }
    }
}

// ── Load user data ───────────────────────────────────────────
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
$stmt->execute([$user_id]);
$u = $stmt->fetch(PDO::FETCH_ASSOC);

// Stats
$stmtS = $pdo->prepare("
    SELECT
        COUNT(DISTINCT m.id_materia) AS materias,
        COUNT(t.id_tarea)            AS total,
        SUM(t.estado = 'completada') AS completadas,
        SUM(t.estado = 'pendiente')  AS pendientes
    FROM materias m
    LEFT JOIN tareas t ON m.id_materia = t.id_materia
    WHERE m.id_usuario = ?
");
$stmtS->execute([$user_id]);
$stats = $stmtS->fetch(PDO::FETCH_ASSOC);
$pct = $stats['total'] > 0 ? round($stats['completadas'] / $stats['total'] * 100) : 0;

// Member since
$since = $u['fecha_registro'] ?? null;
$since_fmt = $since ? date('M Y', strtotime($since)) : '2024';

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

    <div style="display:grid;grid-template-columns:300px 1fr;gap:20px;align-items:start;">

        <!-- LEFT: Avatar card -->
        <div>
            <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:var(--r-xl);overflow:hidden;">
                <!-- Cover gradient -->
                <div style="height:80px;background:linear-gradient(135deg,#1a1e28 0%,#0d1520 50%,#0a1a14 100%);position:relative;">
                    <div style="position:absolute;inset:0;background:radial-gradient(circle at 60% 40%, rgba(74,240,200,.15) 0%, transparent 70%);"></div>
                </div>

                <!-- Avatar -->
                <div style="padding:0 20px 20px;text-align:center;margin-top:-40px;">
                    <div style="position:relative;display:inline-block;">
                        <img id="avatarPreview"
                             src="<?= $u['imagen'] ? htmlspecialchars($u['imagen']) : '../asset/img/N.png' ?>"
                             style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:3px solid var(--bg-surface);background:var(--bg-overlay);"
                             alt="Avatar">
                        <label for="imgInput" title="Cambiar foto"
                               style="position:absolute;bottom:2px;right:2px;width:24px;height:24px;
                                      background:var(--accent);border-radius:50%;display:grid;place-items:center;
                                      cursor:pointer;border:2px solid var(--bg-surface);">
                            <i class="bi bi-camera-fill" style="font-size:10px;color:#0d0f14;"></i>
                        </label>
                    </div>

                    <div style="margin-top:10px;">
                        <div style="font-size:16px;font-weight:600;color:var(--text-primary);"><?= htmlspecialchars($u['nombre']) ?></div>
                        <div style="font-family:var(--font-mono);font-size:11px;color:var(--text-tertiary);margin-top:2px;">@<?= htmlspecialchars($u['usuario']) ?></div>
                        <div style="font-size:11px;color:var(--text-tertiary);margin-top:4px;">
                            <i class="bi bi-calendar3" style="font-size:10px;"></i> Desde <?= $since_fmt ?>
                        </div>
                    </div>
                </div>

                <!-- Stats pills -->
                <div style="padding:14px 16px;border-top:1px solid var(--border);display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                    <div style="background:var(--bg-elevated);border:1px solid var(--border);border-radius:var(--r-md);padding:10px 12px;text-align:center;">
                        <div style="font-family:var(--font-mono);font-size:20px;font-weight:500;color:var(--text-primary);"><?= $stats['materias'] ?></div>
                        <div style="font-size:11px;color:var(--text-secondary);">Materias</div>
                    </div>
                    <div style="background:var(--bg-elevated);border:1px solid var(--border);border-radius:var(--r-md);padding:10px 12px;text-align:center;">
                        <div style="font-family:var(--font-mono);font-size:20px;font-weight:500;color:var(--text-primary);"><?= $stats['total'] ?></div>
                        <div style="font-size:11px;color:var(--text-secondary);">Actividades</div>
                    </div>
                    <div style="background:var(--bg-elevated);border:1px solid var(--border);border-radius:var(--r-md);padding:10px 12px;text-align:center;">
                        <div style="font-family:var(--font-mono);font-size:20px;font-weight:500;color:var(--status-done);"><?= $stats['completadas'] ?></div>
                        <div style="font-size:11px;color:var(--text-secondary);">Completadas</div>
                    </div>
                    <div style="background:var(--bg-elevated);border:1px solid var(--border);border-radius:var(--r-md);padding:10px 12px;text-align:center;">
                        <div style="font-family:var(--font-mono);font-size:20px;font-weight:500;color:var(--status-pending);"><?= $stats['pendientes'] ?></div>
                        <div style="font-size:11px;color:var(--text-secondary);">Pendientes</div>
                    </div>
                </div>

                <!-- Progress -->
                <div style="padding:14px 16px 18px;border-top:1px solid var(--border);">
                    <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                        <span style="font-size:12px;color:var(--text-secondary);">Progreso general</span>
                        <span style="font-family:var(--font-mono);font-size:11px;color:var(--accent);"><?= $pct ?>%</span>
                    </div>
                    <div style="height:4px;background:var(--bg-overlay);border-radius:2px;overflow:hidden;">
                        <div style="width:<?= $pct ?>%;height:100%;background:var(--accent);border-radius:2px;transition:.8s;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT: Edit form -->
        <div style="display:flex;flex-direction:column;gap:16px;">

            <!-- Account info -->
            <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:var(--r-xl);overflow:hidden;">
                <div style="padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
                    <div>
                        <div style="font-family:var(--font-display);font-size:16px;color:var(--text-primary);">Información de cuenta</div>
                        <div style="font-size:12px;color:var(--text-tertiary);margin-top:2px;">Actualiza tu nombre y foto de perfil</div>
                    </div>
                    <i class="bi bi-person-circle" style="font-size:22px;color:var(--text-tertiary);"></i>
                </div>

                <form action="profile.php" method="post" enctype="multipart/form-data" style="padding:22px;">
                    <input type="file" id="imgInput" name="imagen" accept="image/jpg,image/jpeg,image/png,image/webp" style="display:none" onchange="previewAvatar(this)">

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label-nbs">Nombre completo</label>
                            <input type="text" name="nombre" class="form-control-nbs"
                                   value="<?= htmlspecialchars($u['nombre']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label-nbs">Nombre de usuario</label>
                            <input type="text" name="usuario" class="form-control-nbs"
                                   value="<?= htmlspecialchars($u['usuario']) ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label-nbs">Correo electrónico</label>
                        <input type="email" class="form-control-nbs"
                               value="<?= htmlspecialchars($u['correo']) ?>" disabled
                               style="opacity:.5;cursor:not-allowed;">
                        <div style="font-size:11px;color:var(--text-tertiary);margin-top:4px;">
                            <i class="bi bi-lock-fill"></i> El correo no se puede cambiar.
                        </div>
                    </div>

                    <div style="display:flex;justify-content:flex-end;margin-top:4px;">
                        <button type="submit" class="btn-primary-nbs">
                            <i class="bi bi-check2"></i> Guardar cambios
                        </button>
                    </div>
                </form>
            </div>

            <!-- Security -->
            <div style="background:var(--bg-surface);border:1px solid var(--border);border-radius:var(--r-xl);overflow:hidden;">
                <div style="padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
                    <div>
                        <div style="font-family:var(--font-display);font-size:16px;color:var(--text-primary);">Seguridad</div>
                        <div style="font-size:12px;color:var(--text-tertiary);margin-top:2px;">Cambiar contraseña</div>
                    </div>
                    <i class="bi bi-shield-lock-fill" style="font-size:22px;color:var(--text-tertiary);"></i>
                </div>
                <form action="profile.php" method="post" style="padding:22px;" id="frmPass">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label-nbs">Nueva contraseña</label>
                            <input type="password" name="password_new" class="form-control-nbs" placeholder="••••••••" minlength="6">
                        </div>
                        <div class="form-group">
                            <label class="form-label-nbs">Confirmar contraseña</label>
                            <input type="password" name="password_confirm" class="form-control-nbs" placeholder="••••••••" minlength="6">
                        </div>
                    </div>
                    <div style="display:flex;justify-content:flex-end;margin-top:4px;">
                        <button type="submit" class="btn-ghost" style="opacity:.55;cursor:not-allowed;" disabled title="Próximamente">
                            <i class="bi bi-lock"></i> Cambiar contraseña <span style="font-size:10px;font-family:var(--font-mono);">(próximamente)</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Danger zone -->
            <div style="background:var(--bg-surface);border:1px solid rgba(252,92,125,.2);border-radius:var(--r-xl);overflow:hidden;">
                <div style="padding:18px 22px;border-bottom:1px solid rgba(252,92,125,.15);display:flex;align-items:center;justify-content:space-between;">
                    <div>
                        <div style="font-family:var(--font-display);font-size:16px;color:var(--status-cancelled);">Zona de peligro</div>
                        <div style="font-size:12px;color:var(--text-tertiary);margin-top:2px;">Acciones irreversibles</div>
                    </div>
                    <i class="bi bi-exclamation-triangle-fill" style="font-size:20px;color:var(--status-cancelled);opacity:.6;"></i>
                </div>
                <div style="padding:18px 22px;display:flex;align-items:center;justify-content:space-between;gap:20px;">
                    <div>
                        <div style="font-size:13px;font-weight:500;color:var(--text-primary);">Cerrar sesión</div>
                        <div style="font-size:12px;color:var(--text-tertiary);">Finaliza tu sesión actual en este dispositivo.</div>
                    </div>
                    <a href="../model/logout.php"
                       style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:var(--r-md);
                              border:1px solid rgba(252,92,125,.35);color:var(--status-cancelled);font-size:13px;
                              background:rgba(252,92,125,.08);text-decoration:none;transition:.15s;white-space:nowrap;"
                       onmouseover="this.style.background='rgba(252,92,125,.18)'"
                       onmouseout="this.style.background='rgba(252,92,125,.08)'">
                        <i class="bi bi-box-arrow-left"></i> Cerrar sesión
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('avatarPreview').src = e.target.result;
            showToast('Foto seleccionada. Guarda los cambios para aplicarla.', 'success');
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<style>
/* Responsive: stack on small screens */
@media (max-width: 860px) {
    div[style*="grid-template-columns:300px"] {
        display: flex !important;
        flex-direction: column !important;
    }
}
</style>

<?php include_once 'footer.php'; ?>