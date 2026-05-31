<?php
include "../controller/cnt_registro.php"
?>






<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Crear cuenta • Notebookst</title>
    <link rel="icon" href="../asset/img/N.png">
    <link rel="stylesheet" href="../asset/css/notebookst.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, var(--bg-base) 0%, #0f1419 50%, var(--bg-overlay) 100%);
            position: relative;
            overflow: hidden;
        }

        /* Fondo decorativo */
        body::before {
            content: '';
            position: absolute;
            inset: 0;
            background: 
                radial-gradient(circle at 20% 30%, rgba(17,164,255,0.08) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(74,240,200,0.04) 0%, transparent 50%);
            pointer-events: none;
        }

        .register-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }

        .register-panel {
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: var(--r-xl);
            padding: 32px;
            box-shadow: 0 24px 80px rgba(0,0,0,.6);
            animation: slideUp 0.6s ease-out;
        }

        .register-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .register-logo {
            font-size: 48px;
            color: var(--accent);
            margin-bottom: 16px;
            display: inline-block;
        }

        .register-title {
            font-size: 24px;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0 0 8px 0;
            font-family: 'Instrument Serif', serif;
        }

        .register-subtitle {
            font-size: 14px;
            color: var(--text-secondary);
            margin: 0;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .btn-register {
            width: 100%;
            padding: 10px 16px;
            background: var(--accent);
            color: #000;
            border: none;
            border-radius: var(--r-md);
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all var(--t-std);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 8px;
        }

        .btn-register:hover {
            background: var(--accent-dim);
            box-shadow: 0 0 24px rgba(17, 164, 255, 0.4);
            transform: translateY(-2px);
        }

        .btn-register i {
            font-size: 16px;
        }

        .register-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: var(--text-secondary);
        }

        .register-footer a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
            transition: color var(--t-fast);
        }

        .register-footer a:hover {
            color: var(--accent-dim);
        }

        .alert-message {
            background: rgba(252, 92, 125, 0.1);
            border: 1px solid rgba(252, 92, 125, 0.3);
            border-radius: var(--r-md);
            padding: 12px 14px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #fc5c7d;
            display: flex;
            gap: 10px;
            align-items: flex-start;
            animation: slideDown 0.3s ease-out;
        }

        .alert-message.success {
            background: rgba(74, 240, 200, 0.1);
            border-color: rgba(74, 240, 200, 0.3);
            color: #4af0c8;
        }

        .alert-message i {
            flex-shrink: 0;
            margin-top: 2px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        @media (max-width: 480px) {
            .register-panel {
                padding: 24px;
            }
            .register-title {
                font-size: 20px;
            }
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>


<body>
    <div class="register-container">
        <div class="register-panel">
            <!-- Header -->
            <div class="register-header">
                <div class="register-logo"><i class="bi bi-notebook"></i></div>
                <h1 class="register-title">Crear cuenta</h1>
                <p class="register-subtitle">Únete a Notebookst hoy</p>
            </div>

            <!-- Alert Messages -->
            <?php
            if (!empty($mensaje)) {
                foreach ($mensaje as $msg) {
                    echo '<div class="alert-message">';
                    echo '<i class="bi bi-exclamation-circle"></i>';
                    echo '<span>' . htmlspecialchars($msg) . '</span>';
                    echo '</div>';
                }
            }
            if (!empty($mensaje2)) {
                foreach ($mensaje2 as $msg) {
                    echo '<div class="alert-message success">';
                    echo '<i class="bi bi-check-circle"></i>';
                    echo '<span>' . htmlspecialchars($msg) . '</span>';
                    echo '</div>';
                }
            }
            ?>

            <!-- Form -->
            <form action="" method="post" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label-nbs" for="nombre">
                            <i class="bi bi-person"></i> Nombre completo
                        </label>
                        <input type="text" id="nombre" name="nombre" class="form-control-nbs" 
                               placeholder="Juan Pérez" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label-nbs" for="usuario">
                            <i class="bi bi-at"></i> Usuario
                        </label>
                        <input type="text" id="usuario" name="usuario" class="form-control-nbs" 
                               placeholder="juan_perez" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label-nbs" for="correo">
                        <i class="bi bi-envelope"></i> Correo electrónico
                    </label>
                    <input type="email" id="correo" name="correo" class="form-control-nbs" 
                           placeholder="correo@ejemplo.com" required>
                </div>

                <div class="form-group">
                    <label class="form-label-nbs" for="imagen">
                        <i class="bi bi-image"></i> Foto de perfil
                    </label>
                    <input type="file" id="imagen" name="imagen" class="form-control-nbs" accept="image/*">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label-nbs" for="contraseña">
                            <i class="bi bi-lock"></i> Contraseña
                        </label>
                        <input type="password" id="contraseña" name="contraseña" class="form-control-nbs" 
                               placeholder="••••••••" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label-nbs" for="confirma_contraseña">
                            <i class="bi bi-lock-fill"></i> Confirmar
                        </label>
                        <input type="password" id="confirma_contraseña" name="confirma_contraseña" class="form-control-nbs" 
                               placeholder="••••••••" required>
                    </div>
                </div>

                <div style="margin-bottom: 16px; display: flex; gap: 8px; align-items: flex-start;">
                    <input type="checkbox" id="connected" name="connected" class="form-check-input" 
                           style="margin-top: 4px;" required>
                    <label for="connected" style="font-size: 13px; color: var(--text-secondary); margin: 0; cursor: pointer;">
                        Acepto los <a href="#" style="color: var(--accent); text-decoration: none;">términos y condiciones</a>
                    </label>
                </div>

                <button type="submit" class="btn-register">
                    <i class="bi bi-person-plus"></i> Crear cuenta
                </button>
            </form>

            <!-- Footer -->
            <div class="register-footer">
                ¿Ya tienes cuenta? 
                <a href="login.php">Inicia sesión</a>
            </div>
        </div>
    </div>
</body>

</html>