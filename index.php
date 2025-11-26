<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'config/db.php';

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$origenes_query = $conn->query("SELECT DISTINCT origen FROM rutas ORDER BY origen ASC");
if (!$origenes_query) {
    die("Error en consulta de orígenes: " . $conn->error);
}

$destinos_query = $conn->query("SELECT DISTINCT destino FROM rutas ORDER BY destino ASC");
if (!$destinos_query) {
    die("Error en consulta de destinos: " . $conn->error);
}

$origenes = [];
while($row = $origenes_query->fetch_assoc()){
    $origenes[] = $row['origen'];
}

$destinos = [];
while($row = $destinos_query->fetch_assoc()){
    $destinos[] = $row['destino'];
}

$selected_origen = $_GET['origen'] ?? '';
$selected_destino = $_GET['destino'] ?? '';
$selected_fecha_ida = $_GET['fecha_ida'] ?? date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buses Flores Hnos - Viaja por todo el Perú</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <style>
        :root {
            --primary-blue: #004a99;
            --secondary-red: #d9534f;
            --light-grey: #f4f4f4;
            --dark-grey: #333;
            --text-color: #555;
            --header-height: 80px;
        }

        body {
            font-family: 'Roboto', Arial, sans-serif;
            margin: 0;
            background-color: var(--light-grey);
            color: var(--text-color);
            line-height: 1.6;
        }

        a {
            text-decoration: none;
            color: var(--primary-blue);
        }

        a:hover {
            text-decoration: underline;
        }
        
        /* Helper para alinear iconos de Google */
        .icon {
            vertical-align: middle;
            margin-right: 0.5em;
            font-size: 1.2em; /* Tamaño por defecto */
        }


        /* --- Header Top --- */
        .header-top {
            position: relative;
            z-index: 2000;
            background-color: var(--primary-blue);
            color: white;
            padding: 0.5em 0;
            font-size: 0.9em;
        }
        .header-top-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 1em;
        }
        .header-top-content .left a {
            color: white;
            margin-right: 1.5em;
        }
        .header-top-content .right a {
            color: white;
            margin-left: 1.5em;
        }

        /* --- Main Header / Navbar --- */
        .main-header {
            background-color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .navbar-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: var(--header-height);
            padding: 0 1em;
        }
        .navbar-logo {
            /* APLICANDO CAMBIO: Usando logo.png y ajustando altura */
            height: 60px; /* Ajusta esta altura como veas conveniente */
            width: auto; /* Mantiene la proporción */
        }
        .navbar-links a {
            color: var(--dark-grey);
            padding: 0 1em;
            font-weight: bold;
        }
        .navbar-links a:hover {
            color: var(--primary-blue);
            text-decoration: none;
        }

        /* --- Hero Section / Banner principal --- */
        .hero-section {
            background-image: url('public/img/portada.png'); 
            background-size: cover;
            background-position: center center;
            min-height: 600px; /* Altura generosa para el banner */
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            color: white;
            text-align: center;
            padding: 2em;
        }
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.4); /* Capa oscura para que el texto sea legible */
            z-index: 1;
        }
        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 900px;
        }
        .hero-content h1 {
            font-size: 3.5em;
            margin-bottom: 0.5em;
            color: white;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        .hero-content p {
            font-size: 1.5em;
            margin-bottom: 2em;
            color: #eee;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        }

        /* --- Buscador de Viajes (dentro del Hero) --- */
        .trip-search-form {
            background-color: rgba(255, 255, 255, 0.95); /* Fondo blanco semitransparente */
            padding: 2em;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr auto; /* 4 columnas + botón */
            gap: 1em;
            align-items: flex-end;
            margin-top: 2em;
        }
        .trip-search-form div {
            text-align: left;
        }
        .trip-search-form label {
            display: block;
            margin-bottom: 0.5em;
            font-weight: bold;
            color: var(--dark-grey);
            font-size: 0.9em;
        }
        .trip-search-form select,
        .trip-search-form input[type="date"],
        .trip-search-form button {
            width: 100%;
            padding: 0.8em;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1em;
            box-sizing: border-box;
        }
        .trip-search-form button {
            background-color: var(--secondary-red);
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
            padding: 1em; /* Un poco más de padding para el botón */
        }
        .trip-search-form button:hover {
            background-color: #c9302c;
        }

        /* --- Resultados de Búsqueda --- */
        .search-results-container {
            max-width: 1200px;
            margin: 2em auto;
            padding: 0 1em;
        }
        .search-results h2 {
            color: var(--primary-blue);
            text-align: center;
            margin-bottom: 1.5em;
        }
        .viaje-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 1.5em;
            padding: 1.5em;
            display: grid;
            grid-template-columns: 2fr 2fr 1.5fr 1fr 1.5fr; /* 5 columnas */
            gap: 1em;
            align-items: center;
        }
        .viaje-card div {
            padding: 0.5em 0;
            border-right: 1px solid #eee; /* Separador entre columnas */
            text-align: center;
        }
        .viaje-card div:first-child {
            text-align: left;
        }
        .viaje-card div:last-child {
            border-right: none;
            text-align: center;
        }
        .viaje-card strong {
            color: var(--primary-blue);
            display: block;
            margin-bottom: 0.3em;
            font-size: 0.9em;
        }
        .viaje-card span {
            font-size: 1.1em;
            color: var(--dark-grey);
        }
        .viaje-card .buy-button {
            background-color: var(--secondary-red);
            color: white;
            padding: 0.8em 1.2em;
            border-radius: 5px;
            display: inline-block;
            text-align: center;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        .viaje-card .buy-button:hover {
            background-color: #c9302c;
            text-decoration: none;
        }
        .no-results {
            text-align: center;
            padding: 2em;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        /* --- Secciones de Servicios y Calidad --- */
        .section-title {
            text-align: center;
            color: var(--primary-blue);
            font-size: 2.5em;
            margin-bottom: 1.5em;
            margin-top: 2em;
            font-weight: 700;
        }
        .services-grid, .quality-grid {
            max-width: 1200px;
            margin: 2em auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2em;
            padding: 0 1em;
            text-align: center;
        }
        .service-item, .quality-item {
            background-color: white;
            padding: 2em;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
        }
        .service-item:hover, .quality-item:hover {
            transform: translateY(-5px);
        }
        
        /* APLICANDO CAMBIO: Estilo para iconos de Google */
        .service-item .icon, .quality-item .icon {
            font-size: 60px; /* Tamaño grande para el icono */
            color: var(--primary-blue);
            margin-bottom: 0.5em;
        }

        .service-item h3, .quality-item h3 {
            color: var(--primary-blue);
            margin-top: 0;
            font-size: 1.4em;
        }
        .service-item p, .quality-item p {
            font-size: 0.95em;
            color: var(--text-color);
        }
        /* Ajuste para sección calidad donde el h3 es el texto principal */
        .quality-item h3 {
             font-size: 1.1em;
             color: var(--text-color);
             font-weight: 400;
             min-height: 4em; /* Ayuda a alinear verticalmente */
        }


        /* --- Viaja con Nosotros (Rutas Destacadas) --- */
        .travel-with-us {
            background-color: var(--primary-blue); /* Fondo azul oscuro */
            color: white;
            padding: 4em 1em;
            margin-top: 4em;
        }
        .travel-with-us-content {
            max-width: 1200px;
            margin: 0 auto;
            text-align: center;
        }
        .travel-with-us h2 {
            font-size: 2.8em;
            margin-bottom: 1.5em;
            color: white;
        }
        .routes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5em;
            text-align: left;
        }
        .route-item {
            background-color: rgba(255, 255, 255, 0.1);
            padding: 1em 1.5em;
            border-radius: 5px;
            color: white;
            transition: background-color 0.3s ease;
        }
        .route-item:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }
        .route-item a {
            color: white;
            font-weight: bold;
        }

        /* --- Footer --- */
        .main-footer {
            background-color: #1a1a1a;
            color: #ccc;
            padding: 3em 1em;
            font-size: 0.9em;
        }
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2em;
        }
        .footer-logo {
            width: 150px; /* Ajusta el tamaño del logo en el footer */
            margin-bottom: 1em;
        }
        .footer-section h3 {
            color: white;
            margin-bottom: 1em;
            font-size: 1.2em;
        }
        .footer-section ul {
            list-style: none;
            padding: 0;
        }
        .footer-section ul li {
            margin-bottom: 0.8em;
        }
        .footer-section ul li a, .footer-section p {
            color: #ccc;
        }
        .footer-section .social-links a {
            color: white;
            font-size: 1.5em;
            margin-right: 1em;
        }
        .footer-bottom {
            background-color: #0d0d0d;
            color: #888;
            text-align: center;
            padding: 1em 0;
            margin-top: 3em;
            font-size: 0.8em;
        }
        .footer-bottom a {
            color: #888;
        }
        .footer-contact .icon {
            margin-right: 0.5em;
            color: var(--secondary-red);
        }

        /* --- WhatsApp Button --- */
        .whatsapp-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            background-color: #25D366;
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5em; /* Tamaño icono WhatsApp */
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            transition: transform 0.3s ease;
        }
        .whatsapp-button:hover {
            transform: scale(1.1);
            color: white;
        }


        /* Responsive Adjustments */
        @media (max-width: 900px) {
            .trip-search-form {
                grid-template-columns: 1fr 1fr; /* 2 columnas en tablet */
            }
            .trip-search-form button {
                grid-column: 1 / -1; /* Botón ocupa todo el ancho */
            }
            .viaje-card {
                grid-template-columns: 1fr 1fr 1fr; /* 3 columnas */
            }
            .viaje-card div:nth-child(4), .viaje-card div:nth-child(5) {
                grid-column: span 1;
            }
             .viaje-card div:last-child {
                grid-column: 1 / -1; /* Botón de comprar al final */
            }
        }

        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2.5em;
            }
            .hero-content p {
                font-size: 1.2em;
            }
            .trip-search-form {
                grid-template-columns: 1fr; /* 1 columna en móvil */
            }
            .viaje-card {
                grid-template-columns: 1fr; /* Stack columns on small screens */
                text-align: center;
            }
            .viaje-card div {
                border-right: none;
                border-bottom: 1px solid #eee;
                text-align: center !important; /* Forzar centrado */
            }
            .viaje-card div:last-child {
                border-bottom: none;
            }
            .navbar-content {
                flex-direction: column;
                height: auto;
                padding-bottom: 1em;
            }
            .navbar-links {
                margin-top: 1em;
            }
            .navbar-links a {
                display: block;
                padding: 0.5em 0;
            }
            .header-top-content {
                flex-direction: column;
            }
            .header-top-content .left, .header-top-content .right {
                margin-bottom: 0.5em;
            }
        }
    </style>
