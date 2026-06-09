<?php
/**
 * ============================================================
 * NOTEBOOKST — Cron: Notificaciones de vencimiento por email
 * ============================================================
 * Ubicación sugerida : /cron/notificar_vencimientos.php
 * Ejecución          : cada hora
 * 0 * * * * php /ruta/al/proyecto/cron/notificar_vencimientos.php
 * ============================================================
 */

// ── Bloquear acceso desde el navegador ──────────────────────
if (php_sapi_name() !== 'cli' && !defined('ALLOW_WEB_CRON')) {
    http_response_code(403);
    exit('Acceso denegado.');
}

// ── Dependencias Nativas (Estructura confirmada con /src/) ──
require_once __DIR__ . '/../model/conexion.php';
require_once __DIR__ . '/../model/libs/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../model/libs/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../model/libs/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ── ⚙️ CONFIGURACIÓN ──────────────────────────────────────────
define('MAIL_HOST',     'smtp.gmail.com');
define('MAIL_PORT',     587);
define('MAIL_USER',     'zakufoxzf@gmail.com');      
define('MAIL_PASS',     'ouay zyss nxmm iqet');     
define('MAIL_FROM',     'zakufoxzf@gmail.com');
define('MAIL_FROMNAME', 'Notebookst');
define('APP_URL',       'http://localhost/notbookst/view/home.php'); 
// ─────────────────────────────────────────────────────────────

// ── Logger ───────────────────────────────────────────────────
function log_cron(string $msg): void {
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
    echo $line;
    file_put_contents(__DIR__ . '/notificaciones.log', $line, FILE_APPEND);
}

// ── Función de envío ─────────────────────────────────────────
function enviarCorreo(string $destino, string $nombreUsuario, array $tarea, string $tipo): bool {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USER;
        $mail->Password   = MAIL_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(MAIL_FROM, MAIL_FROMNAME);
        $mail->addAddress($destino, $nombreUsuario);

        $etiquetaTiempo = $tipo === '1h' ? 'en menos de 1 hora' : 'en menos de 24 horas';
        $colorAlerta    = $tipo === '1h' ? '#fc5c7d' : '#f5a623';
        $iconoAlerta    = $tipo === '1h' ? '🔴' : '🟡';

        $mail->Subject = "⏰ Tarea por vencer {$etiquetaTiempo} — {$tarea['nombre_tarea']}";

        $nombreTarea   = htmlspecialchars($tarea['nombre_tarea']);
        $nombreMateria = htmlspecialchars($tarea['nombre_materia']);
        $fechaCierre   = date('d/m/Y', strtotime($tarea['fecha_cierre']));
        $horaCierre    = substr($tarea['hora_cierre'], 0, 5); 
        $tipoTarea     = strtoupper(str_replace('_', ' ', $tarea['tipo_tarea']));
        $appUrl        = APP_URL;

        $mail->isHTML(true);
        $mail->Body = "
<!DOCTYPE html>
<html lang='es'>
<head><meta charset='UTF-8'><meta name='viewport' content='width=device-width,initial-scale=1'></head>
<body style='margin:0;padding:0;background:#0d0f14;font-family:system-ui,sans-serif;'>
<table width='100%' cellpadding='0' cellspacing='0' style='background:#0d0f14;padding:40px 20px;'>
<tr><td align='center'>
<table width='520' cellpadding='0' cellspacing='0'
       style='background:#13161d;border-radius:16px;border:1px solid rgba(255,255,255,.07);overflow:hidden;max-width:100%;'>

  <tr><td style='background:linear-gradient(90deg,transparent,{$colorAlerta},transparent);height:3px;'></td></tr>

  <tr><td style='padding:24px 32px 20px;border-bottom:1px solid rgba(255,255,255,.07);'>
    <table width='100%' cellpadding='0' cellspacing='0'><tr>
      <td>
        <span style='background:#4af0c8;border-radius:6px;padding:5px 10px;
                     font-size:12px;font-weight:700;color:#0d0f14;letter-spacing:.06em;'>
          NOTEBOOKST
        </span>
      </td>
      <td align='right' style='font-size:11px;color:#525c6e;font-family:monospace;'>
        {$iconoAlerta}&nbsp;Alerta de vencimiento
      </td>
    </tr></table>
  </td></tr>

  <tr><td style='padding:28px 32px;'>
    <p style='margin:0 0 6px;font-size:11px;color:#525c6e;letter-spacing:.1em;text-transform:uppercase;'>
      Hola, {$nombreUsuario}
    </p>
    <h1 style='margin:0 0 22px;font-size:21px;color:#eef0f5;line-height:1.3;font-weight:400;'>
      Tu tarea vence<br>
      <strong style='color:{$colorAlerta};'>{$etiquetaTiempo}</strong>
    </h1>

    <table width='100%' cellpadding='0' cellspacing='0'
           style='background:#1a1e28;border-radius:10px;
                  border:1px solid rgba(255,255,255,.07);
                  border-left:3px solid {$colorAlerta};
                  margin-bottom:26px;'>
      <tr><td style='padding:18px 20px;'>

        <div style='margin-bottom:10px;'>
          <span style='background:rgba(255,255,255,.06);border-radius:4px;
                       padding:3px 8px;font-size:10px;color:#8892a4;
                       letter-spacing:.08em;font-family:monospace;'>
            {$tipoTarea}
          </span>
        </div>

        <p style='margin:0 0 4px;font-size:15px;font-weight:600;color:#eef0f5;'>
          {$nombreTarea}
        </p>
        <p style='margin:0 0 16px;font-size:12px;color:#8892a4;'>
          {$nombreMateria}
        </p>

        <table cellpadding='0' cellspacing='8'><tr>
          <td style='padding-right:24px;'>
            <span style='display:block;font-size:10px;color:#525c6e;
                         text-transform:uppercase;letter-spacing:.07em;margin-bottom:2px;'>
              Fecha límite
            </span>
            <span style='font-size:14px;color:#eef0f5;font-family:monospace;font-weight:500;'>
              {$fechaCierre}
            </span>
          </td>
          <td>
            <span style='display:block;font-size:10px;color:#525c6e;
                         text-transform:uppercase;letter-spacing:.07em;margin-bottom:2px;'>
              Hora
            </span>
            <span style='font-size:14px;color:#eef0f5;font-family:monospace;font-weight:500;'>
              {$horaCierre}
            </span>
          </td>
        </tr></table>

      </td></tr>
    </table>

    <table cellpadding='0' cellspacing='0'><tr>
      <td style='background:#4af0c8;border-radius:8px;'>
        <a href='{$appUrl}'
           style='display:inline-block;padding:10px 26px;font-size:13px;
                  font-weight:700;color:#0d0f14;text-decoration:none;letter-spacing:.03em;'>
          Ver mis tareas →
        </a>
      </td>
    </tr></table>
  </td></tr>

  <tr><td style='padding:16px 32px;border-top:1px solid rgba(255,255,255,.07);'>
    <p style='margin:0;font-size:11px;color:#525c6e;line-height:1.6;'>
      Recibiste este correo porque tienes notificaciones activas en Notebookst.<br>
      Mensaje automático — no respondas a este correo.
    </p>
  </td></tr>

</table>
</td></tr>
</table>
</body></html>";

        $mail->AltBody =
            "Hola {$nombreUsuario},\n\n"
            . "Tu {$tipoTarea} \"{$nombreTarea}\" de {$nombreMateria} "
            . "vence {$etiquetaTiempo}.\n"
            . "Fecha límite: {$fechaCierre} a las {$horaCierre}\n\n"
            . "Ingresa a tu cuenta: {$appUrl}\n\n"
            . "— Notebookst";

        $mail->send();
        return true;

    } catch (Exception $e) {
        log_cron("ERROR al enviar a {$destino}: {$mail->ErrorInfo}");
        return false;
    }
}

