# módulo Satisfacción Clientes 

Este modulo tendra importación de excel, y se guardaran los datos en la base de datos. como los otros modulos. importacion automática y manual.

pero tiene un concepto diferente, que lo abordaremos mas adelante.

Primero las credenciales y rutas 

- **Email:** `satisfaccion_clientes@gadium.com`
- **Password:** `password`
- **Rol:** Gestor de Satisfacción Clientes
- **Ruta:** `/satisfaccion_clientes/dashboard`

esto significa que en el sidebar debe aparecer 
- el link de importacion automática /satisfaccion_clientes/importacion_automatica y 
- el de importacion manual /satisfaccion_clientes/
- el de historial de importaciones /satisfaccion_clientes/historial_importacion
- el de dashboard /satisfaccion_clientes/dashboard 
- el de perfil /satisfaccion_clientes/profile

primero muestro la estructura de el header y la primer fila del excel

Fecha	Cliente	Proyecto:	¿Qué grado de satisfacción tiene sobre la obra/producto/servicio terminado?	¿Cómo calificaría el servicio en cuanto al desempeño técnico?	Durante la ejecución del proyecto, ¿tuvo respuestas a todas sus necesidades?	¿Cómo calificaría el servicio ofrecido en cuanto al plazo de ejecución?


5/5/2025	Saint Gobain	3360-Mejoras en línea de producción de masilla	5	5	5	5

el modelo esta en D:\Gadium\doc\excel pruebas\Satisfacción Clientes\Satisfacción Clientes.xlsx

cuando se carga eso, habra unas reglas internas 

Tabla 2

Valor esperado	Valor esperado	Valor esperado	Valor esperado
55	55	55	55
Valor obtenido	Valor obtenido	Valor obtenido	Valor obtenido
52	49	51	49

para que esto nos permita hacer esto

Tabla 3

¿Qué grado de satisfacción tiene sobre la obra/producto/servicio terminado?	¿Cómo calificaría el servicio en cuanto al desempeño técnico?	Durante la ejecución del proyecto, ¿tuvo respuestas a todas sus necesidades?	¿Cómo calificaría el servicio ofrecido en cuanto al plazo de ejecución?

95%	89%	93%	89%


Es decir que cuando se cargan esos datos, va a la DB, luego trae esos datos y pasa por los campos de tabla 1 antes mencionados, para analizar los valores esperados, y luegos los obtenidos se responde a las preguntas de tabla 3

las tablas también 2 y 3 se guardan a la base de datos, ya que se van a consultar para hacer graficos y estadisticas. en grafana por api en un futuro

el modelo para que lo veas está en D:\Gadium\doc\excel pruebas\Satisfacción Clientes\Satisfacción Clientes - tablas 2 y 3.xlsx



para aclara la importación automática debería ser con el excel de la tabla 1, D:\Gadium\doc\excel pruebas\Satisfacción Clientes\Satisfacción Clientes.xlsx

todo eso documentarlo en D:\Gadium\doc\01_ARQUITECTURA\módulo_importacion.md