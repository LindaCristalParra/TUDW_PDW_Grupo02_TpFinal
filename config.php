<?php
// Configurar zona horaria para toda la aplicación
date_default_timezone_set('America/Argentina/Buenos_Aires');

include_once __DIR__ . '/confidencial.php';
// Config global del proyecto
// Rutas para imágenes: ruta en filesystem y prefijo web público

// Ruta absoluta en disco donde se guardan las imágenes (termina con slash)
$GLOBALS['IMAGES_FS'] = __DIR__ . '/Util/Imagenes/';

// Prefijo URL público para acceder a las imágenes desde el navegador (termina con slash)
$GLOBALS['IMAGES_WEB_PREFIX'] = '/TUDW_PDW_Grupo02_TpFinal/Util/Imagenes/';

// Compatibilidad con código anterior que usaba $GLOBALS['IMGS']
$GLOBALS['IMGS'] = $GLOBALS['IMAGES_FS'];

// Nombre de imagen por defecto si no hay disponible
$GLOBALS['IMAGES_DEFAULT'] = 'default.png';

// Configuración de email (se puede sobreescribir mediante variables de entorno)
$GLOBALS['EMAIL_CONFIG'] = [
	'smtp_host' => getenv('MAIL_HOST') !== false ? getenv('MAIL_HOST') : 'smtp.gmail.com',
	'smtp_port' => getenv('MAIL_PORT') !== false ? intval(getenv('MAIL_PORT')) : 587,
	'smtp_encryption' => getenv('MAIL_ENCRYPTION') !== false ? getenv('MAIL_ENCRYPTION') : 'tls',
	'smtp_username' => getenv('MAIL_USERNAME') !== false ? getenv('MAIL_USERNAME') : $email,
	'smtp_password' => getenv('MAIL_PASSWORD') !== false ? getenv('MAIL_PASSWORD') : $password,
	'from_email' => getenv('MAIL_FROM_ADDRESS') !== false ? getenv('MAIL_FROM_ADDRESS') : $email,
	'from_name' => getenv('MAIL_FROM_NAME') !== false ? getenv('MAIL_FROM_NAME') : 'ChristmasMarket'
];

?>
