
<div align="center">

# 📦 TechSolutions

### Sistema Web de Gestión de Inventarios y Pedidos

**Una solución para administrar productos, controlar existencias y gestionar pedidos desde una plataforma web.**

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)

**Proyecto académico | Desarrollo de Software**

</div>

---

## 📖 Descripción del proyecto

**TechSolutions** es un sistema web desarrollado con PHP y MySQL que permite gestionar productos, controlar el inventario y registrar pedidos de clientes.

El sistema cuenta con autenticación de usuarios, un panel de estadísticas y un historial de movimientos de inventario.

Su desarrollo permite aplicar conocimientos de programación web, gestión de bases de datos y seguridad en aplicaciones.

---

## ✨ Funcionalidades principales

| Módulo | Funcionalidades |
|---|---|
| 🔐 Autenticación | Inicio y cierre de sesión |
| 📦 Productos | Registro, edición, listado y búsqueda |
| 📊 Dashboard | Estadísticas de productos, ventas e inventario |
| 🗃️ Inventario | Control de stock y alertas de existencias bajas |
| 🔄 Movimientos | Registro de entradas, salidas e historial |
| 🛒 Pedidos | Registro y consulta de pedidos |
| 📉 Stock | Descuento automático al registrar pedidos |
| 📋 Detalles | Visualización del detalle de pedidos |

---

## 🛠️ Tecnologías utilizadas

### Backend
- PHP
- PDO para conexión con MySQL

### Frontend
- HTML5
- CSS3
- JavaScript

### Base de datos
- MySQL
- phpMyAdmin

### Entorno de desarrollo
- XAMPP
- Apache

---

## 🖥️ Capturas del sistema

En esta sección se incorporarán imágenes reales de las principales pantallas de TechSolutions.

| Pantalla | Descripción |
|---|---|
| Login | Acceso mediante credenciales |
| Dashboard | Panel de estadísticas |
| Productos | Administración de productos |
| Inventario | Control de movimientos |
| Pedidos | Registro y consulta de pedidos |

---

## ⚙️ Requisitos de instalación

Para ejecutar TechSolutions localmente se necesita:

- XAMPP con Apache y MySQL.
- PHP con extensión PDO MySQL habilitada.
- Navegador web.
- phpMyAdmin para importar la base de datos.

---

## 🚀 Instalación y ejecución

### 1. Descargar el repositorio

```bash
git clone https://github.com/christianponzon/techsolutions.git
```

### 2. Ubicar el proyecto

Copiar la carpeta `techsolutions` dentro de:

```text
C:\xampp\htdocs\
```

### 3. Iniciar los servicios

Abrir XAMPP y activar:

- Apache
- MySQL

### 4. Configurar la base de datos

Ingresar a:

http://localhost/phpmyadmin

Crear una base de datos llamada:

```sql
CREATE DATABASE techsolutions_db;
```

Importar el archivo:

```text
database/techsolutions_db.sql
```

### 5. Configurar la conexión

Revisar el archivo:

```text
config/database.php
```

Configurar los datos de conexión según el entorno local.

### 6. Crear el administrador

Abrir una terminal dentro del proyecto y ejecutar:

```powershell
C:\xampp\php\php.exe instalar_admin.php
```

Ingresar los datos solicitados:

- Nombre
- Correo electrónico
- Contraseña de al menos 12 caracteres

**Importante:** Ejecutar este archivo únicamente desde la terminal durante la instalación inicial. Una vez creada la cuenta, eliminarlo de la instalación.

### 7. Ejecutar el sistema

Abrir el navegador e ingresar:

http://localhost/techsolutions/

Iniciar sesión con las credenciales registradas.

---

## 📁 Estructura del proyecto

```text
techsolutions/
│
├── auth/              # Autenticación
├── config/            # Conexión MySQL
├── css/               # Estilos
├── database/          # Script SQL
├── inventario/        # Movimientos y stock
├── pedidos/           # Gestión de pedidos
├── productos/         # Gestión de productos
│
├── dashboard.php      # Panel principal
├── index.php          # Inicio de sesión
├── instalar_admin.php # Instalación inicial
├── logout.php         # Cierre de sesión
├── .gitignore
└── README.md
```

---

## 🔒 Seguridad

TechSolutions incorpora medidas de seguridad para proteger la información y las operaciones del sistema.

- Validación de sesiones de usuario.
- Consultas preparadas mediante PDO.
- Protección CSRF en formularios.
- Validaciones de datos en el servidor.
- Contraseña de administrador de al menos 12 caracteres durante su creación.

---

## 🎓 Objetivo académico

Este proyecto fue desarrollado con fines académicos para demostrar la implementación de un sistema web de gestión de inventarios y pedidos.

Permite aplicar conocimientos de desarrollo backend, frontend, bases de datos, autenticación y organización de sistemas web.

---

## 👨‍💻 Desarrollador

**Charistian Dylan Gamarra Ponzoni**

Desarrollo de Software — SENATI

[![GitHub](https://img.shields.io/badge/GitHub-christianponzon-181717?style=for-the-badge&logo=github)](https://github.com/christianponzon)

---

<div align="center">

### 💡 Tecnología aplicada a soluciones reales

**Desarrollar · Aprender · Mejorar**

⭐ Gracias por visitar este repositorio.

</div>
