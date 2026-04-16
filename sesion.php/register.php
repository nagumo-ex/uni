<?php
// ============================================================
//  register.php — Crear nuevas credenciales
// ============================================================

require_once 'config.php';

// Redirigir si ya está autenticado
if (isset($_SESSION['auth_user'])) {
    header('Location: dashboard.php');
    exit;
}

// ── Procesar formulario de registro ─────────────────────────
$reg_error = '';
$reg_ok    = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u  = preg_replace('/\s+/', '', $_POST['username'] ?? '');
    $p  = preg_replace('/\s+/', '', $_POST['password'] ?? '');
    $p2 = preg_replace('/\s+/', '', $_POST['password2'] ?? '');
    $cap = isset($_POST['captcha_answer']) ? (int)$_POST['captcha_answer'] : null;

    // Validar CAPTCHA
    if ($cap === null || !isset($_SESSION['captcha_result']) || $cap !== $_SESSION['captcha_result']) {
        $reg_error = 'Respuesta del CAPTCHA incorrecta. Inténtalo de nuevo.';
    }
    // Regenerar captcha siempre tras POST
    unset($_SESSION['captcha_result'], $_SESSION['captcha_a'], $_SESSION['captcha_b']);

    if (!$reg_error) {
        if ($u === '' || $p === '') {
            $reg_error = 'Todos los campos son obligatorios.';
        } elseif (strlen($u) < 3) {
            $reg_error = 'El usuario debe tener al menos 3 caracteres.';
        } elseif (strlen($p) < 6) {
            $reg_error = 'La contraseña debe tener al menos 6 caracteres.';
        } elseif (!preg_match('/[a-zA-Z]/', $p) || !preg_match('/[0-9]/', $p) || !preg_match('/[A-Z]/', $p)) {
            $reg_error = 'La contraseña debe contener letras (mayúscula incluida) y números.';
        } elseif ($p !== $p2) {
            $reg_error = 'Las contraseñas no coinciden.';
        } elseif (user_exists($u)) {
            $reg_error = 'Ese nombre de usuario ya está en uso.';
        } else {
            create_user($u, $p);
            $reg_ok = true;
        }
    }
}

// ── Generar CAPTCHA ─────────────────────────────────────────
if (empty($_SESSION['captcha_a'])) {
    $_SESSION['captcha_a']      = rand(1, 15);
    $_SESSION['captcha_b']      = rand(1, 15);
    $_SESSION['captcha_result'] = $_SESSION['captcha_a'] + $_SESSION['captcha_b'];
}
$cap_a = $_SESSION['captcha_a'];
$cap_b = $_SESSION['captcha_b'];

