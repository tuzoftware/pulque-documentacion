```
├── wamp/
│   ├── www/
│   │   ├── pulque-master/
│   │   ├───────────────────app
│   │   ├───────────────────│──────repositories
```
Los `Repositorios` representan el acceso las operaciones hacia,
una tabla, opereaciones como inserciones, actualizaciones,
eliminaciones y busquedas sobre una tabla, todos los repositorios
deben de extender de `BaseRepository` y van dentro de la carpeta
 `repositories` ejemplo.


        class NombreTablaRepository extends BaseRepository(){
        
        }
  
  Inyectando los repositorios a los controladores
  
  

        class nombreTablaController extends BaseController(){
         
            private $nombreTablaRepository;
            
             function __construct() {
                 parent::__construct();
        	    $this->nombreTablaRepository=new NombreTablaRepository();
             }
        }
   
   Obteniendo un objeto que representa la tabla
   
     

        $this->nombreTablaRepository->obtenerInstancia('nombre_tabla');
        
    
   Obteniendo un objeto que representa la tabla por medio de su id
    
     

        $this->nombreTablaRepository->obtenerInstanciaIdValor('nombre_tabla','id_tabla',5);
        
 Eliminar un registro de la tabla por medio de su id
     
     

        $this->nombreTablaRepository->eliminarInstanciaIdValor('nombre_tabla','id_tabla',5);
     
 
   