</head>
<body>

   <!-- Header Top -->
<!--<div class="header-top">
    <div class="header-top-content">
        <div class="left">
            <a href="#"><span class="material-symbols-outlined icon">desktop_windows</span> Mis compras por internet</a>
        </div>
        <div class="right">
            <?php if (isset($_SESSION['id_usuario'])): ?>
                <?php if ($_SESSION['rol'] == 'admin'): ?>
                    <a href="admin/dashboard.php">Panel Admin</a>
                <?php else: ?>
                    <a href="cliente/index.php">Mi Perfil</a>
                <?php endif; ?>
                <a href="auth/logout.php"><span class="material-symbols-outlined icon">logout</span> Cerrar Sesión</a>
            <?php else: ?>
                <a href="auth/login.php"><span class="material-symbols-outlined icon">person</span> Iniciar Sesión</a>
                <a href="auth/register.php"><span class="material-symbols-outlined icon">person_add</span> Registrarse</a>
            <?php endif; ?>
        </div>
    </div>
</div>


    <-- Navbar -->
<!--<header class="main-header">
    <div class="navbar-content">
        <a href="index.php">
            <img src="public/img/logo.png" alt="Logo Flores Hnos" class="navbar-logo">
        </a>
        <div class="navbar-links">
            <a href="#services">Servicios</a>
            <a href="#quality">Calidad</a>
            <a href="#travel">Destinos</a>
        </div>
    </div>
