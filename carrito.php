<?php
require_once 'sistema/acceso.php';

$mensajeExito = '';
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $productoId = (int) ($_POST['producto_id'] ?? 0);

    if ($accion === 'actualizar') {
        $cantidad = (int) ($_POST['cantidad'] ?? 1);
        actualizar_cantidad_carrito($productoId, $cantidad);
        $mensajeExito = 'Carrito actualizado correctamente.';
    }

    if ($accion === 'eliminar') {
        eliminar_del_carrito($productoId);
        $mensajeExito = 'Producto eliminado del carrito.';
    }

    if ($accion === 'vaciar') {
        vaciar_carrito();
        $mensajeExito = 'Carrito vaciado correctamente.';
    }

    if ($accion === 'finalizar') {
        if (!usuario_autenticado()) {
            $errores[] = 'Debes iniciar sesión para finalizar la compra.';
        } else {
            $carritoActual = obtener_carrito();

            if (!$carritoActual) {
                $errores[] = 'El carrito está vacío.';
            } else {
                require_once 'sistema/conexion.php';

                $tablaCompras = $conexion->query("SHOW TABLES LIKE 'compras'");
                $tablaDetalles = $conexion->query("SHOW TABLES LIKE 'compra_detalles'");
                $tieneCompras = $tablaCompras && $tablaCompras->num_rows > 0;
                $tieneDetalles = $tablaDetalles && $tablaDetalles->num_rows > 0;

                if ($tablaCompras) {
                    $tablaCompras->free();
                }

                if ($tablaDetalles) {
                    $tablaDetalles->free();
                }

                if (!$tieneCompras || !$tieneDetalles) {
                    $errores[] = 'Debes ejecutar el archivo sql/actualizar_compras.sql para habilitar el guardado de compras.';
                } else {
                    $usuario = usuario_actual();
                    $totalCompra = total_carrito();

                    $conexion->begin_transaction();

                    try {
                        $consultaCompra = $conexion->prepare('INSERT INTO compras (usuario_id, total, estado) VALUES (?, ?, ?)');

                        if (!$consultaCompra) {
                            throw new Exception('No se pudo preparar la compra.');
                        }

                        $estadoCompra = 'completada';
                        $consultaCompra->bind_param('ids', $usuario['id'], $totalCompra, $estadoCompra);

                        if (!$consultaCompra->execute()) {
                            throw new Exception('No se pudo guardar la compra.');
                        }

                        $compraId = $conexion->insert_id;
                        $consultaCompra->close();

                        $consultaDetalle = $conexion->prepare('INSERT INTO compra_detalles (compra_id, producto_id, producto_nombre, precio_unitario, cantidad, subtotal) VALUES (?, ?, ?, ?, ?, ?)');

                        if (!$consultaDetalle) {
                            throw new Exception('No se pudo preparar el detalle de compra.');
                        }

                        foreach ($carritoActual as $item) {
                            $subtotal = ((float) $item['precio']) * ((int) $item['cantidad']);
                            $consultaDetalle->bind_param(
                                'iisdid',
                                $compraId,
                                $item['id'],
                                $item['nombre'],
                                $item['precio'],
                                $item['cantidad'],
                                $subtotal
                            );

                            if (!$consultaDetalle->execute()) {
                                throw new Exception('No se pudo guardar el detalle de la compra.');
                            }
                        }

                        $consultaDetalle->close();
                        $conexion->commit();
                        vaciar_carrito();
                        $mensajeExito = 'Compra finalizada correctamente. Número de compra: ' . $compraId . '.';
                    } catch (Exception $exception) {
                        $conexion->rollback();
                        $errores[] = $exception->getMessage();
                    }
                }

                $conexion->close();
            }
        }
    }
}

$carrito = obtener_carrito();
$total = total_carrito();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Carrito | Minimarket Espinal</title>
<link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
<header>
<h1><a href="index.php" class="brand-link">Minimarket Espinal</a></h1>

