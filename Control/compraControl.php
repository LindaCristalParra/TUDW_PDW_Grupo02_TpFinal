<?php
require_once __DIR__ . '/Mailer.php';
require_once __DIR__ . '/../Modelo/compra.php';
require_once __DIR__ . '/../Modelo/usuario.php';
require_once __DIR__ . '/../Modelo/compraItem.php';
require_once __DIR__ . '/compraItemControl.php';
require_once __DIR__ . '/Session.php';
require_once __DIR__ . '/usuarioControl.php';
// Asegúrate de tener acceso a BaseDatos para el listado SQL
require_once __DIR__ . '/../Modelo/Conector/BaseDatos.php';
// Cargar Carbon para manejo de fechas
require_once __DIR__ . '/../vendor/autoload.php';

use Carbon\Carbon; 

class CompraControl
{

    public function abm($datos)
    {
        $resp = false;
        if ($datos['action'] == 'eliminar') {
            if ($this->baja($datos)) {
                $resp = true;
            }
        }
        if ($datos['action'] == 'modificar') {
            if ($this->modificacion($datos)) {
                $resp = true;
            }
        }
        if ($datos['action'] == 'alta') {
            if ($this->alta($datos)) {
                $resp = true;
            }
        }
        return $resp;
    }

    /**
     * --- CORREGIDO PARA EVITAR EL ERROR DE OBJUSUARIO ---
     * Espera como parametro un arreglo asociativo 
     * @param array $param
     * @return Compra
     */
    private function cargarObjeto($param)
    {
        $obj = null;
        
        // 1. Verificamos si viene el ID de compra para cargar una existente o crear vacía
        if (array_key_exists('idcompra', $param) and $param['idcompra'] != null) {
            $obj = new Compra();
            $obj->setID($param['idcompra']);
            if (!$obj->cargar()) {
                $obj = null;
            }
        } else {
            $obj = new Compra();
        }

        // 2. Asignamos el Usuario y la Fecha si el objeto es válido
        if ($obj != null) {
            
            // LOGICA ROBUSTA PARA EL USUARIO
            $objUsuario = null;
            
            // Caso A: Viene el objeto Usuario entero
            if (array_key_exists('objusuario', $param) && is_object($param['objusuario'])) {
                $objUsuario = $param['objusuario'];
            }
            // Caso B: Viene el ID de usuario (lo más común)
            elseif (array_key_exists('idusuario', $param) && $param['idusuario'] != null) {
                $objUsuario = new Usuario();
                $objUsuario->setID($param['idusuario']);
                $objUsuario->cargar();
            }

            // Si encontramos usuario, lo seteamos
            if ($objUsuario != null) {
                $obj->setObjUsuario($objUsuario);
            }

            // Seteamos la fecha si viene
            if (array_key_exists('cofecha', $param)) {
                $obj->setCoFecha($param['cofecha']);
            }
        }
        return $obj;
    }

    // Mantenemos este por compatibilidad si lo usas en otro lado
    private function cargarObjetoSinID($param)
    {
        $obj = null;
        if (
            array_key_exists('cofecha', $param) &&
            array_key_exists('idusuario', $param)
        ) {
            $objusuario = new Usuario();
            $objusuario->setID($param['idusuario']);
            $objusuario->cargar();

            $obj = new Compra();
            $obj->setearSinID($param['cofecha'], $objusuario);
        }
        return $obj;
    }

    private function cargarObjetoConClave($param)
    {
        $obj = null;
        if (isset($param['idcompra'])) {
            $obj = new Compra();
            $obj->setear($param['idcompra'], null, null);
        }
        return $obj;
    }

    private function seteadosCamposClaves($param)
    {
        $resp = false;
        if (isset($param['idcompra'])) {
            $resp = true;
        }
        return $resp;
    }

    public function alta($param)
    {
        $resp = false;
        $objcompra = $this->cargarObjeto($param);
        if ($objcompra != null and $objcompra->insertar()) {
            $resp = true;
        }
        return $resp;
    }

    public function altaSinID($param)
    {
        $resp = false;
        $objCompra = $this->cargarObjetoSinID($param);
        if ($objCompra != null and $objCompra->insertar()) {
            $resp = true;
        }
        return $resp;
    }

    public function baja($param)
    {
        $resp = false;
        if ($this->seteadosCamposClaves($param)) {
            $objcompra = $this->cargarObjetoConClave($param);
            if ($objcompra != null and $objcompra->eliminar()) {
                $resp = true;
            }
        }
        return $resp;
    }

    public function modificacion($param)
    {
        $resp = false;
        if ($this->seteadosCamposClaves($param)) {
            $objcompra = $this->cargarObjeto($param);
            if ($objcompra != null and $objcompra->modificar()) {
                $resp = true;
            }
        }
        return $resp;
    }