</header>-->

<!-- INICIO HEADER -->
<div class="header-top">
    <div class="header-top-content" style="align-items: center;">
        <div class="left">
            <a href="index.php">
                <img src="public/img/logo.png" alt="Logo Flores Hnos" class="navbar-logo" style="margin-left: -2.5cm; height:60px; vertical-align: middle;">
            </a>
        </div>
        <div class="right" style="margin-right: 2cm; margin-left: -2.5cm; display: flex; align-items: center;">
            <a href="tel:+51014800078"><i class="fas fa-phone icon"></i> (01) 4800078</a>
            <a href="auth/login.php"><i class="fas fa-user icon"></i> Compra tu pasaje en línea</a>
            <a href="#documento"><i class="fas fa-file-alt icon"></i> Mi documento electrónico</a>
            <a href="#envios"><span class="material-symbols-outlined icon">location_on</span> Seguimiento de envíos</a>
        </div>        
<!--<div style="position: relative; display: inline-block;">
    <button class="hamburger" id="hamburgerBtn"
        aria-label="Menú"
        aria-controls="dropdown-menu"
        aria-expanded="false">
        ☰
    </button>

    <!- Menú desplegable ->
    <nav id="dropdown-menu" class="dropdown hidden" aria-label="Menú secundario">
        <a href="#services"><span class="material-symbols-outlined icon">build</span> Servicios</a>
        <a href="#travel"><span class="material-symbols-outlined icon">map</span> Destinos</a>
        <a href="#contacto"><span class="material-symbols-outlined icon">mail</span> Contacto</a>
    </nav>
