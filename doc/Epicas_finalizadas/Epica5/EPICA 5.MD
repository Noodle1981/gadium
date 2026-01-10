ÉPICA 05: Motor de Inteligencia de Negocios (Algoritmos Estratégicos)
Justificación Estratégica:
Esta épica implementa la "lógica de negocio profunda". Se centra en el algoritmo de Pareto para la diversificación de ventas. Como se extrajo de Objetivos.csv, la empresa necesita saber si depende peligrosamente de pocos clientes.
HU-09: Implementación del Algoritmo de Pareto (Diversificación de Ventas)
Prioridad: Crítica
Estimación: 13 Puntos de Historia
Dependencias: HU-04 (Clientes Normalizados)
Narrativa de Usuario:
Como Gerente General,
Quiero visualizar en tiempo real el indicador de concentración de ventas (Regla 80/20),
Para tomar decisiones estratégicas sobre la expansión de la cartera de clientes y mitigar el riesgo financiero de dependencia excesiva.
Contexto Técnico:
El algoritmo debe ser preciso. 1 detalla los pasos: Ranking, Corte del 20%, Suma Acumulada.
Criterios de Aceptación (Lógica Matemática):
Normalización Monetaria:
Antes de calcular, el sistema debe convertir todas las ventas en USD a ARS (o viceversa) usando la tasa de cambio de la fecha de la factura. No se pueden sumar peras con manzanas.
Ejecución del Algoritmo:
Calcular VentaTotal del periodo.
Ordenar clientes (normalizados) de mayor a menor venta.
Determinar el número de clientes que constituyen el 20% superior (TopN).
Sumar las ventas de esos TopN clientes.
Calcular el porcentaje que representan sobre la VentaTotal.
Visualización Semafórica:
Si el % resultante > 80%: Mostrar indicador ROJO (Riesgo Alto).
Si el % resultante <= 80%: Mostrar indicador VERDE (Objetivo Cumplido).