    public function buscar($param)
    {
        $where = " true ";
        if ($param <> null) {
            if (isset($param['idcompra'])) {
                $where .= " and idcompra ='" . $param['idcompra'] . "'";
            }
            if (isset($param['cofecha'])) {
                $where .= " and cofecha ='" . $param['cofecha'] . "'";
            }
            if (isset($param['idusuario'])) {
                $where .= " and idusuario ='" . $param['idusuario'] . "'";
            }
        }
        $objC =  new Compra();
        $arreglo = $objC->listar($where);
        return $arreglo;
    }

    /*############### FUNCIONES QUE UTILIZAN LOS ACTION #######################*/

    /* LISTAR PRODUCTOS CARRITO (VERSIÓN SQL ROBUSTA) */
    // Usamos SQL directo para asegurarnos de traer 'proimagen' y 'precio'
    // aunque la clase Producto no esté actualizada.
    public function listadoProdCarrito($carrito)
    {
        $arreglo = [];
        
        if ($carrito != null) {
            $base = new BaseDatos();
            $idCompra = $carrito->getID();
            
            // JOIN entre compraitem y producto para traer todo junto
            $sql = "SELECT 
                        ci.idcompraitem, 
                        ci.cicantidad, 
                        p.idproducto, 
                        p.pronombre, 
                        p.prodetalle, 
                        p.precio,      
                        p.proimagen    
                    FROM compraitem ci
                    INNER JOIN producto p ON ci.idproducto = p.idproducto
                    WHERE ci.idcompra = " . $idCompra;

            if ($base->Iniciar()) {
                $res = $base->Ejecutar($sql);
                if ($res > -1) {
                    if ($res > 0) {
                        while ($row = $base->Registro()) {
                            $arreglo[] = [
                                'idcompraitem' => $row['idcompraitem'],
                                'cicantidad'   => $row['cicantidad'],
                                'idproducto'   => $row['idproducto'],
                                'pronombre'    => $row['pronombre'],
                                'prodetalle'   => $row['prodetalle'],
                                'precio'       => $row['precio'],      
                                'proimagen'    => $row['proimagen']    
                            ];
                        }
                    }
                }
            }
        }
        return $arreglo;
    }
    /* FIN LISTAR PRODUCTOS CARRITO */


    /* AGREGAR PRODUCTO AL CARRITO */
    public function agregarProdCarrito($data)
    {
        $respuesta = false;
        $objSession = new Session();
        $objUsuario = new UsuarioControl();
        $idUserLogueado = $objSession->getIDUsuarioLogueado();
        $carrito = $objUsuario->obtenerCarrito($idUserLogueado);
        if ($carrito <> null) {
            $respuesta = $this->verificarStockProd($carrito, $data);
            if ($respuesta) {
                $respuesta = $this->sumarProdCarrito($carrito, $data);
            }
        } else { 
            $carritoNuevo = $this->crearCarrito($idUserLogueado);
            if ($carritoNuevo <> null) {
                $respuesta = $this->sumarProdCarrito($carritoNuevo, $data);
            }
        }
        return $respuesta;
    }

    public function sumarProdCarrito($objCompraCarrito, $data)
    {
        $respuesta = false;
        $objCompraItemControl = new CompraItemControl();
        $idCompra = $objCompraCarrito->getID();
        $param = array(
            'idproducto' => $data['idproducto'],
            'idcompra' => $idCompra
        );
        $listaCompraItem = $objCompraItemControl->buscar($param);
        if (count($listaCompraItem) > 0) { 
            $objCompraItemControl = $listaCompraItem[0];
            $idCI = $objCompraItemControl->getID();
            $cantidadCI = $objCompraItemControl->getCiCantidad();
            $nuevaCantCI = $cantidadCI + 1;
            $paramCI = array(
                'idcompraitem' => $idCI,
                'idproducto' => $data['idproducto'],
                'idcompra' => $idCompra,
                'cicantidad' => $nuevaCantCI
            );
            $respuesta = $objCompraItemControl->modificacion($paramCI);
            if (!$respuesta) {
                // echo "no se modifico";
            }
        } else { 
            $data['idcompra'] = $idCompra;
            $respuesta = $objCompraItemControl->altaSinID($data);
        }
        return $respuesta;
    }

    public function crearCarrito($idUser)
    {
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $carrito = null;
        $objCompraControl = new CompraControl();
        $param = array(
            'cofecha' => date('Y-m-d H:i:s'),
            'idusuario' => $idUser
        );
        $respuesta = $objCompraControl->altaSinID($param);
        if (!$respuesta) {
            // echo "no se creo el carrito";
        }
        if ($respuesta) { 
            $paramIDUsuario['idusuario'] = $idUser;
            $objCompraEstadoControl = new CompraEstadoControl();
            $listaCompras = $this->buscar($paramIDUsuario);
            $posCompra = count($listaCompras) - 1; 
            $idCompra = $listaCompras[$posCompra]->getID();
            $paramCompraEstado = array(
                'idcompra' => $idCompra,
                'idcompraestadotipo' => 5,
                'cefechaini' => date('Y-m-d H:i:s'), 
                'cefechafin' => '0000-00-00 00:00:00'
            ); 
            $respuesta = $objCompraEstadoControl->altaSinID($paramCompraEstado);
            if ($respuesta) { 
                $carrito = $listaCompras[$posCompra];
            }
        }
        return $carrito;
    }

