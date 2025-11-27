<?php
require_once __DIR__ . '/menuRolControl.php';
require_once __DIR__ . '/menuControl.php';
require_once __DIR__ . '/RouteMap.php';
require_once __DIR__ . '/rolControl.php';

/**
 * Servicio que arma los datos del menú (left/right) para un rol dado.
 * Retorna un arreglo con claves 'left' y 'right', cada una lista de items ['label','url','icon'(opcional),'icon_only'(opcional)].
 */
class MenuRenderer
{
    private $routeMap;

    public function __construct()
    {
        $this->routeMap = new RouteMap();
    }

    /**
     * Devuelve menuData para el rol (idrol)
     */
    public function obtenerMenuParaRol($idrol)
    {
        $menuData = ['left' => [], 'right' => []];

        // Obtener asociaciones menú-rol a través de MenuControl (centraliza la lógica)
        $menuCtrl = new MenuControl();
        $listaMR = $menuCtrl->ObtenerMenu(['idrol' => $idrol]);

        // Determinar descripción del rol (ej: 'anonimo') si se pasó un id
        $rolDesc = '';
        if (!empty($idrol)) {
            $rolCtrl = new RolControl();
            $rols = $rolCtrl->buscar(['idrol' => $idrol]);
            if (!empty($rols) && isset($rols[0])) {
                // Normalizar: trim + lowercase
                $rolDesc = trim(strtolower((string)$rols[0]->getRolDescripcion()));
            }
        }

        // Determinar si el rol es anónimo/publico. Preferir la verificación por id (3 = anonimo)
        $anonAliases = ['anonimo', 'publico', 'public', 'anon', 'guest', ''];
        $isAnon = ($idrol === 3) || in_array($rolDesc, $anonAliases, true);

        // Claves de menús administrativos que nunca deben mostrarse a visitantes
        $adminKeys = ['panel', 'paneladmin', 'adminpanel', 'admin'];

        if (!empty($listaMR)) {
            foreach ($listaMR as $mr) {
                $menuObj = $mr->getObjMenu();
                if (!$menuObj) continue;
                $label = $menuObj->getMeNombre();
                $key = $this->routeMap->normalize($label);

                // Decide URL vía RouteMap; si no existe, fallback a enConstruccion
                $url = $this->routeMap->resolve($label);
                if ($url === null) {
                    $url = '/TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Menu/enConstruccion.php';
                }

                // Decide posicionamiento especial de ciertos items
                if ($key === 'carrito') {
                    // carrito siempre a la derecha como icono (si corresponde)
                    $menuData['right'][] = [
                        'url' => $url,
                        'label' => $label,
                        'icon' => '/TUDW_PDW_Grupo02_TpFinal/Util/Imagenes/IconShop.png',
                        'icon_only' => true
                    ];
                    continue;
                }

                if ($key === 'login') {
                    // login mostrado a la derecha (por defecto icono de usuario)
                    $menuData['right'][] = [
                        'url' => $url,
                        'label' => $label,
                        'icon' => '/TUDW_PDW_Grupo02_TpFinal/Util/Imagenes/IconLogin.png',
                        'icon_only' => true
                    ];
                    continue;
                }

                if ($key === 'logout') {
                    // logout mostrado a la derecha (icono)
                    $menuData['right'][] = [
                        'url' => $url,
                        'label' => $label,
                        'icon' => '/TUDW_PDW_Grupo02_TpFinal/Util/Imagenes/IconLogout.png',
                        'icon_only' => true
                    ];
                    continue;
                }

                $menuData['left'][] = ['label' => $label, 'url' => $url];
            }
        }

        // Si el rol NO es 'anonimo' y no hay un logout provisto por BD, añadir uno por defecto
        $hasLogout = false;
        foreach ($menuData['right'] as $it) {
            $k = strtolower(preg_replace('/[^a-z0-9]/i', '', $it['label'] ?? ''));
            if ($k === 'logout' || stripos($it['label'] ?? '', 'cerrar') !== false) {
                $hasLogout = true;
                break;
            }
        }

        // Considerar varios alias para rol público/anonimo
        $anonAliases = ['anonimo', 'publico', 'public', 'anon', 'guest', ''];
        if (!in_array($rolDesc, $anonAliases, true)) {
            if (!$hasLogout) {
                $menuData['right'][] = [
                    'url' => '/TUDW_PDW_Grupo02_TpFinal/Vista/Estructura/Accion/Login/logout.php',
                    'label' => 'Cerrar sesión',
                    'icon' => '/TUDW_PDW_Grupo02_TpFinal/Util/Imagenes/IconLogout.png',
                    'icon_only' => true
                ];
            }
        } else {
            // Para rol anonimo: si no hay item de login en right, añadimos uno
            $hasLogin = false;
            foreach ($menuData['right'] as $it) {
                $k = strtolower(preg_replace('/[^a-z0-9]/i', '', $it['label'] ?? ''));
                if ($k === 'login') { $hasLogin = true; break; }
            }
            if (!$hasLogin) {
                $menuData['right'][] = [
                    'url' => '/TUDW_PDW_Grupo02_TpFinal/Vista/login.php',
                    'label' => 'Iniciar sesión',
                    'icon' => '/TUDW_PDW_Grupo02_TpFinal/Util/Imagenes/IconLogin.png',
                    'icon_only' => true
                ];
            }
        }

        return $menuData;
    }
}
