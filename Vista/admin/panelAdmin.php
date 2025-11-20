<?php
// Vista/admin/panelAdmin.php - panel principal para administradores
require_once __DIR__ . '/../Estructura/header.php';
require_once __DIR__ . '/../../Control/Session.php';

$session = new Session();
if (!$session->sesionActiva()) {
    header('Location: /TUDW_PDW_Grupo02_TpFinal/Vista/login.php');
    exit;
}

$rol = $session->getRolActivo();
$isAdmin = (!empty($rol) && isset($rol['rol']) && strtolower($rol['rol']) === 'administrador');
if (!$isAdmin) {
    echo '<div class="container mt-4">';
    echo '<div class="alert alert-danger">Acceso denegado. Debés ser administrador.</div>';
    echo '</div>';
    require_once __DIR__ . '/../Estructura/footer.php';
    exit;
}

?>
<div class="container mt-4">
    <h2>Panel de Administración</h2>
    <div class="list-group mt-3">
        <a href="/TUDW_PDW_Grupo02_TpFinal/Vista/admin/cargarProducto.php" class="list-group-item list-group-item-action">Cargar productos</a>
        <a href="/TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Compra/listado.php" class="list-group-item list-group-item-action">Mis ventas</a>
    
    </div>
</div>

<?php require_once __DIR__ . '/../Estructura/footer.php'; ?>
