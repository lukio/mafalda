<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title> ABM de Modelos - FLEXAR SRL </TITLE>
<meta name="Generator" content="gvim">
<meta name="Author" content="">
<meta name="Keywords" content="">
<meta name="Description" content="">
<link rel="icon" href="favicon.ico" type="image/x-icon"> 
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php
/**
*	Altas, Bajas, Modificaciones (ABM) de Modelos - Flexar SRL - Enero 2006
*	1) Al abrir aplicacion chequear contra la db de Tango si se ha agregado un nuevo
*		modelo. De ser asi que de la posibilidad de dar nuevas altas. Sino AVISO de que NO hay nuevos Modelos 
*	2) Si hay inconsistencias entonces aparece el front-end con nuevo modelo a insertar  los inputs correspondientes
*	IMPORTANTE: Tablas a Utilizar:
*									- Modelos
*									- ModelosMarcaEmb
*									- Marcas
*				Altas: Si se omiten entrada de datos, se toman valores por default. 
**/

/**
* TipoABM :
*			Alta = 1
*			Baja = 2 
*			Modificaciones = 3 
**/



echo '<div style="position:absolute; left:40; top:140;"><a href="f_abm3.php?tipoabm=1" target=resultados alt="Altas">Altas</a></div>';
echo '<div style="position:absolute; left:40; top:170;"><a href="f_abm3.php?tipoabm=2" target=resultados alt="Bajas">Bajas</a></div>';
echo '<div style="position:absolute; left:40; top:200;"><a href="f_abm3.php?tipoabm=3" target=resultados alt="Modificaciones">Modificaciones</a></div>';


echo '<div style="position:absolute; left:30; top:30;"><img src="logo-abm-flexar2.jpg" width="70" height="90" border="0" alt="Logo ABM Modelos"></div>';

print '<div style="position:absolute; left:150; top:60;"><iframe  style="width: 628px; height: 550px" name=resultados border=0 frameborder=0 ></iframe></div>';
?>

</body>
</html>
