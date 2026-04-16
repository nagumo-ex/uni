<?php
// ============================================================
//  index.php — Formulario de inicio de sesión
// ============================================================

require_once 'config.php';

// Redirigir si ya está autenticado
if (isset($_SESSION['auth_user'])) {
    header('Location: dashboard.php');
    exit;
}

// ── Refrescar CAPTCHA si se pide ───────────────────────────
if (isset($_GET['refresh_captcha'])) {
    unset($_SESSION['captcha_a'], $_SESSION['captcha_b'], $_SESSION['captcha_result']);
    header('Location: index.php');
    exit;
}

// ── Leer parámetros de error/estado ────────────────────────
$error     = isset($_GET['error'])             ? $_GET['error']             : '';
$attempts  = isset($_GET['attempts'])          ? (int)$_GET['attempts']     : ($_SESSION['attempts'] ?? 0);
$remaining = isset($_GET['remaining'])         ? (int)$_GET['remaining']    : 0;
$rem_att   = isset($_GET['remaining_attempts'])? (int)$_GET['remaining_attempts'] : (MAX_ATTEMPTS - $attempts);

// Verificar bloqueo activo
$is_locked = false;
$lock_remaining = 0;
if (isset($_SESSION['locked_at']) && $_SESSION['locked_at'] !== null) {
    $elapsed = time() - $_SESSION['locked_at'];
    if ($elapsed < LOCK_SECONDS) {
        $is_locked = true;
        $lock_remaining = LOCK_SECONDS - $elapsed;
    } else {
        $_SESSION['attempts']  = 0;
        $_SESSION['locked_at'] = null;
    }
}

// Mensajes de error
$error_messages = [
    'empty'       => 'Los campos usuario y contraseña son obligatorios.',
    'spaces'      => 'No se permiten espacios en ningún campo.',
    'pass_format' => 'La contraseña debe contener letras (mayúscula incluida) y números.',
    'captcha'     => 'CAPTCHA incorrecto. Intentos restantes: ' . $rem_att . '.',
    'invalid'     => 'Credenciales incorrectas. Intentos restantes: ' . $rem_att . '.',
    'locked'      => 'Demasiados intentos fallidos. Bloqueado por 2 minutos.',
];

// ── Generar CAPTCHA si no existe en sesión ──────────────────
if (empty($_SESSION['captcha_a'])) {
    $_SESSION['captcha_a']      = rand(1, 15);
    $_SESSION['captcha_b']      = rand(1, 15);
    $_SESSION['captcha_result'] = $_SESSION['captcha_a'] + $_SESSION['captcha_b'];
}
$cap_a = $_SESSION['captcha_a'];
$cap_b = $_SESSION['captcha_b'];

