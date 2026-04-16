<?php
// ============================================================
//  dashboard.php — Página protegida (solo usuarios autenticados)
// ============================================================

require_once 'config.php';

// Redirigir si no está autenticado
if (!isset($_SESSION['auth_user'])) {
    header('Location: index.php');
    exit;
}

$username  = htmlspecialchars($_SESSION['auth_user']);
$auth_time = date('d/m/Y H:i:s', $_SESSION['auth_time']);

// Manejar cierre de sesión
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Sora:wght@300;400;600&display=swap');
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    min-height: 100vh; display: flex; align-items: center; justify-content: center;
    background: #0d0f14; font-family: 'Sora', sans-serif; color: #e8eaf2; padding: 1.5rem;
  }
  body::before {
    content: ''; position: fixed; inset: 0;
    background: radial-gradient(ellipse 60% 40% at 80% 10%, rgba(0,229,160,0.07) 0%, transparent 70%);
    pointer-events: none;
  }
  .card {
    background: #13161e; border: 0.5px solid #1e2330; border-radius: 16px;
    padding: 2.5rem 2rem; width: 100%; max-width: 420px; position: relative;
    text-align: center;
  }
  .card::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 1px;
    background: linear-gradient(90deg, transparent, rgba(0,229,160,0.4), transparent);
    border-radius: 16px 16px 0 0;
  }
  .icon { font-size: 44px; margin-bottom: 1rem; }
  .title { font-size: 20px; font-weight: 600; margin-bottom: 0.5rem; }
  .info  { font-size: 13px; color: #6b7280; font-family: 'DM Mono', monospace; line-height: 1.8; }
  .info strong { color: #00e5a0; }
  .btn {
    display: inline-block; margin-top: 1.75rem; padding: 11px 24px;
    background: rgba(255,68,102,0.1); border: 0.5px solid rgba(255,68,102,0.25);
    border-radius: 10px; color: #ff4466; font-family: 'Sora', sans-serif;
    font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: none;
    transition: opacity 0.2s;
  }
  .btn:hover { opacity: 0.8; }
</style>
</head>
<body>
<div class="card">
  <div class="icon">✅</div>
  <div class="title">¡Acceso concedido!</div>
  <p class="info">
    Bienvenido/a: <strong><?php echo $username; ?></strong><br>
    Sesión iniciada: <?php echo $auth_time; ?>
  </p>
  <a class="btn" href="dashboard.php?logout=1">Cerrar sesión</a>
</div>
</body>
</html>
