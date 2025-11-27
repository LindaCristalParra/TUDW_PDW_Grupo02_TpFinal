<?php
// Vista/Estructura/Accion/Menu/cargar.php

require_once __DIR__ . '/../../../../Control/Session.php';
require_once __DIR__ . '/../../../../Control/menuControl.php';
require_once __DIR__ . '/../../../../Control/menuRolControl.php'; 

$session = new Session();
if (!$session->sesionActiva()) {
    header('Location: /TUDW_PDW_Grupo02_TpFinal/Vista/login.php');
    exit;
}

// Validación de Admin 
if (method_exists($session, 'exigirAdmin')) {
    $session->exigirAdmin(__DIR__ . '/../../../../Vista/Estructura/footer.php');
} else {
    $rol = $session->getRolActivo();
    if (empty($rol) || ($rol['id'] != 1 && strtolower($rol['rol']) != 'administrador')) {
        die("Acceso Denegado");
    }
}

$menuCtrl = new MenuControl();
$mensaje = '';
$tipoMensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['menombre'] ?? '';
    $descripcion = $_POST['medescripcion'] ?? '';
    
    // Manejo correcto del padre NULL
    $idpadre = $_POST['idpadre'] ?? '';
    $idpadre = ($idpadre === '' ? null : $idpadre);

    // Recibir los roles marcados (Array de IDs)
    $roles = $_POST['roles'] ?? []; 

    $param = [
        'menombre' => $nombre,
        'medescripcion' => $descripcion,
        'idpadre' => $idpadre
    ];

    // Usamos altaConRoles en lugar de alta simple
    if ($menuCtrl->altaConRoles($param, $roles)) {
        $mensaje = 'Menú creado y asignado a roles con éxito.';
        $tipoMensaje = 'success';
    } else {
        $mensaje = 'Error al crear el menú o asignar los permisos.';
        $tipoMensaje = 'danger';
    }
    
}

// Lista de posibles padres
$posiblesPadres = $menuCtrl->buscar([]);

require_once __DIR__ . '/../../../../Vista/admin/cargarMenu.php';
?>