$error_msg = isset($error_messages[$error]) ? $error_messages[$error] : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Inicio de Sesión</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Sora:wght@300;400;600&display=swap');

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    min-height: 100vh;
    display: flex; align-items: center; justify-content: center;
    background: #0d0f14;
    font-family: 'Sora', sans-serif;
    color: #e8eaf2;
    padding: 1.5rem;
  }

  body::before {
    content: '';
    position: fixed; inset: 0;
    background:
      radial-gradient(ellipse 60% 40% at 80% 10%, rgba(0,229,160,0.07) 0%, transparent 70%),
      radial-gradient(ellipse 50% 40% at 10% 90%, rgba(0,153,255,0.06) 0%, transparent 70%);
    pointer-events: none;
  }

  .grid-bg {
    position: fixed; inset: 0;
    background-image:
      linear-gradient(rgba(255,255,255,0.015) 1px, transparent 1px),
      linear-gradient(90deg, rgba(255,255,255,0.015) 1px, transparent 1px);
    background-size: 32px 32px;
    pointer-events: none;
  }

  .card {
    background: #13161e;
    border: 0.5px solid #1e2330;
    border-radius: 16px;
    padding: 2.5rem 2rem;
    width: 100%; max-width: 420px;
    position: relative;
    z-index: 1;
  }

  .card::before {
    content: '';
    position: absolute; top: 0; left: 0; right: 0; height: 1px;
    background: linear-gradient(90deg, transparent, rgba(0,229,160,0.4), transparent);
    border-radius: 16px 16px 0 0;
  }

  /* ── Header ── */
  .header { text-align: center; margin-bottom: 2rem; }
  .icon {
    width: 54px; height: 54px; border-radius: 14px;
    background: rgba(0,229,160,0.1);
    border: 0.5px solid rgba(0,229,160,0.25);
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 24px; margin-bottom: 1rem;
  }
  .title { font-size: 20px; font-weight: 600; letter-spacing: -0.3px; }
  .subtitle { font-size: 12px; color: #6b7280; font-family: 'DM Mono', monospace; margin-top: 4px; }

  /* ── Campos ── */
  .field { margin-bottom: 1.25rem; }
  .label {
    font-size: 11px; font-weight: 600; color: #6b7280;
    letter-spacing: 0.08em; text-transform: uppercase;
    display: flex; align-items: center; gap: 6px;
    margin-bottom: 6px;
  }
  .label .req { color: #00e5a0; font-size: 10px; }

  .input-wrap { position: relative; }

  input[type="text"],
  input[type="password"] {
    width: 100%;
    background: #0a0c11;
    border: 0.5px solid #1e2330;
    border-radius: 10px;
    padding: 12px 44px 12px 14px;
    font-family: 'DM Mono', monospace;
    font-size: 14px; color: #e8eaf2;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
  }

  input:focus {
    border-color: rgba(0,229,160,0.4);
    box-shadow: 0 0 0 3px rgba(0,229,160,0.06);
  }

  input.input-error {
    border-color: rgba(255,68,102,0.5);
    box-shadow: 0 0 0 3px rgba(255,68,102,0.06);
  }

  .eye-btn {
    position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
    background: none; border: none; cursor: pointer;
    color: #6b7280; font-size: 16px; padding: 4px;
    transition: color 0.2s;
  }
  .eye-btn:hover { color: #e8eaf2; }

  /* ── Reglas en tiempo real ── */
  .rules { font-size: 11px; color: #6b7280; font-family: 'DM Mono', monospace; margin-top: 6px; line-height: 1.8; }
  .rule  { display: flex; align-items: center; gap: 5px; }
  .rule .dot { width: 4px; height: 4px; border-radius: 50%; background: #6b7280; flex-shrink: 0; }

  /* ── Intentos ── */
  .attempts-label { font-size: 11px; color: #6b7280; font-family: 'DM Mono', monospace; text-align: center; margin-bottom: 8px; }
  .dots { display: flex; align-items: center; justify-content: center; gap: 6px; margin-bottom: 1.25rem; }
  .dot-item { width: 10px; height: 10px; border-radius: 50%; background: #00e5a0; transition: background 0.3s, transform 0.2s; }
  .dot-item.used { background: #ff4466; transform: scale(0.8); }
  .dot-item.gone { background: #2a2d35; transform: scale(0.7); }

  /* ── Error global ── */
  .alert {
    padding: 10px 14px; border-radius: 10px;
    font-size: 12px; font-family: 'DM Mono', monospace;
    margin-bottom: 1.25rem;
    background: rgba(255,68,102,0.1);
    border: 0.5px solid rgba(255,68,102,0.25);
    color: #ff4466;
  }

  /* ── Bloqueo ── */
  .locked-view { text-align: center; }
  .locked-icon { font-size: 44px; margin-bottom: 0.75rem; }
  .locked-title { font-size: 16px; font-weight: 600; color: #ff4466; margin-bottom: 4px; }
  .locked-sub { font-size: 12px; color: #6b7280; font-family: 'DM Mono', monospace; }
  .timer { font-size: 36px; font-weight: 300; color: #ffaa00; font-family: 'DM Mono', monospace; margin: 1rem 0; }

  /* ── Botón ── */
  .btn {
    width: 100%; padding: 13px;
    background: #00e5a0;
    border: none; border-radius: 10px;
    font-family: 'Sora', sans-serif;
    font-size: 14px; font-weight: 600;
    color: #070a0f; cursor: pointer;
    transition: opacity 0.2s, transform 0.1s;
    letter-spacing: 0.02em;
    margin-top: 0.25rem;
  }
  .btn:hover { opacity: 0.88; }
  .btn:active { transform: scale(0.98); }

  /* ── CAPTCHA ── */
  .captcha-box {
    background: #0a0c11;
    border: 0.5px solid #1e2330;
    border-radius: 10px;
    padding: 12px 14px;
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 1.25rem;
  }
  .captcha-question {
    font-family: 'DM Mono', monospace;
    font-size: 16px;
    font-weight: 500;
    color: #00e5a0;
    letter-spacing: 0.05em;
    flex-shrink: 0;
    user-select: none;
  }
  .captcha-eq { color: #6b7280; font-size: 14px; }
  .captcha-input {
    flex: 1;
    background: transparent;
    border: none;
    border-bottom: 1px solid #2a2d35;
    border-radius: 0;
    padding: 4px 6px;
    font-family: 'DM Mono', monospace;
    font-size: 15px;
    color: #e8eaf2;
    outline: none;
    transition: border-color 0.2s;
  }
  .captcha-input:focus { border-bottom-color: #00e5a0; }
  .captcha-input.input-error { border-bottom-color: #ff4466; }
  .captcha-refresh {
    background: none; border: none; cursor: pointer;
    color: #6b7280; font-size: 18px; padding: 4px;
    transition: color 0.2s, transform 0.3s;
    flex-shrink: 0;
  }
  .captcha-refresh:hover { color: #00e5a0; transform: rotate(180deg); }

  /* ── Demo hint ── */
  .hint { font-size: 11px; color: #6b7280; font-family: 'DM Mono', monospace; text-align: center; margin-top: 1rem; }
  .hint strong { color: #00e5a0; }
</style>
</head>
<body>
<div class="grid-bg"></div>

<div class="card">

<?php if ($is_locked): ?>
<!-- ═══════════════ VISTA BLOQUEADA ═══════════════ -->
<div class="locked-view">
  <div class="locked-icon">🔒</div>
  <div class="locked-title">Cuenta bloqueada</div>
  <div class="locked-sub">Demasiados intentos fallidos</div>
  <div class="timer" id="timerDisplay">
    <?php printf('%02d:%02d', floor($lock_remaining / 60), $lock_remaining % 60); ?>
  </div>
  <div class="locked-sub">El acceso se restablecerá automáticamente</div>
</div>

<script>
  let secs = <?php echo (int)$lock_remaining; ?>;
  const disp = document.getElementById('timerDisplay');
  const tick = setInterval(() => {
    secs--;
    if (secs <= 0) { clearInterval(tick); location.reload(); return; }
    const m = Math.floor(secs / 60), s = secs % 60;
    disp.textContent = String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
  }, 1000);
</script>

<?php else: ?>
<!-- ═══════════════ VISTA FORMULARIO ═══════════════ -->
<div class="header">
  <div class="icon">🔐</div>
  <div class="title">Iniciar sesión</div>
  <div class="subtitle">credenciales requeridas</div>
</div>

<?php if ($error_msg !== ''): ?>
  <div class="alert"><?php echo htmlspecialchars($error_msg); ?></div>
<?php endif; ?>

<form method="POST" action="process.php" novalidate>

  <!-- Usuario -->
  <div class="field">
    <div class="label">
      Usuario <span class="req">★ requerido</span>
    </div>
    <div class="input-wrap">
      <input
        type="text"
        name="username"
        id="userInput"
        placeholder="usuario123"
        autocomplete="off"
        spellcheck="false"
        class="<?php echo in_array($error, ['empty','spaces','invalid']) ? 'input-error' : ''; ?>"
      >
    </div>
    <div class="rules" id="userRules">
      <div class="rule" id="r-noEmpty"><div class="dot"></div><span>Campo no vacío</span></div>
      <div class="rule" id="r-noSpace"><div class="dot"></div><span>Sin espacios</span></div>
    </div>
  </div>

  <!-- Contraseña -->
  <div class="field">
    <div class="label">
      Contraseña <span class="req">★ requerida</span>
    </div>
    <div class="input-wrap">
      <input
        type="password"
        name="password"
        id="passInput"
        placeholder="••••••••"
        autocomplete="off"
        class="<?php echo in_array($error, ['empty','spaces','invalid']) ? 'input-error' : ''; ?>"
      >
      <button type="button" class="eye-btn" id="togglePass" title="Mostrar/ocultar">👁</button>
    </div>
    <div class="rules" id="passRules">
      <div class="rule" id="r-pEmpty"><div class="dot"></div><span>Campo no vacío</span></div>
      <div class="rule" id="r-pSpace"><div class="dot"></div><span>Sin espacios</span></div>
      <div class="rule" id="r-pLetter"><div class="dot"></div><span>Contiene letras (a-z)</span></div>
      <div class="rule" id="r-pUpper"><div class="dot"></div><span>Al menos una mayúscula (A-Z)</span></div>
      <div class="rule" id="r-pNum"><div class="dot"></div><span>Contiene números (0-9)</span></div>
    </div>
  </div>

  <!-- Indicador de intentos -->
  <div class="attempts-label">
    <?php
      $att_used = min($attempts, MAX_ATTEMPTS);
      $rem_att2 = MAX_ATTEMPTS - $att_used;
      echo $rem_att2 . ' intento' . ($rem_att2 !== 1 ? 's' : '') . ' restante' . ($rem_att2 !== 1 ? 's' : '');
    ?>
  </div>
  <div class="dots">
    <?php for ($i = 0; $i < MAX_ATTEMPTS; $i++):
      $cls = $i < (MAX_ATTEMPTS - $att_used) ? '' : ($i < MAX_ATTEMPTS ? 'used' : 'gone');
    ?>
      <div class="dot-item <?php echo $cls; ?>"></div>
    <?php endfor; ?>
  </div>

  <!-- CAPTCHA -->
  <div class="field">
    <div class="label">Verificación <span class="req">★ requerida</span></div>
    <div class="captcha-box">
      <span class="captcha-question" id="capQuestion">
        <?php echo $cap_a . ' + ' . $cap_b; ?> <span class="captcha-eq">= ?</span>
      </span>
      <input
        type="number"
        name="captcha_answer"
        id="captchaInput"
        class="captcha-input <?php echo $error === 'captcha' ? 'input-error' : ''; ?>"
        placeholder="…"
        autocomplete="off"
        min="0" max="99"
      >
      <button type="button" class="captcha-refresh" id="refreshCaptcha" title="Nueva pregunta">↻</button>
    </div>
  </div>

  <button type="submit" class="btn">Ingresar</button>

</form>

<a href="register.php" style="display:block;text-align:center;margin-top:0.9rem;font-size:12px;color:#6b7280;font-family:'DM Mono',monospace;text-decoration:none;transition:color 0.2s;" onmouseover="this.style.color='#e8eaf2'" onmouseout="this.style.color='#6b7280'">¿No tienes cuenta? <span style="color:#00e5a0;">Crear credenciales →</span></a>

<?php endif; ?>

</div><!-- /card -->

<script>
  // ── Validación en tiempo real (JavaScript lado cliente) ──
  const SPECIAL = /[#@!$%^&*\-_+=?]/;

  function setRule(id, ok) {
    const el = document.getElementById(id);
    if (!el) return;
    const dot = el.querySelector('.dot');
    const span = el.querySelector('span');
    if (ok) {
      dot.style.background = '#00e5a0';
      span.style.color = 'rgba(0,229,160,0.8)';
    } else {
      dot.style.background = '#6b7280';
      span.style.color = '#6b7280';
    }
  }

  function updateRules(value, ids) {
    const notEmpty = value.length > 0;
    const noSpace  = !/\s/.test(value);
    setRule(ids[0], notEmpty);
    setRule(ids[1], notEmpty && noSpace);
  }

  const userInput = document.getElementById('userInput');
  const passInput = document.getElementById('passInput');
  const toggleBtn = document.getElementById('togglePass');

  if (userInput) {
    userInput.addEventListener('input', e => {
      const v = e.target.value.replace(/\s+/g, '');
      if (e.target.value !== v) e.target.value = v;
      updateRules(v, ['r-noEmpty','r-noSpace']);
    });
    userInput.addEventListener('keydown', e => { if (e.key === ' ') e.preventDefault(); });
  }

  if (passInput) {
    passInput.addEventListener('input', e => {
      const v = e.target.value.replace(/\s+/g, '');
      if (e.target.value !== v) e.target.value = v;
      setRule('r-pEmpty',  v.length > 0);
      setRule('r-pSpace',  !/\s/.test(v));
      setRule('r-pLetter', /[a-z]/.test(v));
      setRule('r-pUpper',  /[A-Z]/.test(v));
      setRule('r-pNum',    /[0-9]/.test(v));
    });
    passInput.addEventListener('keydown', e => { if (e.key === ' ') e.preventDefault(); });
  }

  if (toggleBtn && passInput) {
    toggleBtn.addEventListener('click', () => {
      passInput.type = passInput.type === 'password' ? 'text' : 'password';
      toggleBtn.textContent = passInput.type === 'password' ? '👁' : '🙈';
    });
  }

  // ── CAPTCHA: botón de refrescar (recarga la página para nuevo par) ──
  const refreshBtn = document.getElementById('refreshCaptcha');
  if (refreshBtn) {
    refreshBtn.addEventListener('click', () => {
      // Limpiamos el campo y pedimos al servidor un nuevo CAPTCHA via GET
      window.location.href = 'index.php?refresh_captcha=1';
    });
  }

  // ── Limpiar variable SPECIAL que ya no se usa en updateRules ──
  // (se deja declarada para compatibilidad pero updateRules ya no la llama)
</script>
</body>
</html>
