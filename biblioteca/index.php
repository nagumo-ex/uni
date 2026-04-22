<?php
require_once 'includes/db.php';
require_once 'includes/layout.php';

$usuarios = loadData('usuarios');
$libros = loadData('libros');
$prestamos = loadData('prestamos');

$activos = array_filter($prestamos, function ($p) {
    return $p['estado'] === 'activo';
});

$devueltos = array_filter($prestamos, function ($p) {
    return $p['estado'] === 'devuelto';
});

$recent = array_slice(array_reverse($prestamos), 0, 5);

ob_start();
?>

<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(200,146,42,0.1);">👤</div>
    <div>
      <div class="stat-num"><?= count($usuarios) ?></div>
      <div class="stat-label">Usuarios registrados</div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(26,18,8,0.07);">📖</div>
    <div>
      <div class="stat-num"><?= count($libros) ?></div>
      <div class="stat-label">Libros en catálogo</div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(74,103,65,0.1);">🔖</div>
    <div>
      <div class="stat-num"><?= count($activos) ?></div>
      <div class="stat-label">Préstamos activos</div>
    </div>
  </div>

  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(139,58,28,0.08);">↩️</div>
    <div>
      <div class="stat-num"><?= count($devueltos) ?></div>
      <div class="stat-label">Devoluciones</div>
    </div>
  </div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem;">
  <div class="card">
    <div class="card-header">
      <h3>📋 Préstamos Recientes</h3>
      <p>Últimas operaciones registradas</p>
    </div>
    <div class="card-body" style="padding:0;">
      <?php if (empty($recent)): ?>
        <div class="empty"><div class="empty-icon">🔖</div><p>No hay préstamos aún</p></div>
      <?php else: ?>
        <table>
          <thead>
            <tr><th>Usuario</th><th>Libro</th><th>Estado</th></tr>
          </thead>
          <tbody>
            <?php foreach ($recent as $p): ?>
              <?php
              $u = findById('usuarios', $p['usuario_id']);
              $b = findById('libros', $p['libro_id']);
              $badge = $p['estado'] === 'activo' ? 'badge-active' : 'badge-returned';
              $label = $p['estado'] === 'activo' ? 'Activo' : 'Devuelto';
              ?>
              <tr>
                <td><?= htmlspecialchars($u ? $u['nombre'] : 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                <td style="max-width:140px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                  <?= htmlspecialchars($b ? $b['titulo'] : 'N/A', ENT_QUOTES, 'UTF-8') ?>
                </td>
                <td><span class="badge <?= $badge ?>"><?= $label ?></span></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <h3>⚡ Acciones Rápidas</h3>
      <p>Accesos directos a funciones principales</p>
    </div>
    <div class="card-body" style="display:flex; flex-direction:column; gap:0.8rem;">
      <a href="usuarios.php?action=nuevo" class="btn btn-secondary" style="justify-content:flex-start;"><span>👤</span> Registrar nuevo usuario</a>
      <a href="libros.php?action=nuevo" class="btn btn-secondary" style="justify-content:flex-start;"><span>📖</span> Agregar nuevo libro</a>
      <a href="prestamos.php?action=nuevo" class="btn btn-secondary" style="justify-content:flex-start;"><span>🔖</span> Crear préstamo</a>
      <a href="devoluciones.php" class="btn btn-secondary" style="justify-content:flex-start;"><span>↩️</span> Registrar devolución</a>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
renderLayout('Dashboard', 'dashboard', $content);
?>
