<?php require_once 'config/auth.php'; ?>
<?php
$nombre = '';
$correo = '';
$mensaje = '';
$errores = [];
$mensajeExito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $mensaje = trim($_POST['mensaje'] ?? '');

    if ($nombre === '') {
        $errores[] = 'El nombre es obligatorio.';
    }

    if ($correo === '') {
        $errores[] = 'El correo es obligatorio.';
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El correo no tiene un formato valido.';
    }

    if ($mensaje === '') {
        $errores[] = 'El mensaje es obligatorio.';
    }

    if (!$errores) {
        require_once 'config/db.php';

        $consulta = $conexion->prepare('INSERT INTO mensajes_contacto (nombre, correo, mensaje) VALUES (?, ?, ?)');

        if ($consulta) {
            $consulta->bind_param('sss', $nombre, $correo, $mensaje);

            if ($consulta->execute()) {
                $mensajeExito = 'Mensaje enviado y guardado correctamente.';
                $nombre = '';
                $correo = '';
                $mensaje = '';
            } else {
                $errores[] = 'No se pudo guardar el mensaje. Intenta de nuevo.';
            }

            $consulta->close();
        } else {
            $errores[] = 'No se pudo preparar la consulta SQL.';
        }

        $conexion->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Contacto | Minimarket Espinal</title>
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

<section class="contacto contacto-hero">
<div class="contacto-copy">
<span class="contacto-etiqueta">Estamos para ayudarte</span>
<h2>Hablemos de tu compra o consulta</h2>
<p>
Escríbenos para resolver dudas, recibir orientación sobre productos o comunicarte directamente con Minimarket Espinal.
</p>
<div class="contacto-resumen">
<div class="contacto-resumen-item">
<strong>Respuesta rápida</strong>
<span>Atención clara y directa</span>
</div>
<div class="contacto-resumen-item">
<strong>Ubicación local</strong>
<span>Esperanza, Valverde</span>
</div>
</div>
</div>

<div class="contacto-panel-info">
<div class="info-container contacto-info-grid">

<div class="info-card contacto-info-card">
<h3>Dirección</h3>
<p>Esperanza, Valverde, República Dominicana</p>
</div>

<div class="info-card contacto-info-card">
<h3>Teléfono</h3>
<p>809-755-3241</p>
</div>

<div class="info-card contacto-info-card">
<h3>Email</h3>
<p>minimarketespinal@gmail.com</p>
</div>

</div>
</div>
</section>

<section class="formulario contacto-formulario">
<div class="contacto-formulario-wrap">
<div class="contacto-formulario-copy">
<h2>Envíanos un mensaje</h2>
<p>Completa el formulario y te responderemos lo más pronto posible.</p>
<ul class="contacto-lista">
<li>Consultas sobre productos</li>
<li>Información general del minimarket</li>
<li>Comunicación rápida y directa</li>
</ul>
</div>

<div class="contacto-formulario-panel">

<?php if ($errores): ?>
<div class="alerta alerta-error">
    <ul>
        <?php foreach ($errores as $error): ?>
        <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<?php if ($mensajeExito !== ''): ?>
<div class="alerta alerta-exito">
    <p><?php echo htmlspecialchars($mensajeExito, ENT_QUOTES, 'UTF-8'); ?></p>
</div>
<?php endif; ?>

<form id="contacto-form" action="contactos.php" method="post" novalidate>

<label for="nombre">Nombre</label>
<input id="nombre" name="nombre" type="text" placeholder="Tu nombre" value="<?php echo htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'); ?>">
<small class="error-text" data-for="nombre"></small>

<label for="correo">Correo</label>
<input id="correo" name="correo" type="email" placeholder="tucorreo@email.com" value="<?php echo htmlspecialchars($correo, ENT_QUOTES, 'UTF-8'); ?>">
<small class="error-text" data-for="correo"></small>

<label for="mensaje">Mensaje</label>
<textarea id="mensaje" name="mensaje" rows="4" placeholder="Escribe tu mensaje"><?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?></textarea>
<small class="error-text" data-for="mensaje"></small>

<button type="submit">Enviar mensaje</button>
</form>
</div>
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

<script src="assets/js/app.js"></script>
</body>
</html>