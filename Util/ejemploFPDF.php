<?php
/**
 * Ejemplo de uso de FPDF para generar PDFs
 * FPDF es una librería para crear documentos PDF con PHP
 */

// Incluir el autoload de Composer para cargar FPDF
require_once __DIR__ . '/../vendor/autoload.php';

// EJEMPLO 1: PDF Básico
function ejemploBasico() {
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(40, 10, 'Hola Mundo!');
    $pdf->Output('I'); // 'I' muestra en navegador, 'D' descarga, 'F' guarda en archivo
}

// EJEMPLO 2: PDF con más contenido
function ejemploCompleto() {
    $pdf = new FPDF();
    $pdf->AddPage();
    
    // Título
    $pdf->SetFont('Arial', 'B', 20);
    $pdf->Cell(0, 10, 'ChristmasMarket - Factura', 0, 1, 'C');
    $pdf->Ln(5); // Salto de línea
    
    // Información del cliente
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 8, 'Cliente: Juan Perez', 0, 1);
    $pdf->Cell(0, 8, 'Fecha: ' . date('d/m/Y'), 0, 1);
    $pdf->Ln(5);
    
    // Tabla de productos
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(80, 10, 'Producto', 1);
    $pdf->Cell(30, 10, 'Cantidad', 1);
    $pdf->Cell(30, 10, 'Precio', 1);
    $pdf->Cell(30, 10, 'Total', 1);
    $pdf->Ln();
    
    // Datos de productos
    $pdf->SetFont('Arial', '', 11);
    $productos = [
        ['nombre' => 'Adorno Navidad', 'cantidad' => 2, 'precio' => 50.00],
        ['nombre' => 'Esferas', 'cantidad' => 1, 'precio' => 54.00],
    ];
    
    $total = 0;
    foreach ($productos as $prod) {
        $subtotal = $prod['cantidad'] * $prod['precio'];
        $total += $subtotal;
        
        $pdf->Cell(80, 8, $prod['nombre'], 1);
        $pdf->Cell(30, 8, $prod['cantidad'], 1, 0, 'C');
        $pdf->Cell(30, 8, '$' . number_format($prod['precio'], 2), 1, 0, 'R');
        $pdf->Cell(30, 8, '$' . number_format($subtotal, 2), 1, 0, 'R');
        $pdf->Ln();
    }
    
    // Total
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(140, 10, 'TOTAL', 1, 0, 'R');
    $pdf->Cell(30, 10, '$' . number_format($total, 2), 1, 0, 'R');
    
    $pdf->Output('I');
}

// EJEMPLO 3: PDF de Compra (para tu proyecto)
function generarPDFCompra($idCompra) {
    require_once __DIR__ . '/../Control/compraControl.php';
    require_once __DIR__ . '/../Control/Session.php';
    
    $session = new Session();
    $compraCtrl = new CompraControl();
    
    // Aquí obtendrías los datos de la compra
    // Por ahora es un ejemplo
    
    $pdf = new FPDF();
    $pdf->AddPage();
    
    // Logo o encabezado
    $pdf->SetFont('Arial', 'B', 24);
    $pdf->SetTextColor(1, 121, 111); // Color verde del tema
    $pdf->Cell(0, 15, 'ChristmasMarket', 0, 1, 'C');
    $pdf->SetTextColor(0, 0, 0);
    
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 5, 'Comprobante de Compra', 0, 1, 'C');
    $pdf->Ln(10);
    
    // Información de la compra
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 8, 'Detalle de Compra #' . $idCompra, 0, 1);
    $pdf->Ln(3);
    
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(40, 7, 'Fecha:', 0);
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(0, 7, date('d/m/Y H:i'), 0, 1);
    
    $pdf->SetFont('Arial', '', 11);
    $pdf->Cell(40, 7, 'Estado:', 0);
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(0, 7, 'Aceptada', 0, 1);
    $pdf->Ln(5);
    
    // Tabla de productos
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetFillColor(220, 220, 220);
    $pdf->Cell(90, 10, 'Producto', 1, 0, 'C', true);
    $pdf->Cell(25, 10, 'Cant.', 1, 0, 'C', true);
    $pdf->Cell(35, 10, 'Precio Unit.', 1, 0, 'C', true);
    $pdf->Cell(35, 10, 'Subtotal', 1, 1, 'C', true);
    
    // Productos (ejemplo)
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(90, 8, 'Bolas navidenas', 1);
    $pdf->Cell(25, 8, '2', 1, 0, 'C');
    $pdf->Cell(35, 8, '$50.00', 1, 0, 'R');
    $pdf->Cell(35, 8, '$100.00', 1, 1, 'R');
    
    $pdf->Cell(90, 8, 'Adorno', 1);
    $pdf->Cell(25, 8, '1', 1, 0, 'C');
    $pdf->Cell(35, 8, '$54.00', 1, 0, 'R');
    $pdf->Cell(35, 8, '$54.00', 1, 1, 'R');
    
    // Total
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(150, 10, 'TOTAL', 1, 0, 'R');
    $pdf->Cell(35, 10, '$154.00', 1, 1, 'R');
    
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'I', 9);
    $pdf->Cell(0, 5, 'Gracias por su compra!', 0, 1, 'C');
    $pdf->Cell(0, 5, 'www.christmasmarket.com', 0, 1, 'C');
    
    $pdf->Output('I', 'Compra_' . $idCompra . '.pdf');
}

// Ejecutar ejemplo según parámetro GET
if (isset($_GET['ejemplo'])) {
    switch ($_GET['ejemplo']) {
        case '1':
            ejemploBasico();
            break;
        case '2':
            ejemploCompleto();
            break;
        case '3':
            $idCompra = isset($_GET['id']) ? $_GET['id'] : 1;
            generarPDFCompra($idCompra);
            break;
        default:
            echo "Ejemplos disponibles:<br>";
            echo "<a href='?ejemplo=1'>Ejemplo 1: PDF Básico</a><br>";
            echo "<a href='?ejemplo=2'>Ejemplo 2: PDF Completo</a><br>";
            echo "<a href='?ejemplo=3&id=1'>Ejemplo 3: PDF de Compra</a><br>";
    }
} else {
    echo "<h2>Ejemplos de FPDF</h2>";
    echo "<p>Elige un ejemplo:</p>";
    echo "<ul>";
    echo "<li><a href='?ejemplo=1'>Ejemplo 1: PDF Básico</a></li>";
    echo "<li><a href='?ejemplo=2'>Ejemplo 2: PDF Completo (Factura)</a></li>";
    echo "<li><a href='?ejemplo=3&id=1'>Ejemplo 3: PDF de Compra</a></li>";
    echo "</ul>";
}
?>
