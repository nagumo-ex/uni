<?php
require_once 'includes/db.php';
require_once 'includes/layout.php';

$action = $_GET['action'] ?? 'lista';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'crear') {
        $usuario_id = (int)($_POST['usuario_id'] ?? 0);
        $libro_id = (int)($_POST['libro_id'] ?? 0);
        $fecha_prestamo = $_POST['fecha_prestamo'] ?? date('Y-m-d');
        $fecha_devolucion = $_POST['fecha_devolucion'] ?? '';
        $observaciones = trim($_POST['observaciones'] ?? '');

        if (!$usuario_id || !$libro_id || !$fecha_devolucion) {
            setMessage('error', 'Selecciona un usuario, un libro y la fecha de devolución esperada.');
        } else {
            $libro = findById('libros', $libro_id);

            if (!$libro) {
                setMessage('error', 'El libro seleccionado no existe.');
            } else {
                $disponibles = $libro['disponibles'] ?? $libro['cantidad'];

                if ($disponibles <= 0) {
                    setMessage('error', 'Este libro no tiene ejemplares disponibles.');
                } else {
                    addRecord('prestamos', [
                        'usuario_id' => $usuario_id,
                        'libro_id' => $libro_id,
                        'fecha_prestamo' => $fecha_prestamo,
                        'fecha_devolucion_esperada' => $fecha_devolucion,
                        'fecha_devolucion_real' => null,
                        'estado' => 'activo',
                        'observaciones' => $observaciones
                    ]);

                    updateRecord('libros', $libro_id, [
                        'disponibles' => $disponibles - 1
                    ]);

                    setMessage('success', 'Préstamo registrado correctamente.');
                    header('Location: prestamos.php');
                    exit;
                }
            }
        }
    }
}

$prestamos = loadData('prestamos');
$usuarios = loadData('usuarios');
$libros = loadData('libros');

$librosDisponibles = array_filter($libros, function ($b) {
    return ($b['disponibles'] ?? $b['cantidad']) > 0;
});

ob_start();

if ($action === 'nuevo'):
?>
<div class="card" style="max-width:700px;">
  <div class="card-header">
    <h3>🔖 Registrar Préstamo</h3>
    <p>Asocia un libro a un usuario con fecha de devolución</p>
  </div>
  <div class="card-body">
    <?php if (empty($usuarios) || empty($librosDisponibles)): ?>
      <div class="empty">
        <div class="empty-icon">⚠️</div>
        <p>
          <?php if (empty($usuarios)): ?>
            No hay usuarios registrados. <a href="usuarios.php?action=nuevo">Crear usuario →</a>
          <?php else: ?>
            No hay libros disponibles para préstamo. <a href="libros.php?action=nuevo">Agregar libro →</a>
          <?php endif; ?>
        </p>
      </div>
    <?php else: ?>
      <form method="POST">
        <input type="hidden" name="action" value="crear">
        <div class="form-grid">
          <div class="form-group full">
            <label>Usuario *</label>
            <select name="usuario_id" required>
              <option value="">— Seleccionar usuario —</option>
              <?php foreach ($usuarios as $u): ?>
                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['nombre'], ENT_QUOTES, 'UTF-8') ?> — <?= htmlspecialchars($u['cedula'], ENT_QUOTES, 'UTF-8') ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group full">
            <label>Libro *</label>
            <select name="libro_id" required>
              <option value="">— Seleccionar libro disponible —</option>
              <?php foreach ($librosDisponibles as $b): ?>
                <?php $disp = $b['disponibles'] ?? $b['cantidad']; ?>
                <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['titulo'], ENT_QUOTES, 'UTF-8') ?> — <?= htmlspecialchars($b['autor'], ENT_QUOTES, 'UTF-8') ?> (<?= $disp ?> disp.)</option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label>Fecha de préstamo</label>
            <input type="date" name="fecha_prestamo" value="<?= date('Y-m-d') ?>">
          </div>

          <div class="form-group">
            <label>Fecha de devolución esperada *</label>
            <input type="date" name="fecha_devolucion" value="<?= date('Y-m-d', strtotime('+15 days')) ?>" required>
          </div>

          <div class="form-group full">
            <label>Observaciones</label>
            <textarea name="observaciones" placeholder="Notas adicionales sobre el préstamo..."></textarea>
          </div>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary">✓ Registrar préstamo</button>
          <a href="prestamos.php" class="btn btn-secondary">Cancelar</a>
        </div>
      </form>
    <?php endif; ?>
  </div>
</div>
<?php else: ?>
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
  <p style="color:rgba(26,18,8,0.5); font-size:0.85rem;"><?= count($prestamos) ?> préstamo(s) total</p>
  <a href="prestamos.php?action=nuevo" class="btn btn-primary">+ Nuevo préstamo</a>
</div>

<div class="card">
  <div class="card-body" style="padding:0;">
    <?php if (empty($prestamos)): ?>
      <div class="empty">
        <div class="empty-icon">🔖</div>
        <p>No hay préstamos registrados.</p>
        <a href="prestamos.php?action=nuevo" class="btn btn-primary" style="margin-top:1rem;">Crear préstamo</a>
      </div>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Usuario</th>
              <th>Libro</th>
              <th>F. Préstamo</th>
              <th>F. Esperada</th>
              <th>F. Devolución</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (array_reverse($prestamos) as $p): ?>
              <?php
              $u = findById('usuarios', $p['usuario_id']);
              $b = findById('libros', $p['libro_id']);
              $vencido = $p['estado'] === 'activo' && $p['fecha_devolucion_esperada'] < date('Y-m-d');
              $badgeClass = $p['estado'] === 'devuelto' ? 'badge-returned' : ($vencido ? 'badge-overdue' : 'badge-active');
              $label = $p['estado'] === 'devuelto' ? 'Devuelto' : ($vencido ? 'Vencido' : 'Activo');
              ?>
              <tr>
                <td style="color:rgba(26,18,8,0.3); font-size:0.8rem;"><?= $p['id'] ?></td>
                <td style="font-weight:500;"><?= htmlspecialchars($u ? $u['nombre'] : 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                <td style="max-width:160px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?= htmlspecialchars($b ? $b['titulo'] : 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                <td style="font-size:0.82rem;"><?= date('d/m/Y', strtotime($p['fecha_prestamo'])) ?></td>
                <td style="font-size:0.82rem;"><?= date('d/m/Y', strtotime($p['fecha_devolucion_esperada'])) ?></td>
                <td style="font-size:0.82rem;"><?= !empty($p['fecha_devolucion_real']) ? date('d/m/Y', strtotime($p['fecha_devolucion_real'])) : '—' ?></td>
                <td><span class="badge <?= $badgeClass ?>"><?= $label ?></span></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php
endif;

$content = ob_get_clean();
renderLayout('Préstamos', 'prestamos', $content);
?>
