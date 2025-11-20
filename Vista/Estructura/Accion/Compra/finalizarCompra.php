<?php
// Vista/Estructura/Accion/Compra/finalizarCompra.php

// 1. RUTAS
$root = __DIR__ . '/../../../../';
require_once $root . 'Control/Session.php';
require_once $root . 'Control/compraControl.php';
require_once $root . 'Control/compraEstadoControl.php';
require_once $root . 'Control/productoControl.php';
require_once $root . 'Control/usuarioControl.php';
require_once $root . 'Control/compraItemControl.php'; // Asegúrate que este nombre sea el que usas

// 2. SESIÓN
$session = new Session();
if (!$session->activa()) {
    header('Location: /TUDW_PDW_Grupo02_TpFinal/Vista/login.php');
    exit;
}

// Obtener ID Usuario de forma segura
if (method_exists($session, 'getIDUsuarioLogueado')) {
    $idUsuario = $session->getIDUsuarioLogueado();
} else {
    $idUsuario = $_SESSION['idusuario'] ?? null;
}

// 3. BUSCAR EL CARRITO (ESTADO 1)
$uControl = new UsuarioControl();
$carrito = $uControl->obtenerCarrito($idUsuario);

if ($carrito == null) {
    // No hay carrito para comprar
    header("Location: /TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Producto/listado.php?msg=carrito_vacio");
    exit;
}

// 4. OBTENER PRODUCTOS
$compraCtrl = new CompraControl();
$productos = $compraCtrl->listadoProdCarrito($carrito);

if (count($productos) == 0) {
    header("Location: /TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Producto/listado.php?msg=carrito_vacio");
    exit;
}

// 5. VALIDAR STOCK (Antes de cobrar, miramos si hay stock de todo)
$prodCtrl = new ProductoControl();
$stockOk = true;
$msgError = "";

foreach ($productos as $item) {
    // Buscamos el producto original para ver su stock real
    $listaP = $prodCtrl->buscar(['idproducto' => $item['idproducto']]);
    if (count($listaP) > 0) {
        $objProd = $listaP[0];
        $stockActual = $objProd->getProCantStock();
        $cantidadSolicitada = $item['cicantidad'];
        
        if ($stockActual < $cantidadSolicitada) {
            $stockOk = false;
            $msgError = "No hay suficiente stock de " . $item['pronombre'];
            break;
        }
    }
}

if (!$stockOk) {
    // Si falla el stock, lo devolvemos al carrito con aviso
    header("Location: /TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Compra/mostrarCarrito.php?msg=sin_stock&detalle=".urlencode($msgError));
    exit;
}

// 6. EJECUTAR COMPRA (Si llegamos acá, hay stock)

// A) DESCONTAR STOCK
foreach ($productos as $item) {
    $listaP = $prodCtrl->buscar(['idproducto' => $item['idproducto']]);
    $objProd = $listaP[0];
    $nuevoStock = $objProd->getProCantStock() - $item['cicantidad'];
    
    // Actualizamos el producto
    $datosProd = [
        'idproducto' => $objProd->getID(),
        'pronombre' => $objProd->getProNombre(),
        'prodetalle' => $objProd->getProDetalle(),
        'procantstock' => $nuevoStock,
        'precio' => $objProd->getPrecio(), // Mantener precio
        'proimagen' => $objProd->getImagen() // Mantener imagen
    ];
    $prodCtrl->modificacion($datosProd);
}

// B) CAMBIAR ESTADO DE LA COMPRA
// Paso 1: Cerrar el estado actual (1) poniéndole fecha de fin
$estadoCtrl = new CompraEstadoControl();

// Buscamos el estado 1 activo de esta compra
// Usamos una búsqueda manual o recuperamos el último
$estados = $estadoCtrl->buscar(['idcompra' => $carrito->getID()]);
$ultimoEstado = end($estados);

if ($ultimoEstado) {
    $datosModificacion = [
        'idcompraestado' => $ultimoEstado->getID(),
        'idcompra' => $carrito->getID(),
        'idcompraestadotipo' => 1,
        'cefechaini' => $ultimoEstado->getCeFechaIni(),
        'cefechafin' => date('Y-m-d H:i:s') // Cerramos con fecha de hoy
    ];
    $estadoCtrl->modificacion($datosModificacion);
}

// Paso 2: Crear el nuevo estado (2 = Aceptada/Pendiente)
// OJO: Verifica en tu base de datos qué ID tiene el estado "Aceptada" o "Enviada".
// Usualmente: 1=Iniciada, 2=Aceptada, 3=Enviada, 4=Cancelada
$nuevoEstadoID = 2; 

$estadoCtrl->alta([
    'idcompra' => $carrito->getID(),
    'idcompraestadotipo' => $nuevoEstadoID,
    'cefechaini' => date('Y-m-d H:i:s'),
    'cefechafin' => '0000-00-00 00:00:00' // Queda activo en estado 2
]);

// 7. ÉXITO
// Redirigimos a la tienda con mensaje de éxito
header("Location: /TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Producto/listado.php?msg=compra_realizada");
exit;
?>

