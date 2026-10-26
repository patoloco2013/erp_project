# 🚀 ERP System v1.0

Sistema de Planificación de Recursos Empresariales (ERP) desarrollado en **PHP Puro** y **MySQL**. Diseñado para ser ligero, seguro y escalable, con soporte nativo para múltiples empresas y control de roles.

## 🛠️ Características Principales

- **Multiempresa**: Aislamiento total de datos. Cada usuario pertenece a una empresa y solo ve la información de la misma.
- **Gestión de Inventario**: 
    - Control de stock en tiempo real.
    - Registro detallado de cada movimiento (Entradas, Salidas, Ajustes).
    - Alertas de stock mínimo.
- **Ciclo de Ventas y Compras**:
    - Registro de compras a proveedores y ventas a clientes.
    - Soporte para **Cancelaciones y Devoluciones** con reintegración automática de stock.
- **Seguridad Reforzada**:
    - Consultas preparadas con **PDO** (Protección contra SQL Injection).
    - Contraseñas encriptadas con `password_hash`.
    - Control de Acceso Basado en Roles (**RBAC**: Admin, Manager, Staff).
    - Prevención de XSS mediante saneamiento de datos.
- **Auditoría Total**: Registro de cada acción realizada en el sistema, incluyendo usuario, fecha, IP y cambios de valores.
- **Diseño Responsivo**: Interfaz optimizada para PC, Tablets y Smartphones.

## 📦 Requisitos del Sistema

- **PHP** $\ge$ 7.4 (Recomendado 8.x)
- **MySQL** $\ge$ 5.7 / **MariaDB**
- Servidor Web (Apache, Nginx o XAMPP/WampServer)

## 🚀 Guía de Instalación

### 1. Clonar el Proyecto
```bash
git clone https://github.com/TU_USUARIO/erp-system-v1.git
cd erp-system-v1
```

### 2. Configurar la Base de Datos
1. Importar el archivo `database.sql` incluido en la raíz del proyecto en tu gestor de MySQL (phpMyAdmin, MySQL Workbench, etc.):
   ```bash
   mysql -u tu_usuario -p < database.sql
   ```
2. Editar el archivo `config/database.php` con tus credenciales:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'erp_system');
define('DB_USER', 'tu_usuario');
define('DB_PASS', 'tu_password');
```

### 3. Inicializar el Administrador
Ejecuta el script de configuración inicial para crear los roles, la primera empresa y el usuario administrador:
```bash
php setup_admin.php
```
*Credenciales por defecto:*
- **Usuario:** `admin`
- **Contraseña:** `admin123`

### 4. Acceso al Sistema
Abre tu navegador y dirígete a la carpeta del proyecto:
`http://localhost/erp_project/modules/auth/login.php`

## 📂 Estructura del Proyecto

- `/config`: Configuración de DB y constantes.
- `/includes`: Clases globales (Auditor, etc.).
- `/modules`: Lógica de negocio separada por módulos (Auth, Inventory, Sales).
- `/public`: Assets estáticos (CSS, JS) y el Dashboard.
- `/tests`: Scripts de validación de integridad.

## 🛡️ Seguridad
Este proyecto sigue las mejores prácticas de seguridad en PHP puro:
- **Transacciones SQL**: Para evitar descuadres de stock en caso de errores.
- **Regeneración de Sesiones**: Para evitar ataques de Session Fixation.
- **Saneamiento**: Uso de `htmlspecialchars` en todas las salidas al navegador.
