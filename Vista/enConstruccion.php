<?php
// Ubicación: Vista/Estructura/Accion/Menu/enConstruccion.php

include_once __DIR__ . '/Estructura/header.php';
?>

<div class="container d-flex flex-column justify-content-center align-items-center" style="min-height: 60vh;">
    <div class="text-center p-5 shadow-sm rounded bg-white">
        <div style="font-size: 5rem;">🚧</div>
        
        <h1 class="mt-3 mb-3" style="color: var(--pine-green);">Sección en Construcción</h1>
        
        <p class="lead text-muted">
            Estamos trabajando para está sección.<br>
            
        </p>
        
        <div class="mt-4">
            <a href="/TUDW_PDW_Grupo02_TpFinal/Vista/index.php" class="btn btn-outline-success">
                <i class="bi bi-house-door-fill"></i> Volver al Inicio
            </a>
        </div>
    </div>
</div>

<?php
include_once __DIR__ . '/Estructura/footer.php';
?>