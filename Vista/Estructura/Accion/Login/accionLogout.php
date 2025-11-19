<?php

include_once(__DIR__ . '/../../Control/Session.php');


$session = new Session();
$session->cerrar();

header('Location: /TUDW_PDW_Grupo02_TPFinal/Vista/login.php?logout=true');
exit;

?>