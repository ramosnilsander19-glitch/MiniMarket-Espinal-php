<?php
function obtener_configuracion_base_datos()
{
    $urlBaseDatos = getenv('JAWSDB_URL') ?: getenv('CLEARDB_DATABASE_URL') ?: getenv('DATABASE_URL');

    if ($urlBaseDatos) {
        $partes = parse_url($urlBaseDatos);

        if ($partes !== false) {
            return [
                'host' => $partes['host'] ?? 'localhost',
                'user' => $partes['user'] ?? 'root',
                'password' => $partes['pass'] ?? '',
                'database' => isset($partes['path']) ? ltrim($partes['path'], '/') : 'minimarket_espinal',
                'port' => isset($partes['port']) ? (int) $partes['port'] : 3306,
            ];
        }
    }

    return [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'user' => getenv('DB_USER') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
        'database' => getenv('DB_NAME') ?: 'minimarket_espinal',
        'port' => getenv('DB_PORT') ? (int) getenv('DB_PORT') : 3306,
    ];
}

$configuracionBaseDatos = obtener_configuracion_base_datos();

$conexion = new mysqli(
    $configuracionBaseDatos['host'],
    $configuracionBaseDatos['user'],
    $configuracionBaseDatos['password'],
    $configuracionBaseDatos['database'],
    $configuracionBaseDatos['port']
);

if ($conexion->connect_error) {
    die('Error de conexion a la base de datos: ' . $conexion->connect_error);
}

$conexion->set_charset('utf8mb4');