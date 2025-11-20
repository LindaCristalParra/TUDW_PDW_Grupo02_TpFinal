<?php

// Vista/Estructura/header.php

// Header común con Bootstrap navbar

?>

<?php

// Carrito: computar cantidad visible en el header (sesión o usuario logueado)

require_once __DIR__ . '/../../Control/Session.php';
require_once __DIR__ . '/../../Control/compraControl.php';
require_once __DIR__ . '/../../Control/usuarioControl.php';

$cartCount = 0;
$session = new Session();

if ($session->sesionActiva()) {

    $usuarioCtrl = new UsuarioControl();
    $compraCtrl = new CompraControl();
    $idUser = $session->getIDUsuarioLogueado();
    $carrito = $usuarioCtrl->obtenerCarrito($idUser);

    if ($carrito) {
        $productos = $compraCtrl->listadoProdCarrito($carrito);
        foreach ($productos as $p) {
            $cartCount += intval($p['cicantidad'] ?? 0);
        }

    }

} else {
    $anon = $_SESSION['anon_cart'] ?? [];
    foreach ($anon as $q) { $cartCount += intval($q); }
}

// Determinar si el usuario es administrador (para mostrar link en el nav)
$rolActivo = $session->getRolActivo();
$isAdmin = (!empty($rolActivo) && isset($rolActivo['rol']) && strtolower($rolActivo['rol']) === 'administrador');

?>

