<?php
// Vista/Estructura/Accion/Compra/agregarAlCarrito.php

// 1. CONFIGURACIÓN DE RUTAS
$root = __DIR__ . '/../../../../';

require_once $root . 'Control/Session.php';
require_once $root . 'Control/compraControl.php';
require_once $root . 'Control/compraEstadoControl.php';
require_once $root . 'Control/productoControl.php';
require_once $root . 'Control/usuarioControl.php'; // ¡AGREGADO!

// Detectar control de items
if (file_exists($root . 'Control/compraItemControl.php')) {
    require_once $root . 'Control/compraItemControl.php';
    $claseItem = 'CompraItemControl';
} else {
    require_once $root . 'Control/compraProductoControl.php';
    $claseItem = 'CompraProductoControl';
}

$session = new Session();

// 2. VALIDAR SESIÓN
if (method_exists($session, 'getIDUsuarioLogueado')) {
    $idUsuario = $session->getIDUsuarioLogueado();
} else {
    $idUsuario = $_SESSION['idusuario'] ?? null;
}

if (empty($idUsuario)) {
    header('Location: /TUDW_PDW_Grupo02_TpFinal/Vista/login.php');
    exit;
}

// OBTENER PRODUCTO
$idProducto = $_GET['idProducto'] ?? ($_POST['idProducto'] ?? null);

if (!$idProducto) {
    header('Location: /TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Producto/listado.php?msg=no_id_producto');
    exit;
}

// BUSCAR CARRITO ACTIVO 

$uControl = new UsuarioControl();
$carritoObj = $uControl->obtenerCarrito($idUsuario);

$abmCompra = new CompraControl();
$abmCompraEstado = new CompraEstadoControl();
$abmItem = new $claseItem();

$idCompraActiva = null;

if ($carritoObj != null) {
    // ¡Ya existe un carrito activo! Lo usamos.
    $idCompraActiva = $carritoObj->getID();
} else {
    // No existe, creamos uno nuevo 
    if ($abmCompra->alta(['idusuario' => $idUsuario])) {
        $compras = $abmCompra->buscar(['idusuario' => $idUsuario]);
        $ultimaCompra = end($compras);
        $idCompraActiva = $ultimaCompra->getId();
        
        // Estado inicial 1 con fecha ceros forzada
        $abmCompraEstado->alta([
            'idcompra' => $idCompraActiva, 
            'idcompraestadotipo' => 1,
            'cefechafin' => '0000-00-00 00:00:00' 
        ]);
    } else {
        header('Location: /TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Producto/listado.php?msg=error_crear_compra');
        exit;
    }
}

// GESTIONAR EL ITEM
$itemsEnCarrito = $abmItem->buscar([
    'idcompra' => $idCompraActiva,
    'idproducto' => $idProducto
]);

$exito = false;

if (!empty($itemsEnCarrito)) {
    // UPDATE
    $itemExistente = $itemsEnCarrito[0];
    $nuevaCantidad = $itemExistente->getCiCantidad() + 1;
    
    $param = [
        'idcompraitem' => $itemExistente->getId(),
        'idcompra'     => $idCompraActiva,
        'idproducto'   => $idProducto,
        'cicantidad'   => $nuevaCantidad
    ];
    $exito = $abmItem->modificacion($param);

} else {
    // INSERT
    $param = [
        'idcompra'   => $idCompraActiva,
        'idproducto' => $idProducto,
        'cicantidad' => 1
    ];
    $exito = $abmItem->alta($param);
}

// 6. REDIRECCIÓN
if ($exito) {
    header("Location: /TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Compra/mostrarCarrito.php");
} else {
    header("Location: /TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Producto/listado.php?msg=error_al_agregar&prod=$idProducto");
}
exit;
?>