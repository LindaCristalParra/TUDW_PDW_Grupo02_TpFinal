<?php

require_once __DIR__ . '/../../../../Control/productoControl.php';
require_once __DIR__ . '/../../../../Util/funciones.php';

// Cargar config global (project root)
if (file_exists(__DIR__ . '/../../../../config.php')) {
    require_once __DIR__ . '/../../../../config.php';
}

$producto = new ProductoControl();
$datos = carga_datos();


// Asegurar que existe archivo en datos
if (!empty($datos['imagen']) && is_array($datos['imagen']) && !empty($datos['imagen']['tmp_name'])) {
    $archivo = $datos['imagen'];

    // Generar nombre único y mover al filesystem configurado
    $nombreArchivo = imagen_generar_nombre_unico($archivo['name'], 'prod');

    $destinoFS = rtrim($GLOBALS['IMAGES_FS'] ?? (__DIR__ . '/../../../../Util/Imagenes/'), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    if (!is_dir($destinoFS)) { @mkdir($destinoFS, 0755, true); }

    $rutaDestino = $destinoFS . $nombreArchivo;

    if (is_uploaded_file($archivo['tmp_name']) && move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {

        // Guardamos SOLO el nombre del archivo en la BD (campo esperado: proimagen)
        $datos['proimagen'] = $nombreArchivo;

        // --- Insertar producto en BD ---
        $seRegistro = $producto->alta($datos);

        if ($seRegistro) {
            $message = 'Se ingresó correctamente el producto.';
        } else {
            $message = 'Hubo un error al ingresar el producto.';
        }

    } else {
        $message = 'Error al subir la imagen.';
    }

} else {
    // No se subió imagen: insertar sin imagen
    $datos['proimagen'] = null;
    $seRegistro = $producto->alta($datos);
    if ($seRegistro) {
        $message = 'Se ingresó correctamente el producto (sin imagen).';
    } else {
        $message = 'Hubo un error al ingresar el producto.';
    }
}

// --- Redirección con mensaje ---
// Usar ruta absoluta para evitar 404 por resolución relativa desde la carpeta de acciones
header("Location: /TUDW_PDW_Grupo02_TpFinal/Vista/admin/panelAdmin.php?Message=" . urlencode($message));
exit;
?>