<?php
require_once __DIR__ . '/../Modelo/Servicio/EmailService.php';
require_once __DIR__ . '/../Modelo/compra.php';
require_once __DIR__ . '/../Modelo/usuario.php';
require_once __DIR__ . '/../Modelo/compraItem.php';
require_once __DIR__ . '/compraItemControl.php';
require_once __DIR__ . '/Session.php';
require_once __DIR__ . '/usuarioControl.php';
require_once __DIR__ . '/productoControl.php';
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
        $objC = new Compra();
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
                                'cicantidad' => $row['cicantidad'],
                                'idproducto' => $row['idproducto'],
                                'pronombre' => $row['pronombre'],
                                'prodetalle' => $row['prodetalle'],
                                'precio' => $row['precio'],
                                'proimagen' => $row['proimagen']
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

    /**
     * Elimina un item verificando que pertenezca al usuario logueado.
     * @param int $idCompraItem
     * @param int $idUsuario
     * @return boolean
     */
    public function eliminarProductoDelCarrito($idCompraItem, $idUsuario)
    {
        $compraItemCtrl = new CompraItemControl();
        $uControl = new UsuarioControl();
        $puede = true;

        // Buscar el Item
        $items = $compraItemCtrl->buscar(['idcompraitem' => $idCompraItem]);
        if (empty($items)) {
            $puede = false; 
        }
        $item = $items[0];
        $idCompraDelItem = $item->getObjCompra()->getID();

        // Buscar el Carrito del Usuario
        $carrito = $uControl->obtenerCarrito($idUsuario);

        // Verificar Propiedad 
        if ($carrito == null || $carrito->getID() != $idCompraDelItem) {
            $puede = false;
        }

        // Ejecutar Baja
        if (!$puede) {
            return false;
        } else {
            return $compraItemCtrl->baja(['idcompraitem' => $idCompraItem]);
        }
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
                
                // Paso 3: Enviar email de cancelación
                if ($respuesta) {
                    try {
                        $compraCtrl = new CompraControl();
                        $listaCompras = $compraCtrl->buscar(['idcompra' => $data['idcompra']]);
                        
                        if (!empty($listaCompras)) {
                            $objCompra = $listaCompras[0];
                            $objUsuario = $objCompra->getObjUsuario();
                            
                            if ($objUsuario && $objUsuario->getUsMail()) {
                                $email = $objUsuario->getUsMail();
                                
                                // Obtener items de la compra para el email
                                $productos = $compraCtrl->listadoProdCarrito($objCompra);
                                $itemsParaMail = [];
                                $total = 0;
                                
                                foreach ($productos as $p) {
                                    $precio = floatval($p['precio']);
                                    $cantidad = intval($p['cicantidad']);
                                    $subtotal = $precio * $cantidad;
                                    $total += $subtotal;
                                    
                                    $itemsParaMail[] = [
                                        'nombre' => $p['pronombre'],
                                        'cantidad' => $cantidad,
                                        'precio' => $precio,
                                        'subtotal' => $subtotal
                                    ];
                                }
                                
                                $totalFormateado = '$' . number_format($total, 2, ',', '.');
                                
                                if (class_exists('EmailService')) {
                                    $datosExtra = [
                                        'nombre' => $objUsuario->getUsNombre(),
                                        'fecha' => date('d/m/Y H:i'),
                                        'items' => $itemsParaMail,
                                        'total' => $totalFormateado
                                    ];
                                    
                                    EmailService::enviarEstadoCompra($email, $data['idcompra'], 4, $datosExtra);
                                    error_log("Email de cancelación enviado a: " . $email);
                                }
                            }
                        }
                    } catch (Exception $e) {
                        error_log("Error al enviar email de cancelación: " . $e->getMessage());
                    }
                }
            }
        } else {
            error_log("ERROR: No se encontró el estado de compra con ID: " . $data['idcompraestado']);
        }

        return $respuesta;
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
        // Configuración de Fecha
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $fechaHoraActual = date('Y-m-d H:i:s');
        $fechaCeros = '0000-00-00 00:00:00';

        $respuesta = false;
        $objCompraEstadoControl = new CompraEstadoControl();
        $idCompra = $carrito->getID();
        
        // Preparar datos para el email
        $itemsParaMail = [];
        $totalCompra = 0;

        // Reutilizamos la función listar productos
        $listaProductos = $this->listadoProdCarrito($carrito);

        foreach ($listaProductos as $prod) {
            $precioUnitario = floatval($prod['precio']);
            $cantidad = intval($prod['cicantidad']);
            $subtotal = $precioUnitario * $cantidad;
            $totalCompra += $subtotal;

            // Armamos el array 
            $itemsParaMail[] = [
                'nombre' => $prod['pronombre'],
                'cantidad' => $cantidad,
                'precio' => '$' . number_format($precioUnitario, 2, ',', '.')
            ];
        }
        
        $totalFormateado = '$' . number_format($totalCompra, 2, ',', '.');
       


        // CERRAR ESTADOS ANTERIORES
        $estadosActivos = $objCompraEstadoControl->buscar([
            'idcompra' => $idCompra,
            'cefechafin' => $fechaCeros 
        ]);

        if (empty($estadosActivos)) {
             $todosLosEstados = $objCompraEstadoControl->buscar(['idcompra' => $idCompra]);
             foreach ($todosLosEstados as $e) {
                 if ($e->getCeFechaFin() == null) {
                     $estadosActivos[] = $e;
                 }
             }
        }

        foreach ($estadosActivos as $estadoAntiguo) {
            $paramCierre = [
                'idcompraestado' => $estadoAntiguo->getID(),
                'idcompra' => $idCompra,
                'idcompraestadotipo' => $estadoAntiguo->getObjCompraEstadoTipo()->getID(),
                'cefechaini' => $estadoAntiguo->getCeFechaIni(),
                'cefechafin' => $fechaHoraActual
            ];
            $objCompraEstadoControl->modificacion($paramCierre);
        }

        // CREAR NUEVO ESTADO
        $nuevoEstadoID = 2; 

        $paramNuevoEstado = [
            'idcompra' => $idCompra,
            'idcompraestadotipo' => $nuevoEstadoID,
            'cefechaini' => $fechaHoraActual,
            'cefechafin' => $fechaCeros
        ];

        // Guardamos
        if ($objCompraEstadoControl->alta($paramNuevoEstado)) {
            $respuesta = true;

            //  ENVIAR EMAIL CON LOS DATOS REALES
            try {
                $objUsuario = $carrito->getObjUsuario();
                if ($objUsuario == null || $objUsuario->getUsMail() == null) {
                    $objUsuario = new Usuario();
                    $objUsuario->setID($carrito->getObjUsuario()->getID());
                    $objUsuario->cargar();
                }
                
                $email = $objUsuario->getUsMail();
                
                // Verificamos si la clase existe para no romper si falta el mailer
                if (!empty($email) && class_exists('EmailService')) {
                     
                     // Datos 
                     $datosExtra = [
                        'nombre' => $objUsuario->getUsNombre(),
                        'fecha' => date('d/m/Y H:i'),
                        'items' => $itemsParaMail,  
                        'total' => $totalFormateado 
                     ];

                     EmailService::enviarEstadoCompra($email, $idCompra, $nuevoEstadoID, $datosExtra);
                }
            } catch (Exception $e) {
                // Silenciamos error de mail
            }
        }

        return $respuesta;
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
        $arreglo_salida = [];
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

    public function listarVentas()
    {
        $arreglo = [];
        $objC = new Compra();
        $arreglo = $objC->listar();
        return $arreglo;
        if (count($arreglo) > 0) {

            foreach ($arreglo as $elem) {
                $nuevoElem = [
                    "idcompra" => $listaCE[$lastPosCE]->getObjCompra()->getID(),
                    "cofecha" => $listaCE[$lastPosCE]->getCeFechaIni(),
                    "finfecha" => $listaCE[$lastPosCE]->getCeFechaFin(),
                    "estado" => $listaCE[$lastPosCE]->getObjCompraEstadoTipo()->getCetDescripcion(),
                ];
                array_push($arreglo_salida, $nuevoElem);
            }
        }
        return $arreglo_salida;
    }

    /**
     * Verifica si una compra puede ser cancelada (menos de 24 horas desde su creación)
     * @param string $fechaCompra Fecha de la compra en formato string
     * @return bool True si puede cancelarse, False en caso contrario
     */
    public function puedeCancelarCompra($fechaCompra)
    {
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
    public function fechaEntregaEstimada($fechaCompra, $diasEstimados = 7)
    {
        return Carbon::parse($fechaCompra)->addDays($diasEstimados)->format('d/m/Y');
    }

    /**
     * Verifica si una compra es del último mes
     * @param string $fechaCompra Fecha de la compra
     * @return bool True si es del último mes, False en caso contrario
     */
    public function esDelUltimoMes($fechaCompra)
    {
        $fecha = Carbon::parse($fechaCompra);
        $unMesAtras = Carbon::now()->subMonth();
        return $fecha->isAfter($unMesAtras);
    }

   public function actualizarEstadoCompra($idCompra, $nuevoEstadoTipo)
    {
        // 1. Configuración
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $fechaHoraActual = date('Y-m-d H:i:s');
        $fechaCeros = '0000-00-00 00:00:00';
        
        $objCompraEstadoControl = new CompraEstadoControl();
        $exito = false;

        // BUSCAR EL ESTADO ACTUAL ACTIVO
        // Traemos TODOS los estados de esa compra
        $todosLosEstados = $objCompraEstadoControl->buscar(['idcompra' => $idCompra]);
        
        $estadosACerrar = [];

        // Filtramos manualmente en PHP (Más seguro que confiar en el SQL del buscar)
        foreach ($todosLosEstados as $e) {
            $fin = $e->getCeFechaFin();
            // Si la fecha es nula, vacía o ceros, hay que cerrarlo
            if ($fin == null || $fin == '' || $fin == $fechaCeros) {
                $estadosACerrar[] = $e;
            }
        }

        // CERRAR LOS VIEJOS
        
        if (!empty($estadosACerrar)) {
            foreach ($estadosACerrar as $estadoViejo) {
                
                // Instanciamos un objeto limpio
                $objCierre = new CompraEstado();
                
                // Seteamos el ID y cargamos los datos actuales de la BD
                $objCierre->setID($estadoViejo->getID());
                $objCierre->cargar(); 
                
                // Cambiamos SOLO la fecha de fin
                $objCierre->setCeFechaFin($fechaHoraActual);
                
                // Guardamos los cambios
                if (!$objCierre->modificar()) {
                    error_log("Error al cerrar estado ID: " . $estadoViejo->getID());
                }
            }
        }

        // ABRIR EL NUEVO ESTADO
        $paramNuevo = [
            'idcompra' => $idCompra,
            'idcompraestadotipo' => $nuevoEstadoTipo,
            'cefechaini' => $fechaHoraActual,
            'cefechafin' => $fechaCeros 
        ];

        if ($objCompraEstadoControl->alta($paramNuevo)) {
            $exito = true;

            // EMAIL
            try {
                $objCompra = new Compra();
                $objCompra->setID($idCompra);
                if ($objCompra->cargar()) {
                    $rawUser = $objCompra->getObjUsuario();
                    $idUsuarioCompra = is_object($rawUser) ? $rawUser->getID() : $rawUser;

                    if (!empty($idUsuarioCompra)) {
                        $objUsuarioFinal = new Usuario();
                        $objUsuarioFinal->setID($idUsuarioCompra);
                        if ($objUsuarioFinal->cargar()) {
                            $email = $objUsuarioFinal->getUsMail();
                            $nombre = $objUsuarioFinal->getUsNombre();
                            
                            if ($email && class_exists('EmailService')) {
                                $datos = ['nombre' => $nombre, 'fecha' => date('d/m/Y H:i')];
                                EmailService::enviarEstadoCompra($email, $idCompra, $nuevoEstadoTipo, $datos);
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                // Ignorar error mail
            }
        }

        return $exito;
    }

    /**
     * Encapsula toda la lógica para agregar o sumar un producto al carrito activo.
     * @param int $idUsuario
     * @param int $idProducto
     * @param string $claseItemControl (Nombre de la clase CompraItemControl/CompraProductoControl)
     * @return boolean Éxito o Fracaso de la transacción
     */
    public function agregarProductoAlCarrito($idUsuario, $idProducto, $claseItemControl)
    {
        // Nota: Asumimos que los requires de CompraEstadoControl, UsuarioControl, etc. están al inicio del archivo.
        $abmItem = new $claseItemControl();
        $abmCompraEstado = new CompraEstadoControl();
        $abmCompra = new CompraControl(); // Usamos la misma clase
        $uControl = new UsuarioControl(); // Usamos el mismo control

        // 1. BUSCAR O CREAR CARRITO ACTIVO
        $carritoObj = $uControl->obtenerCarrito($idUsuario);
        $idCompraActiva = null;

        if ($carritoObj != null) {
            $idCompraActiva = $carritoObj->getID();
        } else {
            // Si no existe, creamos uno nuevo
            if ($abmCompra->alta(['idusuario' => $idUsuario])) {
                $compras = $abmCompra->buscar(['idusuario' => $idUsuario]);
                $ultimaCompra = end($compras);
                $idCompraActiva = $ultimaCompra->getId();
                
                // Asignar el estado inicial (1 = iniciada)
                $abmCompraEstado->alta([
                    'idcompra' => $idCompraActiva, 
                    'idcompraestadotipo' => 1,
                    'cefechafin' => '0000-00-00 00:00:00' 
                ]);
            } else {
                return false; // Falló la creación de la compra
            }
        }

        // 2. GESTIONAR EL ITEM (Insertar o Sumar)
        $itemsEnCarrito = $abmItem->buscar([
            'idcompra' => $idCompraActiva,
            'idproducto' => $idProducto
        ]);

        if (!empty($itemsEnCarrito)) {
            // UPDATE: Sumar cantidad
            $itemExistente = $itemsEnCarrito[0];
            $nuevaCantidad = $itemExistente->getCiCantidad() + 1;
            
            $param = [
                'idcompraitem' => $itemExistente->getId(),
                'idcompra'     => $idCompraActiva,
                'idproducto'   => $idProducto,
                'cicantidad'   => $nuevaCantidad
            ];
            return $abmItem->modificacion($param);

        } else {
            // INSERT: Nuevo item
            $param = [
                'idcompra'   => $idCompraActiva,
                'idproducto' => $idProducto,
                'cicantidad' => 1
            ];
            return $abmItem->alta($param);
        }
    }

/**
     * Realiza todo el proceso de finalizar la compra: validación de stock, descuento y cambio de estado.
     * @param int $idUsuario
     * @return array ['exito' => bool, 'mensaje' => string, 'idcompra' => int, 'detalle' => string]
     */
    public function finalizarCompraCompleta($idUsuario)
    {
        $uControl = new UsuarioControl();
        $carrito = $uControl->obtenerCarrito($idUsuario);

        if ($carrito == null) {
            return ['exito' => false, 'mensaje' => 'carrito_vacio_error'];
        }

        // 1. Obtener productos
        $productos = $this->listadoProdCarrito($carrito);
        if (empty($productos)) {
            return ['exito' => false, 'mensaje' => 'carrito_vacio'];
        }

        // 2. Validar Stock
        $prodCtrl = new ProductoControl();
        foreach ($productos as $item) {
            // Nos aseguramos de traer el objeto producto fresco
            $listaP = $prodCtrl->buscar(['idproducto' => $item['idproducto']]);
            if (count($listaP) > 0) {
                $objProd = $listaP[0];
                $stockActual = $objProd->getProCantStock();
                $cantidadSolicitada = $item['cicantidad'];

                if ($stockActual < $cantidadSolicitada) {
                    return [
                        'exito' => false, 
                        'mensaje' => 'sin_stock', 
                        'detalle' => "No hay suficiente stock de " . $item['pronombre']
                    ];
                }
            }
        }

        // 3. Descontar Stock (Si llegamos aquí, hay stock de todo)
        foreach ($productos as $item) {
            $listaP = $prodCtrl->buscar(['idproducto' => $item['idproducto']]);
            if (count($listaP) > 0) {
                $objProd = $listaP[0];
                $nuevoStock = $objProd->getProCantStock() - $item['cicantidad'];
                
                $datosProd = [
                    'idproducto' => $objProd->getID(),
                    'pronombre' => $objProd->getProNombre(),
                    'prodetalle' => $objProd->getProDetalle(),
                    'procantstock' => $nuevoStock,
                    'precio' => $objProd->getPrecio(),
                    'proimagen' => $objProd->getImagen()
                ];
                $prodCtrl->modificacion($datosProd);
            }
        }

        // 4. Cambiar Estado y Enviar Mail
        // Llamamos a la función interna que ya tenías programada
        $resultado = $this->iniciarCompra($carrito);

        if ($resultado) {
            return ['exito' => true, 'idcompra' => $carrito->getID()];
        } else {
            return ['exito' => false, 'mensaje' => 'error_procesando'];
        }
    }

/**
     * Devuelve el listado de compras correspondiente según el rol del usuario.
     * Si es Admin -> Todas. Si es Cliente -> Solo las suyas.
     * @param int $idUsuario
     * @param array $rolActivo
     * @return array Lista de compras
     */
    public function listarComprasSegunRol($idUsuario, $rolActivo)
    {
        // Normalizamos el nombre del rol para evitar errores de mayúsculas
        $esAdmin = false;
        if (!empty($rolActivo) && isset($rolActivo['rol'])) {
            if (strtolower($rolActivo['rol']) === 'administrador' || $rolActivo['id'] == 1) {
                $esAdmin = true;
            }
        }

        if ($esAdmin) {
            // El Admin ve todo el historial del sistema
            return $this->listarComprasUsuarios();
        } else {
            // El Cliente solo ve su historial
            return $this->listarCompras($idUsuario);
        }
    }

}