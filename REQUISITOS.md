# Prueba técnica

Laravel + MySQL + Bootstrap

## Tecnologías por utilizar

- Laravel 6 o superior
- Bootstrap
- MySQL

## Instrucciones

### 1. Estados

Obtener el listado de las 32 entidades federativas utilizando el Servicio Web del Catálogo Único de Claves Geoestadísticas del INEGI.

Endpoint:

<https://gaia.inegi.org.mx/wscatgeo/v2/mgee/>

Guardar los estados en una tabla llamada estados.

La información almacenada deberá incluir, como mínimo:

- Clave del estado, utilizando el campo cve_ent.
- Nombre del estado, utilizando el campo nomgeo.
- Población total, utilizando el campo pob_total.

Mostrar los estados en un listado, idealmente utilizando DataTables, con las siguientes columnas:

- Clave.
- Estado.
- Población total.

El listado deberá contar con:

- Paginación.
- Búsqueda.
- Ordenamiento.

La población total deberá mostrarse en un formato legible, incluyendo separadores de miles.

Ejemplo:

```text
798447 deberá mostrarse como 798,447.
```

Evitar registros duplicados si se vuelve a ejecutar la carga; es decir, el proceso deberá ser idempotente.

### 2. Municipios por estado

Al dar clic en un estado del listado, consultar sus municipios utilizando el Servicio Web del Catálogo Único de Claves Geoestadísticas del INEGI.

Endpoint:

<https://gaia.inegi.org.mx/wscatgeo/v2/mgem/{CLAVE_ESTADO}>

La clave del estado corresponde al campo cve_ent obtenido en la consulta de entidades federativas.

Ejemplo para Aguascalientes, cuya clave es 01:

<https://gaia.inegi.org.mx/wscatgeo/v2/mgem/01>

Mostrar todos los municipios correspondientes al estado seleccionado.

---
