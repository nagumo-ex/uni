<?php

function renderLayout($title, $activeMenu, $content) {
    $msg = getMessage();
    $alertHtml = '';

    if ($msg) {
        $icon = $msg['type'] === 'success' ? '✓' : '✗';
        $type = htmlspecialchars($msg['type'], ENT_QUOTES, 'UTF-8');
        $text = htmlspecialchars($msg['text'], ENT_QUOTES, 'UTF-8');
        $alertHtml = "<div class='alert alert-{$type}'><span class='alert-icon'>{$icon}</span>{$text}</div>";
    }

    $dashboardClass = $activeMenu === 'dashboard' ? 'active' : '';
    $usuariosClass = $activeMenu === 'usuarios' ? 'active' : '';
    $librosClass = $activeMenu === 'libros' ? 'active' : '';
    $prestamosClass = $activeMenu === 'prestamos' ? 'active' : '';
    $devolucionesClass = $activeMenu === 'devoluciones' ? 'active' : '';
    $todayLabel = date('d M Y');

    echo <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{$title} — BiblioSys</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
:root {
  --ink: #1a1208;
  --paper: #f5f0e8;
  --cream: #ede8dd;
  --gold: #c8922a;
  --gold-light: #e8b84b;
  --rust: #8b3a1c;
  --sage: #4a6741;
  --shadow: rgba(26,18,8,0.15);
  --border: rgba(200,146,42,0.25);
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { font-size: 16px; }
body {
  font-family: 'DM Sans', sans-serif;
  background: var(--paper);
  color: var(--ink);
  min-height: 100vh;
  display: flex;
  background-image:
    radial-gradient(ellipse at 0% 0%, rgba(200,146,42,0.06) 0%, transparent 50%),
    radial-gradient(ellipse at 100% 100%, rgba(139,58,28,0.05) 0%, transparent 50%);
}

/* SIDEBAR */
.sidebar {
  width: 280px;
  min-height: 100vh;
  background: var(--ink);
  position: fixed;
  left: 0;
  top: 0;
  display: flex;
  flex-direction: column;
  z-index: 100;
  border-right: 1px solid rgba(200,146,42,0.2);
}
.sidebar-brand {
  padding: 2rem 1.8rem 1.5rem;
  border-bottom: 1px solid rgba(200,146,42,0.15);
}
.sidebar-brand .logo-icon {
  width: 42px;
  height: 42px;
  background: linear-gradient(135deg, var(--gold), var(--gold-light));
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.3rem;
  margin-bottom: 0.8rem;
  box-shadow: 0 4px 12px rgba(200,146,42,0.3);
}
.sidebar-brand h1 {
  font-family: 'Playfair Display', serif;
  color: #fff;
  font-size: 1.4rem;
  font-weight: 700;
  letter-spacing: 0.02em;
}
.sidebar-brand p {
  color: rgba(255,255,255,0.4);
  font-size: 0.72rem;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  margin-top: 0.2rem;
}
.sidebar-nav {
  padding: 1.5rem 0;
  flex: 1;
}
.nav-section {
  padding: 0 1.2rem 0.5rem;
  font-size: 0.65rem;
  color: rgba(255,255,255,0.25);
  letter-spacing: 0.15em;
  text-transform: uppercase;
  margin-top: 1rem;
}
.nav-link {
  display: flex;
  align-items: center;
  gap: 0.8rem;
  padding: 0.75rem 1.8rem;
  color: rgba(255,255,255,0.6);
  text-decoration: none;
  font-size: 0.88rem;
  font-weight: 400;
  transition: all 0.2s;
  position: relative;
}
.nav-link:hover {
  color: #fff;
  background: rgba(200,146,42,0.1);
}
.nav-link.active {
  color: var(--gold-light);
  background: rgba(200,146,42,0.12);
}
.nav-link.active::before {
  content: '';
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 3px;
  height: 70%;
  background: var(--gold);
  border-radius: 0 2px 2px 0;
}
.nav-link .nav-icon {
  font-size: 1rem;
  width: 20px;
  text-align: center;
  opacity: 0.8;
}
.sidebar-footer {
  padding: 1.2rem 1.8rem;
  border-top: 1px solid rgba(200,146,42,0.1);
}
.sidebar-footer p {
  color: rgba(255,255,255,0.2);
  font-size: 0.7rem;
  text-align: center;
}

/* MAIN CONTENT */
.main {
  margin-left: 280px;
  flex: 1;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}
.topbar {
  background: rgba(245,240,232,0.95);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid var(--border);
  padding: 1.1rem 2.5rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: sticky;
  top: 0;
  z-index: 50;
}
.topbar h2 {
  font-family: 'Playfair Display', serif;
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--ink);
}
.topbar-badge {
  background: var(--ink);
  color: var(--gold-light);
  font-size: 0.72rem;
  padding: 0.3rem 0.8rem;
  border-radius: 20px;
  letter-spacing: 0.08em;
  font-weight: 500;
}
.content {
  padding: 2.5rem;
  flex: 1;
}

/* ALERTS */
.alert {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 1.4rem;
  border-radius: 8px;
  margin-bottom: 1.8rem;
  font-size: 0.9rem;
  font-weight: 500;
  animation: slideDown 0.3s ease;
}
@keyframes slideDown {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}
.alert-icon { font-size: 1rem; }
.alert-success { background: rgba(74,103,65,0.12); color: var(--sage); border: 1px solid rgba(74,103,65,0.25); }
.alert-error { background: rgba(139,58,28,0.1); color: var(--rust); border: 1px solid rgba(139,58,28,0.2); }

/* CARDS */
.card {
  background: #fff;
  border-radius: 12px;
  border: 1px solid var(--border);
  box-shadow: 0 2px 20px var(--shadow);
  overflow: hidden;
}
.card-header {
  padding: 1.5rem 2rem;
  border-bottom: 1px solid var(--border);
  background: linear-gradient(to right, rgba(200,146,42,0.04), transparent);
}
.card-header h3 {
  font-family: 'Playfair Display', serif;
  font-size: 1.15rem;
  color: var(--ink);
}
.card-header p {
  color: rgba(26,18,8,0.5);
  font-size: 0.82rem;
  margin-top: 0.2rem;
}
.card-body { padding: 2rem; }

/* FORMS */
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.2rem; }
.form-group { display: flex; flex-direction: column; gap: 0.45rem; }
.form-group.full { grid-column: 1 / -1; }
label {
  font-size: 0.78rem;
  font-weight: 500;
  color: rgba(26,18,8,0.6);
  letter-spacing: 0.06em;
  text-transform: uppercase;
}
input, select, textarea {
  padding: 0.75rem 1rem;
  border: 1.5px solid rgba(200,146,42,0.2);
  border-radius: 8px;
  font-family: 'DM Sans', sans-serif;
  font-size: 0.9rem;
  color: var(--ink);
  background: var(--paper);
  transition: all 0.2s;
  outline: none;
}
input:focus, select:focus, textarea:focus {
  border-color: var(--gold);
  background: #fff;
  box-shadow: 0 0 0 3px rgba(200,146,42,0.1);
}
textarea { resize: vertical; min-height: 90px; }
select {
  appearance: none;
  cursor: pointer;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%23c8922a' stroke-width='1.5' fill='none'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 1rem center;
}

