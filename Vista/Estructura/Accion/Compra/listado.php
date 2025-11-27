<?php
// Vista/Estructura/Accion/Compra/listado.php
// ACCIÓN MINIMALISTA

// RUTAS
$root = __DIR__ . '/../../../../';
require_once $root . 'Control/Session.php';
require_once $root . 'Control/compraControl.php';
require_once $root . 'Control/menuControl.php';

$session = new Session();
if (!$session->activa()) {
    header('Location: /TUDW_PDW_Grupo02_TpFinal/Vista/login.php');
    exit;
}

//  RECUPERAR DATOS DE SESIÓN
$idUser = $session->getIDUsuarioLogueado();
$rol = $session->getRolActivo();

//  INVOCAR CONTROLADOR 
$compraCtrl = new CompraControl();
$compras = $compraCtrl->listarComprasSegunRol($idUser, $rol);


//  CARGAR VISTA 
require_once __DIR__ . '/../../../../Vista/Compra/listadoCompras.php';
?>