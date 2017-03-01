En esta secci&oacute;n se muestra la vista general de la arquitectura

<img src="../img/vista_general.png" />

Capas
-------------------------------
La capa uno `lib` es el MicroFramework Fat Free Framework, el cual en caso de alguna
actualizaci&oacute;n puede ser descargado del sitio http://fatfreeframework.com,
si requiere una actualizaci&oacute;n solo hay que substituir la carpeta lib.

La capa dos `core` es el caparaz&oacute;n le da funcionalidad extra mediante dos subcapetas,
para extensi&oacute;n o para librerias nuevas se tiene la carpeta `core/lib` y en `base`
se tiene un conjunto de clases que son utilizadas por el framework, para
el manejo de seguridad, errores, peticiones y flujos.

La capa tres `app` dentro de esta capa, van todos los m&oacute;dulos del
aplicativo.

La capa cuatro `ui` dentro de esta capa, van los archivos de vista del sistema,
archivos html, css, librer&iacute;as javascript.


Estructura de la arquitectura:
```
├── wamp/
│   ├── www/
│   │   ├── pulque-master/
│   │   ├───────────────────app
│   │   ├───────────────────core
│   │   ├───────────────────lib
│   │   ├───────────────────ui
│   │   ├───────────────────index.php
│   │   ├───────────────────config.ini
│   │   ├───────────────────datasource.ini

```
Rutas
-------------------------------
Las rutas se configuran en el archivo index.php

Archivos de Configuraci&oacute;n
-------------------------------
Se cuentan con dos archivos para las configuraciones generales
del sistema, las cuales son descritas en la secci&oacute;n Configuraci&oacute;n

`config.ini` : Configuraci&oacute;n General del Sistema
 
 `datasource.ini`: Configura los accesos a la base de datos 