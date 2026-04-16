<?php
// ============================================================
//  config.php — Configuración del sistema de login
// ============================================================

// Reglas de validación
define('MAX_ATTEMPTS',  3);    // intentos antes del bloqueo
define('LOCK_SECONDS',  120);  // segundos de bloqueo (2 minutos)

// Ruta al archivo de usuarios
define('USERS_FILE', __DIR__ . '/users.json');

// ── Helpers de usuarios ─────────────────────────────────────

function load_users(): array {
    if (!file_exists(USERS_FILE)) return [];
    $data = json_decode(file_get_contents(USERS_FILE), true);
    return is_array($data) ? $data : [];
}

function save_users(array $users): void {
    file_put_contents(USERS_FILE, json_encode($users, JSON_PRETTY_PRINT));
}

function user_exists(string $username): bool {
    return array_key_exists($username, load_users());
}

function verify_user(string $username, string $password): bool {
    $users = load_users();
    if (!isset($users[$username])) return false;
    return password_verify($password, $users[$username]);
}

function create_user(string $username, string $password): bool {
    $users = load_users();
    if (isset($users[$username])) return false; // ya existe
    $users[$username] = password_hash($password, PASSWORD_BCRYPT);
    save_users($users);
    return true;
}

// Iniciar sesión PHP
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