</div>-->
<div style="position: relative; display: inline-block; transform: translateX(-4cm);">
    <button class="hamburger" id="hamburgerBtn"
        aria-label="Menú"
        aria-controls="dropdown-menu"
        aria-expanded="false">
        ☰
    </button>

    <!-- Menú desplegable -->
    <nav id="dropdown-menu" class="dropdown hidden" aria-label="Menú secundario">
        <a href="#services"><span class="material-symbols-outlined icon">build</span> Servicios</a>
        <a href="#travel"><span class="material-symbols-outlined icon">map</span> Destinos</a>
        <a href="#contacto"><span class="material-symbols-outlined icon">mail</span> Contacto</a>
    </nav>
</div>




        <div class="session-buttons">
            <?php if (isset($_SESSION['id_usuario'])): ?>
                <?php if ($_SESSION['rol'] == 'admin'): ?>
                    <a href="admin/dashboard.php">Panel Admin</a>
                <?php else: ?>
                    <a href="cliente/index.php">Mi Perfil</a>
                <?php endif; ?>
                <a href="auth/logout.php"><span class="material-symbols-outlined icon">logout</span> Cerrar Sesión</a>
            <?php else: ?>
                <a href="auth/login.php"><span class="material-symbols-outlined icon">person</span> Iniciar Sesión</a>
                <a href="auth/register.php"><span class="material-symbols-outlined icon">person_add</span> Registrarse</a>
            <?php endif; ?>
        </div>
    </div>
</div>


<style>
.hamburger {
    position: relative; /* referencia para el menú */
    font-size: 1.38em;
    background: white;
    border: none;
    color: var(--primary-blue);
    cursor: pointer;
    padding: 0.35em 0.7em;
    border-radius: 4px;
    vertical-align: middle;
}

.dropdown {
    position: absolute;
    top: 100%;   /* justo debajo del botón */
    right: 0;    /* alineado al borde derecho del botón */
    background: var(--primary-blue);
    border-radius: 6px;
    padding: 0.5em;
    display: flex;
    flex-direction: column;
    box-shadow: 0 4px 12px rgba(0,0,0,0.25);
    min-width: 160px;
    /* clave para sobreponerse */
    z-index: 9999;
}
.hamburger:hover {
    background-color: #e6e6e6;
}
.session-buttons {
    position: absolute;
    top: 0.5em;         /* un poco debajo del borde superior */
    right: 1cm;         /* frontera a 1 cm del borde derecho */
    display: flex;
    gap: 1em;           /* espacio entre botones */
    align-items: center;
}

.session-buttons a {
    color: white;       /* ajusta según tu paleta */
    text-decoration: none;
    font-size: 0.9em;
}

.session-buttons a:hover {
    text-decoration: underline;
}






.dropdown a {
    color: white;
    padding: 0.6em 0.8em;
    text-decoration: none;
    border-radius: 4px;
    display: flex;
    align-items: center;
}
.dropdown a:hover {
    background: rgba(255,255,255,0.15);
}
.hidden { display: none; }


