## SPRINT REFACTORIZACIÓN

## REFACTORIZAR IMPORT

primero borrar la base de datos y ejecutar el seeder para las credenciales


El objetivo es refactorizar el import, la logica esta muy bien, solo que me pidieron que en vez de csv, tiene que ser excel, cualquiera de sus variantes de excel.

## VENTAS

ahora el arhivo a importar ventas se va modificar las cantidad de columnas. el arhivo es de Tango gestión tiene su cualidades, las columnas son las siguientes

COD_CLI	RAZON_SOCI	N_REMITO	T_COMP	N_COMP	FECHA_EMI	COND_VTA	PORC_DESC	COTIZ	MONEDA	TOTAL_COMP	COD_TRANSP	NOM_TRANSP	COD_ARTICU	DESCRIPCIO	COD_DEP	UM	CANTIDAD	PRECIO	TOT_S_IMP	N_COMP_REM	CANT_REM	FECHA_REM

las columnas traen esto el siguiente caracter antes del nombre ' , no tengo ni idea, pero si se que para los valores es para identificar si es texto o numerico, te muestro. 

COD_CLI	RAZON_SOCI	N_REMITO	T_COMP	N_COMP	FECHA_EMI	COND_VTA	PORC_DESC	COTIZ	MONEDA	TOTAL_COMP	COD_TRANSP	NOM_TRANSP	COD_ARTICU	DESCRIPCIO	COD_DEP	UM	CANTIDAD	PRECIO	TOT_S_IMP	N_COMP_REM	CANT_REM	FECHA_REM
000001	TRIELEC S A		FAC	A0000200001198	22/05/2023	1	0,00	1,00	CTE	860056,89	01	PROPIO	VTA-V385-MAT	OC 39452 MATERIALES	01	UN	78,37	9069,68	710790,82		0,00	

no se porque no sale al copiar y pegar, revisa en el archivo D:\Gadium\doc\excel pruebas\ventas.xlsx. y revisar como implementarlo, la idea es que suban ese excel con esos datos.

## PRESUPUESTO

Para presupuesto tienen estas columnas

Centro de Costo	Empresa	Nombre Proyecto	Fecha	Orden de Pedido	Fecha de OC	Fecha estimada de culminación	Estado del proyecto en días	Fecha de culminación real	Monto	U$D	Estado	Enviado a facturar	Nº de Factura	% Facturación	Saldo [$]	Horas ponderadas


Centro de Costo	Empresa	Nombre Proyecto	Fecha	Orden de Pedido	Fecha de OC	Fecha estimada de culminación	Estado del proyecto en días	Fecha de culminación real	Monto	U$D	Estado	Enviado a facturar	Nº de Factura	% Facturación	Saldo [$]	Horas ponderadas
V385	TRIELEC S.A.	Módulos Flex I/O	27/3/2023	OC39452					3896,78	U$D	Aprobado		1198 - 1345	100%	0	150

debemos aplicar lo mismo. 

###

la ruta http://127.0.0.1:8000/test-import la vamos a refuncionalizar

la ideas es http://127.0.0.1:8000/historial_ventas y http://127.0.0.1:8000/historial_presupuesto.



