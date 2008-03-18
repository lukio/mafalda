<?php
require 'validaciones.php';
require 'dbinfo.php';
//session_start();
//
//	if (!array_key_exists('username', $_SESSION)) {
//		print 'session ha sido finalizada';
//		//print '<a href="login.html">Pagina Login</a>';
//		}else {
//
$nomecanizado = $_GET['nomecanizado'];
//$nomecanizado=2504;
if (is_number($nomecanizado) && !is_text($nomecanizado)){
//conecto a base Tango
if (!($id_db_tango = odbc_connect($db_tango,$usuariotango, $pwdtango))){
	echo 'Fallo conexion base de datos dsn_tango';
	exit();
}

$nomecanizado = '00010000' . $nomecanizado; 
/* le doy el formato que tiene el Tango !
   consulta a dbms  tango !!! Me traigo la descripcion.
   corregirlo el CPA41.DESCRIP para agarrar los ultimos numeros,
   porque como esta ahora si se agranda el numero yo estoy agarrando solamente los ULTIMOS 6
*/

$query= "SELECT CPA41.DESCRIP AS Num_Lote_Meca, FLOOR(CPA36.CAN_RECIBI) AS Cant_Recib, 
	 FLOOR(CPA36.CAN_PENDIE) AS Cant_Pend, FLOOR(CPA36.CAN_PEDIDA) AS Cant_Pedida, 
	 CPA36.COD_ARTICU AS Cod_Articulo, LEFT(CPA35.FEC_EMISIO,11) AS Fecha_Emision 
	 
	 FROM CPA41 RIGHT OUTER JOIN CPA36 ON CPA41.N_ORDEN_CO = CPA36.N_ORDEN_CO LEFT OUTER JOIN CPA35 ON CPA36.N_ORDEN_CO = CPA35.N_ORDEN_CO 
	 
	 WHERE  (CPA41.N_ORDEN_CO = $nomecanizado)";


//tomo resultados de ensayos 
if(!($result = odbc_do($id_db_tango, $query))){
	echo 'Fallo query a TANGO';
	exit();
}
				

echo '<div style="position:absolute; left:100; top:0;">';
echo '<center><b>Orden de Mecanizado - Materia Prima: '.$_GET['nomecanizado'].'</b><br><br>';
if (!$descrip_cpa41 = odbc_result_all($result, "border=1 cellspacing=0 cellpadding=1")){
	echo 'Fallo Impresion Orden Mecanizado - Materia Prima';
	exit();
}
echo '</div>';

//segundo query, solamente quiero el Numero de LOTE DE MECANIZADO:)
$query = "SELECT DESCRIP FROM CPA41 WHERE N_ORDEN_CO=$nomecanizado";
if(!($result = odbc_do($id_db_tango, $query))){
	echo 'Fallo query a tabla cpa41';
	exit();
}
if (!$descrip_cpa41 = odbc_result($result, 1)){
	echo 'Fallo Impresion Orden Mecanizado - Materia Prima';
	exit();
}

//echo $descrip_cpa41;
//casteo el descrip_cpa41 para quedarme unicamente con el numero de lote y su item
$len = strlen(trim($descrip_cpa41));
for ($i =0, $j=$len; $i<$len; $i++, $j--)
	if ( is_number($descrip_cpa41[$i]) ){
		$descrip_cpa41 = substr($descrip_cpa41, $i);
		break;
	}
	//casteo el lote por un lado y el item por otro
	List($loteOM, $item) = explode("/", $descrip_cpa41);
	//	echo '<br>';
	//	echo $lote;
	//	echo '<br>';
	//	echo $item;
	//le damos formato a loteOM para la busqueda en el query	
	$loteoriginal= $loteOM;
	$loteOM = '00010000' . $loteOM;
	//query totalmente loco !!
	//$query = "SELECT CPA41.DESCRIP AS Num_Lote_Meca, FLOOR(CPA36.CAN_RECIBI) AS Cant_Recib, FLOOR(CPA36.CAN_PENDIE) AS Cant_Pend, FLOOR(CPA36.CAN_PEDIDA) AS Cant_Pedida, CPA36.COD_ARTICU AS Cod_Articulo, LEFT(CPA35.FEC_EMISIO,11) AS Fecha_Emision FROM CPA41 RIGHT OUTER JOIN CPA36 ON CPA41.N_ORDEN_CO = CPA36.N_ORDEN_CO LEFT OUTER JOIN CPA35 ON CPA36.N_ORDEN_CO = CPA35.N_ORDEN_CO WHERE  (CPA41.N_ORDEN_CO = $loteOM) AND CPA36.COD_ARTICU !=''";
		
	if(!($result = odbc_do($id_db_tango, $query))){
		echo 'Fallo conexion odbc_do: TANGO';
		exit();
	}
//	echo '<center><BR><BR><b>Orden de Mecanizado - Materia Prima: '.$loteOM.'</b><br><br>';
	//if (!$descrip_cpa41 = odbc_result_all($result, "border=1 cellspacing=0 cellpadding=1")){
	//	echo 'Fallo Impresion Orden Mecanizado - Materia Prima';
	//	exit();
	//}

	//query boludo a ver que sucede
	$query_descrip_cpa41 = "SELECT CPA41.DESCRIP FROM CPA41 WHERE (CPA41.N_ORDEN_CO = $loteOM)";
	if(!($result_cpa41 = odbc_do($id_db_tango, $query_descrip_cpa41))){
		echo 'Fallo conexion odbc_do: TANGO';
		exit();
	}
//	echo '<center><BR><BR><b>Orden de Mecanizado - Materia Prima: '.$loteOM.'</b><br><br>';
	//imprime descripcion!
//	$resultado_cpa41 = odbc_result_all($result, 1);
				
	$query_cpa36 = "SELECT FLOOR(CPA36.CAN_PENDIE) AS Cant_Pend, FLOOR(CPA36.CAN_PEDIDA) AS Cant_Pedida, CPA36.COD_ARTICU AS Cod_Articul FROM CPA36 WHERE (CPA36.N_ORDEN_CO = $loteOM)";
	if(!($result_cpa36 = odbc_do($id_db_tango, $query_cpa36))){
		echo 'Fallo conexion odbc_do: TANGO';
		exit();
	}
	$query_cpa35 = "SELECT LEFT(CPA35.FEC_EMISIO,11) AS Fecha_Emision FROM CPA35 WHERE (CPA35.N_ORDEN_CO = $loteOM)";
	if(!($result_cpa35 = odbc_do($id_db_tango, $query_cpa35))){
		echo 'Fallo conexion odbc_do: TANGO';
		exit();
	}
	echo '<div style="position:absolute; left:150; top:240;">';
	echo '<center><BR><BR><b>Orden de Mecanizado - Materia Prima: Nro. Lote Mecanizado '.$loteoriginal.' (la vuelta)</b><br><br>';
		echo '</div>';
	//imprime descripcion!
	echo '<div style="position:absolute; left:100; top:300;">';
	$resultado_cpa41 = odbc_result_all($result_cpa41, 1);
	echo '</div>';
	echo '<div style="position:absolute; left:300; top:300;">';
	$resultado_cpa36 = odbc_result_all($result_cpa36, 1);
	echo '</div>';
	echo '<div style="position:absolute; left:550; top:300;">';
	$resultado_cpa35 = odbc_result_all($result_cpa35, 1);
	echo '</div>';
				


	echo '</center>';
}else echo '<center><b>DATO de tipo NO VALIDO</b></center>';

		//} fin session
?>