// ── Refrescar CAPTCHA ───────────────────────────────────────
if (isset($_GET['refresh_captcha'])) {
    unset($_SESSION['captcha_a'], $_SESSION['captcha_b'], $_SESSION['captcha_result']);
    header('Location: register.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Crear cuenta</title>
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
      radial-gradient(ellipse 60% 40% at 20% 10%, rgba(99,102,241,0.08) 0%, transparent 70%),
      radial-gradient(ellipse 50% 40% at 85% 85%, rgba(0,229,160,0.06) 0%, transparent 70%);
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
    background: linear-gradient(90deg, transparent, rgba(99,102,241,0.5), transparent);
    border-radius: 16px 16px 0 0;
  }

  .header { text-align: center; margin-bottom: 2rem; }
  .icon {
    width: 54px; height: 54px; border-radius: 14px;
    background: rgba(99,102,241,0.12);
    border: 0.5px solid rgba(99,102,241,0.3);
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 24px; margin-bottom: 1rem;
  }
  .title { font-size: 20px; font-weight: 600; letter-spacing: -0.3px; }
  .subtitle { font-size: 12px; color: #6b7280; font-family: 'DM Mono', monospace; margin-top: 4px; }

  .field { margin-bottom: 1.25rem; }
  .label {
    font-size: 11px; font-weight: 600; color: #6b7280;
    letter-spacing: 0.08em; text-transform: uppercase;
    display: flex; align-items: center; gap: 6px;
    margin-bottom: 6px;
  }
  .label .req { color: #6366f1; font-size: 10px; }

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
  input[type="text"]:focus,
  input[type="password"]:focus {
    border-color: rgba(99,102,241,0.45);
    box-shadow: 0 0 0 3px rgba(99,102,241,0.07);
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

  /* Reglas en tiempo real */
  .rules { font-size: 11px; color: #6b7280; font-family: 'DM Mono', monospace; margin-top: 6px; line-height: 1.8; }
  .rule  { display: flex; align-items: center; gap: 5px; }
  .rule .dot { width: 4px; height: 4px; border-radius: 50%; background: #6b7280; flex-shrink: 0; transition: background 0.2s; }

  /* Fuerza de contraseña */
  .strength-wrap { margin-top: 8px; }
  .strength-bar {
    height: 3px; border-radius: 2px;
    background: #1e2330;
    overflow: hidden;
  }
  .strength-fill {
    height: 100%; border-radius: 2px;
    transition: width 0.3s, background 0.3s;
    width: 0%;
  }
  .strength-label { font-size: 10px; font-family: 'DM Mono', monospace; color: #6b7280; margin-top: 4px; }

  /* Alert */
  .alert {
    padding: 10px 14px; border-radius: 10px;
    font-size: 12px; font-family: 'DM Mono', monospace;
    margin-bottom: 1.25rem;
    background: rgba(255,68,102,0.1);
    border: 0.5px solid rgba(255,68,102,0.25);
    color: #ff4466;
  }
  .alert-ok {
    background: rgba(0,229,160,0.08);
    border: 0.5px solid rgba(0,229,160,0.25);
    color: #00e5a0;
  }

  /* CAPTCHA */
  .captcha-box {
    background: #0a0c11;
    border: 0.5px solid #1e2330;
    border-radius: 10px;
    padding: 12px 14px;
    display: flex; align-items: center; gap: 12px;
    margin-bottom: 1.25rem;
  }
  .captcha-question {
    font-family: 'DM Mono', monospace;
    font-size: 16px; font-weight: 500;
    color: #6366f1;
    letter-spacing: 0.05em;
    flex-shrink: 0; user-select: none;
  }
  .captcha-eq { color: #6b7280; font-size: 14px; }
  .captcha-input {
    flex: 1;
    background: transparent; border: none;
    border-bottom: 1px solid #2a2d35;
    border-radius: 0; padding: 4px 6px;
    font-family: 'DM Mono', monospace;
    font-size: 15px; color: #e8eaf2;
    outline: none; transition: border-color 0.2s;
  }
  .captcha-input:focus { border-bottom-color: #6366f1; }
  .captcha-input.input-error { border-bottom-color: #ff4466; }
  .captcha-refresh {
    background: none; border: none; cursor: pointer;
    color: #6b7280; font-size: 18px; padding: 4px;
    transition: color 0.2s, transform 0.3s;
    flex-shrink: 0;
  }
  .captcha-refresh:hover { color: #6366f1; transform: rotate(180deg); }

  /* Botones */
  .btn {
    width: 100%; padding: 13px;
    background: #6366f1;
    border: none; border-radius: 10px;
    font-family: 'Sora', sans-serif;
    font-size: 14px; font-weight: 600;
    color: #fff; cursor: pointer;
    transition: opacity 0.2s, transform 0.1s;
    letter-spacing: 0.02em;
    margin-top: 0.25rem;
  }
  .btn:hover { opacity: 0.88; }
  .btn:active { transform: scale(0.98); }
  .btn-outline {
    display: block; text-align: center; margin-top: 0.9rem;
    font-size: 12px; color: #6b7280;
    font-family: 'DM Mono', monospace;
    text-decoration: none;
    transition: color 0.2s;
  }
  .btn-outline:hover { color: #e8eaf2; }
  .btn-outline span { color: #6366f1; }
</style>
</head>
<body>
<div class="grid-bg"></div>

<div class="card">

  <div class="header">
    <div class="icon">✨</div>
    <div class="title">Crear cuenta</div>
    <div class="subtitle">registro de credenciales</div>
  </div>

  <?php if ($reg_ok): ?>
    <!-- ══ ÉXITO ══ -->
    <div class="alert alert-ok">✓ Cuenta creada exitosamente. Ya puedes iniciar sesión.</div>
    <a href="index.php" class="btn" style="display:block;text-align:center;text-decoration:none;line-height:1;">Ir al inicio de sesión</a>

  <?php else: ?>

    <?php if ($reg_error): ?>
      <div class="alert"><?php echo htmlspecialchars($reg_error); ?></div>
    <?php endif; ?>

    <form method="POST" action="register.php" novalidate>

      <!-- Usuario -->
      <div class="field">
        <div class="label">Usuario <span class="req">★ requerido</span></div>
        <div class="input-wrap">
          <input
            type="text"
            name="username"
            id="regUser"
            placeholder="minombre"
            autocomplete="off"
            spellcheck="false"
            class="<?php echo $reg_error ? 'input-error' : ''; ?>"
          >
        </div>
        <div class="rules">
          <div class="rule" id="ru-noEmpty"><div class="dot"></div><span>Campo no vacío</span></div>
          <div class="rule" id="ru-noSpace"><div class="dot"></div><span>Sin espacios</span></div>
          <div class="rule" id="ru-minLen"><div class="dot"></div><span>Mínimo 3 caracteres</span></div>
        </div>
      </div>

      <!-- Contraseña -->
      <div class="field">
        <div class="label">Contraseña <span class="req">★ requerida</span></div>
        <div class="input-wrap">
          <input
            type="password"
            name="password"
            id="regPass"
            placeholder="••••••••"
            autocomplete="off"
            class="<?php echo $reg_error ? 'input-error' : ''; ?>"
          >
          <button type="button" class="eye-btn" id="togglePass1" title="Mostrar/ocultar">👁</button>
        </div>
        <div class="rules">
          <div class="rule" id="rp-noEmpty"><div class="dot"></div><span>Campo no vacío</span></div>
          <div class="rule" id="rp-noSpace"><div class="dot"></div><span>Sin espacios</span></div>
          <div class="rule" id="rp-minLen"><div class="dot"></div><span>Mínimo 6 caracteres</span></div>
          <div class="rule" id="rp-hasLetter"><div class="dot"></div><span>Contiene letras (a-z)</span></div>
          <div class="rule" id="rp-hasUpper"><div class="dot"></div><span>Al menos una mayúscula (A-Z)</span></div>
          <div class="rule" id="rp-hasNum"><div class="dot"></div><span>Contiene números (0-9)</span></div>
        </div>
        <div class="strength-wrap">
          <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
          <div class="strength-label" id="strengthLabel">Seguridad de la contraseña</div>
        </div>
      </div>

      <!-- Confirmar contraseña -->
      <div class="field">
        <div class="label">Confirmar contraseña <span class="req">★ requerida</span></div>
        <div class="input-wrap">
          <input
            type="password"
            name="password2"
            id="regPass2"
            placeholder="••••••••"
            autocomplete="off"
            class="<?php echo $reg_error ? 'input-error' : ''; ?>"
          >
          <button type="button" class="eye-btn" id="togglePass2" title="Mostrar/ocultar">👁</button>
        </div>
        <div class="rules">
          <div class="rule" id="rp2-match"><div class="dot"></div><span>Las contraseñas coinciden</span></div>
        </div>
      </div>

      <!-- CAPTCHA -->
      <div class="field">
        <div class="label">Verificación <span class="req">★ requerida</span></div>
        <div class="captcha-box">
          <span class="captcha-question">
            <?php echo $cap_a . ' + ' . $cap_b; ?> <span class="captcha-eq">= ?</span>
          </span>
          <input
            type="number"
            name="captcha_answer"
            id="captchaInput"
            class="captcha-input"
            placeholder="…"
            autocomplete="off"
            min="0" max="99"
          >
          <button type="button" class="captcha-refresh" id="refreshCaptcha" title="Nueva pregunta">↻</button>
        </div>
      </div>

      <button type="submit" class="btn">Crear cuenta</button>
    </form>

    <a href="index.php" class="btn-outline">¿Ya tienes cuenta? <span>Iniciar sesión →</span></a>

  <?php endif; ?>

</div><!-- /card -->

<script>
  // ── Helpers ──────────────────────────────────────────────
  function setRule(id, ok) {
    const el = document.getElementById(id);
    if (!el) return;
    const dot  = el.querySelector('.dot');
    const span = el.querySelector('span');
    const color = ok ? '#6366f1' : '#6b7280';
    dot.style.background  = ok ? '#6366f1' : '#6b7280';
    span.style.color      = ok ? 'rgba(99,102,241,0.85)' : '#6b7280';
  }

  // ── Usuario ───────────────────────────────────────────────
  const regUser = document.getElementById('regUser');
  if (regUser) {
    regUser.addEventListener('input', e => {
      const v = e.target.value.replace(/\s+/g, '');
      if (e.target.value !== v) e.target.value = v;
      setRule('ru-noEmpty', v.length > 0);
      setRule('ru-noSpace', true); // se filtra automáticamente
      setRule('ru-minLen', v.length >= 3);
    });
    regUser.addEventListener('keydown', e => { if (e.key === ' ') e.preventDefault(); });
  }

  // ── Contraseña ────────────────────────────────────────────
  const regPass  = document.getElementById('regPass');
  const regPass2 = document.getElementById('regPass2');
  const fill     = document.getElementById('strengthFill');
  const lbl      = document.getElementById('strengthLabel');

  function calcStrength(v) {
    let score = 0;
    if (v.length >= 6)  score++;
    if (v.length >= 10) score++;
    if (/[a-z]/.test(v) && /[A-Z]/.test(v)) score++;
    if (/[0-9]/.test(v)) score++;
    if (/[^a-zA-Z0-9]/.test(v)) score++;
    return score;
  }

  const strengthColors = ['#ff4466','#ff4466','#ffaa00','#00e5a0','#6366f1','#6366f1'];
  const strengthNames  = ['','Muy débil','Débil','Buena','Fuerte','Muy fuerte'];

  function updatePassRules(v) {
    setRule('rp-noEmpty',   v.length > 0);
    setRule('rp-noSpace',   !/\s/.test(v));
    setRule('rp-minLen',    v.length >= 6);
    setRule('rp-hasLetter', /[a-z]/.test(v));
    setRule('rp-hasUpper',  /[A-Z]/.test(v));
    setRule('rp-hasNum',    /[0-9]/.test(v));
    // Fuerza
    const s = calcStrength(v);
    fill.style.width      = v.length === 0 ? '0%' : (s / 5 * 100) + '%';
    fill.style.background = strengthColors[s] || '#6b7280';
    lbl.textContent       = v.length === 0 ? 'Seguridad de la contraseña' : (strengthNames[s] || '');
    // Coincidencia
    if (regPass2 && regPass2.value.length > 0) {
      setRule('rp2-match', regPass2.value === v);
    }
  }

  if (regPass) {
    regPass.addEventListener('input', e => {
      const v = e.target.value.replace(/\s+/g, '');
      if (e.target.value !== v) e.target.value = v;
      updatePassRules(v);
    });
    regPass.addEventListener('keydown', e => { if (e.key === ' ') e.preventDefault(); });
  }

  if (regPass2) {
    regPass2.addEventListener('input', e => {
      const v  = e.target.value.replace(/\s+/g, '');
      if (e.target.value !== v) e.target.value = v;
      setRule('rp2-match', regPass && v === regPass.value && v.length > 0);
    });
    regPass2.addEventListener('keydown', e => { if (e.key === ' ') e.preventDefault(); });
  }

  // ── Ojos ──────────────────────────────────────────────────
  function setupToggle(btnId, inputEl) {
    const btn = document.getElementById(btnId);
    if (btn && inputEl) {
      btn.addEventListener('click', () => {
        inputEl.type = inputEl.type === 'password' ? 'text' : 'password';
        btn.textContent = inputEl.type === 'password' ? '👁' : '🙈';
      });
    }
  }
  setupToggle('togglePass1', regPass);
  setupToggle('togglePass2', regPass2);

  // ── CAPTCHA ───────────────────────────────────────────────
  document.getElementById('refreshCaptcha')?.addEventListener('click', () => {
    window.location.href = 'register.php?refresh_captcha=1';
  });
</script>
</body>
</html>
