<?php
// Vista/compra/detalleCompra.php
require_once __DIR__ . '/../Estructura/header.php';

// Colores para estados
$coloresEstado = [
    1 => 'bg-secondary', // Iniciada
    2 => 'bg-success',   // Aceptada
    3 => 'bg-primary',   // Enviada
    4 => 'bg-danger',    // Cancelada
];

$idEstadoTipo = $ultimoEstado ? $ultimoEstado->getObjCompraEstadoTipo()->getID() : 0;
$descEstado = $ultimoEstado ? $ultimoEstado->getObjCompraEstadoTipo()->getCetDescripcion() : 'Desconocido';
$badgeColor = $coloresEstado[$idEstadoTipo] ?? 'bg-info';
?>

<div class="container mt-5 mb-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 style="color:var(--pine-green);">
            <i class="bi bi-receipt"></i> Detalle de Compra #<?php echo $objCompra->getID(); ?>
        </h2>
        <div>
            <a href="/TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Compra/descargarPDF.php?id=<?php echo $objCompra->getID(); ?>" class="btn btn-danger me-2">
                <i class="bi bi-file-pdf"></i> Descargar PDF
            </a>
            <a href="/TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Compra/listado.php" class="btn btn-outline-secondary">
                &larr; Volver a Mis Compras
            </a>
        </div>
    </div>

    <div class="card shadow-sm mb-4 border-top-0 border-start-0 border-end-0 border-bottom-0" style="border-left: 5px solid var(--pine-green) !important;">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <small class="text-muted text-uppercase">Fecha de Compra</small><br>
                    <strong><?php echo $objCompra->getCoFecha(); ?></strong>
                </div>
                <div class="col-md-4">
                    <small class="text-muted text-uppercase">Estado Actual</small><br>
                    <span class="badge <?php echo $badgeColor; ?> fs-6 rounded-pill px-3">
                        <?php echo ucfirst($descEstado); ?>
                    </span>
                </div>
                <div class="col-md-4">
                    <small class="text-muted text-uppercase">Cliente</small><br>
                    <strong><?php echo $objCompra->getObjUsuario()->getUsNombre(); ?></strong>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Productos Adquiridos</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Producto</th>
                            <th>Precio Unit.</th>
                            <th class="text-center">Cant.</th>
                            <th class="text-end pe-4">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalGeneral = 0;
                        foreach ($productos as $p): 
                            $precio = floatval($p['precio']);
                            $cantidad = intval($p['cicantidad']);
                            $subtotal = $precio * $cantidad;
                            $totalGeneral += $subtotal;
                            
                            $imgNombre = $p['proimagen'] ?? 'sin_imagen.png';
                            $rutaImagen = "/TUDW_PDW_Grupo02_TpFinal/Util/Imagenes/" . $imgNombre;
                        ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <img src="<?= htmlspecialchars($rutaImagen) ?>" 
                                             class="rounded border" 
                                             style="width: 50px; height: 50px; object-fit: contain; margin-right: 12px;">
                                        <div>
                                            <span class="fw-bold d-block"><?= htmlspecialchars($p['pronombre']) ?></span>
                                            <small class="text-muted"><?= htmlspecialchars($p['prodetalle']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>$<?= number_format($precio, 2, ',', '.') ?></td>
                                <td class="text-center"><?= $cantidad ?></td>
                                <td class="text-end pe-4 fw-bold">$<?= number_format($subtotal, 2, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="3" class="text-end pe-3 fs-5"><strong>Total Final:</strong></td>
                            <td class="text-end pe-4 fs-5 text-success fw-bold">$<?= number_format($totalGeneral, 2, ',', '.') ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    
    <?php if ($idEstadoTipo == 1): ?>
        <div class="alert alert-warning mt-3 d-flex justify-content-between align-items-center">
            <span>Esta compra aún no ha sido finalizada.</span>
            <a href="/TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Compra/mostrarCarrito.php" class="btn btn-warning btn-sm">Ir al Carrito Actual</a>
        </div>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/../Estructura/footer.php'; ?>