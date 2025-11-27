<?php
// Vista/Estructura/Accion/Compra/mostrarCarrito.php


//  RUTAS
$root = __DIR__ . '/../../../../';
require_once $root . 'Control/Session.php';
require_once $root . 'Control/compraControl.php';
require_once $root . 'Control/menuControl.php';
require_once $root . 'Control/usuarioControl.php'; // Necesario por dependencias internas

$session = new Session();
if (!$session->activa()) {
    header('Location: /TUDW_PDW_Grupo02_TpFinal/Vista/login.php');
    exit;
}

// RECUPERAR DATOS
$idUser = $session->getIDUsuarioLogueado();

// INVOCAR CONTROLADORES
$compraCtrl = new CompraControl();
$productos = $compraCtrl->obtenerProductosDelCarrito($idUser);


// CARGAR VISTA
require_once __DIR__ . '/../../../../Vista/compra/carrito.php';
?>