/* BUTTONS */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.8rem 1.8rem;
  border: none;
  border-radius: 8px;
  font-family: 'DM Sans', sans-serif;
  font-size: 0.88rem;
  font-weight: 500;
  cursor: pointer;
  text-decoration: none;
  transition: all 0.2s;
}
.btn-primary {
  background: linear-gradient(135deg, var(--ink), #2d1e0a);
  color: var(--gold-light);
  box-shadow: 0 4px 12px rgba(26,18,8,0.2);
}
.btn-primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(26,18,8,0.3);
}
.btn-secondary {
  background: var(--cream);
  color: var(--ink);
  border: 1.5px solid var(--border);
}
.btn-secondary:hover { background: #e5dfd2; }
.form-actions {
  display: flex;
  gap: 1rem;
  margin-top: 1.8rem;
  padding-top: 1.5rem;
  border-top: 1px solid var(--border);
}

/* TABLES */
.table-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; font-size: 0.88rem; }
thead tr { border-bottom: 2px solid var(--border); }
thead th {
  padding: 0.85rem 1rem;
  text-align: left;
  font-size: 0.72rem;
  font-weight: 500;
  color: rgba(26,18,8,0.5);
  letter-spacing: 0.1em;
  text-transform: uppercase;
}
tbody tr {
  border-bottom: 1px solid rgba(200,146,42,0.08);
  transition: background 0.15s;
}
tbody tr:hover { background: rgba(200,146,42,0.04); }
tbody td { padding: 0.9rem 1rem; color: var(--ink); }
.badge {
  display: inline-block;
  padding: 0.25rem 0.7rem;
  border-radius: 20px;
  font-size: 0.72rem;
  font-weight: 500;
}
.badge-active { background: rgba(74,103,65,0.12); color: var(--sage); }
.badge-returned { background: rgba(200,146,42,0.12); color: var(--gold); }
.badge-overdue { background: rgba(139,58,28,0.1); color: var(--rust); }

