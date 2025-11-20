<?php
// Vista/Estructura/Accion/Compra/descargarPDF.php
// Genera PDF de la compra

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../../../../Control/Session.php';
require_once __DIR__ . '/../../../../Control/compraControl.php';
require_once __DIR__ . '/../../../../Control/compraEstadoControl.php';

// Configurar zona horaria
date_default_timezone_set('America/Argentina/Buenos_Aires');

$session = new Session();
if (!$session->activa()) {
    die('Debe iniciar sesión para descargar el PDF');
}

$idUsuarioLogueado = $session->getIDUsuarioLogueado() ?? $_SESSION['idusuario'];
$idCompra = $_GET['id'] ?? null;

if (!$idCompra) {
    die('ID de compra no especificado');
}

// BUSCAR LA COMPRA
$compraCtrl = new CompraControl();
$listaCompras = $compraCtrl->buscar(['idcompra' => $idCompra]);

if (empty($listaCompras)) {
    die("La compra no existe.");
}

$objCompra = $listaCompras[0];

// Verificar que la compra pertenezca al usuario logueado
if ($objCompra->getObjUsuario()->getID() != $idUsuarioLogueado) {
    die("Acceso Denegado: Esta compra no te pertenece.");
}

// OBTENER ESTADO ACTUAL
$estadoCtrl = new CompraEstadoControl();
$estados = $estadoCtrl->buscar(['idcompra' => $idCompra]);
$ultimoEstado = !empty($estados) ? end($estados) : null;

// OBTENER PRODUCTOS 
$productos = $compraCtrl->listadoProdCarrito($objCompra);

// GENERAR PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 15);

// ===== ENCABEZADO =====
$pdf->SetFont('Arial', 'B', 26);
$pdf->SetTextColor(1, 121, 111); // Color verde del tema
$pdf->Cell(0, 12, 'ChristmasMarket', 0, 1, 'C');
$pdf->SetTextColor(0, 0, 0);

$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 6, 'Comprobante de Compra', 0, 1, 'C');
$pdf->Ln(8);

// ===== INFORMACIÓN DE LA COMPRA =====
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetFillColor(1, 121, 111);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(0, 10, '  Detalle de Compra #' . $objCompra->getID(), 0, 1, 'L', true);
$pdf->SetTextColor(0, 0, 0);
$pdf->Ln(3);

// Información en dos columnas
$pdf->SetFont('Arial', '', 10);
$colWidth = 95;

// Columna izquierda
$pdf->Cell(40, 6, 'Fecha de Compra:', 0, 0);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($colWidth - 40, 6, $objCompra->getCoFecha(), 0, 0);

// Columna derecha
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(35, 6, 'Cliente:', 0, 0);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 6, $objCompra->getObjUsuario()->getUsNombre(), 0, 1);

// Segunda fila
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(40, 6, 'Estado:', 0, 0);
$pdf->SetFont('Arial', 'B', 10);
$estadoDesc = $ultimoEstado ? ucfirst($ultimoEstado->getObjCompraEstadoTipo()->getCetDescripcion()) : 'Desconocido';
$pdf->Cell($colWidth - 40, 6, $estadoDesc, 0, 1);

$pdf->Ln(6);

// ===== TABLA DE PRODUCTOS =====
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetFillColor(220, 220, 220);
$pdf->Cell(90, 9, 'Producto', 1, 0, 'L', true);
$pdf->Cell(25, 9, 'Cant.', 1, 0, 'C', true);
$pdf->Cell(35, 9, 'Precio Unit.', 1, 0, 'R', true);
$pdf->Cell(35, 9, 'Subtotal', 1, 1, 'R', true);

// Productos
$pdf->SetFont('Arial', '', 10);
$totalGeneral = 0;

foreach ($productos as $p) {
    $precio = floatval($p['precio']);
    $cantidad = intval($p['cicantidad']);
    $subtotal = $precio * $cantidad;
    $totalGeneral += $subtotal;
    
    $nombreProducto = $p['pronombre'];
    
    // Si el nombre es muy largo, truncarlo
    if (strlen($nombreProducto) > 40) {
        $nombreProducto = substr($nombreProducto, 0, 37) . '...';
    }
    
    $pdf->Cell(90, 8, $nombreProducto, 1, 0, 'L');
    $pdf->Cell(25, 8, $cantidad, 1, 0, 'C');
    $pdf->Cell(35, 8, '$' . number_format($precio, 2, ',', '.'), 1, 0, 'R');
    $pdf->Cell(35, 8, '$' . number_format($subtotal, 2, ',', '.'), 1, 1, 'R');
}

// ===== TOTAL =====
$pdf->SetFont('Arial', 'B', 13);
$pdf->SetFillColor(1, 121, 111);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(150, 10, 'TOTAL FINAL', 1, 0, 'R', true);
$pdf->Cell(35, 10, '$' . number_format($totalGeneral, 2, ',', '.'), 1, 1, 'R', true);
$pdf->SetTextColor(0, 0, 0);

$pdf->Ln(12);

// ===== PIE DE PÁGINA =====
$pdf->SetFont('Arial', 'I', 9);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(0, 5, utf8_decode('¡Gracias por su compra!'), 0, 1, 'C');
$pdf->Cell(0, 5, 'www.christmasmarket.com', 0, 1, 'C');
$pdf->Ln(3);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(0, 4, 'Documento generado el: ' . date('d/m/Y H:i:s'), 0, 1, 'C');

// Descargar PDF
$nombreArchivo = 'Compra_' . $idCompra . '_' . date('Ymd') . '.pdf';
$pdf->Output('D', $nombreArchivo);
?>
