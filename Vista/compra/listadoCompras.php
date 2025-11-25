<?php
// Vista/compra/listadoCompras.php
require_once __DIR__ . '/../Estructura/header.php';
?>

<div class="container mt-5 mb-5">
    <h2 class="mb-4" style="color:var(--pine-green);">
        <i class="bi bi-clock-history"></i> Historial de Compras
    </h2>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-info alert-dismissible fade show">
            <?php echo htmlspecialchars($_GET['msg']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (empty($compras)): ?>
        <div class="alert alert-secondary text-center p-5">
            <h4>No se encontraron compras.</h4>
            <a href="/TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Producto/listado.php" class="btn btn-success mt-3">Ir a la Tienda</a>
        </div>
    <?php else: ?>
        
        <div class="card shadow">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">ID</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Usuario</th>
                                <th class="text-end pe-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($compras as $c): 
                                $estado = ucfirst($c['estado']);
                                // Colores simples según estado
                                $color = 'bg-secondary';
                                if ($c['estado'] == 'aceptada') $color = 'bg-success';
                                if ($c['estado'] == 'enviada') $color = 'bg-primary';
                                if ($c['estado'] == 'cancelada') $color = 'bg-danger';
                            ?>
                                <tr>
                                    <td class="ps-4 fw-bold">#<?php echo $c['idcompra']; ?></td>
                                    <td><?php echo $c['cofecha']; ?></td>
                                    <td><span class="badge <?php echo $color; ?>"><?php echo $estado; ?></span></td>
                                    <td><?php echo $c['usnombre']; ?></td>
                                    <td class="text-end pe-4">
                                        <a href="/TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Compra/verCompra.php?id=<?php echo $c['idcompra']; ?>" class="btn btn-sm btn-outline-primary">
                                            Ver Detalle
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../Estructura/footer.php'; ?>

