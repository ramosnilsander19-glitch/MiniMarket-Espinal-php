<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/cart.php';

define('CORREO_ADMIN_PRINCIPAL', 'minimarketespinal@gmail.com');

function usuario_autenticado()
{
    return isset($_SESSION['usuario']);
}

function usuario_actual()
{
    return $_SESSION['usuario'] ?? null;
}

function rol_actual()
{
    return $_SESSION['usuario']['rol'] ?? 'cliente';
}

function es_admin()
{
    return rol_actual() === 'admin';
}

function es_admin_principal($correo)
{
    return strcasecmp($correo, CORREO_ADMIN_PRINCIPAL) === 0;
}

function determinar_rol_usuario($correo, $rol = null)
{
    if ($rol === 'admin') {
        return 'admin';
    }

    return 'cliente';
}

function columna_rol_usuarios_disponible($conexion)
{
    $resultado = $conexion->query("SHOW COLUMNS FROM usuarios LIKE 'rol'");

    if (!$resultado) {
        return false;
    }

    $disponible = $resultado->num_rows > 0;
    $resultado->free();

    return $disponible;
}

function redirigir_si_no_autenticado()
{
    if (!usuario_autenticado()) {
        header('Location: login.php');
        exit;
    }
}

function redirigir_si_autenticado()
{
    if (usuario_autenticado()) {
        header('Location: panel.php');
        exit;
    }
}

function redirigir_si_no_admin()
{
    if (!usuario_autenticado()) {
        header('Location: login.php');
        exit;
    }

    if (!es_admin()) {
        header('Location: panel.php');
        exit;
    }
}