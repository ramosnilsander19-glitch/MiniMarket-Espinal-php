<?php
require_once 'sistema/acceso.php';
redirigir_si_autenticado();

$correo = '';
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    $clave = $_POST['clave'] ?? '';

    if ($correo === '') {
        $errores[] = 'El correo es obligatorio.';
    }

    if ($clave === '') {
        $errores[] = 'La contraseña es obligatoria.';
    }

    if (!$errores) {
        require_once 'sistema/conexion.php';

        $usaRol = columna_rol_usuarios_disponible($conexion);

        if ($usaRol) {
            $consulta = $conexion->prepare('SELECT id, nombre, correo, clave, rol FROM usuarios WHERE correo = ? LIMIT 1');
        } else {
            $consulta = $conexion->prepare('SELECT id, nombre, correo, clave FROM usuarios WHERE correo = ? LIMIT 1');
        }

        if ($consulta) {
            $consulta->bind_param('s', $correo);
            $consulta->execute();
            $resultado = $consulta->get_result();
            $usuario = $resultado->fetch_assoc();
            $consulta->close();

            if ($usuario && password_verify($clave, $usuario['clave'])) {
                $rol = determinar_rol_usuario($usuario['correo'], $usuario['rol'] ?? null);

                $_SESSION['usuario'] = [
                    'id' => $usuario['id'],
                    'nombre' => $usuario['nombre'],
                    'correo' => $usuario['correo'],
                    'rol' => $rol,
                ];

                $conexion->close();

                if ($rol === 'admin') {
                    header('Location: admin.php');
                } else {
                    header('Location: panel.php');
                }

                exit;
            }

            $errores[] = 'Correo o contraseña incorrectos.';
        } else {
            $errores[] = 'No se pudo validar el acceso.';
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
<title>Iniciar Sesion | Minimarket Espinal</title>
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
<a href="login.php">Ingresar</a>
<a href="registro.php">Registrarse</a>
</nav>

</header>

<section class="formulario auth-page">
<h2>Iniciar sesión</h2>
<p>Accede a tu cuenta para entrar al panel de usuario.</p>

<?php if ($errores): ?>
<div class="alerta alerta-error">
    <ul>
        <?php foreach ($errores as $error): ?>
        <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<form action="login.php" method="post" class="auth-form" novalidate>
<label for="correo">Correo</label>
<input id="correo" name="correo" type="email" placeholder="tucorreo@email.com" value="<?php echo htmlspecialchars($correo, ENT_QUOTES, 'UTF-8'); ?>">

<label for="clave">Contraseña</label>
<input id="clave" name="clave" type="password" placeholder="Ingresa tu contraseña">

<button type="submit">Entrar</button>
</form>

<p class="auth-link">¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a>.</p>
</section>
</body>
</html>