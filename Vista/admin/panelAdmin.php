<?php
// Vista/admin/panelAdmin.php - panel principal para administradores
require_once __DIR__ . '/../Estructura/header.php';
require_once __DIR__ . '/../../Control/Session.php';

$session = new Session();
if (!$session->sesionActiva()) {
    header('Location: /TUDW_PDW_Grupo02_TpFinal/Vista/login.php');
    exit;
}

$session->exigirAdmin(__DIR__ . '/../Estructura/footer.php');

?>
<div class="container mt-4">
    <h2>Panel de Administración</h2>
    <div class="list-group mt-3">
        <a href="/TUDW_PDW_Grupo02_TpFinal/Vista/admin/cargarProducto.php" class="list-group-item list-group-item-action">Cargar productos</a>
        <a href="/TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Venta/listado.php" class="list-group-item list-group-item-action">Mis ventas</a>
        <a href="/TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Menu/listado.php" class="list-group-item list-group-item-action">Administrar menús</a>
    
    </div>
</div>

<?php require_once __DIR__ . '/../Estructura/footer.php'; ?>
