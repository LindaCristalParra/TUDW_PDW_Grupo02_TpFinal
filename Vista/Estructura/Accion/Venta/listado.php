<?php
// Vista/Estructura/Accion/Venta/listado.php
require_once __DIR__ . '/../../../../Control/Session.php';
require_once __DIR__ . '/../../../../Control/compraControl.php';
require_once __DIR__ . '/../../../../Control/menuControl.php';

$session = new Session();
if (!$session->sesionActiva()) {
	header('Location: /TUDW_PDW_Grupo02_TpFinal/Vista/login.php');
	exit;
}

$rolActivo = $session->getRolActivo();
if (empty($rolActivo) || !isset($rolActivo['rol']) || strtolower($rolActivo['rol']) !== 'administrador') {
	echo '<div class="container mt-4"><div class="alert alert-danger">Acceso denegado. Debés ser administrador.</div></div>';
	require_once __DIR__ . '/../../../../Vista/Estructura/footer.php';
	exit;
}

$compraCtrl = new CompraControl();


// Obtener todas las ventas
// listarVentas devuelve objetos Compra; convertimos a arrays que la vista espera
$comprasObj = $compraCtrl->listarVentas();
$compras = [];
require_once __DIR__ . '/../../../../Control/compraEstadoControl.php';
$compraEstadoCtrl = new CompraEstadoControl();

if (is_array($comprasObj)) {
	foreach ($comprasObj as $elem) {
		if (is_object($elem) && method_exists($elem, 'getID')) {
			$id = $elem->getID();
			// obtener estados de la compra
			$listaCE = $compraEstadoCtrl->buscar(['idcompra' => $id]);
			$estado = '';
			$idcompraestado = null;
			$usnombre = '';
			$cofecha = $elem->getCofecha();

			if (count($listaCE) > 0) {
				$lastPos = count($listaCE) - 1;
				$estado = $listaCE[$lastPos]->getObjCompraEstadoTipo()->getCetDescripcion();
				$idcompraestado = $listaCE[$lastPos]->getID();
				$usnombre = $listaCE[$lastPos]->getObjCompra()->getObjUsuario()->getUsNombre();
				$cofecha = $listaCE[$lastPos]->getCeFechaIni();
			}

			$compras[] = [
				'idcompra' => $id,
				'cofecha' => $cofecha,
				'finfecha' => null,
				'usnombre' => $usnombre,
				'estado' => $estado,
				'idcompraestado' => $idcompraestado
			];
		} elseif (is_array($elem)) {
			// Si ya viene como array asociativo, lo agregamos tal cual
			$compras[] = $elem;
		}
	}
}

// Preferir una vista dedicada para ventas de administrador; si no existe, reutilizar la vista genérica
$vistaDedicada = __DIR__ . '/../../../../Vista/venta/listado.php';
if (file_exists($vistaDedicada)) {
	require_once $vistaDedicada;
} else {
	require_once __DIR__ . '/../../../../Vista/listadoCompras.php';
}

