<?php
// Vista/Estructura/Accion/Compra/agregarProdCarrito.php
require_once __DIR__ . '/../../../../Control/Session.php';
require_once __DIR__ . '/../../../../Control/compraControl.php';

$session = new Session();
if (!$session->sesionActiva()) {
    header('Location: /TUDW_PDW_Grupo02_TpFinal/Vista/login.php');
    exit;
}

$idproducto = isset($_REQUEST['idproducto']) ? intval($_REQUEST['idproducto']) : null;
if ($idproducto === null) {
    $_SESSION['flash'] = 'Producto inválido.';
    header('Location: /TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Producto/listado.php');
    exit;
}

$compraCtrl = new CompraControl();
$data = ['idproducto' => $idproducto];
$ok = $compraCtrl->agregarProdCarrito($data);

if ($ok) {
    $_SESSION['flash'] = 'Producto agregado al carrito.';
} else {
    $_SESSION['flash'] = 'No se pudo agregar el producto.';
}

        header('Location: /TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Producto/listado.php');
exit;
