<?php
// Vista/admin/cargarProducto.php - formulario para que el admin cargue productos
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
    echo '<div class="container mt-4"><div class="alert alert-danger">Acceso denegado.</div></div>';
    require_once __DIR__ . '/../Estructura/footer.php';
    exit;
}

?>
<div class="container mt-4">
    <h2>Cargar nuevo producto</h2>
    <form action="/TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Producto/cargarNuevoProducto.php" method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" name="pronombre" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Detalle</label>
            <textarea name="prodetalle" class="form-control" rows="3"></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Stock</label>
            <input type="number" name="procantstock" class="form-control" min="0" value="0">
        </div>
        <div class="mb-3">
            <label class="form-label">Precio</label>
            <input type="number" step="0.01" name="precio" class="form-control" required>
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" name="prodeshabilitado" value="1" class="form-check-input" id="deshabilitado">
            <label class="form-check-label" for="deshabilitado">Deshabilitado</label>
        </div>
        <div class="mb-3">
            <label class="form-label">Imagen</label>
            <input type="file" name="imagen" class="form-control" accept="image/*">
        </div>
        <button class="btn btn-success" type="submit">Cargar producto</button>
        <a href="/TUDW_PDW_Grupo02_TpFinal/Vista/admin/panelAdmin.php" class="btn btn-secondary">Volver</a>
    </form>
</div>

<?php require_once __DIR__ . '/../Estructura/footer.php'; ?>
