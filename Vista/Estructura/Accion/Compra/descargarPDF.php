<?php
// Vista/Estructura/Accion/Compra/descargarPDF.php

// RUTAS
$root = __DIR__ . '/../../../../';
require_once $root . 'Control/Session.php';
require_once $root . 'Control/compraControl.php';
require_once $root . 'Control/compraEstadoControl.php';
// Incluimos nuestra nueva Vista PDF
require_once $root . 'Vista/PDF/ComprobantePDF.php'; 

$session = new Session();
if (!$session->activa()) { exit('Acceso denegado'); }

// DATOS
$idCompra = $_GET['id'] ?? null;
if (!$idCompra) { exit('Falta ID'); }

// LLAMAR AL CONTROLADOR 
$compraCtrl = new CompraControl();
$lista = $compraCtrl->buscar(['idcompra' => $idCompra]);
$objCompra = $lista[0];

// Validar propiedad

$idUsuarioLogueado = $session->getIDUsuarioLogueado();
$rol = $session->getRolActivo();
$esAdmin = false;
if (!empty($rol) && ($rol['id'] == 1 || $rol['rol'] == 'Administrador')) {
    $esAdmin = true;
}
// Obtenemos quién es el dueño real de la compra
$idDueñoCompra = $objCompra->getObjUsuario()->getID();
if (!$esAdmin && $idUsuarioLogueado != $idDueñoCompra) {
    die("ACCESO DENEGADO: No tienes permiso para descargar el comprobante de otra persona.");
}

// Obtener items
$productos = $compraCtrl->listadoProdCarrito($objCompra);

// Obtener estado
$estadoCtrl = new CompraEstadoControl();
$estados = $estadoCtrl->buscar(['idcompra' => $idCompra]);
$ultimoEstado = end($estados);

// 4. LLAMAR A LA VISTA (Generar visualización)
$vistaPDF = new ComprobantePDF();
$contenido = $vistaPDF->generarContenido($objCompra, $ultimoEstado, $productos);

// 5. ENTREGAR AL USUARIO
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="Orden_'.$idCompra.'.pdf"');
echo $contenido;
exit;
?>