<?php
// Vista/admin/cargarMenu.php
require_once __DIR__ . '/../Estructura/header.php';
?>
<div class="container mt-4">
    <h2>Crear nuevo Menú</h2>

    <?php if (!empty($mensaje)) : ?>
        <div class="alert alert-<?= htmlspecialchars($tipoMensaje) ?>"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <form method="post" action="/TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Menu/cargar.php">
        
        <div class="mb-3">
            <label class="form-label">Nombre (Clave para RouteMap)</label>
            <input type="text" name="menombre" class="form-control" placeholder="Ej: Ofertas" required>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Descripción</label>
            <input type="text" name="medescripcion" class="form-control" placeholder="Ej: Descuentos especiales">
        </div>

        <div class="mb-4 border p-3 rounded bg-light">
            <label class="form-label fw-bold mb-2">Asignar Permisos (¿Quién puede ver este botón?):</label>
            
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="roles[]" value="1" id="rolAdmin" checked>
                <label class="form-check-label" for="rolAdmin">Administrador</label>
            </div>
            
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="roles[]" value="2" id="rolCliente">
                <label class="form-check-label" for="rolCliente">Cliente</label>
            </div>
            
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="roles[]" value="3" id="rolAnonimo">
                <label class="form-check-label" for="rolAnonimo">Público (Anónimo)</label>
            </div>
            <div class="form-text text-muted mt-1">Si no seleccionas ninguno, el menú estará oculto para todos.</div>
        </div>
        <button class="btn btn-success" type="submit">Crear menú</button>
        <a href="/TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Menu/listado.php" class="btn btn-secondary">Volver</a>
    </form>
</div>

<?php require_once __DIR__ . '/../Estructura/footer.php'; ?>