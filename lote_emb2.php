<?php
require 'validaciones.php';
require 'dbinfo.php';
$nlotemba = $_GET['nlote_emba'];

if (is_number($nlotemba) && !is_text($nlotemba)){

	//conecto a db Flexar
	if (!($connectstring = odbc_connect($sql_db_flexar,$sql_usuarioflexar, $sql_pwdflexar))){
		echo 'Fallo conexion base de datos flexar';
		exit();
	}

	//consulta a dbms  . Selecciona nroserie, dia embalado segun el nro lote embalado
	$query = "SELECT serie AS Nro_Serie, LEFT(fecha,20) AS Dia_Embalado,  
		  (SELECT Lote FROM Impedancias impe WHERE emba.serie=impe.Serie) AS LoteProd
		  
		  FROM EMBALADO emba 
		  WHERE ID_Grupo=$nlotemba";
	
	//tomo resultados
	if (!($result   = odbc_do($connectstring, $query))){
		echo '<b>Fallo query tabla: embalado</b>';
		exit();
	 }

	/************* IMPRESION *****************/
	print("<center><br /><b>Celdas con mismo Lote de Embalado: ". $nlotemba  . "</b><br /><br />");
	if (!odbc_result_all($result, "BORDER=1 cellspacing=0 cellpadding=3 align=center")){
		echo '<center><b>Numero Lote de Embalado INCORRECTO</b></center>';
		exit();
	}

}else
	echo '<center><b>DATO de tipo NO VALIDO</b></center>';

?>