</style>

<script>
(function () {
    const btn = document.getElementById('hamburgerBtn');
    const menu = document.getElementById('dropdown-menu');

    function openMenu() {
        menu.classList.remove('hidden');
        btn.setAttribute('aria-expanded', 'true');
        const firstLink = menu.querySelector('a');
        if (firstLink) firstLink.focus();
    }

    function closeMenu() {
        menu.classList.add('hidden');
        btn.setAttribute('aria-expanded', 'false');
    }

    function toggleMenu() {
        menu.classList.contains('hidden') ? openMenu() : closeMenu();
    }

    btn.addEventListener('click', toggleMenu);
    document.addEventListener('click', (e) => {
        if (!menu.contains(e.target) && !btn.contains(e.target)) closeMenu();
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeMenu();
    });
})();
</script>


    <!-- Hero Section -->
<section class="hero-section">
    <div class="hero-content">
        <h1>Viaja por todo el Perú con Flores Hnos</h1>
        <p>Conecta con tus destinos favoritos de forma segura y cómoda.</p>
        <form action="cliente/resultados.php" method="GET" class="trip-search-form">
            <div>
                <label for="origen">ORIGEN</label>
                <select id="origen" name="origen" required>
                    <option value="">Seleccione</option>
                    <?php foreach($origenes as $or): ?>
                        <option value="<?php echo htmlspecialchars($or); ?>" <?php echo ($selected_origen == $or) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($or); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="destino">DESTINO</label>
                <select id="destino" name="destino" required>
                    <option value="">Seleccione</option>
                    <?php foreach($destinos as $des): ?>
                        <option value="<?php echo htmlspecialchars($des); ?>" <?php echo ($selected_destino == $des) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($des); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="fecha_ida">SALIDA</label>
                <input type="date" id="fecha_ida" name="fecha_ida" value="<?php echo htmlspecialchars($selected_fecha_ida); ?>" required min="<?php echo date('Y-m-d'); ?>">
            </div>
            <div>
                <label for="fecha_retorno">RETORNO (OPCIONAL)</label>
                <input type="date" id="fecha_retorno" name="fecha_retorno" min="<?php echo date('Y-m-d'); ?>">
            </div>
            <button type="submit">Buscar</button>
        </form>

    </div>
</section>

