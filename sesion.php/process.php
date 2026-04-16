<?php
// ============================================================
//  process.php — Procesa el formulario de login
// ============================================================

require_once 'config.php';

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// ── Inicializar contadores en sesión ────────────────────────
if (!isset($_SESSION['attempts']))  $_SESSION['attempts']  = 0;
if (!isset($_SESSION['locked_at'])) $_SESSION['locked_at'] = null;

// ── Verificar si la cuenta está bloqueada ───────────────────
if ($_SESSION['locked_at'] !== null) {
    $elapsed = time() - $_SESSION['locked_at'];

    if ($elapsed < LOCK_SECONDS) {
        $remaining = LOCK_SECONDS - $elapsed;
        header('Location: index.php?error=locked&remaining=' . $remaining);
        exit;
    } else {
        $_SESSION['attempts']  = 0;
        $_SESSION['locked_at'] = null;
    }
}

// ── Obtener y limpiar campos (sin espacios) ─────────────────
$username = isset($_POST['username']) ? preg_replace('/\s+/', '', $_POST['username']) : '';
$password = isset($_POST['password']) ? preg_replace('/\s+/', '', $_POST['password']) : '';

// ── Validar campos vacíos ───────────────────────────────────
if ($username === '' || $password === '') {
    header('Location: index.php?error=empty');
    exit;
}

// ── Validar que no contengan espacios (doble chequeo) ───────
if (preg_match('/\s/', $username) || preg_match('/\s/', $password)) {
    header('Location: index.php?error=spaces');
    exit;
}

// ── Validar que la contraseña tenga letras, mayúscula Y números ────
if (!preg_match('/[a-zA-Z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[A-Z]/', $password)) {
    header('Location: index.php?error=pass_format');
    exit;
}

// ── Verificar CAPTCHA ───────────────────────────────────────
$captcha_answer = isset($_POST['captcha_answer']) ? (int)$_POST['captcha_answer'] : null;
if ($captcha_answer === null || !isset($_SESSION['captcha_result']) || $captcha_answer !== $_SESSION['captcha_result']) {
    // Captcha incorrecto cuenta como intento fallido
    $_SESSION['attempts']++;
    unset($_SESSION['captcha_result'], $_SESSION['captcha_a'], $_SESSION['captcha_b']);

    if ($_SESSION['attempts'] >= MAX_ATTEMPTS) {
        $_SESSION['locked_at'] = time();
        header('Location: index.php?error=locked&remaining=' . LOCK_SECONDS);
    } else {
        $remaining_attempts = MAX_ATTEMPTS - $_SESSION['attempts'];
        header('Location: index.php?error=captcha&attempts=' . $_SESSION['attempts'] . '&remaining_attempts=' . $remaining_attempts);
    }
    exit;
}
// Regenerar CAPTCHA tras cada intento exitoso
unset($_SESSION['captcha_result'], $_SESSION['captcha_a'], $_SESSION['captcha_b']);

// ── Verificar credenciales contra JSON ──────────────────────
if (verify_user($username, $password)) {
    // ✓ Acceso concedido
    $_SESSION['attempts']  = 0;
    $_SESSION['locked_at'] = null;
    $_SESSION['auth_user'] = $username;
    $_SESSION['auth_time'] = time();
    header('Location: dashboard.php');
    exit;
} else {
    // ✗ Credenciales incorrectas
    $_SESSION['attempts']++;

    if ($_SESSION['attempts'] >= MAX_ATTEMPTS) {
        $_SESSION['locked_at'] = time();
        header('Location: index.php?error=locked&remaining=' . LOCK_SECONDS);
    } else {
        $remaining_attempts = MAX_ATTEMPTS - $_SESSION['attempts'];
        header('Location: index.php?error=invalid&attempts=' . $_SESSION['attempts'] . '&remaining_attempts=' . $remaining_attempts);
    }
    exit;
}
