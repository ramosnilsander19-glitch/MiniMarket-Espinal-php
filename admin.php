<?php
require_once 'sistema/acceso.php';
redirigir_si_no_admin();
require_once 'sistema/conexion.php';

$usuarioSesion = usuario_actual();
$usuarios = [];
$mensajes = [];
$productos = [];
$errores = [];
$mensajeExito = '';
$productoEnEdicion = null;
$nombreProductoFormulario = '';
$descripcionProductoFormulario = '';
$precioProductoFormulario = '';
$imagenProductoFormulario = '';
$usaRol = columna_rol_usuarios_disponible($conexion);
$tablaProductosDisponible = false;

$resultadoTablaProductos = $conexion->query("SHOW TABLES LIKE 'productos'");

if ($resultadoTablaProductos) {
    $tablaProductosDisponible = $resultadoTablaProductos->num_rows > 0;
    $resultadoTablaProductos->free();
}

if (!$usaRol) {
    $errores[] = 'Debes ejecutar el archivo sql/actualizar_admin.sql para habilitar la gestión de roles.';
}

if (!$tablaProductosDisponible) {
    $errores[] = 'Debes ejecutar el archivo sql/actualizar_productos.sql para habilitar el registro de productos.';
}

if ($tablaProductosDisponible && isset($_GET['editar_producto'])) {
    $productoIdEditar = (int) $_GET['editar_producto'];

    if ($productoIdEditar > 0) {
        $consultaProductoEditar = $conexion->prepare('SELECT id, nombre, descripcion, precio, imagen FROM productos WHERE id = ? LIMIT 1');

        if ($consultaProductoEditar) {
            $consultaProductoEditar->bind_param('i', $productoIdEditar);
            $consultaProductoEditar->execute();
            $resultadoProductoEditar = $consultaProductoEditar->get_result();
            $productoEnEdicion = $resultadoProductoEditar->fetch_assoc();
            $consultaProductoEditar->close();

            if ($productoEnEdicion) {
                $nombreProductoFormulario = $productoEnEdicion['nombre'];
                $descripcionProductoFormulario = $productoEnEdicion['descripcion'];
                $precioProductoFormulario = $productoEnEdicion['precio'];
                $imagenProductoFormulario = $productoEnEdicion['imagen'];
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $usaRol) {
    $accion = $_POST['accion'] ?? '';
    $usuarioId = (int) ($_POST['usuario_id'] ?? 0);
    $nuevoRol = $_POST['nuevo_rol'] ?? '';

    if ($accion === 'cambiar_rol') {
        if ($usuarioId <= 0) {
            $errores[] = 'Usuario no válido.';
        }

        if (!in_array($nuevoRol, ['cliente', 'admin'], true)) {
            $errores[] = 'Rol no válido.';
        }

        if (!$errores) {
            $consultaUsuario = $conexion->prepare('SELECT id, nombre, correo, COALESCE(rol, "cliente") AS rol FROM usuarios WHERE id = ? LIMIT 1');

            if ($consultaUsuario) {
                $consultaUsuario->bind_param('i', $usuarioId);
                $consultaUsuario->execute();
                $resultadoUsuario = $consultaUsuario->get_result();
                $usuarioObjetivo = $resultadoUsuario->fetch_assoc();
                $consultaUsuario->close();

                if (!$usuarioObjetivo) {
                    $errores[] = 'No se encontró el usuario seleccionado.';
                } elseif (es_admin_principal($usuarioObjetivo['correo']) && $nuevoRol !== 'admin') {
                    $errores[] = 'No se puede cambiar el rol del administrador principal.';
                } elseif ((int) $usuarioObjetivo['id'] === (int) $usuarioSesion['id'] && $nuevoRol !== 'admin') {
                    $errores[] = 'No puedes quitarte tu propio acceso de administrador.';
                } elseif ($usuarioObjetivo['rol'] === $nuevoRol) {
                    $mensajeExito = 'El usuario ya tiene ese rol asignado.';
                } else {
                    $actualizarRol = $conexion->prepare('UPDATE usuarios SET rol = ? WHERE id = ?');

                    if ($actualizarRol) {
                        $actualizarRol->bind_param('si', $nuevoRol, $usuarioId);

                        if ($actualizarRol->execute()) {
                            $mensajeExito = 'Rol actualizado correctamente para ' . $usuarioObjetivo['nombre'] . '.';
                        } else {
                            $errores[] = 'No se pudo actualizar el rol del usuario.';
                        }

                        $actualizarRol->close();
                    } else {
                        $errores[] = 'No se pudo preparar la actualización del rol.';
                    }
                }
            } else {
                $errores[] = 'No se pudo consultar el usuario seleccionado.';
            }
        }
    }

    if ($accion === 'registrar_producto' && $tablaProductosDisponible) {
        $nombreProductoFormulario = trim($_POST['nombre_producto'] ?? '');
        $descripcionProductoFormulario = trim($_POST['descripcion_producto'] ?? '');
        $precioProductoFormulario = trim($_POST['precio_producto'] ?? '');
        $imagenProductoFormulario = trim($_POST['imagen_producto'] ?? '');

        if ($nombreProductoFormulario === '') {
            $errores[] = 'El nombre del producto es obligatorio.';
        }

        if ($descripcionProductoFormulario === '') {
            $errores[] = 'La descripción del producto es obligatoria.';
        }

        if ($precioProductoFormulario === '') {
            $errores[] = 'El precio del producto es obligatorio.';
        } elseif (!is_numeric($precioProductoFormulario) || (float) $precioProductoFormulario < 0) {
            $errores[] = 'El precio debe ser un número válido.';
        }

        if ($imagenProductoFormulario === '') {
            $errores[] = 'Debes indicar el nombre de la imagen del producto.';
        } elseif (!file_exists(__DIR__ . '/img/' . $imagenProductoFormulario)) {
            $errores[] = 'La imagen indicada no existe dentro de la carpeta img.';
        }

        if (!$errores) {
            $precioNormalizado = (float) $precioProductoFormulario;
            $consultaProducto = $conexion->prepare('INSERT INTO productos (nombre, descripcion, precio, imagen) VALUES (?, ?, ?, ?)');

            if ($consultaProducto) {
                $consultaProducto->bind_param('ssds', $nombreProductoFormulario, $descripcionProductoFormulario, $precioNormalizado, $imagenProductoFormulario);

                if ($consultaProducto->execute()) {
                    $mensajeExito = 'Producto registrado correctamente.';
                    $nombreProductoFormulario = '';
                    $descripcionProductoFormulario = '';
                    $precioProductoFormulario = '';
                    $imagenProductoFormulario = '';
                } else {
                    $errores[] = 'No se pudo registrar el producto.';
                }

                $consultaProducto->close();
            } else {
                $errores[] = 'No se pudo preparar el registro del producto.';
            }
        }
    }

    if ($accion === 'editar_producto' && $tablaProductosDisponible) {
        $productoId = (int) ($_POST['producto_id'] ?? 0);
        $nombreProductoFormulario = trim($_POST['nombre_producto'] ?? '');
        $descripcionProductoFormulario = trim($_POST['descripcion_producto'] ?? '');
        $precioProductoFormulario = trim($_POST['precio_producto'] ?? '');
        $imagenProductoFormulario = trim($_POST['imagen_producto'] ?? '');

        if ($productoId <= 0) {
            $errores[] = 'Producto no válido.';
        }

        if ($nombreProductoFormulario === '') {
            $errores[] = 'El nombre del producto es obligatorio.';
        }

        if ($descripcionProductoFormulario === '') {
            $errores[] = 'La descripción del producto es obligatoria.';
        }

        if ($precioProductoFormulario === '') {
            $errores[] = 'El precio del producto es obligatorio.';
        } elseif (!is_numeric($precioProductoFormulario) || (float) $precioProductoFormulario < 0) {
            $errores[] = 'El precio debe ser un número válido.';
        }

        if ($imagenProductoFormulario === '') {
            $errores[] = 'Debes indicar el nombre de la imagen del producto.';
        } elseif (!file_exists(__DIR__ . '/img/' . $imagenProductoFormulario)) {
            $errores[] = 'La imagen indicada no existe dentro de la carpeta img.';
        }

        if (!$errores) {
            $precioNormalizado = (float) $precioProductoFormulario;
            $actualizarProducto = $conexion->prepare('UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, imagen = ? WHERE id = ?');

            if ($actualizarProducto) {
                $actualizarProducto->bind_param('ssdsi', $nombreProductoFormulario, $descripcionProductoFormulario, $precioNormalizado, $imagenProductoFormulario, $productoId);

                if ($actualizarProducto->execute()) {
                    $mensajeExito = 'Producto actualizado correctamente.';
                    $productoEnEdicion = null;
                    $nombreProductoFormulario = '';
                    $descripcionProductoFormulario = '';
                    $precioProductoFormulario = '';
                    $imagenProductoFormulario = '';
                } else {
                    $errores[] = 'No se pudo actualizar el producto.';
                }

                $actualizarProducto->close();
            } else {
                $errores[] = 'No se pudo preparar la edición del producto.';
            }
        } else {
            $productoEnEdicion = [
                'id' => $productoId,
                'nombre' => $nombreProductoFormulario,
                'descripcion' => $descripcionProductoFormulario,
                'precio' => $precioProductoFormulario,
                'imagen' => $imagenProductoFormulario,
            ];
        }
    }

    if ($accion === 'eliminar_producto' && $tablaProductosDisponible) {
        $productoId = (int) ($_POST['producto_id'] ?? 0);

        if ($productoId <= 0) {
            $errores[] = 'Producto no válido para eliminar.';
        } else {
            $eliminarProducto = $conexion->prepare('DELETE FROM productos WHERE id = ?');

            if ($eliminarProducto) {
                $eliminarProducto->bind_param('i', $productoId);

                if ($eliminarProducto->execute()) {
                    $mensajeExito = 'Producto eliminado correctamente.';

                    if ($productoEnEdicion && (int) $productoEnEdicion['id'] === $productoId) {
                        $productoEnEdicion = null;
                        $nombreProductoFormulario = '';
                        $descripcionProductoFormulario = '';
                        $precioProductoFormulario = '';
                        $imagenProductoFormulario = '';
                    }
                } else {
                    $errores[] = 'No se pudo eliminar el producto.';
                }

                $eliminarProducto->close();
            } else {
                $errores[] = 'No se pudo preparar la eliminación del producto.';
            }
        }
    }
}

if ($usaRol) {
    $consultaUsuarios = $conexion->query('SELECT id, nombre, correo, COALESCE(rol, "cliente") AS rol, fecha_registro FROM usuarios ORDER BY fecha_registro DESC, id DESC');
} else {
    $consultaUsuarios = $conexion->query('SELECT id, nombre, correo, "cliente" AS rol, fecha_registro FROM usuarios ORDER BY fecha_registro DESC, id DESC');
}

if ($consultaUsuarios) {
    while ($fila = $consultaUsuarios->fetch_assoc()) {
        $usuarios[] = $fila;
    }

    $consultaUsuarios->free();
}

$consultaMensajes = $conexion->query('SELECT id, nombre, correo, mensaje, fecha_envio FROM mensajes_contacto ORDER BY fecha_envio DESC, id DESC');

if ($consultaMensajes) {
    while ($fila = $consultaMensajes->fetch_assoc()) {
        $mensajes[] = $fila;
    }

    $consultaMensajes->free();
}

if ($tablaProductosDisponible) {
    $consultaProductos = $conexion->query('SELECT id, nombre, descripcion, precio, imagen, fecha_registro FROM productos ORDER BY fecha_registro DESC, id DESC');

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
<title>Administración | Minimarket Espinal</title>
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
<a href="panel.php">Mi panel</a>
<a href="admin.php">Administración</a>
<a href="logout.php">Cerrar sesión</a>
</nav>

</header>

<section class="admin-section">
<div class="admin-header">
<h2>Panel de Administración</h2>
<p>Consulta los usuarios registrados y los mensajes enviados desde el formulario de contacto.</p>
</div>

<?php if ($errores): ?>
<div class="alerta alerta-error admin-alerta">
    <ul>
        <?php foreach ($errores as $error): ?>
        <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<?php if ($mensajeExito !== ''): ?>
<div class="alerta alerta-exito admin-alerta">
    <p><?php echo htmlspecialchars($mensajeExito, ENT_QUOTES, 'UTF-8'); ?></p>
</div>
<?php endif; ?>

<div class="admin-grid">
<div class="admin-card">
<h3><?php echo $productoEnEdicion ? 'Editar producto' : 'Registrar producto'; ?></h3>
<p class="admin-count"><?php echo count($productos); ?> productos registrados</p>

<?php if ($tablaProductosDisponible): ?>
<form action="admin.php" method="post" class="admin-product-form">
<input type="hidden" name="accion" value="<?php echo $productoEnEdicion ? 'editar_producto' : 'registrar_producto'; ?>">
<?php if ($productoEnEdicion): ?>
<input type="hidden" name="producto_id" value="<?php echo htmlspecialchars($productoEnEdicion['id'], ENT_QUOTES, 'UTF-8'); ?>">
<?php endif; ?>

<label for="nombre_producto">Nombre</label>
<input id="nombre_producto" name="nombre_producto" type="text" placeholder="Ejemplo: Coca Cola" value="<?php echo htmlspecialchars($nombreProductoFormulario, ENT_QUOTES, 'UTF-8'); ?>">

<label for="descripcion_producto">Descripción</label>
<textarea id="descripcion_producto" name="descripcion_producto" rows="4" placeholder="Describe el producto"><?php echo htmlspecialchars($descripcionProductoFormulario, ENT_QUOTES, 'UTF-8'); ?></textarea>

<label for="precio_producto">Precio</label>
<input id="precio_producto" name="precio_producto" type="number" min="0" step="0.01" placeholder="80" value="<?php echo htmlspecialchars($precioProductoFormulario, ENT_QUOTES, 'UTF-8'); ?>">

<label for="imagen_producto">Imagen</label>
<input id="imagen_producto" name="imagen_producto" type="text" placeholder="Ejemplo: cocacola.jpg" value="<?php echo htmlspecialchars($imagenProductoFormulario, ENT_QUOTES, 'UTF-8'); ?>">

<div class="admin-form-actions">
<button type="submit" class="admin-submit-button"><?php echo $productoEnEdicion ? 'Actualizar producto' : 'Guardar producto'; ?></button>
<?php if ($productoEnEdicion): ?>
<a href="admin.php" class="admin-cancel-link">Cancelar edición</a>
<?php endif; ?>
</div>
</form>
<?php else: ?>
<p>Primero ejecuta el archivo sql/actualizar_productos.sql en phpMyAdmin.</p>
<?php endif; ?>
</div>

<div class="admin-card">
<h3>Usuarios registrados</h3>
<p class="admin-count"><?php echo count($usuarios); ?> usuarios</p>

<?php if ($usuarios): ?>
<div class="tabla-wrap">
<table class="admin-table">
<thead>
<tr>
<th>ID</th>
<th>Nombre</th>
<th>Correo</th>
<th>Rol</th>
<th>Fecha</th>
<th>Acción</th>
</tr>
</thead>
<tbody>
<?php foreach ($usuarios as $usuario): ?>
<?php $rolUsuario = determinar_rol_usuario($usuario['correo'], $usuario['rol'] ?? 'cliente'); ?>
<tr>
<td><?php echo htmlspecialchars($usuario['id'], ENT_QUOTES, 'UTF-8'); ?></td>
<td><?php echo htmlspecialchars($usuario['nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
<td><?php echo htmlspecialchars($usuario['correo'], ENT_QUOTES, 'UTF-8'); ?></td>
<td><?php echo htmlspecialchars($rolUsuario, ENT_QUOTES, 'UTF-8'); ?></td>
<td><?php echo htmlspecialchars($usuario['fecha_registro'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
<td>
<?php if ($usaRol): ?>
    <?php if (es_admin_principal($usuario['correo'])): ?>
    <span class="estado-fijo">Admin principal</span>
    <?php else: ?>
    <form action="admin.php" method="post" class="admin-role-form">
        <input type="hidden" name="accion" value="cambiar_rol">
        <input type="hidden" name="usuario_id" value="<?php echo htmlspecialchars($usuario['id'], ENT_QUOTES, 'UTF-8'); ?>">
        <select name="nuevo_rol" class="admin-select">
            <option value="cliente" <?php echo $rolUsuario === 'cliente' ? 'selected' : ''; ?>>Cliente</option>
            <option value="admin" <?php echo $rolUsuario === 'admin' ? 'selected' : ''; ?>>Admin</option>
        </select>
        <button type="submit" class="admin-action-button">Guardar</button>
    </form>
    <?php endif; ?>
<?php else: ?>
    <span class="estado-fijo">Actualiza SQL</span>
<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php else: ?>
<p>No hay usuarios registrados.</p>
<?php endif; ?>
</div>

<div class="admin-card">
<h3>Productos guardados</h3>
<p class="admin-count"><?php echo count($productos); ?> productos</p>

<?php if ($productos): ?>
<div class="tabla-wrap">
<table class="admin-table">
<thead>
<tr>
<th>ID</th>
<th>Nombre</th>
<th>Descripción</th>
<th>Precio</th>
<th>Imagen</th>
<th>Fecha</th>
<th>Acción</th>
</tr>
</thead>
<tbody>
<?php foreach ($productos as $producto): ?>
<tr>
<td><?php echo htmlspecialchars($producto['id'], ENT_QUOTES, 'UTF-8'); ?></td>
<td><?php echo htmlspecialchars($producto['nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
<td><?php echo htmlspecialchars($producto['descripcion'], ENT_QUOTES, 'UTF-8'); ?></td>
<td>RD$ <?php echo htmlspecialchars(number_format((float) $producto['precio'], 2), ENT_QUOTES, 'UTF-8'); ?></td>
<td><?php echo htmlspecialchars($producto['imagen'], ENT_QUOTES, 'UTF-8'); ?></td>
<td><?php echo htmlspecialchars($producto['fecha_registro'], ENT_QUOTES, 'UTF-8'); ?></td>
<td>
<div class="admin-actions-stack">
<a href="admin.php?editar_producto=<?php echo htmlspecialchars($producto['id'], ENT_QUOTES, 'UTF-8'); ?>" class="admin-link-button">Editar</a>
<form action="admin.php" method="post" class="admin-delete-form">
<input type="hidden" name="accion" value="eliminar_producto">
<input type="hidden" name="producto_id" value="<?php echo htmlspecialchars($producto['id'], ENT_QUOTES, 'UTF-8'); ?>">
<button type="submit" class="admin-danger-button">Eliminar</button>
</form>
</div>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php else: ?>
<p>No hay productos registrados todavía.</p>
<?php endif; ?>
</div>

<div class="admin-card">
<h3>Mensajes recibidos</h3>
<p class="admin-count"><?php echo count($mensajes); ?> mensajes</p>

<?php if ($mensajes): ?>
<div class="tabla-wrap">
<table class="admin-table">
<thead>
<tr>
<th>ID</th>
<th>Nombre</th>
<th>Correo</th>
<th>Mensaje</th>
<th>Fecha</th>
</tr>
</thead>
<tbody>
<?php foreach ($mensajes as $mensaje): ?>
<tr>
<td><?php echo htmlspecialchars($mensaje['id'], ENT_QUOTES, 'UTF-8'); ?></td>
<td><?php echo htmlspecialchars($mensaje['nombre'], ENT_QUOTES, 'UTF-8'); ?></td>
<td><?php echo htmlspecialchars($mensaje['correo'], ENT_QUOTES, 'UTF-8'); ?></td>
<td><?php echo nl2br(htmlspecialchars($mensaje['mensaje'], ENT_QUOTES, 'UTF-8')); ?></td>
<td><?php echo htmlspecialchars($mensaje['fecha_envio'], ENT_QUOTES, 'UTF-8'); ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php else: ?>
<p>No hay mensajes guardados.</p>
<?php endif; ?>
</div>
</div>
</section>
</body>
</html>