// ── Query base reutilizable ──────────────────────────────────
$queryBase = "
    SELECT
        t.id_tarea,
        t.nombre_tarea,
        t.tipo_tarea,
        t.fecha_cierre,
        t.hora_cierre,
        m.nombre_materia,
        u.nombre,
        u.correo
    FROM tareas t
    JOIN materias m ON t.id_materia  = m.id_materia
    JOIN usuarios u ON m.id_usuario  = u.id_usuario
    WHERE t.estado NOT IN ('completada', 'cancelada')
      AND u.correo IS NOT NULL
      AND u.correo <> ''
";

// ── Alerta 24 horas ──────────────────────────────────────────
$stmt24h = $pdo->prepare($queryBase . "
    AND t.notificado_24h = 0
    AND CONCAT(t.fecha_cierre, ' ', COALESCE(t.hora_cierre, '23:59:00'))
        BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 24 HOUR)
");
$stmt24h->execute();
$tareas24h = $stmt24h->fetchAll(PDO::FETCH_ASSOC);

// ── Alerta 1 hora ────────────────────────────────────────────
$stmt1h = $pdo->prepare($queryBase . "
    AND t.notificado_1h = 0
    AND CONCAT(t.fecha_cierre, ' ', COALESCE(t.hora_cierre, '23:59:00'))
        BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 1 HOUR)
");
$stmt1h->execute();
$tareas1h = $stmt1h->fetchAll(PDO::FETCH_ASSOC);

// ── Enviar alertas 24h ───────────────────────────────────────
$enviados24h = 0;
foreach ($tareas24h as $tarea) {
    log_cron("Alerta 24h → {$tarea['correo']} | {$tarea['nombre_tarea']}");
    if (enviarCorreo($tarea['correo'], $tarea['nombre'], $tarea, '24h')) {
        $pdo->prepare("UPDATE tareas SET notificado_24h = 1 WHERE id_tarea = ?")
            ->execute([$tarea['id_tarea']]);
        $enviados24h++;
        log_cron("✓ OK (24h) id_tarea={$tarea['id_tarea']}");
    }
}

// ── Enviar alertas 1h ────────────────────────────────────────
$enviados1h = 0;
foreach ($tareas1h as $tarea) {
    log_cron("Alerta 1h  → {$tarea['correo']} | {$tarea['nombre_tarea']}");
    if (enviarCorreo($tarea['correo'], $tarea['nombre'], $tarea, '1h')) {
        $pdo->prepare("UPDATE tareas SET notificado_1h = 1 WHERE id_tarea = ?")
            ->execute([$tarea['id_tarea']]);
        $enviados1h++;
        log_cron("✓ OK (1h)  id_tarea={$tarea['id_tarea']}");
    }
}

// ── Resumen ──────────────────────────────────────────────────
log_cron("── Fin: {$enviados24h} correo(s) 24h · {$enviados1h} correo(s) 1h enviados ──");