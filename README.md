# 🎄 Sistema de Punto de Venta (POS) para Tienda

Sistema web completo (Full Stack) diseñado a medida para la gestión de ventas y control de inventario de una tienda pequeña. Desarrollado con **PHP 8 puro (sin frameworks)**, MySQL y Bootstrap 5.

## 🚀 Características Principales

* **Punto de Venta (POS):** Carrito de compras dinámico con validación de stock en tiempo real.
* **Gestión de Inventario:** CRUD completo de productos con control de disponibilidad y alertas de stock.
* **Generación de Tickets:** Creación de tickets de venta formateados para impresión y exportables a imagen (PNG).
* **Integración con WhatsApp:** Envío directo del ticket en imagen a clientes sin usar APIs de pago (usando `html2canvas` y Clipboard API).
* **Reportes y Exportación:** Módulo de resumen de ventas con filtros por fechas y exportación de datos a formato Excel (CSV).
* **Anulación de Ventas:** Reversión de tickets con devolución automática del stock a los productos.
* **Seguridad:** Protección contra ataques CSRF, uso estricto de Sentencias Preparadas (PDO) contra SQL Injection, y encriptación de contraseñas (Bcrypt).
* **PWA (Progressive Web App):** Instalable en dispositivos móviles con soporte offline básico mediante Service Worker.

## 🛠️ Stack Tecnológico

* **Backend:** PHP 8 (POO, PDO)
* **Base de Datos:** MySQL
* **Frontend:** HTML5, CSS3, Bootstrap 5, JavaScript (Vanilla)
* **Librerías JS:** `html2canvas` (generación de imágenes)
* **Arquitectura:** Separación de responsabilidades (Patrón MVC simplificado en la carpeta `/includes`)

## ⚙️ Instalación Local

1. Clona este repositorio en tu servidor local (XAMPP, Laragon, etc.).
2. Importa el archivo `schema.sql` en tu gestor de base de datos (phpMyAdmin).
3. Copia el archivo `config/tienda.example.php` hacia una carpeta segura fuera de la raíz pública (o renómbralo a `config/tienda.php` en la raíz para pruebas) y configura tus credenciales de base de datos.
4. Inicia tu servidor local y accede a la ruta del proyecto.
5. Crea tu usuario administrador inicial (ejecutando un script o insertando el hash de la contraseña directamente en la BD).

---
*Desarrollado por [Tu Nombre] como proyecto de portafolio.*