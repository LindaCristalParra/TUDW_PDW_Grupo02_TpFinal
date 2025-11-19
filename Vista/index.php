<?php
// Vista/index.php - página de inicio con listado de productos tipo tienda
require_once __DIR__ . '/../Control/menuControl.php';
require_once __DIR__ . '/../Control/Session.php';

$menuCtrl = new MenuControl();
$session = new Session();
$menuData = $menuCtrl->armarMenu();

require_once __DIR__ . '/Estructura/header.php';
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 text-center">
            <img src="/TUDW_PDW_Grupo02_TpFinal/Util/Imagenes/logoPNG.png" alt="logo" style="height:120px; width:auto; margin-bottom:16px;" onerror="this.style.display='none'">

            <h1 class="display-4">Bienvenidos a ChristmasMarket</h1>
            <p class="lead mt-4">Somos una tienda familiar que cree en la magia de compartir. En estas épocas de festividad y alegría trabajamos con cariño para que cada hogar pueda tener "la navidad de sus sueños": regalos con historia, detalles hechos con amor y productos pensados para reunir a la familia alrededor de momentos inolvidables.</p>
            <p>Desde artículos tradicionales hasta novedades cuidadosamente seleccionadas, nuestro propósito es acercar sonrisas. Creemos que las pequeñas cosas construyen grandes recuerdos, y estamos aquí para ayudarte a encontrarlas.</p>
            <div class="mt-4">
                <a href="/TUDW_PDW_Grupo02_TpFinal/Vista/producto/ver.php" class="btn btn-primary btn-lg me-2">Ver productos</a>
            </div>
            <p class="text-muted small mt-3">Gracias por apoyar a las tiendas locales. ¡Felices fiestas!</p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/Estructura/footer.php';
