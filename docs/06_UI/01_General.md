Dentro de esta capa se agregan modulos y repositorios

Estructura General de esta Capa
```
├── wamp/
│   ├── www/
│   │   ├── pulque-master/
│   │   ├───────────────────ui
│   │   ├───────────────────│──────css
│   │   ├───────────────────│──────js
│   │   ├───────────────────│──────images
│   │   ├───────────────────│──────modulos
│   │   ├───────────────────│──────│─────[nombreModulo]
```

Como regla n&uacute;mero uno, siempre debe existir una carpeta llamada 
html dentro del modulo `ui/nombreModulo/html/` se puede tener adentros
submodulos pero siempre debe existir dentro la carpeta html
`ui/nombreModulo/submodulo/html/`.

Los archivos `html` no pueden tener nombre repetidos, o dicho de otra forma
no pueden existir por ejemplo dos archivos `index.html`.

Para el manejo de plantillas se usa `twig` por lo que todos los comandos
de twig estan permitidos la única diferencia es que aquí se usan 
los `CORCHETES [[ ]]` en lugar de las `LLAVES {{ }}`.