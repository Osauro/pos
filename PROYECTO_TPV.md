# Sistema TPV (Punto de Venta)

Sistema de Punto de Venta desarrollado con Laravel 13 y Livewire 3, con diseño profesional y funcionalidades completas para gestión de ventas, productos, usuarios, turnos y movimientos.

## 🚀 Características

- ✅ **Autenticación segura** con PIN de 4 dígitos
- ✅ **Gestión de Usuarios** (Admin y Usuario)
- ✅ **Gestión de Productos** con imágenes y categorías
- ✅ **Punto de Venta (POS)** interactivo y responsivo
- ✅ **Gestión de Turnos** semanales
- ✅ **Registro de Movimientos** (ingresos/egresos)
- ✅ **Historial de Ventas** con detalles
- ✅ **Diseño profesional** con template moderno
- ✅ **Responsive** para móvil, tablet y escritorio

## 📋 Requisitos

- PHP 8.2 o superior
- MySQL 5.7 o superior
- Composer
- Node.js y NPM (opcional, no se usa Vite)

## 🔧 Instalación

### 1. Clonar el proyecto o navegar a la carpeta

```bash
cd c:\laragon\www\tpv
```

### 2. Instalar dependencias

```bash
composer install
```

### 3. Configurar base de datos

Editar el archivo `.env` con las credenciales de tu base de datos:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tpv
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Ejecutar migraciones y seeders

```bash
php artisan migrate:fresh --seed
```

Esto creará el usuario administrador:
- **Nombre:** Diego
- **Celular:** 73010688
- **PIN:** 5421
- **Tipo:** admin

### 5. Crear symlink para imágenes

```bash
php artisan storage:link
```

### 6. Iniciar el servidor

```bash
php artisan serve
```

El sistema estará disponible en: `http://localhost:8000` o `http://tpv.test` (con Laragon)

## 👤 Acceso al Sistema

### Credenciales de Administrador
- **Celular:** 73010688
- **PIN:** 5421

## 📱 Módulos del Sistema

### 1. Login
- Autenticación con número de celular (8 dígitos)
- PIN de seguridad (4 dígitos)
- Opción "Recuérdame"
- Diseño moderno y profesional

### 2. Punto de Venta (POS)
- Interfaz dividida: Productos | Carrito
- Búsqueda rápida de productos
- Filtros por tipo (Platos, Refrescos, Porciones)
- Carrito con control de cantidades
- Procesamiento de ventas con cálculo automático

### 3. Usuarios
- CRUD completo de usuarios
- Tipos: Admin y Usuario
- Búsqueda por nombre o celular
- Validación de PIN de 4 dígitos

### 4. Productos
- CRUD completo con imágenes
- Precio, tipo y estado (activo/inactivo)
- Toggle rápido de estado
- Búsqueda y filtros

### 5. Turnos
- Gestión de turnos semanales
- Asignación de encargados
- Fechas de inicio (Lunes) y fin (Domingo)
- Relación con movimientos

### 6. Movimientos
- Registro de ingresos y egresos
- Cálculo automático de saldo
- Vinculados a turnos activos
- Historial completo

### 7. Ventas
- Historial de todas las ventas
- Detalles expandibles por venta
- Filtros por fecha y usuario
- Información de productos vendidos

## 🎨 Diseño

El sistema utiliza un template profesional con:
- **Color principal:** Púrpura (#7366ff)
- **Framework CSS:** Bootstrap 5 (via assets)
- **Iconos:** Font Awesome 6
- **Animaciones:** Transiciones suaves
- **Sidebar:** Colapsable con navegación intuitiva
- **Loader:** Pantalla de carga animada
- **Profile Sidebar:** Panel deslizante de perfil

## 🗂️ Estructura de Base de Datos

### Tablas Principales

1. **usuarios**
   - id, nombre, celular, pin, tipo, remember_token
   
2. **productos**
   - id, nombre, imagen, precio, tipo, estado
   
3. **turnos**
   - id, encargado_id, fecha_inicio, fecha_fin
   
4. **movimientos**
   - id, turno_id, detalle, ingreso, egreso, saldo
   
5. **ventas**
   - id, user_id, fecha_hora, total
   
6. **venta_items**
   - id, venta_id, producto_id, cantidad, precio, subtotal, detalle

## 📚 Tecnologías Utilizadas

- **Backend:** Laravel 13.0.0
- **Frontend:** Livewire 3.x (componentes reactivos)
- **Base de Datos:** MySQL
- **CSS:** Bootstrap 5 + Assets profesionales
- **JavaScript:** jQuery + Scripts del template
- **Iconos:** Font Awesome 6
- **Notificaciones:** SweetAlert2

## 🔐 Seguridad

- Autenticación con Laravel Auth
- Middleware de protección en rutas
- Hashing de PINs con bcrypt
- CSRF protection en formularios
- Regeneración de sesión en login
- Validación de datos en servidor

## 📝 Notas Importantes

1. **Registro de Usuarios:** Se realiza exclusivamente a través del módulo de Usuarios (CRUD). No hay registro público.

2. **Turnos Activos:** Para que las ventas se registren en movimientos, debe existir un turno activo (fecha actual entre inicio y fin).

3. **Imágenes de Productos:** Se almacenan en `storage/app/public/productos` y son accesibles via `/storage`.

4. **Colores del Template:** Modificar `assets/css/color-5.css` para cambiar el color principal del tema.

## 🐛 Troubleshooting

### Error: "Storage link not found"
```bash
php artisan storage:link
```

### Error de migración
```bash
php artisan migrate:fresh --seed
```

### Cache de configuración
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

## 👨‍💻 Desarrollado por

**DieguitoSoft.com**

---

## 🎯 Estado del Proyecto

✅ **100% COMPLETADO** - Listo para producción

- [x] Sistema de autenticación funcional
- [x] Todos los módulos implementados
- [x] Diseño profesional integrado
- [x] Base de datos configurada
- [x] Middleware de protección activo
- [x] Responsive design verificado
- [x] Usuario administrador creado

**Fecha de finalización:** 17 de marzo de 2026
