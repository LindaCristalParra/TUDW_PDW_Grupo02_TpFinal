<?php
// Modelo/EmailService.php
// Lógica de negocio para envío de emails usando Symfony Mailer
// Utiliza las plantillas HTML en Vista/Email/

require_once __DIR__ . '/../../vendor/autoload.php';

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

class EmailService
{
    private static $config = null;
    
    /**
     * Carga la configuración de email
     */
    private static function cargarConfig(): array
    {
        if (self::$config === null) {
            $configFile = __DIR__ . '/../../config/email.php';
            if (file_exists($configFile)) {
                self::$config = require $configFile;
            } else {
                // Configuración por defecto
                self::$config = [
                    'smtp_host' => 'smtp.gmail.com',
                    'smtp_port' => 587,
                    'smtp_encryption' => 'tls',
                    'smtp_username' => 'tu-email@gmail.com',
                    'smtp_password' => 'tu-contraseña-de-aplicacion',
                    'from_email' => 'tu-email@gmail.com',
                    'from_name' => 'ChrismasMarket'
                ];
            }
        }
        return self::$config;
    }
    
    /**
     * Crea y configura el mailer
     */
    private static function crearMailer(): Mailer
    {
        $config = self::cargarConfig();
        
        // Crear DSN (Data Source Name) para el transporte SMTP
        // Regla:
        //  - TLS (587):  smtp://user:pass@host:587?encryption=tls&auth_mode=login
        //  - SSL (465): smtps://user:pass@host:465
        //  - Sin cifrado: smtp://user:pass@host:25
        $encryption = $config['smtp_encryption'] ?? null;
        $scheme = ($encryption === 'ssl') ? 'smtps' : 'smtp';
        $dsn = sprintf(
            '%s://%s:%s@%s:%d',
            $scheme,
            urlencode($config['smtp_username'] ?? ''),
            urlencode($config['smtp_password'] ?? ''),
            $config['smtp_host'] ?? 'localhost',
            (int)($config['smtp_port'] ?? 25)
        );
        // Agregar query params para TLS u otras opciones
        $query = [];
        if ($encryption === 'tls') {
            $query[] = 'encryption=tls';
        }
        // Forzar modo de auth 'login' para máxima compatibilidad con Gmail/Outlook
        $query[] = 'auth_mode=login';
        if (!empty($query)) {
            $dsn .= '?' . implode('&', $query);
        }
        
        // Log de diagnóstico (sin contraseña)
        $dsnSafe = preg_replace('/:(.*?)@/', ':****@', $dsn);
        error_log('[EmailService] Creando transporte con DSN: ' . $dsnSafe);

        $transport = Transport::fromDsn($dsn);
        return new Mailer($transport);
    }
    
    /**
     * Renderiza una plantilla HTML con variables
     */
    private static function renderizarPlantilla(string $plantilla, array $variables): string
    {
        extract($variables);
        ob_start();
        include __DIR__ . '/../../Vista/Email/' . $plantilla;
        return ob_get_clean();
    }
    
    // Los métodos específicos de confirmación/cancelación se removieron.
    // Usar EmailService::enviarEstadoCompra(destinatario, idCompra, idCompraEstadoTipo, extra)

    /**
     * Envía un correo informando el estado de una compra (un único método que adapta el texto según estado).
     * @param string $destinatario
     * @param int $idCompra
     * @param int $idCompraEstadoTipo
     * @param array $extra Datos adicionales (items, total, nombre, fecha)
     * @return array Resultado ['exito' => bool, 'mensaje' => string]
     */
    public static function enviarEstadoCompra(string $destinatario, int $idCompra, int $idCompraEstadoTipo, array $extra = []): array
    {
        try {
            $config = self::cargarConfig();
            $mailer = self::crearMailer();

            // Mapear id a texto legible
            $estados = [
                1 => 'Procesando',
                2 => 'Aprobada',
                3 => 'Enviada',
                4 => 'Cancelada',
                5 => 'Carrito'
            ];
            $estadoTexto = $estados[$idCompraEstadoTipo] ?? 'Actualización';

            // Preparar variables para la plantilla
            $variables = array_merge([
                'nombre' => $extra['nombre'] ?? '',
                'fecha' => $extra['fecha'] ?? '',
                'items' => $extra['items'] ?? [],
                'total' => $extra['total'] ?? '',
                'estadoTexto' => $estadoTexto,
                'idCompra' => $idCompra
            ], $extra);

            // Renderizar plantilla HTML específica para compra/estado
            $htmlBody = self::renderizarPlantilla('compraEstado.php', $variables);

            $subject = sprintf('ChrismasMarket - Estado de tu compra: %s', $estadoTexto);

            $email = (new Email())
                ->from(sprintf('%s <%s>', $config['from_name'] ?? 'ChrismasMarket', $config['from_email'] ?? 'no-reply@chromarket.local'))
                ->to($destinatario)
                ->subject($subject)
                ->html($htmlBody);

            error_log('[EmailService] Enviando estado de compra a: ' . $destinatario . ' id:' . $idCompra . ' estado:' . $estadoTexto);
            $mailer->send($email);

            return ['exito' => true, 'mensaje' => 'Email enviado correctamente'];
        } catch (\Exception $e) {
            error_log('[EmailService] Error enviarEstadoCompra: ' . $e->getMessage());
            return ['exito' => false, 'mensaje' => 'Error al enviar email: ' . $e->getMessage()];
        }
    }
}
