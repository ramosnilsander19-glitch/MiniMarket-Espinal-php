<?php require_once 'config/auth.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minimarket Espinal</title>
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

    <section class="inicio inicio-hero">
    <div class="inicio-copy">
        <span class="inicio-etiqueta">Compra diaria sin complicaciones</span>
        <h2>Todo lo esencial del barrio en un solo lugar</h2>
        <p>
            Minimarket Espinal te conecta con bebidas, alimentos y productos básicos de forma rápida, cercana y práctica.
        </p>

        <div class="inicio-acciones">
            <a href="productos.php" class="inicio-boton inicio-boton-principal">Ver productos</a>
            <a href="carrito.php" class="inicio-boton inicio-boton-secundario">Ir al carrito</a>
        </div>

        <div class="inicio-resumen">
            <div class="inicio-dato">
                <strong>+20</strong>
                <span>Productos iniciales</span>
            </div>
            <div class="inicio-dato">
                <strong>Rápido</strong>
                <span>Compra simple y directa</span>
            </div>
            <div class="inicio-dato">
                <strong>Local</strong>
                <span>Atención cercana al barrio</span>
            </div>
        </div>
    </div>

    <div class="inicio-media">
        <img src="img/Minimarket.jpg" alt="Minimarket Espinal" width="600">
        <div class="inicio-tarjeta-flotante">
            <h3>Siempre a mano</h3>
            <p>Bebidas frías, productos de casa y compras rápidas para el día a día.</p>
        </div>
    </div>
    </section>

    <section class="inicio-beneficios">
    <div class="inicio-beneficio">
        <h3>Compra rápida</h3>
        <p>Encuentra lo que necesitas sin vueltas y con un recorrido claro desde el inicio.</p>
    </div>

    <div class="inicio-beneficio">
        <h3>Precios accesibles</h3>
        <p>Productos pensados para la compra diaria con precios directos y visibles.</p>
    </div>

    <div class="inicio-beneficio">
        <h3>Servicio cercano</h3>
        <p>Un minimarket local enfocado en resolver compras comunes de forma práctica.</p>
    </div>
    </section>



    <section class="destacados">

<h2>Productos Destacados</h2>
<p class="destacados-texto">Empieza por algunos de los artículos más buscados en la tienda.</p>

<div class="destacados-container">

<div class="destacado">
<img src="img/refresco.jpg" alt="Refresco">
<span class="destacado-etiqueta">Frío</span>
<h3>Refresco</h3>
<p>Refresco frío</p>
</div>

<div class="destacado">
<img src="img/lays.jpg" alt="Papitas">
<span class="destacado-etiqueta">Snack</span>
<h3>Papitas</h3>
<p>Snack crujiente</p>
</div>

<div class="destacado">
<img src="img/c.jpg" alt="Cerveza">
<span class="destacado-etiqueta">Popular</span>
<h3>Cerveza</h3>
<p>La más refrescante</p>
</div>

</div>

<div class="destacados-acciones">
    <a href="productos.php" class="inicio-boton inicio-boton-principal">Explorar catálogo</a>
    <a href="contactos.php" class="inicio-boton inicio-boton-secundario">Hacer una consulta</a>
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