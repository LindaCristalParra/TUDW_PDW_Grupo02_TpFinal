<?php
// Acción: deshabilitarProducto.php
// Recibe POST: idproducto, accion=deshabilitar
require_once __DIR__ . '/../../../../Control/Session.php';
require_once __DIR__ . '/../../../../Control/productoControl.php';

$session = new Session();
if (!$session->sesionActiva()) {
    header('Location: /TUDW_PDW_Grupo02_TpFinal/Vista/login.php');
    exit;
}

$rol = $session->getRolActivo();
$isAdmin = false;
if (!empty($rol) && isset($rol['rol'])) {
    $rolDesc = strtolower($rol['rol']);
    if (strpos($rolDesc, 'admin') !== false || $rolDesc === 'administrador') {
        $isAdmin = true;
    }
}
if (!$isAdmin) {
    // acceso denegado
    header('HTTP/1.1 403 Forbidden');
    echo 'Acceso denegado';
    exit;
}

$id = null;
$accion = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['idproducto']) ? intval($_POST['idproducto']) : null;
    $accion = isset($_POST['accion']) ? $_POST['accion'] : null;
} else {
    $id = isset($_GET['idproducto']) ? intval($_GET['idproducto']) : null;
    $accion = isset($_GET['accion']) ? $_GET['accion'] : null;
}

$redirect = '/TUDW_PDW_Grupo02_TpFinal/Vista/listadoProductos.php';

if ($id && $accion === 'deshabilitar') {
    $prodCtrl = new ProductoControl();
    $ok = $prodCtrl->deshabilitarProducto(['idproducto' => $id, 'accion' => 'deshabilitar']);
    if ($ok) {
        $msg = 'Producto deshabilitado correctamente';
    } else {
        $msg = 'No se pudo deshabilitar el producto';
    }
    header('Location: ' . $redirect . '?msg=' . urlencode($msg));
    exit;
}

header('Location: ' . $redirect);
exit;
