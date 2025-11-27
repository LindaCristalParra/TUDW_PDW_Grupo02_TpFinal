<?php
/**
 * Control/RouteMap.php
 * Encapsula el mapeo entre etiquetas de menú y rutas del sitio.
 */
class RouteMap
{
    private $map = [];

    public function __construct()
    {
        // Mapa por defecto. Las claves deben estar normalizadas (lowercase, alnum).
        $this->map = [
            'inicio' => '/TUDW_PDW_Grupo02_TpFinal/Vista/index.php',
            'productos' => '/TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Producto/listado.php',
            'miscompras' => '/TUDW_PDW_Grupo02_TpFinal/Vista/listadoCompras.php',
            'carrito' => '/TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Compra/mostrarCarrito.php',
            'panel' => '/TUDW_PDW_Grupo02_TpFinal/Vista/admin/panelAdmin.php',
            'ofertas' => '/TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Menu/enConstruccion.php',
            // Login / Logout mapping
            'login' => '/TUDW_PDW_Grupo02_TpFinal/Vista/login.php',
            'logout' => '/TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Login/logout.php'
        ];
    }

    /**
     * Resolver ruta para una etiqueta de menú (no normalizada).
     * Devuelve la ruta absoluta o null si no está mapeada.
     */
    public function resolve($label)
    {
        $key = $this->normalize($label);
        return $this->map[$key] ?? null;
    }

    /**
     * Normaliza una etiqueta a la clave usada en el mapa.
     */
    public function normalize($label)
    {
        return strtolower(preg_replace('/[^a-z0-9]/i', '', trim($label)));
    }

    /**
     * Obtener todo el mapa (útil para vista admin/depuración).
     */
    public function all()
    {
        return $this->map;
    }

    /**
     * Permite añadir o sobrescribir una ruta para una etiqueta.
     */
    public function set($label, $route)
    {
        $this->map[$this->normalize($label)] = $route;
    }
}
