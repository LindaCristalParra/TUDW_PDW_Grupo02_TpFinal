<?php
// Control/Mailer.php
// Wrapper simple para mantener la API usada por los controllers.

require_once __DIR__ . '/../Modelo/Servicio/EmailService.php';

class Mailer
{
    /**
     * Datos esperados en $data:
     * - destinatario (string)
     * - idCompra (int)
     * - idCompraEstadoTipo (int)
     * - extra (array)
     */
    public static function enviarMail(array $data): array
    {
        $destinatario = $data['destinatario'] ?? $data['email'] ?? '';
        $idCompra = isset($data['idCompra']) ? (int)$data['idCompra'] : 0;
        $idCompraEstadoTipo = isset($data['idCompraEstadoTipo']) ? (int)$data['idCompraEstadoTipo'] : 0;
        $extra = $data['extra'] ?? [];

        return \EmailService::enviarEstadoCompra($destinatario, $idCompra, $idCompraEstadoTipo, $extra);
    }
}
