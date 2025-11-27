<?php

// Vista/Estructura/header.php

// Header común con Bootstrap navbar

?>

<?php

// Carrito: computar cantidad visible en el header (sesión o usuario logueado)

require_once __DIR__ . '/../../Control/Session.php';
require_once __DIR__ . '/../../Control/compraControl.php';
require_once __DIR__ . '/../../Control/usuarioControl.php';
require_once __DIR__ . '/../../Control/MenuRenderer.php';
require_once __DIR__ . '/../../Control/rolControl.php';

$cartCount = 0;
$session = new Session();

// Calcular cantidad del carrito (si existe) para mostrar badge cuando corresponda
if ($session->sesionActiva()) {
    $usuarioCtrl = new UsuarioControl();
    $compraCtrl = new CompraControl();
    $idUser = $session->getIDUsuarioLogueado();
    $carrito = $usuarioCtrl->obtenerCarrito($idUser);
    if ($carrito) {
        $cartProducts = $compraCtrl->listadoProdCarrito($carrito);
        foreach ($cartProducts as $p) {
            $cartCount += intval($p['cicantidad'] ?? 0);
        }
    }
} else {
    $anon = $_SESSION['anon_cart'] ?? [];
    foreach ($anon as $q) { $cartCount += intval($q); }
}

// Armar $menuData exclusivamente desde la BD usando MenuRenderer
$menuData = ['left' => [], 'right' => []];
$mr = new MenuRenderer();

if ($session->sesionActiva()) {
    $rolActivo = $session->getRolActivo();
    $idRol = $rolActivo['id'] ?? null;
    if (!empty($idRol)) {
        $menuData = $mr->obtenerMenuParaRol($idRol);
    }
} else {
        // No hay sesión: usar por convención el id 3 como rol anónimo
        $idAnon = 3;
        $menuData = $mr->obtenerMenuParaRol($idAnon);
}

    // Si no hay sesión y el menuData no incluye un item de 'login' en la derecha,
    // añadir un item de login por defecto para que los visitantes puedan iniciar sesión.
    if (!$session->sesionActiva()) {
        $hasLogin = false;
        foreach ($menuData['right'] as $it) {
            $k = strtolower(preg_replace('/[^a-z0-9]/i', '', $it['label'] ?? ''));
            if ($k === 'login' || $k === 'iniciosesion' || stripos($it['label'] ?? '', 'login') !== false || stripos($it['label'] ?? '', 'iniciar') !== false) {
                $hasLogin = true;
                break;
            }
        }
        if (!$hasLogin) {
            $menuData['right'][] = [
                'url' => '/TUDW_PDW_Grupo02_TpFinal/Vista/login.php',
                'label' => 'Iniciar sesión',
                'icon' => '/TUDW_PDW_Grupo02_TpFinal/Util/Imagenes/IconLogin.png',
                'icon_only' => true
            ];
        }
    }

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

            // El header se renderiza exclusivamente con lo que devuelva $menuData (DB-driven).
            // Estructura esperada: $menuData = ['left'=> [ ['url'=>'...','label'=>'...'], ... ], 'right'=>[...] ]

            // Render left items
            echo '<ul class="navbar-nav me-auto mb-2 mb-lg-0">';
            if (!empty($menuData['left'])) {
                foreach ($menuData['left'] as $item) {
                    $url = isset($item['url']) ? $item['url'] : '#';
                    $label = isset($item['label']) ? $item['label'] : (isset($item['nombre']) ? $item['nombre'] : 'Menu');
                    echo '<li class="nav-item"><a class="nav-link" href="'.htmlspecialchars($url).'">'.htmlspecialchars($label).'</a></li>';
                }
            }
            echo '</ul>';

            // Render right items (icon-only or label+icon)
            echo '<ul class="navbar-nav">';
            if (!empty($menuData['right'])) {
                foreach ($menuData['right'] as $item) {
                    $url = isset($item['url']) ? $item['url'] : '#';
                    $label = isset($item['label']) ? $item['label'] : (isset($item['nombre']) ? $item['nombre'] : 'Accion');
                    $icon = isset($item['icon']) ? $item['icon'] : '';
                    $iconOnly = isset($item['icon_only']) && $item['icon_only'];

                    // Detectar key para mostrar badge en carrito
                    $itemKey = strtolower(preg_replace('/[^a-z0-9]/i', '', $label));

                    if ($iconOnly && !empty($icon)) {
                        echo '<li class="nav-item position-relative">';
                        echo '<a class="nav-link" href="'.htmlspecialchars($url).'" aria-label="'.htmlspecialchars($label).'">'
                            . '<img src="'.htmlspecialchars($icon).'" class="menu-icon" alt="'.htmlspecialchars($label).'">'
                            . '</a>';
                        if ($itemKey === 'carrito' && $cartCount > 0) {
                            echo '<span class="badge rounded-pill bg-danger position-absolute" style="top:4px;right:0;">'.intval($cartCount).'</span>';
                        }
                        echo '</li>';
                    } else {
                        $iconHtml = '';
                        if (!empty($icon)) {
                            $iconHtml = '<img src="'.htmlspecialchars($icon).'" class="menu-icon" alt=""> ';
                        }
                        echo '<li class="nav-item"><a class="nav-link d-flex align-items-center" href="'.htmlspecialchars($url).'">'.$iconHtml.htmlspecialchars($label).'</a></li>';
                    }
                }
            }
            echo '</ul>';

            ?>
        </div>
    </div>
</nav>