<nav>
<a href="productos.php">Productos</a>
<a href="carrito.php">Carrito (<?php echo cantidad_total_carrito(); ?>)</a>
<a href="contactos.php">Contacto</a>
<a href="nosotros.php">Nosotros</a>
<?php if (usuario_autenticado()): ?>
<a href="panel.php">Mi panel</a>
<?php if (es_admin()): ?>
<a href="admin.php">Administración</a>
<?php endif; ?>
<a href="logout.php">Cerrar sesión</a>
<?php else: ?>
<a href="login.php">Ingresar</a>
<a href="registro.php">Registrarse</a>
<?php endif; ?>
</nav>

</header>

<section class="carrito-page">
<div class="carrito-header">
<h2>Carrito de compra</h2>
<p>Revisa tus productos seleccionados antes de continuar.</p>
</div>

<?php if ($errores): ?>
<div class="alerta alerta-error carrito-alerta">
<ul>
<?php foreach ($errores as $error): ?>
<li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
<?php endforeach; ?>
</ul>
</div>
<?php endif; ?>

<?php if ($mensajeExito !== ''): ?>
<div class="alerta alerta-exito carrito-alerta">
<p><?php echo htmlspecialchars($mensajeExito, ENT_QUOTES, 'UTF-8'); ?></p>
</div>
<?php endif; ?>

<?php if ($carrito): ?>
<div class="carrito-card">
<div class="tabla-wrap">
<table class="carrito-table">
<thead>
<tr>
<th>Producto</th>
<th>Precio</th>
<th>Cantidad</th>
<th>Subtotal</th>
<th>Acción</th>
</tr>
</thead>
<tbody>
<?php foreach ($carrito as $item): ?>
<tr>
<td>
<div class="carrito-producto">
<img src="img/<?php echo htmlspecialchars($item['imagen'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($item['nombre'], ENT_QUOTES, 'UTF-8'); ?>">
<span><?php echo htmlspecialchars($item['nombre'], ENT_QUOTES, 'UTF-8'); ?></span>
</div>
</td>
<td>RD$ <?php echo htmlspecialchars(number_format((float) $item['precio'], 2), ENT_QUOTES, 'UTF-8'); ?></td>
<td>
<form action="carrito.php" method="post" class="carrito-inline-form">
<input type="hidden" name="accion" value="actualizar">
<input type="hidden" name="producto_id" value="<?php echo htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8'); ?>">
<input type="number" name="cantidad" min="1" value="<?php echo htmlspecialchars($item['cantidad'], ENT_QUOTES, 'UTF-8'); ?>" class="carrito-cantidad">
<button type="submit" class="admin-action-button">Actualizar</button>
</form>
</td>
<td>RD$ <?php echo htmlspecialchars(number_format(((float) $item['precio']) * ((int) $item['cantidad']), 2), ENT_QUOTES, 'UTF-8'); ?></td>
<td>
<form action="carrito.php" method="post" class="carrito-inline-form">
<input type="hidden" name="accion" value="eliminar">
<input type="hidden" name="producto_id" value="<?php echo htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8'); ?>">
<button type="submit" class="admin-danger-button">Eliminar</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<div class="carrito-resumen">
<p><strong>Total:</strong> RD$ <?php echo htmlspecialchars(number_format($total, 2), ENT_QUOTES, 'UTF-8'); ?></p>
<div class="carrito-resumen-acciones">
<a href="productos.php" class="admin-link-button">Seguir comprando</a>
<?php if (usuario_autenticado()): ?>
<form action="carrito.php" method="post" class="carrito-inline-form">
<input type="hidden" name="accion" value="finalizar">
<button type="submit" class="carrito-finalizar-boton">Finalizar compra</button>
</form>
<?php else: ?>
<a href="login.php" class="carrito-login-link">Inicia sesión para finalizar</a>
<?php endif; ?>
<form action="carrito.php" method="post" class="carrito-inline-form">
<input type="hidden" name="accion" value="vaciar">
<button type="submit" class="admin-danger-button">Vaciar carrito</button>
</form>
</div>
</div>
</div>
<?php else: ?>
<div class="productos-vacio">
<p>Tu carrito está vacío. Agrega productos desde la tienda.</p>
<a href="productos.php" class="admin-link-button carrito-link">Ir a productos</a>
</div>
<?php endif; ?>
</section>
</body>
</html>