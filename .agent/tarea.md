# módulo Satisfacción Operario

Este modulo tendra importación de excel, y se guardaran los datos en la base de datos. como los otros modulos. importacion automática y manual.

y tiene le mismo concepto que el de satifacción de clientes, aunque cambia un poco la dinamica de las tablas pero el fin es el mismo

Primero las credenciales y rutas 


- **Email:** `satisfaccion_personal@gadium.com`
- **Password:** `password`
- **Rol:** Gestor de Satisfacción Personal
- **Ruta:** `/satisfaccion_personal/dashboard` 

esto significa que en el sidebar debe aparecer 
- el link de importacion automática /satisfaccion_personal/importacion_automatica y 
- el de importacion manual /satisfaccion_personal/
- el de historial de importaciones /satisfaccion_personal/historial_importacion
- el de dashboard /satisfaccion_personal/dashboard 
- el de perfil /satisfaccion_personal/profile

primero muestro la estructura de el header y la primer fila del excel


son 4 grupos de preguntas grandes, dentro de ellas sus preguntas para marcar X si corresponde 

1. ¿Cómo se siente con el trato que recibe de su jefe/supervisor directo?
    Me siento mal
    Ni mal ni biem
    Me siento bien    
2. ¿Cómo se siente con el trato que recibe de sus compañeros?
    Me siento mal
    Ni mal ni biem
    Me siento bien
3. En general, no solo su área¿Cómo considera el clima laboral de la empresa?
    Mal clima
    Clima normal
    Buen clima
4. En general ¿Qué grado de comodidad siente trabajando en la empresa?
    Incómodo
    Normal
    Cómodo


el modelo esta en D:\Gadium\doc\excel pruebas\Satifacción Personal\Satisfacción Operarios.xlsx

cuando se carga eso, habra unas reglas resultados  

Valor obtenido	Valor obtenido	Valor obtenido	Valor obtenido	Valor obtenido	Valor obtenido	Valor obtenido	Valor obtenido
4	16	2	11	7	3	9	8
20%	80%	10%	55%	35%	15%	45%	40%




Es decir que cuando se cargan esos datos, va a la DB, luego trae esos datos y pasa por los campos de tabla 1 antes mencionados, para analizar los valores esperados, y luegos los obtenidos se responde a las preguntas de tabla 2

las tablas también se guardan a la base de datos, ya que se van a consultar para hacer graficos y estadisticas. en grafana por api en un futuro

el modelo para que lo veas está en D:\Gadium\doc\excel pruebas\Satifacción Personal\Satisfacción Operarios - resultados.xlsx



para aclara la importación automática debería ser con el excel de la tabla 1, D:\Gadium\doc\excel 

en el dashboard mostrar como el de satisfacción clientes, pero usar la grafica pastel para cada preguntas generales

No olvidar tambien hacer el historial como si fuera un log

todo eso documentarlo en D:\Gadium\doc\01_ARQUITECTURA\módulo_importacion.md