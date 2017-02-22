En este sección se describe, como deber&iacute;a de ser el nombrado de variables, objetos y archivos que componen el código.
Se usar&aacute;n dos tipos de notaciones snake y Camel

* SNAKE: <b>este_es_un_ejemplo_de_snake</b>
* CAME: <b>EsteEsUnEjemploDeNotacionCamel</b>

Variables
-------------------------

* Variables que representan la instacia de un objeto con `Camel Case`

<pre>
$objetoNuevo=new ObjetoNuevo();
</pre>
* Variables que representan <b>un campo o una tabla de la base de datos</b> con `snake case`

<pre>
$usuario->nombre='Neo';
$apellido_paterno='Fernandez';
</pre>

Constantes
-------------------------------
Las constanstes deben ser nombradas con `CAMEL CASE`

        const CAPTURISTA_HIDALGO="Capturista del Estado de Hidalgo";
Funciones
-------------------------------
Las funciones deben ser nombradas con `snake case`;

        public function buscar_usuario(){
        //Aqui va todo el codigo
        }
Clases, Interfaces y Enumeraciones
------------------------

Los nombres de las clases deben ser nombrados con `Camel Case`, deben ser <b>igual</b> al nombre del archivo donde se encuentren,ejemplo.

        class BaseController{
        //el archivo donde se encuentra se debe de llamar BaseController.php
        }