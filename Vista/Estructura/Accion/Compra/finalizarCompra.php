<?php
// Vista/Estructura/Accion/Compra/finalizarCompra.php
// ACCIÓN MINIMALISTA

$root = __DIR__ . '/../../../../';
require_once $root . 'Control/Session.php';
require_once $root . 'Control/compraControl.php';
// No necesitamos incluir ProductoControl ni UsuarioControl aquí, 
// porque CompraControl se encarga de llamarlos internamente.

$session = new Session();
if (!$session->activa()) {
    header('Location: /TUDW_PDW_Grupo02_TpFinal/Vista/login.php');
    exit;
}

// 1. Obtener Usuario
if (method_exists($session, 'getIDUsuarioLogueado')) {
    $idUsuario = $session->getIDUsuarioLogueado();
} else {
    $idUsuario = $_SESSION['idusuario'] ?? null;
}

if (empty($idUsuario)) {
    header('Location: /TUDW_PDW_Grupo02_TpFinal/Vista/login.php');
    exit;
}

// 2. INVOCAR CONTROLADOR
$compraCtrl = new CompraControl();
$resultado = $compraCtrl->finalizarCompraCompleta($idUsuario);

// 3. REDIRECCIÓN SEGÚN RESULTADO
if ($resultado['exito']) {
    // Éxito: Vamos a la vista de éxito
    // Ajusta la ruta si tienes el archivo en otra carpeta (ej: /Vista/compra/exito.php)
    header("Location: /TUDW_PDW_Grupo02_TpFinal/Vista/compra/exito.php?id=" . $resultado['idcompra']);
} else {
    // Fallo: Manejo de errores según el mensaje devuelto
    $msg = $resultado['mensaje'];
    $detalle = isset($resultado['detalle']) ? "&detalle=" . urlencode($resultado['detalle']) : "";
    
    if ($msg == 'sin_stock') {
        // Si falta stock volvemos al carrito
        header("Location: /TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Compra/mostrarCarrito.php?msg=" . $msg . $detalle);
    } else {
        // Otros errores volvemos al listado
        header("Location: /TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Producto/listado.php?msg=" . $msg);
    }
}
exit;
?>