<!-- Resultados -->


    <!-- Sección de Nuestros Servicios -->
    <section id="services">
        <h2 class="section-title">Nuestros Servicios</h2>
        <div class="services-grid">
            <div class="service-item">
                <!-- APLICANDO CAMBIO: Icono Google -->
                <span class="material-symbols-outlined icon">directions_bus</span>
                <h3>Transporte de pasajeros</h3>
                <p>Ofrecemos viajes seguros y cómodos a los principales destinos del Perú.</p>
            </div>
            <div class="service-item">
                <!-- APLICANDO CAMBIO: Icono Google -->
                <span class="material-symbols-outlined icon">inventory_2</span>
                <h3>Encomiendas y carga</h3>
                <p>Servicio de envío de encomiendas a nivel nacional, rápido y confiable.</p>
            </div>
            <div class="service-item">
                <!-- APLICANDO CAMBIO: Icono Google -->
                <span class="material-symbols-outlined icon">business_center</span>
                <h3>Servicios corporativos</h3>
                <p>Soluciones de transporte personalizadas para empresas y grupos grandes.</p>
            </div>
            <div class="service-item">
                <!-- APLICANDO CAMBIO: Icono Google -->
                <span class="material-symbols-outlined icon">handshake</span>
                <h3>Convenios con empresas</h3>
                <p>Establecemos alianzas estratégicas para ofrecer beneficios exclusivos.</p>
            </div>
        </div>
    </section>

    <!-- Sección de Calidad del Servicio -->
    <section id="quality">
        <h2 class="section-title">Calidad del servicio</h2>
        <div class="quality-grid">
            <div class="quality-item">
                <!-- APLICANDO CAMBIO: Icono Google -->
                <span class="material-symbols-outlined icon">style</span>
                <h3>Tenemos diferentes planes de viaje, escoge el que más se te acomoda.</h3>
            </div>
            <div class="quality-item">
                <!-- APLICANDO CAMBIO: Icono Google -->
                <span class="material-symbols-outlined icon">health_and_safety</span>
                <h3>Todos quieren llegar a su destino, a nosotros ahora nos importa más tu seguridad.</h3>
            </div>
            <div class="quality-item">
                <!-- APLICANDO CAMBIO: Icono Google -->
                <span class="material-symbols-outlined icon">support_agent</span>
                <h3>En Flores Hnos. tenemos el equipo preparado para hacer tu viaje placentero.</h3>
            </div>
        </div>
    </section>

    <!-- Sección Viaja con nosotros (Rutas destacadas) -->
    <section class="travel-with-us" id="travel">
        <div class="travel-with-us-content">
            <h2>Viaja con nosotros</h2>
            <div class="routes-grid">
                <!-- Estas rutas deberían ser dinámicas de tu BD eventualmente -->
                <div class="route-item"><a href="#">Bus de Lima a Tacna</a></div>
                <div class="route-item"><a href="#">Bus de Lima a Arequipa</a></div>
                <div class="route-item"><a href="#">Bus de Lima a Ica</a></div>
                <div class="route-item"><a href="#">Bus de Lima a Trujillo</a></div>
                <div class="route-item"><a href="#">Bus de Lima a Chiclayo</a></div>
                <div class="route-item"><a href="#">Bus de Lima a Piura</a></div>
                <div class="route-item"><a href="#">Bus de Lima a Cajamarca</a></div>
                <div class="route-item"><a href="#">Bus de Lima a Camaná</a></div>
                <div class="route-item"><a href="#">Bus de Lima a Cusco</a></div>
                <div class="route-item"><a href="#">Bus de Lima a Ilo</a></div>
                <div class="route-item"><a href="#">Bus de Lima a Puno</a></div>
                <div class="route-item"><a href="#">Bus de Lima a Juliaca</a></div>
                <div class="route-item"><a href="#">Bus de Lima a Nazca</a></div>
                <div class="route-item"><a href="#">Bus de Tumbes a Lima</a></div>
                <div class="route-item"><a href="#">Bus de Tacna a Lima</a></div>
            </div>
        </div>
    </section>

    <!-- Footer Principal -->
    <footer class="main-footer">
        <div class="footer-content">
            <div class="footer-section">
                <img src="public/img/logo_mini_white.png" alt="Logo Flores Hnos Blanco" class="footer-logo">
                <p>Ofrecemos a nuestros pasajeros un servicio de 30 destinos, a nivel nacional; con 20 años de experiencia y muchos otros. Entre los servicios que podemos ofrecer están: Buses Doble piso, Asientos Reclinables a 160° en ambos pisos.</p>
            </div>
            <div class="footer-section">
                <h3>Enlaces</h3>
                <ul>
                    <li><a href="#">Seguimiento de encomiendas</a></li>
                    <li><a href="#">Consulta de documentos electrónicos</a></li>
                    <li><a href="#">Libro de reclamaciones</a></li>
                    <li><a href="#">Términos y condiciones</a></li>
                    <li><a href="#">Política de privacidad</a></li>
                </ul>
            </div>
            <div class="footer-section footer-contact">
                <h3>Contacto</h3>
                <!-- APLICANDO CAMBIO: Iconos Google -->
                <p><span class="material-symbols-outlined icon">location_on</span> Av. Paseo de la República 627 / La Victoria - Lima</p>
                <p><span class="material-symbols-outlined icon">mail</span> info@floreshnos.pe</p>
                <p><span class="material-symbols-outlined icon">call</span> (01) 4800 705</p>
            </div>
            <div class="footer-section">
                <h3>Redes Sociales</h3>
                <div class="social-links">
                    <!-- Iconos de marca se mantienen con FontAwesome -->
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
        </div>
    </footer>
    <div class="footer-bottom">
        Desarrollado por Jelat Integradores | &copy; <?php echo date('Y'); ?> Buses Flores Hnos. Todos los derechos reservados.
    </div>
    
    <!-- Botón de WhatsApp flotante (Con icono FA) -->
    <a href="https://wa.me/xxxxxxxxxx" target="_blank" class="whatsapp-button">
        <i class="fab fa-whatsapp"></i>
    </a>

</body>
</html>