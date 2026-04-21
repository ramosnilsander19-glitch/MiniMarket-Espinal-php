<?php require_once 'config/auth.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Nosotros | Minimarket Espinal</title>

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

<section class="nosotros">

<span class="nosotros-etiqueta">Negocio local</span>
<h2>Conoce Minimarket Espinal</h2>
<p>
Una tienda cercana, práctica y pensada para resolver las compras del día a día de la comunidad.
</p>

</section>

<section class="historia historia-ligera">

<div class="section-container">

<div class="historia-contenido">

<div class="historia-texto">

<h2>Nuestra compañía</h2>

<p>
Minimarket Espinal nace para cubrir necesidades básicas del barrio con atención amable, productos esenciales y una experiencia de compra sencilla.
</p>

<div class="nosotros-puntos">

<div class="nosotros-punto">
<h3>Atención cercana</h3>
<p>Servicio directo y amable para compras rápidas.</p>
</div>

<div class="nosotros-punto">
<h3>Productos esenciales</h3>
<p>Lo básico para el hogar y el consumo diario.</p>
</div>

<div class="nosotros-punto">
<h3>Precios accesibles</h3>
<p>Opciones pensadas para la comunidad.</p>
</div>

</div>

</div>

<div class="historia-media">

<img src="img/adentro.jpg" alt="Minimarket Espinal">

</div>

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

</body>

</html>