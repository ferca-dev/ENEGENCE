# ENEGENCE

## Iniciar el proyecto en desarrollo

Requisitos:

- PHP 8.3 o superior.
- Composer 2.
- MySQL 8.
- Node.js 24 y npm.

Instala las dependencias y crea el archivo de entorno:

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Crea una base de datos local:

```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS enegence CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Configura estas variables en `.env` con tus credenciales de MySQL:

```dotenv
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=enegence
DB_USERNAME=root
DB_PASSWORD=
```

Ejecuta las migraciones y carga los estados desde INEGI:

```bash
php artisan migrate
php artisan inegi:sync-states
```

Inicia Laravel:

```bash
php artisan serve
```

En otra terminal, inicia Vite con hot reload:

```bash
npm run dev
```

Abre <http://localhost:8000>. En los siguientes inicios solo necesitas tener MySQL activo y ejecutar los dos últimos comandos.
