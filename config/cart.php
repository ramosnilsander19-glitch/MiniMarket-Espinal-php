<?php
function obtener_carrito()
{
    if (!isset($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) {
        $_SESSION['carrito'] = [];
    }

    return $_SESSION['carrito'];
}

function guardar_carrito($carrito)
{
    $_SESSION['carrito'] = $carrito;
}

function cantidad_total_carrito()
{
    $carrito = obtener_carrito();
    $total = 0;

    foreach ($carrito as $item) {
        $total += (int) ($item['cantidad'] ?? 0);
    }

    return $total;
}

function total_carrito()
{
    $carrito = obtener_carrito();
    $total = 0;

    foreach ($carrito as $item) {
        $total += ((float) ($item['precio'] ?? 0)) * ((int) ($item['cantidad'] ?? 0));
    }

    return $total;
}

function agregar_al_carrito($producto)
{
    $carrito = obtener_carrito();
    $productoId = (int) $producto['id'];

    if (isset($carrito[$productoId])) {
        $carrito[$productoId]['cantidad']++;
    } else {
        $carrito[$productoId] = [
            'id' => $productoId,
            'nombre' => $producto['nombre'],
            'precio' => (float) $producto['precio'],
            'imagen' => $producto['imagen'],
            'cantidad' => 1,
        ];
    }

    guardar_carrito($carrito);
}

function actualizar_cantidad_carrito($productoId, $cantidad)
{
    $carrito = obtener_carrito();
    $productoId = (int) $productoId;
    $cantidad = (int) $cantidad;

    if (!isset($carrito[$productoId])) {
        return;
    }

    if ($cantidad <= 0) {
        unset($carrito[$productoId]);
    } else {
        $carrito[$productoId]['cantidad'] = $cantidad;
    }

    guardar_carrito($carrito);
}

function eliminar_del_carrito($productoId)
{
    $carrito = obtener_carrito();
    $productoId = (int) $productoId;

    if (isset($carrito[$productoId])) {
        unset($carrito[$productoId]);
        guardar_carrito($carrito);
    }
}

function vaciar_carrito()
{
    $_SESSION['carrito'] = [];
}