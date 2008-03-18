<?php
require 'validaciones.php';
require 'dbinfo.php';
$nlote_pro = $_GET['nlotepro'];

//$modelo = trim($modelo);
//$modelo = $_GET['modelo'];
//
if (is_number($nlote_pro) && !is_text($nlote_pro) ){

	//conecto a DB flexar
	if (!($connectstring = odbc_connect($sql_db_flexar,$sql_usuarioflexar, $sql_pwdflexar))){
		echo 'Fallo conexion base de datos flexar';
		exit();
	}

	/**
	 * Se va a agregar dos querys. Uno para el header y el otro para el resto !
	 * header : + merma + modelo + fecha
	 */

	$query_header = "SELECT Lotes.Modelo AS ModeloLote, Lotes.Merma AS MermaLote, LEFT(Lotes.Fecha, 11) AS FechaLote
		FROM Lotes 
		WHERE Lotes.Lote=$nlote_pro";

	$query = "SELECT Impedancias.Serie AS NroSerie, Impedancias.ImpSG AS IMPSG, Impedancias.ImpRB AS IMPRB, Impedancias.ImpRs AS IMPRS 
		FROM Impedancias
		WHERE Impedancias.Lote=$nlote_pro";

	//header modelo = tolerancias por modelo :)

	 if(!($result = odbc_do($connectstring, $query))){
  		echo 'Fallo query tabla: Impedancias';
	  	exit();
	}
  
	 if(!($result_header = odbc_do($connectstring, $query_header))){
		echo 'Fallo odbc_do: Header lote!';
		exit();
	}
	$query_solo_el_modelo = "SELECT Lotes.Modelo FROM Lotes WHERE Lotes.Lote=$nlote_pro";
	 if(!($result_solo_modelo = odbc_do($connectstring, $query_solo_el_modelo))){
		echo 'Fallo odbc_do: solo el modelo!';
		exit();
	}

	$modelo = odbc_result($result_solo_modelo, 1);
	$query_modelo = "SELECT Modelos.[Tol ImpEnt], Modelos.TolImpSal, Modelos.TolCero, Modelos.TolSens
		FROM Modelos
		WHERE Modelos.Modelo='$modelo'";

	if(!($result_modelo = odbc_do($connectstring, $query_modelo))){
		echo 'Fallo odbc_do: Header Modelo!';
		exit();
	}

	echo '<div style="position:absolute; left:100; top:35;">';
	echo'<center><b>Tabla Lotes Produccion</b>';
	 if(!(odbc_result_all($result_header, "border=1"))){
		echo '<center><b>Numero Lote de Produccion INCORRECTO: Header Lote</b></center>';
		exit();
	}
	echo '</div>';

	echo '<div style="position:absolute; left:400; top:35;">';
	echo'<b>Tolerancias del Modelo: '.$modelo.'</b>';

	 if(!(odbc_result_all($result_modelo, "border=1"))){
		echo '<center><b>Numero Lote de Produccion INCORRECTO: Header Modelo</b></center>';
		exit();
	}
	echo '</div>';
	echo '<div style="position:absolute; left:250; top:150;">';
	echo'<b>Tabla Impedancias por nro de serie</b>';

	if(!(odbc_result_all($result, "border=1"))){
		echo '<center><b>Numero Lote de Produccion INCORRECTO: result Impedancias</b></center>';
		exit();
	}
	echo '</div>';
}else
	echo '<center><b>DATO de tipo NO VALIDO</b></center>';
?>
