<?php
// Vista/Estructura/Accion/Compra/cambiarEstado.php

$root = __DIR__ . '/../../../../';
require_once $root . 'Control/Session.php';
require_once $root . 'Control/compraControl.php';

$session = new Session();

// VALIDACIÓN DE ACCESO
$rol = $session->getRolActivo();
if (!$session->activa() || ($rol['id'] != 1 && $rol['rol'] != 'Administrador')) {
    header('Location: /TUDW_PDW_Grupo02_TpFinal/Vista/login.php?msg=acceso_denegado');
    exit;
}

// RECOLECCIÓN DE DATOS
$datos = $_POST; 

// INVOCACIÓN AL CONTROLADOR
$compraCtrl = new CompraControl();
$resultado = false;

if (isset($datos['idcompra']) && isset($datos['nuevoEstado'])) {
    $resultado = $compraCtrl->actualizarEstadoCompra($datos['idcompra'], $datos['nuevoEstado']);
}

// REDIRECCIÓN
if ($resultado) {
    header("Location: /TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Compra/verCompra.php?id=" . $datos['idcompra'] . "&msg=estado_actualizado");
} else {
    header("Location: /TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Producto/listado.php?msg=error_operacion");
}
exit;
?>