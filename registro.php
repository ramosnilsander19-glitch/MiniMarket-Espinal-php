<?php
require_once 'sistema/acceso.php';
redirigir_si_autenticado();

$nombre = '';
$correo = '';
$errores = [];
$mensajeExito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $clave = $_POST['clave'] ?? '';
    $confirmarClave = $_POST['confirmar_clave'] ?? '';

    if ($nombre === '') {
        $errores[] = 'El nombre es obligatorio.';
    }

    if ($correo === '') {
        $errores[] = 'El correo es obligatorio.';
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El correo no tiene un formato valido.';
    }

    if ($clave === '') {
        $errores[] = 'La contraseña es obligatoria.';
    } elseif (strlen($clave) < 6) {
        $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
    }

    if ($confirmarClave === '') {
        $errores[] = 'Debes confirmar la contraseña.';
    } elseif ($clave !== $confirmarClave) {
        $errores[] = 'Las contraseñas no coinciden.';
    }

    if (!$errores) {
        require_once 'sistema/conexion.php';

        $usaRol = columna_rol_usuarios_disponible($conexion);

        $consultaCorreo = $conexion->prepare('SELECT id FROM usuarios WHERE correo = ? LIMIT 1');

        if ($consultaCorreo) {
            $consultaCorreo->bind_param('s', $correo);
            $consultaCorreo->execute();
            $consultaCorreo->store_result();

            if ($consultaCorreo->num_rows > 0) {
                $errores[] = 'Ya existe una cuenta con ese correo.';
            }

            $consultaCorreo->close();
        } else {
            $errores[] = 'No se pudo validar el correo.';
        }

        if (!$errores) {
            $claveHash = password_hash($clave, PASSWORD_DEFAULT);

            if ($usaRol) {
                $rol = 'cliente';
                $consulta = $conexion->prepare('INSERT INTO usuarios (nombre, correo, clave, rol) VALUES (?, ?, ?, ?)');
            } else {
                $consulta = $conexion->prepare('INSERT INTO usuarios (nombre, correo, clave) VALUES (?, ?, ?)');
            }

            if ($consulta) {
                if ($usaRol) {
                    $consulta->bind_param('ssss', $nombre, $correo, $claveHash, $rol);
                } else {
                    $consulta->bind_param('sss', $nombre, $correo, $claveHash);
                }

                if ($consulta->execute()) {
                    $mensajeExito = 'Usuario registrado correctamente. Ahora puedes iniciar sesión.';
                    $nombre = '';
                    $correo = '';
                } else {
                    $errores[] = 'No se pudo registrar el usuario.';
                }

                $consulta->close();
            } else {
                $errores[] = 'No se pudo preparar el registro.';
            }
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
<title>Registro | Minimarket Espinal</title>
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
<h2>Crear cuenta</h2>
<p>Regístrate para tener acceso a tu panel de usuario.</p>

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

<form action="registro.php" method="post" class="auth-form" novalidate>
<label for="nombre">Nombre completo</label>
<input id="nombre" name="nombre" type="text" placeholder="Tu nombre" value="<?php echo htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'); ?>">

<label for="correo">Correo</label>
<input id="correo" name="correo" type="email" placeholder="tucorreo@email.com" value="<?php echo htmlspecialchars($correo, ENT_QUOTES, 'UTF-8'); ?>">

<label for="clave">Contraseña</label>
<input id="clave" name="clave" type="password" placeholder="Mínimo 6 caracteres">

<label for="confirmar_clave">Confirmar contraseña</label>
<input id="confirmar_clave" name="confirmar_clave" type="password" placeholder="Repite la contraseña">

<button type="submit">Crear cuenta</button>
</form>

<p class="auth-link">¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a>.</p>
</section>
</body>
</html>