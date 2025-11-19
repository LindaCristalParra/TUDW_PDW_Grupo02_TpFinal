<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>Estado de tu compra</title>
  <style>
    body { font-family: Arial, sans-serif; color: #222; }
    .container { max-width: 600px; margin: 0 auto; }
    .header { background: #d9534f; color: white; padding: 12px; }
    .content { padding: 16px; }
    .items { margin-top: 12px; }
    .items table { width: 100%; border-collapse: collapse; }
    .items th, .items td { border: 1px solid #ddd; padding: 8px; }
    .total { font-weight: bold; text-align: right; margin-top: 12px; }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h2>ChrismasMarket</h2>
    </div>
    <div class="content">
      <p>Hola <?php echo htmlspecialchars($nombre ?? ''); ?>,</p>
      <p>Queremos informarte que el estado de tu compra <strong>#<?php echo htmlspecialchars($idCompra ?? ''); ?></strong> es:</p>
      <h3><?php echo htmlspecialchars($estadoTexto ?? ''); ?></h3>

      <?php if (!empty($fecha)): ?>
        <p>Fecha de actualización: <?php echo htmlspecialchars($fecha); ?></p>
      <?php endif; ?>

      <?php if (!empty($items) && is_array($items)): ?>
        <div class="items">
          <h4>Detalle</h4>
          <table>
            <thead>
              <tr><th>Producto</th><th>Cantidad</th><th>Precio</th></tr>
            </thead>
            <tbody>
              <?php foreach ($items as $it): ?>
                <tr>
                  <td><?php echo htmlspecialchars($it['nombre'] ?? $it['pronombre'] ?? ''); ?></td>
                  <td><?php echo htmlspecialchars($it['cantidad'] ?? $it['cicantidad'] ?? '1'); ?></td>
                  <td><?php echo htmlspecialchars($it['precio'] ?? ''); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>

      <?php if (!empty($total)): ?>
        <p class="total">Total: $<?php echo htmlspecialchars($total); ?></p>
      <?php endif; ?>

      <p>Gracias por comprar en ChrismasMarket.</p>
      <p>Saludos,<br/>Equipo ChrismasMarket</p>
    </div>
  </div>
</body>
</html>
