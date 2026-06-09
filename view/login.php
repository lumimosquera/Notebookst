
<?php
include "../controller/cnt_login.php";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión • Notebookst</title>
    <link rel="icon" href="../asset/img/N.png">
    <link rel="stylesheet" href="../asset/css/notebookst.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
  body {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    background: #0a0e0a;
    position: relative;
    overflow: hidden;
}

#matrix-bg {
    position: fixed;
    inset: 0;
    z-index: 0;
    opacity: 0.18;
    pointer-events: none;
}

body::before {
    content: '';
    position: fixed;
    inset: 0;
    z-index: 1;
    background-image:
        linear-gradient(rgba(0,255,140,0.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0,255,140,0.04) 1px, transparent 1px);
    background-size: 40px 40px;
    pointer-events: none;
}

body::after {
    content: '';
    position: fixed;
    inset: 0;
    z-index: 2;
    background: repeating-linear-gradient(
        0deg,
        transparent,
        transparent 2px,
        rgba(0,0,0,0.15) 2px,
        rgba(0,0,0,0.15) 4px
    );
    pointer-events: none;
}

.login-container {
    position: relative;
    z-index: 10; /* asegúrate que ya tenga z-index alto */
}

        /* Fondo decorativo */
 

        .login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 400px;
            padding: 20px;
        }

        .login-panel {
            background: var(--bg-surface);
            border: 1px solid var(--border);
            border-radius: var(--r-xl);
            padding: 32px;
            box-shadow: 0 24px 80px rgba(0,0,0,.6);
            animation: slideUp 0.5s ease;
             /* añade estos valores a los que ya tienes */
    border-color: rgba(0,255,140,0.2);
    box-shadow: 0 0 40px rgba(0,255,140,0.06), 0 24px 80px rgba(0,0,0,.8);
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

        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .login-logo {
            width: 48px;
            height: 48px;
            background: var(--accent);
            border-radius: var(--r-sm);
            display: grid;
            place-items: center;
            margin: 0 auto 16px;
            font-size: 24px;
            color: var(--text-inverse);
            font-weight: 700;
        }

        .login-title {
            font-family: var(--font-display);
            font-size: 24px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .login-subtitle {
            font-size: 13px;
            color: var(--text-secondary);
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group:last-of-type {
            margin-bottom: 24px;
        }

        .alert-message {
            background: rgba(252,92,125,0.1);
            border: 1px solid rgba(252,92,125,0.3);
            border-radius: var(--r-md);
            padding: 12px 14px;
            margin-bottom: 20px;
            font-size: 13px;
            color: var(--status-cancelled);
            display: flex;
            align-items: center;
            gap: 8px;
            animation: slideDown 0.3s ease;
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

        .alert-message i {
            font-size: 16px;
            flex-shrink: 0;
        }

        .btn-login {
            width: 100%;
            padding: 10px 16px;
            background: var(--accent);
            color: var(--text-inverse);
            border: none;
            border-radius: var(--r-md);
            font-size: 13.5px;
            font-weight: 600;
            font-family: var(--font-body);
            cursor: pointer;
            transition: background var(--t-fast), transform var(--t-fast), box-shadow var(--t-fast);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-login:hover {
            background: #2de8b8;
            transform: translateY(-1px);
            box-shadow: var(--accent-glow);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .login-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
            color: var(--text-secondary);
        }

        .login-footer a {
            color: var(--accent);
            text-decoration: none;
            transition: color var(--t-fast);
        }

        .login-footer a:hover {
            color: #2de8b8;
        }

        .form-control-nbs {
            display: block;
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 20px 0;
            color: var(--text-tertiary);
            font-size: 12px;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        @media (max-width: 480px) {
            .login-panel {
                padding: 24px;
            }
            .login-title {
                font-size: 20px;
            }
        }
        
    </style>
</head>

<body>
    <canvas id="matrix-bg"></canvas>
    <div class="login-container">
        <div class="login-panel">
            <!-- Header -->
            <div class="login-header">
                <div class="login-logo"> <img src="../asset/img/N.png" alt=""><i class="bi bi-notebook"></i></div>
                <h1 class="login-title">Bienvenido</h1>
                <p class="login-subtitle">Accede a tu cuenta Notebookst</p>
            </div>

            <!-- Alert Message -->
            <?php if(!empty($message)): ?>
            <div class="alert-message">
                <i class="bi bi-exclamation-circle"></i>
                <span><?= htmlspecialchars($message) ?></span>
            </div>
            <?php endif; ?>

            <!-- Form -->
            <form action="" method="post">
                <div class="form-group">
                    <label class="form-label-nbs" for="usuario">
                        <i class="bi bi-person"></i> Usuario o correo
                    </label>
                    <input type="text" id="usuario" name="usuario" class="form-control-nbs" 
                           placeholder="ejemplo@correo.com" required autocomplete="username">
                </div>

                <div class="form-group">
                    <label class="form-label-nbs" for="contraseña">
                        <i class="bi bi-lock"></i> Contraseña
                    </label>
                    <input type="password" id="contraseña" name="contraseña" class="form-control-nbs" 
                           placeholder="••••••••" required autocomplete="current-password">
                </div>

                <button type="submit" name="btniniciar" class="btn-login">
                    <i class="bi bi-arrow-right"></i> Iniciar sesión
                </button>
            </form>

            <!-- Footer -->
            <div class="login-footer">
                ¿No tienes cuenta? 
                <a href="registro.php">Regístrate aquí</a>
            </div>
        </div>
    </div>
    <script>
(function() {
    const canvas = document.getElementById('matrix-bg');
    const ctx = canvas.getContext('2d');
    function resize() {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    }
    resize();
    window.addEventListener('resize', resize);
    const chars = '0123456789ABCDEF<>{}[]|/\\;:.,01';
    let drops = [];
    function initDrops() {
        drops = Array(Math.floor(canvas.width / 16)).fill(0)
            .map(() => Math.random() * -60);
    }
    initDrops();
    window.addEventListener('resize', initDrops);
    function draw() {
        ctx.fillStyle = 'rgba(10,14,10,0.05)';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.font = '13px Courier New';
        drops.forEach((y, i) => {
            const ch = chars[Math.floor(Math.random() * chars.length)];
            ctx.fillStyle = y * 16 < 10
                ? 'rgba(200,255,220,0.9)'
                : 'rgba(0,200,100,0.65)';
            ctx.fillText(ch, i * 16, y * 16);
            if (y * 16 > canvas.height && Math.random() > 0.975) drops[i] = 0;
            drops[i] += 0.4;
        });
    }
    setInterval(draw, 40);
})();
</script>
</body>

</html>