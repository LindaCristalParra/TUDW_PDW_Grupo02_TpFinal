<?php
// Vista/Estructura/Accion/Producto/cambiarStock.php

// RUTAS
$root = __DIR__ . '/../../../../';
require_once $root . 'Control/Session.php';
require_once $root . 'Control/productoControl.php';

$session = new Session();

// SEGURIDAD (Login + Rol Admin)
$rol = $session->getRolActivo();
// Validación
$esAdmin = (!empty($rol) && ($rol['id'] == 1 || strtolower($rol['rol']) === 'administrador'));

if (!$session->activa() || !$esAdmin) {
    header('Location: /TUDW_PDW_Grupo02_TpFinal/Vista/login.php?msg=acceso_denegado');
    exit;
}

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
// Redirigimos al ACTION de listado para que recargue los datos
$rutaListado = '/TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Producto/listado.php';

if ($resultado) {
    header("Location: $rutaListado?msg=stock_actualizado");
} else {
    header("Location: $rutaListado?msg=error_stock");
}
exit;
?>