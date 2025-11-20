<?php
// Vista/listadoProductos.php
// Esta vista espera que la acción prepare la variable $productos.
// Si se accede directamente a la vista sin pasar por la acción, redirigimos al action.
if (!isset($productos)) {
    header('Location: /Vista/Estructura/Accion/Producto/listado.php');
    exit;
}
require_once __DIR__ . '/Estructura/header.php';
require_once __DIR__ . '/../Util/funciones.php';

// Comprobar sesión para controlar acciones disponibles en la vista
require_once __DIR__ . '/../Control/Session.php';
$session = new Session();
$logged = $session->sesionActiva();
// Determinar rol activo para ocultar acciones a administradores
$rolActivo = $session->getRolActivo();
$isAdmin = (!empty($rolActivo) && isset($rolActivo['rol']) && strtolower($rolActivo['rol']) === 'administrador');
?>
<div class="container mt-4">
    <h2>Productos</h2>
    <div class="row">
        <?php if (!empty($productos)) : ?>
            <?php foreach ($productos as $p) : ?>
                <div class="col-md-4 mb-3">
                    <div class="card" style="box-shadow: 0 4px 16px rgba(0,0,0,0.12); border-radius: 12px; padding: 8px;">
                        <?php if (method_exists($p, 'getImagen') && $p->getImagen()) : ?>
                            <?php $imgSrc = img_public_url($p->getImagen()); ?>
                            <img src="<?php echo htmlspecialchars($imgSrc); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($p->getProNombre()); ?>">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($p->getProNombre()); ?></h5>
                            <p class="card-text">Precio: $<?php echo number_format($p->getPrecio(), 2); ?></p>
                            <p class="card-text">Stock: <?php echo intval($p->getProCantStock()); ?></p>
                            <?php if ($logged && !$isAdmin) : ?>
                                <a href="/TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Compra/agregarProdCarrito.php?idproducto=<?php echo urlencode($p->getID()); ?>" class="btn btn-primary">Agregar al carrito</a>
                            <?php elseif (!$logged) : ?>
                                <a href="/TUDW_PDW_Grupo02_TpFinal/Vista/login.php" class="btn btn-secondary" title="Debe iniciar sesión para agregar productos">Iniciar sesión</a>
                            <?php else: ?>
                                <!-- Administrador: no mostrar botón de carrito -->
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <div class="col-12">No hay productos para mostrar.</div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/Estructura/footer.php'; ?>
