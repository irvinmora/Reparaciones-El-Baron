# 🔧 El Barón - Sistema de Gestión para Taller de Reparaciones

![Logo El Barón](assets/img/logo.png)

<div align="center">
  
  ### 🛠️ **Sistema completo para talleres de reparación de electrodomésticos**
  
  [![PHP Version](https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php)](https://php.net)
  [![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql)](https://mysql.com)
  [![JavaScript](https://img.shields.io/badge/JavaScript-ES6-F7DF1E?style=for-the-badge&logo=javascript)](https://developer.mozilla.org/es/docs/Web/JavaScript)
  [![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)](LICENSE)
  
</div>

---

## 📋 **Tabla de Contenidos**
- [Descripción General](#-descripción-general)
- [Características Principales](#-características-principales)
- [Gestión de Usuarios](#-gestión-de-usuarios)
- [Tecnologías Utilizadas](#-tecnologías-utilizadas)
- [Requisitos del Sistema](#-requisitos-del-sistema)
- [Instalación Paso a Paso](#-instalación-paso-a-paso)
- [Configuración de Usuarios](#-configuración-de-usuarios)
- [Estructura de la Base de Datos](#-estructura-de-la-base-de-datos)
- [Uso del Sistema](#-uso-del-sistema)
- [API de Búsquedas](#-api-de-búsquedas)
- [Contribuir al Proyecto](#-contribuir-al-proyecto)
- [Licencia](#-licencia)
- [Contacto](#-contacto)

---

## 📖 **Descripción General**

**El Barón** es un sistema de gestión integral diseñado específicamente para talleres de reparación de electrodomésticos (licuadoras, microondas, ventiladores, planchas, lavadoras, etc.). Permite llevar un control completo de reparaciones, inventario de repuestos, ventas y clientes, con la particularidad de generar stickers personalizados con versículos bíblicos.

> *"Todo lo puedo en Cristo que me fortalece" - Filipenses 4:13*

---

## ✨ **Características Principales**

### 🔐 **Módulo de Autenticación**
- ✅ Registro de nuevos usuarios
- ✅ Login tradicional con email/contraseña
- ✅ Login con Google OAuth 2.0
- ✅ Recuperación de contraseña
- ✅ Sesiones seguras

### 👥 **Gestión de Usuarios (Técnicos y Administradores)**
- ✅ **3 niveles de acceso:**
  - **Administrador:** Acceso total al sistema, gestión de usuarios
  - **Técnico Senior:** Puede gestionar reparaciones y ventas
  - **Técnico Junior:** Solo puede ver y registrar reparaciones
- ✅ CRUD completo de usuarios (Crear, Leer, Actualizar, Eliminar)
- ✅ Asignación de roles y permisos
- ✅ Historial de actividades por usuario

### 🔧 **Gestión de Reparaciones**
- ✅ Registro de clientes con datos completos
- ✅ Asignación de técnico responsable
- ✅ Control de estados (pendiente, en proceso, completado, entregado)
- ✅ Sistema de pagos y abonos
- ✅ Cálculo automático de saldos
- ✅ Generación de stickers/comprobantes personalizados
- ✅ Compartir por WhatsApp

### 📦 **Inventario de Repuestos**
- ✅ CRUD de productos
- ✅ Control de stock con alertas
- ✅ Códigos únicos por producto
- ✅ Precios de venta
- ✅ Stock mínimo configurable
- ✅ Historial de movimientos

### 💰 **Ventas**
- ✅ Registro de ventas de repuestos
- ✅ Búsqueda en tiempo real de productos
- ✅ Descuento automático de stock
- ✅ Múltiples productos por venta
- ✅ Generación de factura/sticker

### 📊 **Reportes y Estadísticas**
- ✅ Reporte de ventas (diario, semanal, mensual, anual)
- ✅ Reporte de reparaciones
- ✅ Reporte de productos más vendidos
- ✅ Reporte de ganancias
- ✅ Exportación a PDF
- ✅ Gráficos estadísticos

### 🔍 **Búsquedas Avanzadas**
- ✅ Búsqueda en tiempo real de clientes
- ✅ Búsqueda de productos por código/nombre
- ✅ Filtros por fechas
- ✅ Autocompletado en formularios

---

## 👤 **Gestión de Usuarios**

### Roles y Permisos

| Rol | Reparaciones | Ventas | Productos | Clientes | Usuarios | Reportes | Configuración |
|-----|:------------:|:------:|:---------:|:--------:|:--------:|:--------:|:-------------:|
| **Administrador** | ✅ CRUD | ✅ CRUD | ✅ CRUD | ✅ CRUD | ✅ CRUD | ✅ Ver | ✅ Editar |
| **Técnico Senior** | ✅ CRUD | ✅ CRUD | ✅ Ver | ✅ CRUD | ❌ | ✅ Ver | ❌ |
| **Técnico Junior** | ✅ Crear/Ver | ❌ | ✅ Ver | ✅ Ver | ❌ | ❌ | ❌ |

### Funcionalidades de Gestión de Usuarios

#### ➕ **Agregar Nuevo Usuario**
```sql
-- El sistema permite agregar usuarios desde el panel de administración
INSERT INTO usuarios (nombre, email, password, rol, activo) 
VALUES ('Juan Pérez', 'juan@email.com', 'hash_password', 'tecnico_senior', 1);
✏️ Editar Usuario
Cambiar nombre, email, rol

Resetear contraseña

Activar/Desactivar cuenta

❌ Eliminar Usuario
Eliminación lógica (desactivar)

Eliminación física (solo admin)

Transferencia de reparaciones a otro técnico

🔄 Historial de Actividades
sql
CREATE TABLE historial_actividades (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT,
    accion VARCHAR(255),
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45)
);
🛠️ Tecnologías Utilizadas
Tecnología	Versión	Uso
PHP	8.2.x	Lógica del negocio, backend
MySQL	8.0.x	Base de datos
Apache	2.4.x	Servidor web
HTML5	-	Estructura de páginas
CSS3	-	Estilos y diseño responsive
JavaScript	ES6	Interactividad, AJAX
Font Awesome	6.0	Iconografía
Google API Client	^2.0	Login con Google
cURL	-	Peticiones HTTP
Composer	2.7.x	Gestor de dependencias
📋 Requisitos del Sistema
Mínimos
PHP: 7.4 o superior

MySQL: 5.7 o superior

Apache: 2.4 con mod_rewrite habilitado

Espacio en disco: 100 MB

Memoria RAM: 512 MB

Recomendados
PHP: 8.0 o superior

MySQL: 8.0

Apache: 2.4

Extensiones PHP: mysqli, curl, json, session, openssl

🚀 Instalación Paso a Paso
1️⃣ Clonar el repositorio
bash
git clone https://github.com/irvinmora/Reparaciones-El-Baron
cd el-baron
2️⃣ Configurar el entorno local (XAMPP/WAMP)
bash
# Si usas XAMPP, mueve el proyecto a:
C:\xampp\htdocs\el-baron
3️⃣ Configurar la base de datos
sql
-- La base de datos se crea automáticamente al primer acceso
-- Pero puedes crearla manualmente:
CREATE DATABASE IF NOT EXISTS el_baron_reparaciones;
USE el_baron_reparaciones;
4️⃣ Configurar archivo de conexión
Editar includes/config.php:

php
<?php
session_start();

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'el_baron_reparaciones');

define('SITE_NAME', 'El Barón - Reparaciones Técnicas');
define('SITE_URL', 'http://localhost/el-baron-reparaciones/index.php');
define('SITE_VERSION', '2.0.0');

// Colores de la marca
define('COLOR_PRIMARY', '#2c3e50');    // Azul oscuro
define('COLOR_SECONDARY', '#e67e22');  // Naranja
define('COLOR_ACCENT', '#27ae60');     // Verde

// Versículo bíblico por defecto
define('VERSICULO', 'Filipenses 4:13 - Todo lo puedo en Cristo que me fortalece');
?>
5️⃣ Instalar dependencias (opcional)
bash
# Solo si vas a usar login con Google
composer require google/apiclient:"^2.0"
6️⃣ Configurar permisos de carpetas
bash
# En Linux/Mac
chmod 755 -R /var/www/el-baron
chmod 777 -R /var/www/el-baron/assets/img

# En Windows, asegurar que las carpetas tengan permisos de escritura
7️⃣ Configurar Google Login (opcional)
Ir a Google Cloud Console

Crear nuevo proyecto

Configurar pantalla de consentimiento OAuth

Crear credenciales OAuth 2.0

Agregar URI de redirección: http://localhost/el-baron/login_google.php

Actualizar login_google.php con Client ID y Secret

8️⃣ Acceder al sistema
text
http://localhost/el-baron-reparaciones/index.php
👥 Configuración de Usuarios
Usuario Administrador por Defecto
sql
-- El primer usuario registrado se convierte automáticamente en administrador
-- O puedes insertar manualmente:
INSERT INTO usuarios (nombre, email, password, rol, activo) 
VALUES ('Admin', 'admin@elbaron.com', '$2y$10$hash_de_ejemplo', 'administrador', 1);
Crear Nuevos Usuarios (desde el sistema)
Inicia sesión como administrador

Ve a Configuración → Usuarios

Haz clic en "Nuevo Usuario"

Completa:

Nombre completo

Email

Contraseña temporal

Rol (Administrador/Técnico Senior/Técnico Junior)

Haz clic en "Guardar"

Editar Usuarios
Cambiar rol

Resetear contraseña

Activar/Desactivar cuenta

Eliminar Usuarios
Desactivar: El usuario no puede iniciar sesión pero sus registros se mantienen

Eliminar: Eliminación permanente (con confirmación)

📁 Estructura de la Base de Datos
Tabla usuarios
sql
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255),
    google_id VARCHAR(255),
    avatar VARCHAR(255),
    rol ENUM('administrador', 'tecnico_senior', 'tecnico_junior') DEFAULT 'tecnico_junior',
    activo BOOLEAN DEFAULT TRUE,
    ultimo_acceso TIMESTAMP NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
Tabla historial_actividades
sql
CREATE TABLE historial_actividades (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT,
    accion VARCHAR(255) NOT NULL,
    detalle TEXT,
    ip_address VARCHAR(45),
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);
🖥️ Uso del Sistema
1️⃣ Primer Inicio
Regístrate con email o Google

El primer usuario será administrador

Completa tu perfil

2️⃣ Panel de Administración (solo admin)
Usuarios: Gestionar técnicos

Configuración: Ajustes del sistema

Reportes globales: Todas las estadísticas

Backup: Exportar base de datos

3️⃣ Para Técnicos
Dashboard: Resumen diario

Reparaciones: Registrar y gestionar

Clientes: Buscar y crear

Ventas: Registrar ventas (senior)

🔍 API de Búsquedas
El sistema incluye endpoints AJAX para búsquedas en tiempo real:

javascript
// Buscar clientes
fetch('buscar_clientes.php?q=termino')
  .then(response => response.json())
  .then(data => console.log(data));

// Buscar productos
fetch('buscar_productos.php?q=termino')
  .then(response => response.json())
  .then(data => console.log(data));
🤝 Contribuir al Proyecto
¡Las contribuciones son bienvenidas!

Pasos para contribuir:
Fork el proyecto

Crea tu rama (git checkout -b feature/NuevaCaracteristica)

Commit tus cambios (git commit -m 'Add: nueva característica')

Push a la rama (git push origin feature/NuevaCaracteristica)

Abre un Pull Request

Reportar Issues
Si encuentras un bug o tienes una sugerencia:

Abre un issue en GitHub Issues

Describe claramente el problema o sugerencia

Incluye capturas de pantalla si es posible

📄 Licencia
Este proyecto está bajo la Licencia MIT.

text
MIT License

Copyright (c) 2024 Edison898

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
📞 Contacto
<div align="center">
Desarrollador: Irvin adinis mora paredes
GitHub: @https://github.com/irvinmora


Sistema Web: http://localhost/el-baron-reparaciones/index.php

⭐ Si te gusta el proyecto, ¡no olvides darle una estrella en GitHub! ⭐
</div>
🙏 Agradecimientos Especiales
A Dios, por la sabiduría y fortaleza

A todos los técnicos que trabajan con dedicación

A la comunidad de código abierto

"El Barón" - Servicio Técnico Especializado
Filipenses 4:13

text

4. **Guarda el archivo** (Ctrl+S)

5. **Súbelo a GitHub:**
```bash
git add README.md
git commit -m "Add: README profesional con gestión de usuarios"
git push
✅ Resultado final
Cuando entres a https://github.com/irvinmora/Reparaciones-El-Baron, verás el README completo con:

Badges de tecnologías

Tabla de contenidos interactiva

Tablas de roles y permisos

Códigos de ejemplo

Secciones bien organizadas

Diseño profesional