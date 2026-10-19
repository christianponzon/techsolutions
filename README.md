# TechSolutions — Sistema Web de Inventarios y Pedidos

## Descripción

TechSolutions es un sistema web desarrollado con PHP y MySQL que permite gestionar productos, controlar el inventario y registrar pedidos de clientes.

El sistema cuenta con autenticación de usuarios, un panel de estadísticas y un historial de movimientos de inventario.

## Tecnologías utilizadas

- PHP
- MySQL
- HTML5
- CSS3
- JavaScript
- XAMPP
- phpMyAdmin

## Funcionalidades principales

- Inicio y cierre de sesión.
- Registro, edición y listado de productos.
- Búsqueda de productos.
- Control de stock y alertas de existencias bajas.
- Registro de entradas y salidas de inventario.
- Historial de movimientos.
- Registro de pedidos.
- Descuento automático de stock al registrar pedidos.
- Visualización del detalle de pedidos.
- Dashboard con estadísticas de productos, ventas e inventario.

## Requisitos

- XAMPP con Apache y MySQL.
- Navegador web.
- PHP con extensión PDO MySQL habilitada.

## Instalación

1. Descargar el repositorio desde GitHub.
2. Descomprimir la carpeta del proyecto.
3. Copiar la carpeta `techsolutions` dentro de `C:\xampp\htdocs\`.
4. Iniciar Apache y MySQL desde XAMPP.
5. Abrir `http://localhost/phpmyadmin`.
6. Crear una base de datos llamada `techsolutions_db`.
7. Importar el archivo `database/techsolutions_db.sql`.
8. Configurar la conexión MySQL en `config/database.php`, según el entorno local.
9. Abrir una terminal dentro de la carpeta `C:\xampp\htdocs\techsolutions`.

10. Ejecutar el siguiente comando para crear una cuenta de administrador:

   ```powershell
   C:\xampp\php\php.exe instalar_admin.php
   ```

11. Ingresar el nombre, correo electrónico y una contraseña de al menos 12 caracteres cuando el programa lo solicite.

12. Una vez creado el administrador, abrir en el navegador:

   `http://localhost/techsolutions/`

13. Iniciar sesión con el correo y la contraseña registrados.

**Nota:** El archivo `instalar_admin.php` solo puede ejecutarse desde la terminal. Se recomienda utilizarlo únicamente durante la instalación inicial y eliminarlo de la instalación una vez creada la cuenta.

## Estructura del proyecto

- `auth/`: autenticación de usuarios.
- `config/`: conexión con MySQL.
- `css/`: estilos de la interfaz.
- `database/`: archivo SQL de la base de datos.
- `includes/`: archivos PHP compartidos.
- `inventario/`: gestión de entradas, salidas e historial.
- `js/`: archivos JavaScript.
- `pedidos/`: registro y consulta de pedidos.
- `productos/`: administración de productos.
- `dashboard.php`: panel principal.
- `index.php`: pantalla de inicio de sesión.
- `logout.php`: cierre de sesión.

## Seguridad

El sistema incorpora validación de sesiones, consultas preparadas mediante PDO, protección CSRF en formularios y validaciones de datos en el servidor.

## Proyecto académico

Proyecto desarrollado con fines académicos para demostrar la implementación de un sistema web de gestión de inventarios y pedidos.