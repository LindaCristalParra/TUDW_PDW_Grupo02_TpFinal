<?php
// Vista/Estructura/Accion/Producto/cambiarStock.php

// RUTAS
$root = __DIR__ . '/../../../../';
require_once $root . 'Control/Session.php';
require_once $root . 'Control/productoControl.php';

$session = new Session();

// SEGURIDAD 
$rol = $session->getRolActivo();

// RECOLECCIÓN DE DATOS
$idProducto = $_POST['idproducto'] ?? null;
$cantStock = $_POST['procantstock'] ?? null;

// INVOCAR CONTROLADOR
$resultado = false;

// Validamos que el stock sea numérico
if ($idProducto !== null && $cantStock !== null && is_numeric($cantStock)) {
    $prodCtrl = new ProductoControl();
    
    $resultado = $prodCtrl->actualizarStock($idProducto, $cantStock);
}

// REDIRECCIÓN
$rutaListado = '/TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Producto/listado.php';

if ($resultado) {
    header("Location: $rutaListado?msg=stock_actualizado");
} else {
    header("Location: $rutaListado?msg=error_stock");
}
exit;
?>