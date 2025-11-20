<?php
// Vista/venta/listado.php
require_once __DIR__ . '/../Estructura/header.php';
require_once __DIR__ . '/../../Control/compraControl.php';
require_once __DIR__ . '/../../vendor/autoload.php';
?>

<style>

.btn-cancel{ background:#ffffff; color:var(--header-red); border:1px solid var(--header-red); }
.btn-cancel:hover, .btn-cancel:focus{ background:var(--header-red) !important; color:#ffffff !important; border-color:var(--header-red) !important; }
.btn-view{ background:#ffffff; color:var(--pine-green); border:1px solid var(--pine-green); }
.btn-view:hover, .btn-view:focus{ background:var(--pine-green) !important; color:#ffffff !important; border-color:var(--pine-green) !important; }
</style>

<?php
use Carbon\Carbon;

function formatearFechaCompra($fechaBD) {
    Carbon::setLocale('es');
    $fecha = Carbon::parse($fechaBD)->timezone('America/Argentina/Buenos_Aires');
    return $fecha->format('d/m/Y H:i') . ' (' . $fecha->diffForHumans() . ')';
}

// $compras debe proveerla la acción `Venta/listado`
if (!isset($compras)) {
    echo '<div class="container mt-4"><div class="alert alert-warning">No hay compras para mostrar o la acción no proporcionó datos.</div></div>';
    require_once __DIR__ . '/../Estructura/footer.php';
    exit;
}

$compraCtrl = new CompraControl();
?>

<div class="container mt-4">
    <h2>Listado de Ventas</h2>
    <?php if (!empty($compras)) : ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Usuario</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($compras as $c) : ?>
                    <?php $fechaFormateada = formatearFechaCompra($c['cofecha']);
                          $puedeCancelar = $compraCtrl->puedeCancelarCompra($c['cofecha']); ?>
                    <tr>
                        <td><?= htmlspecialchars($c['idcompra']) ?></td>
                        <td>
                            <?= htmlspecialchars($fechaFormateada) ?>
                        </td>
                        <td><?= htmlspecialchars($c['usnombre'] ?? 'N/A') ?></td>
                        <td>
                            <?php
                            $estadoBadge = 'secondary';
                            switch(strtolower($c['estado'])) {
                                case 'iniciada': $estadoBadge = 'info'; break;
                                case 'aceptada': $estadoBadge = 'success'; break;
                                case 'enviada': $estadoBadge = 'primary'; break;
                                case 'cancelada': $estadoBadge = 'danger'; break;
                            }
                            ?>
                            <span class="badge bg-<?= $estadoBadge ?>"><?= htmlspecialchars($c['estado'] ?? '') ?></span>
                        </td>
                        <td>
                            <a href="/TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Compra/verCompra.php?id=<?php echo urlencode($c['idcompra']); ?>" class="btn btn-sm btn-view" style="background:#ffffff;color:var(--pine-green);border:1px solid var(--pine-green);">Ver</a>
                            <?php if (strtolower($c['estado']) !== 'enviado' && strtolower($c['estado']) !== 'cancelada') : ?>
                                <?php if ($puedeCancelar) : ?>
                                    <form method="post" action="/TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Compra/modificarEstado.php" style="display:inline;">
                                        <input type="hidden" name="idcompra" value="<?= htmlspecialchars($c['idcompra']) ?>">
                                        <input type="hidden" name="idcompraestado" value="<?= htmlspecialchars($c['idcompraestado']) ?>">
                                        <input type="hidden" name="idcompraestadotipo" value="4">
                                        <button type="submit" class="btn btn-sm btn-cancel" style="background:#ffffff;color:var(--header-red);border:1px solid var(--header-red);" onclick="return confirm('¿Seguro que deseas cancelar esta compra?');">Cancelar</button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-secondary" disabled title="Solo se pueden cancelar compras con menos de 24 horas">Cancelar</button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">No hay ventas para mostrar.</div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../Estructura/footer.php';
