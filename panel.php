<?php
require_once 'sistema/acceso.php';
redirigir_si_no_autenticado();

$usuario = usuario_actual();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Panel de Usuario | Minimarket Espinal</title>
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
<?php if (es_admin()): ?>
<a href="admin.php">Administración</a>
<?php endif; ?>
<a href="logout.php">Cerrar sesión</a>
</nav>

</header>

<section class="panel-usuario">
<div class="panel-card">
<h2>Bienvenido, <?php echo htmlspecialchars($usuario['nombre'], ENT_QUOTES, 'UTF-8'); ?></h2>
<p>Has iniciado sesión correctamente en el sistema.</p>

<div class="panel-datos">
<p><strong>Nombre:</strong> <?php echo htmlspecialchars($usuario['nombre'], ENT_QUOTES, 'UTF-8'); ?></p>
<p><strong>Correo:</strong> <?php echo htmlspecialchars($usuario['correo'], ENT_QUOTES, 'UTF-8'); ?></p>
<p><strong>Rol:</strong> <?php echo htmlspecialchars($usuario['rol'] ?? 'cliente', ENT_QUOTES, 'UTF-8'); ?></p>
</div>

<a class="panel-boton" href="contactos.php">Ir al contacto</a>
<?php if (es_admin()): ?>
<a class="panel-boton" href="admin.php">Abrir panel administrador</a>
<?php endif; ?>
</div>
</section>
</body>
</html>