/* STATS */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.2rem;
  margin-bottom: 2rem;
}
.stat-card {
  background: #fff;
  border: 1px solid var(--border);
  border-radius: 10px;
  padding: 1.4rem 1.6rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 2px 12px var(--shadow);
}
.stat-icon {
  width: 44px;
  height: 44px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
}
.stat-num {
  font-family: 'Playfair Display', serif;
  font-size: 1.8rem;
  font-weight: 700;
  line-height: 1;
}
.stat-label {
  font-size: 0.75rem;
  color: rgba(26,18,8,0.5);
  margin-top: 0.2rem;
}

/* EMPTY STATE */
.empty { text-align: center; padding: 4rem 2rem; color: rgba(26,18,8,0.4); }
.empty .empty-icon { font-size: 3rem; margin-bottom: 1rem; opacity: 0.3; }
.empty p { font-size: 0.9rem; }
</style>
</head>
<body>

<aside class="sidebar">
  <div class="sidebar-brand">
    <div class="logo-icon">📚</div>
    <h1>BiblioSys</h1>
    <p>Sistema de Biblioteca</p>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-section">Principal</div>
    <a href="index.php" class="nav-link {$dashboardClass}">
      <span class="nav-icon">🏠</span> Dashboard
    </a>

    <div class="nav-section">Gestión</div>
    <a href="usuarios.php" class="nav-link {$usuariosClass}">
      <span class="nav-icon">👤</span> Usuarios
    </a>
    <a href="libros.php" class="nav-link {$librosClass}">
      <span class="nav-icon">📖</span> Libros
    </a>

    <div class="nav-section">Operaciones</div>
    <a href="prestamos.php" class="nav-link {$prestamosClass}">
      <span class="nav-icon">🔖</span> Préstamos
    </a>
    <a href="devoluciones.php" class="nav-link {$devolucionesClass}">
      <span class="nav-icon">↩️</span> Devoluciones
    </a>
  </nav>

  <div class="sidebar-footer">
    <p>© 2025 BiblioSys v1.0</p>
  </div>
</aside>

<main class="main">
  <div class="topbar">
    <h2>{$title}</h2>
    <span class="topbar-badge">📅 {$todayLabel}</span>
  </div>
  <div class="content">
    {$alertHtml}
    {$content}
  </div>
</main>

</body>
</html>
HTML;
}
?>
