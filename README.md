## Tecnologías usadas

- Laravel 13 y PHP 8.5.
- MySQL 8.4.
- Bootstrap 5.3.
- DataTables 3.
- Vite 8 y Node.js 24.
- Docker y Docker Compose.

## Requisitos

- Docker Engine o Docker Desktop.
- Docker Compose v2.
- GNU Make.

## Instalación

Desde la raíz del repositorio:

```bash
make init
make artisan cmd="inegi:sync-states"
```

`make init` crea `.env`, construye los contenedores, instala dependencias en volúmenes Docker, genera `APP_KEY`, ejecuta las migraciones y compila los assets.

La aplicación queda disponible en <http://localhost:8000>.

## Desarrollo con recarga automática

Para levantar Laravel y Vite con hot reload:

```bash
make dev
```

Mientras el comando permanezca activo, los cambios en Blade, CSS y JavaScript se reflejan automáticamente en el navegador. `make up` continúa disponible cuando se prefieran los assets compilados de `public/build`.

## Rutas

| Ruta | Descripción |
| --- | --- |
| `/` | Redirige al listado de estados. |
| `/states` | Listado de entidades federativas. |
| `/states/{clave}/municipalities` | Municipios consultados en vivo para el estado seleccionado. |

Ejemplo: <http://localhost:8000/states/01/municipalities>.

## Comandos

| Comando | Uso |
| --- | --- |
| `make init` | Prepara el proyecto por primera vez: dependencias, clave, base de datos, migraciones y assets. |
| `make dev` | Levanta Laravel, MySQL y Vite con hot reload. Se mantiene activo en primer plano. |
| `make up` | Levanta Laravel y MySQL en segundo plano usando los assets compilados. |
| `make down` | Detiene y elimina los contenedores del proyecto. |
| `make artisan cmd="..."` | Ejecuta un comando Artisan dentro del contenedor de Laravel. |
| `make composer cmd="..."` | Ejecuta un comando Composer dentro del contenedor. |
| `make npm cmd="..."` | Ejecuta un comando npm dentro del contenedor de Node.js. |
| `make test` | Ejecuta la suite de pruebas automatizadas. |
| `make pint` | Comprueba el formato PHP con Laravel Pint sin modificar archivos. |
| `make build` | Genera los assets de producción con Vite. |
| `make scan` | Busca código de depuración, marcadores pendientes y secretos conocidos. |
| `make qa` | Ejecuta pruebas, formato, build y escaneo en una sola validación. |
| `make logs` | Muestra los últimos logs de Laravel, MySQL y Vite. |
| `make vercel-sync-states` | Ejecuta la sincronización remota en Vercel usando el token guardado en el llavero de macOS. |

Ejemplos:

```bash
make artisan cmd="migrate --force"
make artisan cmd="inegi:sync-states"
make composer cmd="audit"
make npm cmd="audit"
```

`make qa` ejecuta 22 pruebas con peticiones INEGI simuladas, Laravel Pint, la compilación de producción y un escaneo básico de código de depuración, marcadores de trabajo pendiente y secretos conocidos.

## Sincronización idempotente

El comando `inegi:sync-states` calcula el total de entidades a partir de la respuesta de INEGI, comprueba que coincida con `numReg`, valida que las claves sean únicas y usa `upsert` con `code` como identificador. Una nueva ejecución actualiza nombre, abreviatura, población total, población femenina, población masculina y viviendas habitadas sin duplicar filas. La escritura ocurre dentro de una transacción; una respuesta vacía o inválida conserva los datos existentes.

## Estructura

La solución mantiene una estructura MVC pequeña:

- `State` representa la tabla `estados` y usa la clave estatal para el route model binding.
- `InegiService` concentra el cliente HTTP, la validación y el mapeo de estados y municipios.
- `SyncStates` coordina la carga idempotente.
- `StateController` atiende el listado y la consulta municipal.

## Manejo de errores

El cliente INEGI utiliza tiempos límite y reintentos configurables. Los payloads se validan antes de usarse o escribirse. La interfaz nunca muestra cuerpos externos, URLs internas ni excepciones: un proveedor no disponible genera una página comprensible con estado HTTP `502`.

## Pruebas

La suite usa SQLite en memoria y bloquea solicitudes externas inesperadas. Cubre:

- mapeo y validación de estados y municipios;
- errores del proveedor;
- inserción, actualización e idempotencia;
- conservación de datos ante respuestas inválidas;
- formato y navegación de las vistas;
- estados inexistentes y páginas de error seguras.

La aceptación final también se verificó con una migración limpia y dos sincronizaciones reales sobre MySQL dentro de Docker.

## Despliegue en Railway

El repositorio incluye `Dockerfile.production` y `railway.json`. La imagen final contiene únicamente las dependencias de producción y los assets compilados; Railway ejecuta migraciones, sincroniza los estados y comprueba `/states` antes de activar el servicio.

1. Crear un proyecto y añadir un servicio MySQL.
2. Añadir el repositorio como servicio de aplicación.
3. Configurar estas variables en el servicio web:

```dotenv
APP_NAME=ENEGENCE
APP_ENV=production
APP_KEY=<resultado de: make artisan cmd="key:generate --show">
APP_DEBUG=false
APP_URL=https://${{RAILWAY_PUBLIC_DOMAIN}}
LOG_CHANNEL=stderr
DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}
INEGI_BASE_URL=https://gaia.inegi.org.mx/wscatgeo/v2/
INEGI_CONNECT_TIMEOUT=3
INEGI_TIMEOUT=10
INEGI_RETRIES=3
INEGI_RETRY_SLEEP_MS=250
SESSION_DRIVER=array
CACHE_STORE=file
```

4. Generar un dominio público desde la sección Networking.
5. Confirmar que `/states` y `/states/01/municipalities` respondan correctamente.

Los valores MySQL usan referencias entre servicios; no deben copiarse al repositorio. Railway detecta la configuración declarativa y utiliza `Dockerfile.production`.

## Demostración rápida

1. Ejecutar `make up` y abrir `/states`.
2. Ejecutar dos veces `make artisan cmd="inegi:sync-states"` y mostrar que permanecen 32 estados.
3. Ejecutar `make test`.
