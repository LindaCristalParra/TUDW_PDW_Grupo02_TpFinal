<?php
// Vista/Estructura/Accion/Compra/eliminarItem.php

$root = __DIR__ . '/../../../../';
require_once $root . 'Control/Session.php';
require_once $root . 'Control/compraControl.php';
require_once $root . 'Control/usuarioControl.php'; 

$session = new Session();
if (!$session->activa()) {
    header('Location: /TUDW_PDW_Grupo02_TpFinal/Vista/login.php');
    exit;
}

// RECOLECCIÓN DE DATOS
$idCompItem = $_POST['idcompraitem'] ?? ($_GET['idcompraitem'] ?? null);
$idUsuario = $session->getIDUsuarioLogueado() ?? $_SESSION['idusuario'];

// INVOCACIÓN AL CONTROLADOR
$exito = false;
if ($idCompItem && $idUsuario) {
    $compraCtrl = new CompraControl();
    
    $exito = $compraCtrl->eliminarProductoDelCarrito($idCompItem, $idUsuario);
}

// REDIRECCIÓN
header('Location: /TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Compra/mostrarCarrito.php');
exit;
?>