<!doctype html>
<html lang="es">
<head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>ChristmasMarket</title>
        <link href="/TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/css/bootstrap.min.css" rel="stylesheet">
        <link href="/TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/css/styles.css" rel="stylesheet">

        <style>

            :root { --pine-green: #01796F; --header-red: #dc3545; }
            .site-navbar {
                background: #ffffff;
                border-bottom: 2px solid var(--header-red);
            }

            .site-navbar .navbar-brand {
                color: var(--pine-green) !important;
                font-weight: 700;
            }

            .site-navbar .nav-link {
                color: var(--pine-green) !important;
                font-weight: 600;
            }

            .site-logo {
                height: 60px;
                width: auto;
                margin-right: 12px;
            }

            .nav-user-icon svg { height:30px; width: 30px; vertical-align:middle; }

        </style>

</head>
<body>
<nav class="navbar navbar-expand-lg site-navbar">

    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="/TUDW_PDW_Grupo02_TpFinal/Vista/index.php">
            <img src="/TUDW_PDW_Grupo02_TpFinal/Util/Imagenes/logoTxt.png" alt="logo" class="site-logo" onerror="this.style.display='none'">

        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>

        </button>

        <div class="collapse navbar-collapse" id="navbarNav">

            <?php

            // El header no debe decidir rutas ni roles: el action debe preparar $menuData.
            // Estructura esperada: $menuData = ['left'=> [ ['url'=>'...','label'=>'...'], ... ], 'right'=>[...] ]

            if (isset($menuData) && is_array($menuData)) {
                echo '<ul class="navbar-nav me-auto mb-2 mb-lg-0">';
                // Link público a Inicio y Productos (siempre visibles)
                echo '<li class="nav-item"><a class="nav-link" href="/TUDW_PDW_Grupo02_TpFinal/Vista/index.php">Inicio</a></li>';
                echo '<li class="nav-item"><a class="nav-link" href="/TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Producto/listado.php">Productos</a></li>';

                if (!empty($menuData['left'])) {
                    foreach ($menuData['left'] as $item) {
                        $url = isset($item['url']) ? $item['url'] : '#';
                        $label = isset($item['label']) ? $item['label'] : (isset($item['nombre']) ? $item['nombre'] : 'Menu');
                        echo '<li class="nav-item"><a class="nav-link" href="'.htmlspecialchars($url).'">'.htmlspecialchars($label).'</a></li>';
                    }

                }

                // Mostrar Panel en el left nav si es administrador
                if (!empty($isAdmin)) {
                    echo '<li class="nav-item"><a class="nav-link" href="/TUDW_PDW_Grupo02_TpFinal/Vista/admin/panelAdmin.php">Panel</a></li>';
                }

                echo '</ul>';
                echo '<ul class="navbar-nav">';

                // icono del carrito (oculto para administradores)
                if (empty($isAdmin)) {
                    $cartUrl = '/TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Compra/mostrarCarrito.php';
                    echo '<li class="nav-item"><a class="nav-link position-relative" href="'.htmlspecialchars($cartUrl).'" aria-label="Ver carrito">'
                        . '<img src="/TUDW_PDW_Grupo02_TpFinal/Util/Imagenes/IconShop.png" alt="Carrito" style="width: 24px; height: 24px;">';

                    if ($cartCount > 0) {
                        echo '<span class="badge rounded-pill bg-danger position-absolute" style="top:4px;right:0;">'.intval($cartCount).'</span>';
                    }

                    echo '</a></li>';
                }

                if (!empty($menuData['right'])) {

                    foreach ($menuData['right'] as $item) {
                        $url = isset($item['url']) ? $item['url'] : '#';
                        $label = isset($item['label']) ? $item['label'] : (isset($item['nombre']) ? $item['nombre'] : 'Accion');
                        $icon = isset($item['icon']) ? $item['icon'] : '';
                        $iconOnly = isset($item['icon_only']) && $item['icon_only'];

                        if ($iconOnly && !empty($icon)) {
                            // mostrar solo el icono (con aria-label para accesibilidad)
                            echo '<li class="nav-item"><a class="nav-link" href="'.htmlspecialchars($url).'" aria-label="'.htmlspecialchars($label).'">'
                                . '<img src="'.htmlspecialchars($icon).'" class="menu-icon" alt="'.htmlspecialchars($label).'">'
                                . '</a></li>';
                        } else {
                            $iconHtml = '';
                            if (!empty($icon)) {
                                $iconHtml = '<img src="'.htmlspecialchars($icon).'" class="menu-icon" alt=""> ';
                            }
                            echo '<li class="nav-item"><a class="nav-link d-flex align-items-center" href="'.htmlspecialchars($url).'">'.$iconHtml.htmlspecialchars($label).'</a></li>';
                        }
                    }

                } else {

                    // Si no hay items en menuData['right']:
                    // Mostrar logout si hay sesión, sino login
                    if ($session->sesionActiva()) {
                        $logoutUrl = '/TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Login/logout.php';
                        echo '<li class="nav-item"><a class="nav-link nav-user-icon" href="'.htmlspecialchars($logoutUrl).'" aria-label="Cerrar sesión">'
                            . '<img src="/TUDW_PDW_Grupo02_TpFinal/Util/Imagenes/IconLogout.png" alt="Cerrar sesión" style="width: 24px; height: 24px;">'
                            . '</a></li>';
                    } else {
                        echo '<li class="nav-item"><a class="nav-link nav-user-icon" href="/TUDW_PDW_Grupo02_TpFinal/Vista/login.php" aria-label="Iniciar sesión">'
                            . '<img src="/TUDW_PDW_Grupo02_TpFinal/Util/Imagenes/IconLogin.png" alt="Usuario" style="width: 24px; height: 24px;">'
                            . '</a></li>';
                    }
                }

                echo '</ul>';

            } else {

                // fallback mínimo: Inicio + carrito + Login (iconos)

                echo '<ul class="navbar-nav me-auto mb-2 mb-lg-0">';
                echo '<li class="nav-item"><a class="nav-link" href="/TUDW_PDW_Grupo02_TpFinal/Vista/index.php">Inicio</a></li>';

                // Link público a Productos

                echo '<li class="nav-item"><a class="nav-link" href="/TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Producto/listado.php">Productos</a></li>';
                // Mostrar Panel incluso en el fallback cuando el usuario es administrador
                if (!empty($isAdmin)) {
                    echo '<li class="nav-item"><a class="nav-link" href="/TUDW_PDW_Grupo02_TpFinal/Vista/admin/panelAdmin.php">Panel</a></li>';
                }
                echo '</ul>';

                // carrito

                $cartUrl = '/TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Compra/mostrarCarrito.php';
                echo '<ul class="navbar-nav">';
                echo '<li class="nav-item"><a class="nav-link position-relative" href="'.htmlspecialchars($cartUrl).'" aria-label="Ver carrito">'
               . '<img src="/TUDW_PDW_Grupo02_TpFinal/Util/Imagenes/IconShop.png" alt="Carrito" style="width: 24px; height: 24px;">'
    			. '';

                if ($cartCount > 0) {

                    echo '<span class="badge rounded-pill bg-danger position-absolute" style="top:4px;right:0;">'.intval($cartCount).'</span>';

                }

                echo '</a></li>';

                // login icon

                   echo '<li class="nav-item"><a class="nav-link nav-user-icon" href="/TUDW_PDW_Grupo02_TpFinal/Vista/login.php" aria-label="Iniciar sesión">'
                        . '<img src="/TUDW_PDW_Grupo02_TpFinal/Util/Imagenes/IconLogin.png" alt="Usuario" style="width: 24px; height: 24px;">'
						. '';
						echo '</a></li>';
                echo '</ul>';

            }

            ?>
        </div>
    </div>
</nav>