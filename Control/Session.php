<?php
require_once __DIR__ . '/usuarioControl.php';
require_once __DIR__ . '/UsuarioRolControl.php';
require_once __DIR__ . '/MenuControl.php';
require_once __DIR__ . '/MenuRolControl.php';
class Session
{
    public function __construct()
    {
        // Start session only if not already active to avoid PHP notices
        if (php_sapi_name() !== 'cli') {
            if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
                if (session_status() !== PHP_SESSION_ACTIVE) {
                    session_start();
                }
            } else {
                if (session_id() === '') {
                    session_start();
                }
            }
        }
    }

    /**
     * @param string $nombreUsuario
     * @param string $pswUsuario 
     * @return boolean True si el login es exitoso, false si falla la autenticación
     */
    public function LoginUsuario($nombreUsuario, $pswUsuario)
    {
        // 1. Validar y obtener el objeto Usuario (Lógica de validar())
        $objUsuarioC = new UsuarioControl();
        $param = array("usnombre" => $nombreUsuario);
        $listaUsuario = $objUsuarioC->buscar($param);
        
        if (empty($listaUsuario)) {
            return false; // No existe usuario
        }

        $user = $listaUsuario[0];
        $hash = $user->getUsPass();
        
        if (!password_verify($pswUsuario, $hash)) {
            return false; // Contraseña incorrecta
        }

        // 2. Setear la Sesión (Lógica de iniciar())
       
        $_SESSION['usnombre'] = $nombreUsuario;
        $_SESSION['idusuario'] = $user->getID();
        $_SESSION['usmail'] = $user->getUsMail();
        $_SESSION['usdeshabilitado'] = $user->getUsDeshabilitado();

        $this->setearRolActivo(); // Setear el rol
        
        return true; // Éxito
    }

    public function setearRolActivo()
    {
        $verificador=false;
        $rolesUs = $this->getRoles(); // TRAEMOS EL ARREGLO DE OBJETOS

        if (count($rolesUs) > 0) {
            $rolActivoDescripcion=$rolesUs[0]->getRolDescripcion();
            $_SESSION['rolactivodescripcion'] = $rolActivoDescripcion;
            $idRol = $this->buscarIdRol($rolActivoDescripcion);
            $_SESSION['rolactivoid'] = $idRol;
            $verificador = true;
        } else {
            $_SESSION['rolactivodescripcion'] = null;
            $_SESSION['rolactivoid'] = null;
        }
        return $verificador;
    }

    public function buscarIdRol($param)
    {
        $retorno = null;
        $roles = $this->getRoles();
        foreach ($roles as $rol) {
            if ($rol->getRolDescripcion() === $param) {
                $retorno = $rol->getID();
            }
        }

        return $retorno;
    }

    public function activa()
    {
        if (php_sapi_name() !== 'cli') {
            if (version_compare(phpversion(), '5.4.0', '>=')) {
                //compara la version de php para ver si se puede usar el metodo session_status()
                return session_status() === PHP_SESSION_ACTIVE ? true : false;
            } else {
                //si la version es menor se fija comparando el id de la session actual, para ver si esta seteada.

                return session_id() === '' ? false : true;
            }
        }

        return false;
    }

    public function sesionActiva()
    {
        $resp = false;
        if ($this->getNombreUsuarioLogueado() <> null) {
            $resp = true;
        }
        return $resp;
    }


    private function getUsuario()
    {
        //Método privado para no devolver el usuario fuera de la clase Session
        $user = null;
        if ($this->activa() && isset($_SESSION['usnombre'])) {
            $objUsuarioC = new UsuarioControl();
            $param['usnombre'] = $_SESSION['usnombre'];
            $listaUsuario = $objUsuarioC->buscar($param);
            $user = $listaUsuario[0];
        }
        return $user;
    }

    public function obtenerDeshabilitado($fecha)
    {
        $retorno = false;
        if ($fecha === null || $fecha === '0000-00-00 00:00:00') {
            $retorno = true;
        }
        return $retorno;
    }

    public function getRoles()
    {
        //Devuelve un arreglo con los objetos rol del user
        $roles = [];
        $user = $this->getUsuario();
        if ($user != null) {
            //Primero busco la instancia de UsuarioRol
            $objUsuarioRolC = new UsuarioRolControl();
            //Creo el parametro con el id del usuario
            $parametroUser = array('idusuario' => $user->getID());
            $listaUsuarioRol = $objUsuarioRolC->buscar($parametroUser);
            foreach ($listaUsuarioRol as $tupla) {
                array_push($roles, $tupla->getObjRol());
            }
        }
        return $roles;
    }

    public function getNombreUsuarioLogueado()
    {
        //retorna el nombre del usuario logueado
        $nombreUsuario = null;
        if (isset($_SESSION['usnombre'])) {
            $nombreUsuario = $_SESSION['usnombre'];
        }
        return $nombreUsuario;
    }

    public function getIDUsuarioLogueado()
    {
        //retorna el id del usuario logueado
        $nombreUsuario = null;
        if (isset($_SESSION['idusuario'])) {
            $nombreUsuario = $_SESSION['idusuario'];
        }
        return $nombreUsuario;
    }

    public function getMailUsuarioLogueado()
    {
        //retorna el mail del usuario logueado
        $nombreUsuario = null;
        if (isset($_SESSION['usmail'])) {
            $nombreUsuario = $_SESSION['usmail'];
        }
        return $nombreUsuario;
    }

    public function getRolActivo()
    {
        $resp = [];
        if (isset($_SESSION['rolactivodescripcion']) && isset($_SESSION['rolactivoid'])) {
            $resp = [
                'rol' => $_SESSION['rolactivodescripcion'],
                'id' => $_SESSION['rolactivoid']
            ];
        }
        return $resp;
    }

    /**
     * Devuelve true si el rol activo corresponde a un administrador.
     */
    public function esAdmin()
    {
        $rolActivo = $this->getRolActivo();
        if (empty($rolActivo) || !isset($rolActivo['rol'])) return false;
        $rolDesc = strtolower($rolActivo['rol']);
        if (strpos($rolDesc, 'admin') !== false || $rolDesc === 'administrador' || ($rolActivo['id'] ?? null) == 1) {
            return true;
        }
        return false;
    }

    /**
     * Verifica que el usuario sea administrador; si no lo es, muestra mensaje de acceso denegado,
     * incluye un footer opcional y termina la ejecución. Uso conveniente desde vistas.
     * @param string|null $footerPath Ruta absoluta al archivo footer.php a incluir (opcional)
     * @param string|null $message Mensaje a mostrar (opcional)
     */
    public function exigirAdmin($footerPath = null, $message = null)
    {
        if ($message === null) {
            $message = 'Acceso denegado. Debés ser administrador.';
        }
        if (!$this->sesionActiva() || !$this->esAdmin()) {
            echo '<div class="container mt-4"><div class="alert alert-danger">'.htmlspecialchars($message).'</div></div>';
            if (!empty($footerPath) && file_exists($footerPath)) {
                require_once $footerPath;
            }
            exit;
        }
    }


    public function cerrar()
    {
        //Primero me fijo si esta activa la session
        if ($this->activa()) {
            //elimino sus datos
            unset($_SESSION['idusuario']);
            unset($_SESSION['usnombre']);
            unset($_SESSION['usmail']);
            unset($_SESSION['usdeshabilitado']);
            unset($_SESSION['rolactivodescripcion']);
            unset($_SESSION['rolactivoid']);
            //destruyo la session
            session_destroy();
        }
    }

    public function setIdRolActivo($param)
    {
        $_SESSION['rolactivoid'] = $param;
    }

    public function setDescripcionRolActivo($param)
    {
        $_SESSION['rolactivodescripcion'] = $param;
    }


    public function verificarPermiso($param)
    {
        $user = $this->getUsuario();
        $permiso = false;
        if($user!=null){
        if ($this->obtenerDeshabilitado($user->getUsDeshabilitado())) {
            $permiso = $this->recorrerPermisosPorRoles($this->getRoles(), $param); //LE MANDAMOS TODOS LOS ROLES DEL USUARIO
        }
    }

        return $permiso;
    }

    public function recorrerPermisosPorRoles($roles, $script)
    {
        $objMR = new MenuRolControl();
        $recorrido = false;
        foreach ($roles as $rolActual) { // POR CADA ROL OBTENEMOS LOS MENUES Y BUSCAMOS SI EL SCRIPT SE ENCUENTRA AHI
            $listaMR = $objMR->buscar(['idrol' => $rolActual->getID()]);
            $menuC = new MenuControl(); // MANDAMOS PARA BUSCAR LOS HIJOS EN CASO DE QUE EXISTAN
            $a = 0; //contador
            while (!$recorrido && ($a < count($listaMR))) {
                $recorrido = $this->buscarPermiso($listaMR[$a]->getObjMenu(), $script, $menuC);
                $a++;
            }
        }

        return $recorrido;
    }

    public function buscarPermiso($menu, $param, $abm)
    {
        $respuesta2 = false;
        $hijos = $abm->tieneHijos($menu->getID());
        if (!empty($hijos)) { // SI TIENE HIJOS VERIFICAMOS QUE TENGAN EL ACCESO
            $i = 0; //contador
            while (!$respuesta2 && ($i < count($hijos))) {
                if ($hijos[$i]->getMeDescripcion() == $param) { // PUEDE SER PADRE OSEA DESCRIPCION = "#"
                    $respuesta2 = true;
                } else {
                    $respuesta2 = $this->buscarPermiso($hijos[$i], $param, $abm); // HACEMOS RECURSIVIDAD PORQUE ESOS HIJOS PUEDEN TENER HIJOS
                }
                $i++;
            }
        } else {
            if ($menu->getMeDescripcion() == $param) { // EN CASO DE NO TENER HIJOS VERIFICAMOS SI EL PADRE TIENE EL ACCESO
                $respuesta2 = true;
            }
        }

        return $respuesta2;
    }

    public function cambiarRol($datos)
    {
        $resp = false;
        $rolActivo = $this->getRolActivo();

        if ($rolActivo['rol'] <> $datos['nuevorol']) { // SI EL ROL ES DISTINTO AL YA SETEADO HACEMOS EL CAMBIO
            $idRol = $this->buscarIdRol($datos['nuevorol']);
            $this->setIdRolActivo($idRol);
            $this->setDescripcionRolActivo($datos['nuevorol']);
            $resp = true;
        }

        return $resp;
    }
}