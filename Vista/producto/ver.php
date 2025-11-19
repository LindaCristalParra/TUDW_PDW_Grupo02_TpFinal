<?php
// Vista/producto/ver.php - detalle público de producto
require_once __DIR__ . '/../../Control/Session.php';
require_once __DIR__ . '/../../Control/productoControl.php';
require_once __DIR__ . '/../../Control/menuControl.php';

$session = new Session();
$menuCtrl = new MenuControl();
$menuData = $menuCtrl->armarMenu();

$prodCtrl = new ProductoControl();
$product = null;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $list = $prodCtrl->buscar(['idproducto' => $_GET['id']]);
    if (!empty($list)) {
        $p = $list[0];
        $product = [
            'idproducto' => $p->getID(),
            'pronombre' => $p->getProNombre(),
            'prodetalle' => $p->getProDetalle(),
            'procantstock' => $p->getProCantStock(),
            'precio' => $p->getPrecio(),
            'proimagen' => $p->getImagen(),
        ];
    }
}

require_once __DIR__ . '/../Estructura/header.php';
?>
<div class="container mt-4">
    <?php if ($product): ?>
        <div class="row">
            <div class="col-md-6">
                <?php if (!empty($product['proimagen'])): ?>
                    <img src="/TUDW_PDW_Grupo02_TpFinal/Util/Imagenes/<?= htmlspecialchars($product['proimagen']) ?>" class="img-fluid" alt="<?= htmlspecialchars($product['pronombre']) ?>">
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <h2><?= htmlspecialchars($product['pronombre']) ?></h2>
                <p><?= nl2br(htmlspecialchars($product['prodetalle'])) ?></p>
                <p class="fw-bold">Precio: $<?= number_format($product['precio'],2) ?></p>
                <p>Stock: <?= intval($product['procantstock']) ?></p>
                <?php if ($session->sesionActiva()): ?>
                    <a href="/TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Compra/agregarProdCarrito.php?idproducto=<?= $product['idproducto'] ?>" class="btn btn-primary">Agregar al carrito</a>
                <?php else: ?>
                    <a href="/TUDW_PDW_Grupo02_TpFinal/Vista/login.php" class="btn btn-outline-primary">Iniciar sesión para comprar</a>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">Producto no encontrado.</div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../Estructura/footer.php'; ?>
