# UNCO_FAI_TUDW_PWD_2025_TP_FINAL_INTEGRADOR

# Trabajo Final PWD - E-commerce de Artículos Navideños 🎄

**Tecnicatura Universitaria en Desarrollo Web - Universidad Nacional del Comahue** **Materia:** Programación Web Dinámica (2025)  
**Grupo:** 02

![Estado](https://img.shields.io/badge/Estado-Finalizado-green)
![Lenguaje](https://img.shields.io/badge/PHP-8.x-blue)
![DB](https://img.shields.io/badge/MySQL-MariaDB-orange)
![Arquitectura](https://img.shields.io/badge/Arquitectura-MVC-red)

## 📋 Descripción del Proyecto

Este proyecto consiste en el desarrollo de una tienda en línea (E-commerce) temática de artículos navideños, integrando los conceptos aprendidos durante la cursada de Programación Web Dinámica.

El sistema implementa un **Carrito de Compras** completo con roles de usuario, gestión administrativa y una arquitectura basada en el patrón de diseño **MVC (Modelo-Vista-Controlador)** utilizando **PHP** puro y **MySQL**.

### ✨ Características Principales

* **Arquitectura MVC:** Separación lógica del código en Modelos, Vistas y Controladores.
* **Gestión de Sesiones y Seguridad:** Sistema de Login con roles y permisos (basado en el TP 5).
* **Roles de Usuario:**
    * **Cliente:** Puede navegar el catálogo, gestionar su cuenta (email, contraseña) y realizar compras.
    * **Administrador:** Acceso total al panel de administración para gestionar usuarios, roles, menús y productos.
* **Menú Dinámico:** Gestión de la estructura de navegación desde la base de datos.
* **ABM de Productos:** Alta, Baja y Modificación de productos (incluyendo manejo de imágenes y stock).
* **Carrito de Compras:** Flujo completo de selección de productos y compra.

## 🛠️ Tecnologías Utilizadas

* **Lenguaje:** PHP (POO).
* **Base de Datos:** MySQL / MariaDB (`bdcarritocompras`).
* **Frontend:** HTML5, CSS3, JavaScript (Bootstrap/jQuery según corresponda a la librería utilizada).
* **Gestor de Dependencias:** Composer.
* **Servidor Web:** Apache (XAMPP/WAMP/LAMP).

## 📂 Estructura del Repositorio

El proyecto sigue una estructura de directorios organizada:

* `/Control`: Lógica de negocio y controladores de las acciones.
* `/Modelo`: Clases de acceso a datos (ORM) y conexión con la BD.
* `/Vista`: Archivos de interfaz de usuario (Páginas públicas y privadas, Estructura HTML).
* `/Util`: Funciones auxiliares y configuración.
* `config.php`: Archivo de configuración global del proyecto.
* `composer.json`: Definición de dependencias del proyecto.

## 🚀 Instalación y Despliegue

Sigue estos pasos para poner en marcha el proyecto en tu entorno local:

1.  **Clonar el repositorio:**
    ```bash
    git clone [https://github.com/LindaCristalParra/TUDW_PDW_Grupo02_TpFinal.git](https://github.com/LindaCristalParra/TUDW_PDW_Grupo02_TpFinal.git)
    ```

2.  **Configurar la Base de Datos:**
    * Crea una base de datos llamada `bdcarritocompras` en tu gestor MySQL.
    * Importa el script SQL provisto por la cátedra (o el que se encuentre en la carpeta `/sql` si existe) para generar las tablas y datos iniciales.

3.  **Instalar dependencias:**
    Si tienes Composer instalado, ejecuta en la raíz del proyecto:
    ```bash
    composer install
    ```
    *(Esto generará la carpeta `vendor` necesaria para el funcionamiento de librerías externas).*

4.  **Configuración del entorno:**
    * Verifica el archivo `config.php` y asegúrate de que `BASE_URL` o las credenciales de base de datos coincidan con tu configuración local (usuario, contraseña de MySQL).

5.  **Ejecutar:**
    * Coloca la carpeta del proyecto en el directorio `htdocs` (XAMPP) o `www` (WAMP).
    * Accede desde tu navegador a: `http://localhost/TUDW_PDW_Grupo02_TpFinal/Vista/` (o la ruta correspondiente).

## 👥 Integrantes del Grupo 02

* **Linda Cristal Parra** - [GitHub](https://github.com/LindaCristalParra)
* **Andrea Crespillo** - [GitHub](https://github.com/Andre-C96)
* **Ramiro Rafael Navarrete** - [GitHub](https://github.com/nramiror)
* **Lautaro Mellado** - [GitHub](https://github.com/LautyM22)

---
*Proyecto realizado con fines académicos para la Universidad Nacional del Comahue - 2025.*
  
