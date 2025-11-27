<?php
// Vista/Estructura/Accion/Compra/agregarAlCarrito.php

// CONFIGURACIÓN DE RUTAS Y DEPENDENCIAS
$root = __DIR__ . '/../../../../';
require_once $root . 'Control/Session.php';
require_once $root . 'Control/compraControl.php';
require_once $root . 'Control/usuarioControl.php'; 

// Detección y carga del control de items (para pasar el nombre de la clase)
if (file_exists($root . 'Control/compraItemControl.php')) {
    require_once $root . 'Control/compraItemControl.php';
    $claseItem = 'CompraItemControl';
} else {
    require_once $root . 'Control/compraProductoControl.php';
    $claseItem = 'CompraProductoControl';
}

$session = new Session();

// OBTENER Y VALIDAR ENTRADAS
// Obtenemos ID de Usuario 
if (method_exists($session, 'getIDUsuarioLogueado')) {
    $idUsuario = $session->getIDUsuarioLogueado();
} else {
    $idUsuario = $_SESSION['idusuario'] ?? null;
}
$idProducto = $_GET['idProducto'] ?? ($_POST['idProducto'] ?? null);

// Validación de seguridad y datos
if (empty($idUsuario) || empty($idProducto)) {
    header('Location: /TUDW_PDW_Grupo02_TpFinal/Vista/login.php?msg=sesion_invalida');
    exit;
}

//  CREAR OBJETO DE TRANSACCIÓN E INVOCAR MÉTODO
$abmCompra = new CompraControl();

// Llama función para agregar producto al carrito
$exito = $abmCompra->agregarProductoAlCarrito($idUsuario, $idProducto, $claseItem);

// REDIRECCIÓN (Basada en el resultado booleano)
if ($exito) {
    header("Location: /TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Compra/mostrarCarrito.php");
} else {
    header("Location: /TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Producto/listado.php?msg=error_al_agregar&prod=$idProducto");
}
exit;