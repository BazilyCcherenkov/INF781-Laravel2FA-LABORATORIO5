# INF781-Laravel2FA - Autenticación de Dos Factores con TOTP

## Descripción

Proyecto de laboratorio para la materia INF_781 que implementa autenticación de dos factores (2FA) basada en TOTP (Time-based One-Time Password) usando Laravel 13, PostgreSQL y la librería google2fa-laravel.

La aplicación combina factor de conocimiento (contraseña) con factor de posesión (código TOTP de 6 dígitos que cambia cada 30 segundos) y códigos de respaldo para recuperación.

## Características

- Autenticación de dos factores con TOTP
- Códigos QR para configuración en Google Authenticator
- 8 códigos de respaldo hasheados con bcrypt
- Base de datos PostgreSQL
- Interfaz con Laravel Breeze (Blade + Tailwind)
- Middleware de protección de rutas

## Requisitos

- PHP 8.3+
- Composer 2.x
- Node.js 20.x LTS
- PostgreSQL 15+
- Extensiones PHP: pdo_pgsql, mbstring, xml, gd

## Instalación

```bash
# Clonar el repositorio
git clone https://github.com/BazilyCcherenkov/INF781-Laravel2FA-LABORATORIO5.git
cd INF781-Laravel2FA

# Instalar dependencias PHP
composer install

# Instalar dependencias Node.js
npm install

# Compilar assets
npm run build

# Configurar variables de entorno
cp .env.example .env
# Editar .env con configuración de PostgreSQL

# Generar clave de aplicación
php artisan key:generate

# Ejecutar migraciones
php artisan migrate

# Iniciar servidor
php artisan serve
```

## Configuración de Base de Datos PostgreSQL

```sql
-- Crear usuario y base de datos
CREATE USER laravel_2fa_user WITH PASSWORD 'secret2fa';
CREATE DATABASE laravel_2fa OWNER laravel_2fa_user;
GRANT ALL PRIVILEGES ON DATABASE laravel_2fa TO laravel_2fa_user;
```

## Variables de Entorno (.env)

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=laravel_2fa
DB_USERNAME=laravel_2fa_user
DB_PASSWORD=secret2fa
SESSION_DRIVER=database
```

## Uso

1. Registra un nuevo usuario en `/register`
2. Ve al Dashboard y haz clic en "Configurar Autenticación en Dos Factores"
3. Escanea el código QR con Google Authenticator (o similar)
4. Ingresa el código de 6 dígitos para activar 2FA
5. **Importante**: Guarda los códigos de respaldo que se muestran
6. La próxima vez que inicies sesión, ingresa el código TOTP o un código de respaldo

## Estructura del Proyecto

```
INF781-Laravel2FA/
├── app/Http/Controllers/
│   ├── TwoFactorController.php       # Configuración 2FA
│   └── TwoFactorVerifyController.php # Verificación OTP
├── app/Http/Middleware/
│   └── TwoFactorMiddleware.php       # Protección de rutas
├── app/Models/
│   └── User.php                      # Modelo con campos 2FA
├── database/migrations/
│   ├── 2026_05_18_200619_add_two_factor_to_users_table.php
│   └── 2026_05_18_210653_add_backup_codes_to_users_table.php
├── resources/views/
│   └── two-factor/
│       ├── setup.blade.php           # Configuración 2FA
│       └── verify.blade.php          # Verificación OTP
└── config/
    └── google2fa.php                 # Configuración TOTP
```

## Comandos Útiles

```bash
# Ver rutas
php artisan route:list

# Limpiar caches
php artisan view:clear
php artisan config:clear

# Verificar versión Laravel
php artisan --version
```

## Licencia

MIT License