<?php
// Control/Mailer.php - wrapper mínimo que delega en Modelo/Servicio/EmailService

$svcPath = __DIR__ . '/../Modelo/Servicio/EmailService.php';
if (file_exists($svcPath)) {
    require_once $svcPath;
}

/**
 * enviarMail wrapper compatible con llamadas actuales en controladores.
 * Espera un array $data con al menos 'idcompra' y 'idcompraestadotipo'.
 */
function enviarMail(array $data)
{
    if (!class_exists('EmailService')) {
        error_log('Control/Mailer: EmailService no disponible');
        return false;
    }

    $idCompra = isset($data['idcompra']) ? intval($data['idcompra']) : 0;
    $tipo = isset($data['idcompraestadotipo']) ? intval($data['idcompraestadotipo']) : 0;

    // Determinar destinatario
    $to = $data['to'] ?? null;
    if (empty($to) && $idCompra > 0) {
        try {
            $comp = new Compra();
            $comp->setID($idCompra);
            $comp->cargar();
            $user = $comp->getObjUsuario();
            if (is_object($user) && method_exists($user, 'getUsMail')) {
                $maybe = $user->getUsMail();
                if (!empty($maybe)) $to = $maybe;
            }
        } catch (\Throwable $e) {
            error_log('Control/Mailer: no se pudo obtener email de compra - ' . $e->getMessage());
        }
    }

    if (empty($to)) {
        error_log('Control/Mailer: destinatario no determinado, abortando envío');
        return false;
    }

    // Preparar extras: items y total si vienen en $data
    $extra = $data['extra'] ?? [];

    // Delegar al EmailService
    try {
        return EmailService::enviarEstadoCompra($to, $idCompra, $tipo, $extra);
    } catch (\Throwable $e) {
        error_log('Control/Mailer: EmailService fallo - ' . $e->getMessage());
        return false;
    }
}
