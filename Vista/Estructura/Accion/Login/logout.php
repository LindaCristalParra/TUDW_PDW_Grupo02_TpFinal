<?php
// Cierra la sesión y redirige al inicio
require_once __DIR__ . '/../../../../Control/Session.php';
$session = new Session();
$session->cerrar();
// Redirigir al índice del sitio
header('Location: /TUDW_PDW_Grupo02_TpFinal/Vista/index.php');
exit;

?>