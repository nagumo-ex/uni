<?php
require_once 'includes/db.php';
require_once 'includes/layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'devolver') {
        $prestamo_id = (int)($_POST['prestamo_id'] ?? 0);
        $fecha_real = $_POST['fecha_real'] ?? date('Y-m-d');
        $estado_libro = $_POST['estado_libro'] ?? 'bueno';
        $notas = trim($_POST['notas'] ?? '');

        $prestamo = findById('prestamos', $prestamo_id);

        if (!$prestamo || $prestamo['estado'] === 'devuelto') {
            setMessage('error', 'Préstamo inválido o ya fue devuelto.');
        } else {
            updateRecord('prestamos', $prestamo_id, [
                'estado' => 'devuelto',
                'fecha_devolucion_real' => $fecha_real,
                'estado_libro_devuelto' => $estado_libro,
                'notas_devolucion' => $notas
            ]);

            $libro = findById('libros', $prestamo['libro_id']);
            if ($libro) {
                $disponibles = ($libro['disponibles'] ?? $libro['cantidad']) + 1;
                updateRecord('libros', $libro['id'], [
                    'disponibles' => min($disponibles, $libro['cantidad'])
                ]);
            }

            setMessage('success', 'Devolución registrada correctamente. El libro está disponible nuevamente.');
            header('Location: devoluciones.php');
            exit;
        }
    }
}

$prestamos = loadData('prestamos');

$prestamosActivos = array_filter($prestamos, function ($p) {
    return $p['estado'] === 'activo';
});

$prestamosDevueltos = array_filter($prestamos, function ($p) {
    return $p['estado'] === 'devuelto';
});

ob_start();
?>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:1.8rem;">
  <div>
    <div class="card">
      <div class="card-header">
        <h3>↩️ Registrar Devolución</h3>
        <p>Selecciona el préstamo activo a devolver</p>
      </div>
      <div class="card-body">
        <?php if (empty($prestamosActivos)): ?>
          <div class="empty">
            <div class="empty-icon">✅</div>
            <p>No hay préstamos activos pendientes de devolución.</p>
            <a href="prestamos.php?action=nuevo" class="btn btn-secondary" style="margin-top:1rem;">Crear préstamo</a>
          </div>
        <?php else: ?>
          <form method="POST">
            <input type="hidden" name="action" value="devolver">
            <div class="form-grid" style="grid-template-columns:1fr;">
              <div class="form-group">
                <label>Préstamo activo *</label>
                <select name="prestamo_id" required>
                  <option value="">— Seleccionar préstamo —</option>
                  <?php foreach ($prestamosActivos as $p): ?>
                    <?php
                    $u = findById('usuarios', $p['usuario_id']);
                    $b = findById('libros', $p['libro_id']);
                    $vencido = $p['fecha_devolucion_esperada'] < date('Y-m-d') ? ' ⚠️ VENCIDO' : '';
                    ?>
                    <option value="<?= $p['id'] ?>">
                      #<?= $p['id'] ?> — <?= htmlspecialchars($u ? $u['nombre'] : '?', ENT_QUOTES, 'UTF-8') ?>: "<?= htmlspecialchars($b ? mb_substr($b['titulo'], 0, 25) : '?', ENT_QUOTES, 'UTF-8') ?>" (<?= date('d/m/Y', strtotime($p['fecha_devolucion_esperada'])) ?>)<?= $vencido ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="form-group">
                <label>Fecha de devolución real</label>
                <input type="date" name="fecha_real" value="<?= date('Y-m-d') ?>">
              </div>

              <div class="form-group">
                <label>Estado del libro devuelto</label>
                <select name="estado_libro">
                  <option value="bueno">Buen estado</option>
                  <option value="regular">Estado regular</option>
                  <option value="dañado">Dañado</option>
                  <option value="perdido">Perdido</option>
                </select>
              </div>

              <div class="form-group">
                <label>Notas de devolución</label>
                <textarea name="notas" placeholder="Observaciones sobre la devolución..."></textarea>
              </div>
            </div>

            <div class="form-actions">
              <button type="submit" class="btn btn-primary">↩ Registrar devolución</button>
            </div>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div>
    <div class="card">
      <div class="card-header">
        <h3>📋 Historial de Devoluciones</h3>
        <p><?= count($prestamosDevueltos) ?> devolución(es) registrada(s)</p>
      </div>
      <div class="card-body" style="padding:0; max-height:520px; overflow-y:auto;">
        <?php if (empty($prestamosDevueltos)): ?>
          <div class="empty"><div class="empty-icon">📋</div><p>Sin historial aún</p></div>
        <?php else: ?>
          <?php foreach (array_reverse($prestamosDevueltos) as $p): ?>
            <?php
            $u = findById('usuarios', $p['usuario_id']);
            $b = findById('libros', $p['libro_id']);
            $onTime = $p['fecha_devolucion_real'] <= $p['fecha_devolucion_esperada'];
            $estadoLib = $p['estado_libro_devuelto'] ?? 'bueno';
            ?>
            <div style="padding:1rem 1.4rem; border-bottom:1px solid rgba(200,146,42,0.08);">
              <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div>
                  <div style="font-weight:500; font-size:0.9rem;"><?= htmlspecialchars($b ? $b['titulo'] : 'N/A', ENT_QUOTES, 'UTF-8') ?></div>
                  <div style="font-size:0.78rem; color:rgba(26,18,8,0.5); margin-top:0.2rem;">👤 <?= htmlspecialchars($u ? $u['nombre'] : 'N/A', ENT_QUOTES, 'UTF-8') ?></div>
                </div>
                <div style="text-align:right;">
                  <span class="badge <?= $onTime ? 'badge-active' : 'badge-overdue' ?>" style="font-size:0.68rem;"><?= $onTime ? 'A tiempo' : 'Tardía' ?></span>
                  <div style="font-size:0.72rem; color:rgba(26,18,8,0.4); margin-top:0.3rem;"><?= !empty($p['fecha_devolucion_real']) ? date('d/m/Y', strtotime($p['fecha_devolucion_real'])) : '—' ?></div>
                </div>
              </div>
              <div style="margin-top:0.5rem; font-size:0.75rem; color:rgba(26,18,8,0.4);">
                Libro: <strong><?= htmlspecialchars($estadoLib, ENT_QUOTES, 'UTF-8') ?></strong>
                <?php if (!empty($p['notas_devolucion'])): ?>
                  · <?= htmlspecialchars($p['notas_devolucion'], ENT_QUOTES, 'UTF-8') ?>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
renderLayout('Devoluciones', 'devoluciones', $content);
?>
