<?php
// Vista/Email/compraEstado.php
// Plantilla HTML simple para notificar estado de compra
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Estado de tu compra</title>
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #f8f9fa; padding: 10px; border-radius: 4px; }
        .items { margin-top: 10px; }
        .item { border-bottom: 1px solid #ececec; padding: 8px 0; }
        .total { font-weight: bold; margin-top: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>ChrismasMarket</h2>
            <p>Estado de la compra #<?php echo htmlspecialchars($idCompra); ?></p>
        </div>

        <p>Hola <?php echo htmlspecialchars($nombre ?? ''); ?>,</p>
        <p>Te informamos que el estado de tu compra es: <strong><?php echo htmlspecialchars($estadoTexto ?? ''); ?></strong>.</p>

        <?php if (!empty($items) && is_array($items)): ?>
            <div class="items">
                <h4>Productos</h4>
                <?php foreach ($items as $it): ?>
                    <div class="item">
                        <div><?php echo htmlspecialchars($it['nombre'] ?? $it[0] ?? ''); ?></div>
                        <div>Cantidad: <?php echo htmlspecialchars($it['cantidad'] ?? $it[1] ?? '1'); ?></div>
                        <div>Precio: <?php echo htmlspecialchars($it['precio'] ?? $it[2] ?? ''); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="total">Total: <?php echo htmlspecialchars($total ?? ''); ?></div>

        <p>Fecha: <?php echo htmlspecialchars($fecha ?? date('Y-m-d H:i')); ?></p>

        <p>Gracias por comprar en ChrismasMarket.</p>
    </div>
</body>
</html>