    public function verificarStockProd($objCompraCarrito, $data)
    { 
        $respuesta = false;
        $objCompraItemControl = new CompraItemControl();
        $idCompra = $objCompraCarrito->getID();
        $param = array(
            'idproducto' => $data['idproducto'],
            'idcompra' => $idCompra
        );
        $listaCompraItem = $objCompraItemControl->buscar($param);
        if (count($listaCompraItem) > 0) { 
            $objCompraItemControl = $listaCompraItem[0];
            $nuevaCantCI = $objCompraItemControl->getCiCantidad() + 1;
            $objProductoControl = new ProductoControl();
            $param['idproducto'] = $data['idproducto'];
            $listaProd = $objProductoControl->buscar($param);
            if (count($listaProd)) {
                $cantStockProd = $listaProd[0]->getProCantStock();
                if ($cantStockProd >= $nuevaCantCI) {
                    $respuesta = true;
                }
            }
        } else { 
            $respuesta = true;
        }
        return $respuesta;
    }

    public function cancelarCompra($data)
    {
        $respuesta = false;
        $objCompraEstadoControl = new CompraEstadoControl();
        
        // Buscar el estado actual
        $list = $objCompraEstadoControl->buscar(['idcompraestado' => $data['idcompraestado']]);

        if (count($list) > 0) {
            $estadoActual = $list[0];
            date_default_timezone_set('America/Argentina/Buenos_Aires');
            $fechaFin = date('Y-m-d H:i:s');
            
            // Paso 1: Cerrar el estado actual usando el objeto directamente
            $estadoActual->setCeFechaFin($fechaFin);
            $paso1 = $estadoActual->modificar();
            
            error_log("Paso 1 - Cerrar estado actual: " . ($paso1 ? "OK" : "FALLO"));
            
            if ($paso1) {
                // Paso 2: Crear nuevo estado "cancelada"
                $nuevoEstado = [
                    'idcompra' => $data['idcompra'],
                    'idcompraestadotipo' => 4, // ID de "cancelada"
                    'cefechaini' => $fechaFin,
                    'cefechafin' => null,
                ];
                
                $respuesta = $objCompraEstadoControl->alta($nuevoEstado);
                error_log("Paso 2 - Crear nuevo estado: " . ($respuesta ? "OK" : "FALLO"));
            }
        } else {
            error_log("ERROR: No se encontró el estado de compra con ID: " . $data['idcompraestado']);
        }

        return $respuesta;
    }

    public function cambiarEstado($data, $idCET, $fechaIni, $fechaFin, $objCE)
    {
        // Primero: cerrar el estado actual
        $arregloModCompra = [
            'idcompraestado' => $data['idcompraestado'],
            'idcompra' => $data['idcompra'],
            'idcompraestadotipo' => $idCET,
            'cefechaini' => $fechaIni,
            'cefechafin' => $fechaFin,
        ];

        error_log("Modificando estado actual: " . json_encode($arregloModCompra));
        $resp = $objCE->modificacion($arregloModCompra);
        error_log("Resultado modificación: " . ($resp ? "OK" : "FALLO"));
        
        $res = false;

        if ($resp) { 
            // Segundo: crear nuevo estado
            $arregloNewCompra = [
                'idcompra' => $data['idcompra'],
                'idcompraestadotipo' => $data['idcompraestadotipo'],
                'cefechaini' => $fechaFin,
                'cefechafin' => null,
            ];

            error_log("Creando nuevo estado: " . json_encode($arregloNewCompra));
            $res = $objCE->altaSinID($arregloNewCompra);
            error_log("Resultado alta: " . ($res ? "OK" : "FALLO"));
        }
        return $res;
    }

    public function ejecutarCompraCarrito()
    {
        $objSession = new Session();
        $objUsuarioControl = new UsuarioControl();
        $idUserLogueado = $objSession->getIDUsuarioLogueado();
        $carrito = $objUsuarioControl->obtenerCarrito($idUserLogueado);
        return ($this->iniciarCompra($carrito));
    }

