<?php
require_once 'includes/db.php';
require_once 'includes/layout.php';

$action = $_GET['action'] ?? 'lista';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'crear') {
        $titulo = trim($_POST['titulo'] ?? '');
        $autor = trim($_POST['autor'] ?? '');
        $isbn = trim($_POST['isbn'] ?? '');
        $editorial = trim($_POST['editorial'] ?? '');
        $anio = trim($_POST['anio'] ?? '');
        $categoria = trim($_POST['categoria'] ?? '');
        $cantidad = (int)($_POST['cantidad'] ?? 1);
        $descripcion = trim($_POST['descripcion'] ?? '');

        if (!$titulo || !$autor) {
            setMessage('error', 'El título y el autor son obligatorios.');
        } else {
            addRecord('libros', [
                'titulo' => $titulo,
                'autor' => $autor,
                'isbn' => $isbn,
                'editorial' => $editorial,
                'anio' => $anio,
                'categoria' => $categoria,
                'cantidad' => $cantidad,
                'disponibles' => $cantidad,
                'descripcion' => $descripcion
            ]);

            setMessage('success', "Libro \"{$titulo}\" agregado al catálogo exitosamente.");
            header('Location: libros.php');
            exit;
        }
    }
}

$libros = loadData('libros');
$categorias = ['Ciencias', 'Historia', 'Literatura', 'Tecnología', 'Arte', 'Filosofía', 'Economía', 'Derecho', 'Medicina', 'Otro'];

ob_start();

if ($action === 'nuevo'):
?>
<div class="card" style="max-width:760px;">
  <div class="card-header">
    <h3>📖 Agregar Nuevo Libro</h3>
    <p>Registra un libro en el catálogo de la biblioteca</p>
  </div>
  <div class="card-body">
    <form method="POST">
      <input type="hidden" name="action" value="crear">
      <div class="form-grid">
        <div class="form-group full">
          <label>Título del libro *</label>
          <input type="text" name="titulo" placeholder="Título completo del libro" required>
        </div>
        <div class="form-group">
          <label>Autor *</label>
          <input type="text" name="autor" placeholder="Nombre del autor" required>
        </div>
        <div class="form-group">
          <label>ISBN</label>
          <input type="text" name="isbn" placeholder="978-X-XXXX-XXXX-X">
        </div>
        <div class="form-group">
          <label>Editorial</label>
          <input type="text" name="editorial" placeholder="Ej: Penguin Books">
        </div>
        <div class="form-group">
          <label>Año de publicación</label>
          <input type="number" name="anio" placeholder="Ej: 2020" min="1800" max="2025">
        </div>
        <div class="form-group">
          <label>Categoría</label>
          <select name="categoria">
            <option value="">— Seleccionar —</option>
            <?php foreach ($categorias as $cat): ?>
              <option value="<?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Cantidad de ejemplares</label>
          <input type="number" name="cantidad" value="1" min="1" max="999">
        </div>
        <div class="form-group full">
          <label>Descripción / Sinopsis</label>
          <textarea name="descripcion" placeholder="Breve descripción del libro..."></textarea>
        </div>
      </div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary">✓ Guardar libro</button>
        <a href="libros.php" class="btn btn-secondary">Cancelar</a>
      </div>
    </form>
  </div>
</div>
<?php else: ?>
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
  <p style="color:rgba(26,18,8,0.5); font-size:0.85rem;"><?= count($libros) ?> libro(s) en catálogo</p>
  <a href="libros.php?action=nuevo" class="btn btn-primary">+ Nuevo libro</a>
</div>

<div class="card">
  <div class="card-body" style="padding:0;">
    <?php if (empty($libros)): ?>
      <div class="empty">
        <div class="empty-icon">📚</div>
        <p>El catálogo está vacío. Agrega el primer libro.</p>
        <a href="libros.php?action=nuevo" class="btn btn-primary" style="margin-top:1rem;">Agregar libro</a>
      </div>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Título</th>
              <th>Autor</th>
              <th>Categoría</th>
              <th>Editorial</th>
              <th>Año</th>
              <th>Disponibles</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach (array_reverse($libros) as $b): ?>
              <?php
              $disponibles = $b['disponibles'] ?? $b['cantidad'];
              $badgeClass = $disponibles > 0 ? 'badge-active' : 'badge-returned';
              ?>
              <tr>
                <td style="color:rgba(26,18,8,0.3); font-size:0.8rem;"><?= $b['id'] ?></td>
                <td style="font-weight:500; max-width:200px;"><?= htmlspecialchars($b['titulo'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($b['autor'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><span class="badge badge-returned"><?= htmlspecialchars($b['categoria'] ?: '—', ENT_QUOTES, 'UTF-8') ?></span></td>
                <td style="font-size:0.83rem;"><?= htmlspecialchars($b['editorial'] ?: '—', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($b['anio'] ?: '—', ENT_QUOTES, 'UTF-8') ?></td>
                <td><span class="badge <?= $badgeClass ?>"><?= $disponibles ?> / <?= $b['cantidad'] ?></span></td>
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
renderLayout('Libros', 'libros', $content);
?>
