<?php
// Vista/Estructura/Accion/Venta/listado.php


// RUTAS
$root = __DIR__ . '/../../../../';
require_once $root . 'Control/Session.php';
require_once $root . 'Control/compraControl.php';

$session = new Session();

// SEGURIDAD 
$rol = $session->getRolActivo();
if (!$session->activa() || empty($rol) || ($rol['id'] != 1 && strtolower($rol['rol']) !== 'administrador')) {
    header('Location: /TUDW_PDW_Grupo02_TpFinal/Vista/login.php?msg=acceso_denegado');
    exit;
}

// INVOCAR CONTROLADOR
$compraCtrl = new CompraControl();

// Toda la lógica de bucles y estados se mudó al controlador
$compras = $compraCtrl->obtenerListadoDeVentas(); 

// CARGAR VISTA
// Intentamos cargar una vista específica de ventas, sino usamos la genérica
$vistaVentas = $root . 'Vista/venta/listado.php'; 
$vistaGenerica = $root . 'Vista/compra/listadoCompras.php';

if (file_exists($vistaVentas)) {
    require_once $vistaVentas;
} else {
    // Reutilizamos la vista de historial de compras
    require_once $vistaGenerica;
}
?>
