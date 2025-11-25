<?php
// Vista/Estructura/Accion/Compra/listado.php
// ACCIÓN MINIMALISTA

// 1. RUTAS
$root = __DIR__ . '/../../../../';
require_once $root . 'Control/Session.php';
require_once $root . 'Control/compraControl.php';
require_once $root . 'Control/menuControl.php';

$session = new Session();
if (!$session->activa()) {
    header('Location: /TUDW_PDW_Grupo02_TpFinal/Vista/login.php');
    exit;
}

// 2. RECUPERAR DATOS DE SESIÓN
$idUser = $session->getIDUsuarioLogueado();
$rol = $session->getRolActivo();

// 3. INVOCAR CONTROLADOR (Lógica de negocio)
$compraCtrl = new CompraControl();
$compras = $compraCtrl->listarComprasSegunRol($idUser, $rol);

// (Opcional) Armar menú si lo usas en el header
$menuCtrl = new MenuControl();
$menuData = $menuCtrl->armarMenu();

// 4. CARGAR VISTA (Presentación)
// Ojo a la ruta: Sale 4 niveles -> entra a Vista -> entra a compra
require_once __DIR__ . '/../../../../Vista/Compra/listadoCompras.php';
?>