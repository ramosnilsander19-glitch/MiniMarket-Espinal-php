<?php require_once 'sistema/acceso.php'; ?>
<?php
require_once 'sistema/conexion.php';

$productos = [];
$tablaProductosDisponible = false;
$mensajeExito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'agregar_carrito') {
        $productoId = (int) ($_POST['producto_id'] ?? 0);

        if ($productoId > 0) {
            $consultaProducto = $conexion->prepare('SELECT id, nombre, precio, imagen FROM productos WHERE id = ? LIMIT 1');

            if ($consultaProducto) {
                $consultaProducto->bind_param('i', $productoId);
                $consultaProducto->execute();
                $resultadoProducto = $consultaProducto->get_result();
                $productoSeleccionado = $resultadoProducto->fetch_assoc();
                $consultaProducto->close();

                if ($productoSeleccionado) {
                    agregar_al_carrito($productoSeleccionado);
                    $mensajeExito = 'Producto agregado al carrito.';
                }
            }
        }
    }
}

$resultadoTablaProductos = $conexion->query("SHOW TABLES LIKE 'productos'");

if ($resultadoTablaProductos) {
    $tablaProductosDisponible = $resultadoTablaProductos->num_rows > 0;
    $resultadoTablaProductos->free();
}

if ($tablaProductosDisponible) {
    $consultaProductos = $conexion->query('SELECT id, nombre, descripcion, precio, imagen FROM productos ORDER BY fecha_registro DESC, id DESC');

    if ($consultaProductos) {
        while ($fila = $consultaProductos->fetch_assoc()) {
            $productos[] = $fila;
        }

        $consultaProductos->free();
    }
}

$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Productos | Minimarket Espinal</title>

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


<section class="productos">

<h2>Nuestros productos</h2>

<p>Bebidas y productos esenciales para el hogar.</p>

<?php if ($mensajeExito !== ''): ?>
<div class="alerta alerta-exito carrito-alerta">
<p><?php echo htmlspecialchars($mensajeExito, ENT_QUOTES, 'UTF-8'); ?></p>
</div>
<?php endif; ?>


<div class="productos-container">
<?php if ($tablaProductosDisponible && $productos): ?>
<?php foreach ($productos as $producto): ?>
<div class="producto-card">
<img src="img/<?php echo htmlspecialchars($producto['imagen'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($producto['nombre'], ENT_QUOTES, 'UTF-8'); ?>">
<h3><?php echo htmlspecialchars($producto['nombre'], ENT_QUOTES, 'UTF-8'); ?></h3>
<p><?php echo htmlspecialchars($producto['descripcion'], ENT_QUOTES, 'UTF-8'); ?></p>
<p class="precio">RD$ <?php echo htmlspecialchars(number_format((float) $producto['precio'], 2), ENT_QUOTES, 'UTF-8'); ?></p>
<form action="productos.php" method="post" class="carrito-form">
<input type="hidden" name="accion" value="agregar_carrito">
<input type="hidden" name="producto_id" value="<?php echo htmlspecialchars($producto['id'], ENT_QUOTES, 'UTF-8'); ?>">
<button type="submit" class="carrito-boton">Agregar al carrito</button>
</form>
</div>
<?php endforeach; ?>
<?php else: ?>
<div class="productos-vacio">
<p>No hay productos registrados todavía. Puedes agregarlos desde el panel de administración.</p>
</div>
<?php endif; ?>

</div>

</section>


<footer class="footer">
    <div class="footer-container">
        <div class="footer-brand">
            <h3>Minimarket Espinal</h3>
            <p>Tu compra diaria, rapida y cercana en Esperanza, Valverde.</p>
        </div>
        <div class="footer-info">
            <div class="footer-bloque">
                <span>Ubicacion</span>
                <p>Barrio La Fe, Esperanza, Valverde, Republica Dominicana</p>
            </div>
            <div class="footer-bloque">
                <span>Contacto</span>
                <p>809-755-3241</p>
                <p>minimarketespinal@gmail.com</p>
            </div>
            <div class="footer-bloque">
                <span>Accesos rapidos</span>
                <div class="footer-links">
                    <a href="productos.php">Productos</a>
                    <a href="contactos.php">Contacto</a>
                    <a href="nosotros.php">Nosotros</a>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> Minimarket Espinal. Atencion cercana todos los dias.</p>
    </div>
</footer>

</body>

</html>