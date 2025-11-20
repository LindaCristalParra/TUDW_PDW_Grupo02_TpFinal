<?php
require_once __DIR__ . '/../../Control/Session.php';
require_once __DIR__ . '/../../Control/compraControl.php';
require_once __DIR__ . '/../../Control/compraProductoControl.php';
require_once __DIR__ . '/../../Control/compraEstadoControl.php';
require_once __DIR__ . '/../../Control/productoControl.php'; 

$session = new Session();


if (!$session->activa()) {
    header('Location: /TrabajoFinalPWD/Vista/login.php');
    exit;
}

$idUsuario = $session->getIdUsuario();
$idProducto = $_GET['idProducto'] ?? null;

if (!$idProducto) {
    header('Location: /TrabajoFinalPWD/Vista/tienda.php');
    exit;
}

$abmCompra = new CompraControl();
$abmCompraEstado = new CompraEstadoControl();
$comprasUsuario = $abmCompra->buscar(['idUsuario' => $idUsuario]);
$idCompraActiva = null;

if (!empty($comprasUsuario)) {
    $comprasUsuario = array_reverse($comprasUsuario);
    foreach ($comprasUsuario as $compra) {
        $estados = $abmCompraEstado->buscar(['idCompra' => $compra->getId(), 'fechaFinNull' => true]);
        if (!empty($estados) && $estados[0]->getIdEstadoTipo() == 1) {
            $idCompraActiva = $compra->getId();
            break;
        }
    }
}

if ($idCompraActiva == null) {
    if ($abmCompra->alta(['idUsuario' => $idUsuario])) {
        $compras = $abmCompra->buscar(['idUsuario' => $idUsuario]);
        $idCompraActiva = end($compras)->getId();
        $abmCompraEstado->alta(['idCompra' => $idCompraActiva, 'idEstadoTipo' => 1]);
    } else {
        header('Location: /TrabajoFinalPWD/Vista/tienda.php?msg=error_compra');
        exit;
    }
}

$abmCompraProducto = new CompraItemControl();
$abmProducto = new ProductoControl();


$itemsEnCarrito = $abmCompraProducto->buscar([
    'idCompra' => $idCompraActiva,
    'idProducto' => $idProducto
]);

$exito = false;

if (!empty($itemsEnCarrito)) {
    $itemExistente = $itemsEnCarrito[0];
    $nuevaCantidad = $itemExistente->getCantidad() + 1;
    

    $prodObj = $abmProducto->buscar(['id' => $idProducto])[0];
    if ($prodObj->getStock() >= 1) {
        $exito = $abmCompraProducto->modificacion([
            'id' => $itemExistente->getId(),
            'idCompra' => $idCompraActiva,
            'idProducto' => $idProducto,
            'cantidad' => $nuevaCantidad
        ]);

        
        if ($exito) {
            $nuevoStockGlobal = $prodObj->getStock() - 1;
            $prodObj->estado($nuevoStockGlobal); 
        }
    } else {

        header("Location: /TrabajoFinalPWD/Vista/cart.php?msg=sin_stock");
        exit;
    }

} else {
    $exito = $abmCompraProducto->alta([
        'idCompra' => $idCompraActiva,
        'idProducto' => $idProducto,
        'cantidad' => 1
    ]);
}

if ($exito) {
    header("Location: /TrabajoFinalPWD/Vista/cart.php");
} else {
    header("Location: /TrabajoFinalPWD/Vista/tienda.php?msg=error_agregar");
}
exit;
?>