    public function iniciarCompra($carrito)
    {
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $respuesta = false;
        $objCompraEstadoControl = new CompraEstadoControl();
        $idCompra = $carrito->getID();
        $paramCompra = array(
            'idcompra' => $idCompra,
            'idcompraestadotipo' => 1,
            'cefechaini' => date('Y-m-d H:i:s'),
            'cefechafin' => '0000-00-00 00:00:00'
        );

        $respuesta = $objCompraEstadoControl->altaSinID($paramCompra);

        if ($respuesta) {
            $param = array(
                'idcompra' => $idCompra,
                'idcompraestadotipo' => 5,
                'cefechafin' => null
            );
            $listaCompraEstado = $objCompraEstadoControl->buscar($param);
            if (count($listaCompraEstado) > 0) {
                $idCompraEstado = $listaCompraEstado[0]->getID();
                $paramEdicion = array(
                    'idcompraestado' => $idCompraEstado,
                    'idcompra' => $idCompra,
                    'idcompraestadotipo' => 5,
                    'cefechaini' => $listaCompraEstado[0]->getCeFechaIni(),
                    'cefechafin' => date('Y-m-d H:i:s')
                );
                $respuesta = $objCompraEstadoControl->modificacion($paramEdicion);
            }
            // Enviar correo
            \Mailer::enviarMail(['idcompra' => $idCompra, 'idcompraestadotipo' => 1]);
        }
        return ['idcompra' => $idCompra, 'respuesta' => $respuesta];
    }

    public function vaciarCarrito($idCarrito)
    {
        $respuesta = false;
        $objCompraItemControl = new CompraItemControl();
        $listaCI = $objCompraItemControl->buscar(['idcompra' => $idCarrito]);
        if (count($listaCI) > 0) {
            foreach ($listaCI as $compraItem) {
                $objCompraItemControl->baja(['idcompraitem' => $compraItem->getID()]);
            }
            $respuesta = true;
        }
        return $respuesta;
    }

    public function listarComprasUsuarios()
    {
        $arreglo = [];
        $objUsuarioControl = new UsuarioControl();
        $users = $objUsuarioControl->buscar(null);
        if (count($users) > 0) {
            foreach ($users as $user) {
                $arrDatos = $this->listarCompras($user->getID());
                array_push($arreglo, $arrDatos);
            }
        }
        return $arreglo;
    }

    public function listarCompras($idUsuario)
    {
        $arreglo_salida =  [];
        $listaCompras = $this->buscar(['idusuario' => $idUsuario]);
        if (count($listaCompras) > 0) {

            foreach ($listaCompras as $elem) {
                $objCompraEstadoControl = new CompraEstadoControl();
                $listaCE = $objCompraEstadoControl->buscar(['idcompra' => $elem->getID()]);
                
                if (count($listaCE) > 0) {
                    $lastPosCE = count($listaCE) - 1;
                    if (!($listaCE[$lastPosCE]->getObjCompraEstadoTipo()->getCetDescripcion() === "carrito")) {
                        $nuevoElem = [
                            "idcompra" => $listaCE[$lastPosCE]->getObjCompra()->getID(),
                            "cofecha" => $listaCE[$lastPosCE]->getCeFechaIni(),
                            "finfecha" => $listaCE[$lastPosCE]->getCeFechaFin(),
                            "usnombre" => $listaCE[$lastPosCE]->getObjCompra()->getObjUsuario()->getUsNombre(),
                            "estado" => $listaCE[$lastPosCE]->getObjCompraEstadoTipo()->getCetDescripcion(),
                            "idcompraestado" => $listaCE[$lastPosCE]->getID()
                        ];
                        array_push($arreglo_salida, $nuevoElem);
                    }
                }
            }
        }
        return $arreglo_salida;
    }

    /**
     * Verifica si una compra puede ser cancelada (menos de 24 horas desde su creación)
     * @param string $fechaCompra Fecha de la compra en formato string
     * @return bool True si puede cancelarse, False en caso contrario
     */
    public function puedeCancelarCompra($fechaCompra) {
        $fecha = Carbon::parse($fechaCompra);
        $ahora = Carbon::now();
        $horasDiferencia = $fecha->diffInHours($ahora);
        return $horasDiferencia < 24;
    }

    /**
     * Calcula la fecha estimada de entrega
     * @param string $fechaCompra Fecha de la compra
     * @param int $diasEstimados Días estimados de entrega (por defecto 7)
     * @return string Fecha de entrega formateada
     */
    public function fechaEntregaEstimada($fechaCompra, $diasEstimados = 7) {
        return Carbon::parse($fechaCompra)->addDays($diasEstimados)->format('d/m/Y');
    }

    /**
     * Verifica si una compra es del último mes
     * @param string $fechaCompra Fecha de la compra
     * @return bool True si es del último mes, False en caso contrario
     */
    public function esDelUltimoMes($fechaCompra) {
        $fecha = Carbon::parse($fechaCompra);
        $unMesAtras = Carbon::now()->subMonth();
        return $fecha->isAfter($unMesAtras);
    }
}