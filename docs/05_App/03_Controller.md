```
├── wamp/
│   ├── www/
│   │   ├── pulque-master/
│   │   ├───────────────────app
│   │   ├───────────────────│──────nombreModulo
│   │   ├───────────────────│─────────────│──────controller
```
Los `Controladores` la capa intermedia que permite la comunicaci&oacute;n,
entre cliente,servidor y base de datos, en estos se deben de ejecutar
validaciones y reglas de negocio, ya sea dentro de ellos mismos o
bien inyectando otras clases cuya responsalidad sea la validaci&oacute;n
y el aseguramiento de la ejecuci&oacute;n de reglas de negocio.
Todos los controladores deben de extender de `BaseController` y 
van dentro de la carpeta

 `app/nombremodulo/controller/` ejemplo.


        class NombreTablaController extends BaseController(){
        
        }
  
Obteniendo parametros de `POST`


      $this->post('nombre_parametro');
  

Obteniendo parametros de `GET`



      $this->get('nombre_parametro');
  
Pasando parametros a la vista, en este caso cuando se desea 
hacer un render



      $this->parametros['nombre_parametro']='Hola Mundo';
      $this->render('vista.html');
     
     
Para escribir mensajes JSON se utiliza la clase `MensajeRespuesta`
 
 