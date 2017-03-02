A continuacion, describo los pasos para usar el datatable.

Primero es necesario agregar las librerías al html

     <link href="[[base]]/ui/js/base/datatables-1.10.12/css/dataTables-custom.css" rel="stylesheet" type="text/css">
     
     <script src="[[base]]/ui/js/base/datatables-1.10.12/js/datatables.min.js"></script>
     <script src="[[base]]/ui/js/base/componentes/dataTableComponente.js"></script>
        
Segundo Paso, dentro del html es necesario definir las columnas

     <table>
     <tr>
        <td>Id</td>
        <td>Nombre</td>
     </tr>
     </table>

Tercer Paso, dentro de las secci&oacute;n de scripts Definir las columnas
y mandar llamar al componente
    
    var columnas= [{
               "data":"id_test_continente",
               "defaultContent" : ""
           },{
               "data":"pais",
               "defaultContent" : ""
           }];
   
           var dt= dataTableComponente("#tablaContinente","[[base]]/test/buscar",columnas);
           
           
Cuarto paso definir la ruta en el index.php

    Accesso::permitir('POST /test/buscar','TestController->buscar', array(RolEnum::ADMINISTRADOR,RolEnum::USUARIO));   
      
Quinto paso dentro del controlador agregar el m&eacute;todo de busqueda

        public function buscar(){
            $draw=F3::get('POST.draw');
            $start=F3::get('POST.start');
            $length=F3::get('POST.length');
            // Los filtros se deben de usar para busquedas
            //$filtros=F3::get('POST.filtros');
             $total=$this->testRepository->buscarTotal();
            if($total==0){
                MensajeRespuesta::datosJSON($draw,$total);
            }
            $paises= $this->testRepository->buscar($start,$length);
            MensajeRespuesta::datosJSON($draw,$total,$paises);
        }

Sexto paso, en los repositorios agregar los métodos que realizan la consulta

    public function buscarTotal(){
        $this->sql="SELECT COUNT(id_test_continente) FROM test_pais ";
        return $this->escalar();
    }

    public function buscar($start,$length){
        $this->filtros["start"]=intval($start);
        $this->filtros["length"]=intval($length);
        $this->sql="SELECT id_test_continente,id_test_pais,pais FROM test_pais ";
        $this->sql= $this->sql."LIMIT :start,:length";
        return $this->resultado();
    }

  

    