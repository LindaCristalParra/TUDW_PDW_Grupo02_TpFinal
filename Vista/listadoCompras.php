
<?php
require_once __DIR__ . '/Estructura/header.php';
require_once __DIR__ . '/../Control/Session.php';
require_once __DIR__ . '/../Control/compraControl.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Carbon\Carbon;

/**
 * Formatea la fecha de compra para mostrar al usuario
 * @param string $fechaBD Fecha desde la base de datos
 * @return string Fecha formateada
 */
function formatearFechaCompra($fechaBD) {
    Carbon::setLocale('es');
    
    // Parsear la fecha desde la BD (asumiendo que viene en la zona horaria del servidor)
    // y convertirla a la zona horaria de Argentina
    $fecha = Carbon::parse($fechaBD)->timezone('America/Argentina/Buenos_Aires');
    
    return $fecha->format('d/m/Y H:i') . ' (' . $fecha->diffForHumans() . ')';
}

$session = new Session();
if (!$session->sesionActiva()) {
    echo '<div class="container mt-4"><div class="alert alert-warning">Debes iniciar sesión para ver tus compras.</div></div>';
    require_once __DIR__ . '/../Estructura/footer.php';
    exit;
}

$idUsuario = $session->getIDUsuarioLogueado();
$compraCtrl = new CompraControl();

$mensaje = '';
$tipoMensaje = '';
$debugInfo = '';

// Cancelar compra por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancelar_id']) && isset($_POST['cancelar_estado'])) {
    $idCompra = $_POST['cancelar_id'];
    $idCompraEstado = $_POST['cancelar_estado'];
    
    // Habilitar captura de errores
    ob_start();
    
    // Estado 4 = cancelada
    $resultado = $compraCtrl->cancelarCompra([
        'idcompra' => $idCompra,
        'idcompraestado' => $idCompraEstado,
        'idcompraestadotipo' => 4
    ]);
    
    $debugInfo = ob_get_clean();
    
    if ($resultado) {
        $mensaje = 'Compra cancelada exitosamente.';
        $tipoMensaje = 'success';
    } else {
        $mensaje = 'Error al cancelar la compra. Por favor, intente nuevamente.';
        $tipoMensaje = 'danger';
        // Agregar info de debug si está disponible
        if (!empty($debugInfo)) {
            $mensaje .= '<br><small>Debug: ' . htmlspecialchars($debugInfo) . '</small>';
        }
    }
}

$compras = $compraCtrl->listarCompras($idUsuario);
?>
<div class="container mt-4">
    <h2>Mis Compras</h2>
    
    <?php if (!empty($mensaje)) : ?>
        <div class="alert alert-<?php echo $tipoMensaje; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($mensaje); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($compras)) : ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($compras as $c) : ?>
                    <?php 
                    // Formatear fecha con Carbon
                    $fechaFormateada = formatearFechaCompra($c['cofecha']);
                    
                    // Verificar si puede cancelar (menos de 24 horas)
                    $puedeCancelar = $compraCtrl->puedeCancelarCompra($c['cofecha']);
                    
                    // Calcular fecha estimada de entrega
                    $fechaEntrega = $compraCtrl->fechaEntregaEstimada($c['cofecha']);
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($c['idcompra']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($fechaFormateada); ?>
                            <?php if (strtolower($c['estado']) !== 'enviado' && strtolower($c['estado']) !== 'cancelada') : ?>
                                <br><small class="text-muted">Entrega estimada: <?php echo htmlspecialchars($fechaEntrega); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                            $estadoBadge = 'secondary';
                            switch(strtolower($c['estado'])) {
                                case 'iniciada': $estadoBadge = 'info'; break;
                                case 'aceptada': $estadoBadge = 'success'; break;
                                case 'enviado': $estadoBadge = 'primary'; break;
                                case 'cancelada': $estadoBadge = 'danger'; break;
                            }
                            ?>
                            <span class="badge bg-<?php echo $estadoBadge; ?>"><?php echo htmlspecialchars($c['estado'] ?? ''); ?></span>
                        </td>
                        <td>
                            <a href="/TUDW_PDW_Grupo02_TpFinal/Vista/compra/ver.php?id=<?php echo urlencode($c['idcompra']); ?>" class="btn btn-sm btn-outline-primary">Ver</a>
                            <?php if (strtolower($c['estado']) !== 'enviado' && strtolower($c['estado']) !== 'cancelada') : ?>
                                <?php if ($puedeCancelar) : ?>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="cancelar_id" value="<?php echo htmlspecialchars($c['idcompra']); ?>">
                                        <input type="hidden" name="cancelar_estado" value="<?php echo htmlspecialchars($c['idcompraestado']); ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro que deseas cancelar esta compra?');">Cancelar</button>
                                    </form>
                                <?php else : ?>
                                    <button class="btn btn-sm btn-secondary" disabled title="Solo se pueden cancelar compras con menos de 24 horas">Cancelar</button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else : ?>
        <div class="alert alert-info">No hay compras para mostrar.</div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/Estructura/footer.php'; ?>
