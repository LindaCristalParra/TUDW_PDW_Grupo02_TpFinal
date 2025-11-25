<?php
// Archivo: Vista/PDF/ComprobantePDF.php

require_once __DIR__ . '/../../vendor/autoload.php'; // Carga librería FPDF

class ComprobantePDF extends FPDF {
    
    // Cabecera automática
    function Header() {
        $this->SetFont('Arial', 'B', 26);
        $this->SetTextColor(1, 121, 111); // Color Verde
        $this->Cell(0, 12, 'ChristmasMarket', 0, 1, 'C');
        $this->SetTextColor(0, 0, 0);
        
        $this->SetFont('Arial', '', 11);
        $this->Cell(0, 6, 'Comprobante de Compra', 0, 1, 'C');
        $this->Ln(8);
    }

    // Pie de página automático
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128);
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    // Método para generar el cuerpo
    public function generarContenido($objCompra, $ultimoEstado, $productos) {
        $this->AliasNbPages();
        $this->AddPage();
        
        // --- INFO COMPRA ---
        $this->SetFont('Arial', 'B', 14);
        $this->SetFillColor(1, 121, 111);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(0, 10, '  Orden #' . $objCompra->getID(), 0, 1, 'L', true);
        $this->SetTextColor(0, 0, 0);
        $this->Ln(5);

        // Datos
        $this->SetFont('Arial', '', 10);
        $nombreUser = $objCompra->getObjUsuario() ? $objCompra->getObjUsuario()->getUsNombre() : 'Cliente';
        $estadoDesc = $ultimoEstado ? ucfirst($ultimoEstado->getObjCompraEstadoTipo()->getCetDescripcion()) : 'Desconocido';

        // Fila 1: Fecha
        $this->Cell(30, 6, 'Fecha:', 0, 0);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(60, 6, $objCompra->getCoFecha(), 0, 0);
        
        // Fila 1 (cont): Cliente
        $this->SetFont('Arial', '', 10);
        $this->Cell(20, 6, 'Cliente:', 0, 0);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(0, 6, utf8_decode($nombreUser), 0, 1);

        // --- ¡AQUÍ ESTABA EL ERROR! FALTABA IMPRIMIR EL ESTADO ---
        // Fila 2: Estado
        $this->SetFont('Arial', '', 10);
        $this->Cell(30, 6, 'Estado:', 0, 0);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(0, 6, utf8_decode($estadoDesc), 0, 1);
        // ---------------------------------------------------------

        // --- TABLA PRODUCTOS ---
        $this->Ln(10);
        $this->SetFont('Arial', 'B', 11);
        $this->SetFillColor(230, 230, 230);
        $this->Cell(90, 9, 'Producto', 1, 0, 'L', true);
        $this->Cell(25, 9, 'Cant.', 1, 0, 'C', true);
        $this->Cell(35, 9, 'Precio', 1, 0, 'R', true);
        $this->Cell(35, 9, 'Subtotal', 1, 1, 'R', true);

        $this->SetFont('Arial', '', 10);
        $totalGeneral = 0;

        foreach ($productos as $p) {
            $precio = floatval($p['precio'] ?? 0);
            $cantidad = intval($p['cicantidad'] ?? 0);
            $subtotal = $precio * $cantidad;
            $totalGeneral += $subtotal;
            
            $nombre = utf8_decode($p['pronombre']);
            if (strlen($nombre) > 40) $nombre = substr($nombre, 0, 37) . '...';

            $this->Ln();
            $this->Cell(90, 8, $nombre, 1, 0, 'L');
            $this->Cell(25, 8, $cantidad, 1, 0, 'C');
            $this->Cell(35, 8, '$' . number_format($precio, 2, ',', '.'), 1, 0, 'R');
            $this->Cell(35, 8, '$' . number_format($subtotal, 2, ',', '.'), 1, 0, 'R');
        }

        // --- TOTAL ---
        $this->Ln(10);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(150, 10, 'TOTAL FINAL', 0, 0, 'R');
        $this->SetTextColor(1, 121, 111);
        $this->Cell(35, 10, '$' . number_format($totalGeneral, 2, ',', '.'), 1, 1, 'R');
        
        // Retornamos el PDF generado
        return $this->Output('S'); 
    }
}
?>