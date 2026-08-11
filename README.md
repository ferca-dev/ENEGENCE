# Catálogo geográfico de México

Prueba técnica desarrollada con Laravel, MySQL y Bootstrap. Obtiene las 32 entidades federativas desde INEGI, las almacena de forma idempotente y consulta los municipios en vivo al seleccionar un estado.

El desarrollo y las pruebas se ejecutan con Docker Compose. No es necesario instalar PHP, Composer, Node.js ni MySQL en el equipo anfitrión.

## Funcionalidad

- Sincronización de los 32 estados desde `mgee/`, incluyendo abreviatura, población por sexo y viviendas habitadas.
- Persistencia en la tabla `estados` sin registros duplicados.
- Listado DataTables con paginación, búsqueda y ordenamiento.
- Poblaciones formateadas con separadores de miles.
- Consulta en vivo de municipios mediante `mgem/{CLAVE_ESTADO}`.
- Respuestas `404` para estados inexistentes y `502` seguro ante fallos de INEGI.
- Interfaz responsive construida con Bootstrap 5.

## Tecnologías

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

```bash
# Levantar o detener la aplicación
make up
make dev
make down

# Migraciones y sincronización
make artisan cmd="migrate --force"
make artisan cmd="inegi:sync-states"

# Pruebas y control de calidad
make test
make pint
make build
make qa

# Otros comandos dentro de los contenedores
make artisan cmd="about"
make composer cmd="audit"
make npm cmd="audit"
make logs
```

`make qa` ejecuta 15 pruebas con peticiones INEGI simuladas, Laravel Pint, la compilación de producción y un escaneo básico de código de depuración, marcadores de trabajo pendiente y secretos conocidos.

## Sincronización idempotente

El comando `inegi:sync-states` calcula el total de entidades a partir de la respuesta de INEGI, comprueba que coincida con `numReg`, valida que las claves sean únicas y usa `upsert` con `code` como identificador. Una nueva ejecución actualiza nombre, abreviatura, población total, población femenina, población masculina y viviendas habitadas sin duplicar filas. La escritura ocurre dentro de una transacción; una respuesta vacía o inválida conserva los datos existentes.

## Estructura

La solución mantiene una estructura MVC pequeña:

- `State` representa la tabla `estados` y usa la clave estatal para el route model binding.
- `InegiService` concentra el cliente HTTP, la validación y el mapeo de estados y municipios.
- `SyncStates` coordina la carga idempotente.
- `StateController` atiende el listado y la consulta municipal.
- Dos vistas Blade presentan estados y municipios.

Los municipios no se persisten porque el ejercicio solicita consultarlos al seleccionar un estado. Tampoco se añadieron autenticación, caché, AJAX ni capas de dominio que no aportan al alcance.

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

## Demostración de cinco minutos

1. Ejecutar `make up` y abrir `/states`.
2. Ejecutar dos veces `make artisan cmd="inegi:sync-states"` y mostrar que permanecen 32 estados.
3. Demostrar búsqueda, ordenamiento y paginación.
4. Abrir Aguascalientes y mostrar sus municipios.
5. Ejecutar `make test`.
6. Explicar brevemente `State`, `InegiService`, `SyncStates` y `StateController`.
