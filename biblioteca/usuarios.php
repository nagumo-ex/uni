<?php
require_once 'includes/db.php';
require_once 'includes/layout.php';

$action = $_GET['action'] ?? 'lista';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'crear') {
        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $cedula = trim($_POST['cedula'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');

        if (!$nombre || !$email || !$cedula) {
            setMessage('error', 'Por favor completa los campos obligatorios: Nombre, Email y Cédula.');
        } else {
            addRecord('usuarios', compact('nombre', 'email', 'telefono', 'cedula', 'direccion'));
            setMessage('success', "Usuario \"{$nombre}\" registrado exitosamente.");
            header('Location: usuarios.php');
            exit;
        }
    }
}

$usuarios = loadData('usuarios');
ob_start();

if ($action === 'nuevo'):
?>
<div class="card" style="max-width:720px;">
  <div class="card-header">
    <h3>👤 Registrar Nuevo Usuario</h3>
    <p>Completa el formulario para agregar un usuario al sistema</p>
  </div>
  <div class="card-body">
    <form method="POST">
      <input type="hidden" name="action" value="crear">
      <div class="form-grid">
        <div class="form-group">
          <label>Nombre completo *</label>
          <input type="text" name="nombre" placeholder="Ej: María García" required>
        </div>
        <div class="form-group">
          <label>Cédula / Documento *</label>
          <input type="text" name="cedula" placeholder="Ej: 1234567890" required>
        </div>
        <div class="form-group">
          <label>Email *</label>
          <input type="email" name="email" placeholder="usuario@correo.com" required>
        </div>
        <div class="form-group">
          <label>Teléfono</label>
          <input type="tel" name="telefono" placeholder="Ej: 310 000 0000">
        </div>
        <div class="form-group full">
          <label>Dirección</label>
          <input type="text" name="direccion" placeholder="Calle, barrio, ciudad">
        </div>
      </div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary">✓ Guardar usuario</button>
        <a href="usuarios.php" class="btn btn-secondary">Cancelar</a>
      </div>
    </form>
  </div>
</div>
<?php else: ?>
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
  <div>
    <p style="color:rgba(26,18,8,0.5); font-size:0.85rem;"><?= count($usuarios) ?> usuario(s) registrado(s)</p>
  </div>
  <a href="usuarios.php?action=nuevo" class="btn btn-primary">+ Nuevo usuario</a>
</div>

<div class="card">
  <div class="card-body" style="padding:0;">
    <?php if (empty($usuarios)): ?>
      <div class="empty">
        <div class="empty-icon">👤</div>
        <p>No hay usuarios registrados aún.</p>
        <a href="usuarios.php?action=nuevo" class="btn btn-primary" style="margin-top:1rem;">Registrar primer usuario</a>
      </div>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Nombre</th>
              <th>Cédula</th>
              <th>Email</th>
              <th>Teléfono</th>
              <th>Registrado</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (array_reverse($usuarios) as $u): ?>
              <tr>
                <td style="color:rgba(26,18,8,0.3); font-size:0.8rem;"><?= $u['id'] ?></td>
                <td style="font-weight:500;"><?= htmlspecialchars($u['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($u['cedula'], ENT_QUOTES, 'UTF-8') ?></td>
                <td style="color:rgba(26,18,8,0.6);"><?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($u['telefono'] ?: '—', ENT_QUOTES, 'UTF-8') ?></td>
                <td style="font-size:0.8rem; color:rgba(26,18,8,0.4);"><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
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
renderLayout('Usuarios', 'usuarios', $content);
?>
