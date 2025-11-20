<?php
// Vista/Estructura/Accion/Compra/vaciarCarrito.php

// 1. CONFIGURACIÓN DE RUTAS (CORREGIDO: 4 niveles atrás)
$root = __DIR__ . '/../../../../';

require_once $root . 'Control/Session.php';
require_once $root . 'Control/compraControl.php';
require_once $root . 'Control/usuarioControl.php'; 

$session = new Session();

// 2. VALIDAR SESIÓN
if (!$session->activa()) {
    header('Location: /TUDW_PDW_Grupo02_TpFinal/Vista/login.php');
    exit;
}

// 3. OBTENER USUARIO
if (method_exists($session, 'getIDUsuarioLogueado')) {
    $idUsuario = $session->getIDUsuarioLogueado();
} else {
    $idUsuario = $_SESSION['idusuario'] ?? null;
}

// 4. BUSCAR EL CARRITO ACTIVO
// Usamos el método robusto que ya arreglamos en UsuarioControl
$uControl = new UsuarioControl();
$carritoObj = $uControl->obtenerCarrito($idUsuario);

if ($carritoObj != null) {
    // 5. PROCEDER A VACIAR
    $cControl = new CompraControl();
    $idCompra = $carritoObj->getID();

    // Llamamos a la función vaciarCarrito del controlador
    // Esta función busca todos los items de esa compra y les hace 'baja' uno por uno
    if ($cControl->vaciarCarrito($idCompra)) {
        // Éxito
        header("Location: /TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Compra/mostrarCarrito.php?msg=carrito_vaciado");
    } else {
        // Puede dar false si el carrito ya estaba vacío o si falló la BD
        // Lo mandamos al carrito igual, porque técnicamente ya está vacío
        header("Location: /TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Compra/mostrarCarrito.php");
    }

} else {
    // No había carrito para vaciar
    header("Location: /TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Compra/mostrarCarrito.php");
}
exit;
?>