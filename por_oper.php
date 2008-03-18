<?php
require 'dbinfo.php';
require 'validaciones.php';

session_start();

if (!array_key_exists('username', $_SESSION)) {
	echo '<center><h2>session ha sido finalizada</h2></center>';
//	echo '<br><a href="login.html">Pagina Login</a>';
?>
		<html>
			<head>
			 <script language="Javascript">
				setTimeout("location.replace('consulta.html')",2000);
			</script>
			</head>
		<html>
<?php
}else {

//	if( isset($_POST['noper'])){
	$noper = $_POST['noper'];
	if (is_number($noper) && !is_text($noper)){

		$dia = $_POST['dia'];
		$mes = $_POST['mes'];
		$anio = $_POST['anio'];
		$diaT = $_POST['diaT'];
		$mesT = $_POST['mesT'];
		$anioT = $_POST['anioT'];

		//chequeamos si la fecha es valida

		if ( !checkdate($mes, $dia, $anio) || !checkdate($mesT, $diaT, $anioT)  )
				print "Fecha Invalida, vuelva a consultar";

		//chequear el rango de fecha. que el final no sea menor al fecha incial !
		else{
			//formo fecha
			$fecha_query = $mes . "/" . $dia . "/" . $anio;
			$fecha_queryT = $mesT . "/" . $diaT . "/" . $anioT;
			$fecha = $dia . "/" . $mes . "/" . $anio;
			
				
			//conecta a db flexar
			if (!($connectstring = odbc_connect($sql_db_flexar,$sql_usuarioflexar, $sql_pwdflexar))){
				echo 'Fallo conexion base de datos flexar';
				exit();
			}
		
		 
			  //fecha la saco del formulario y pongo >= . AND Operario y lo cruzo con tabla operarios entonces saco nombre y apellido. CHAN!
			//$query = "SELECT NroOrden 
			//FROM OrdenesDeTrabajo 
			//WHERE FechaInicio between #2005-3-18# AND #2005-3-20#";
			//
			$query = "SELECT NroOrden AS Nro_OT, LEFT(FechaInicio,20) AS Fecha_Inicio 
				FROM OrdenesDeTrabajo 
				
				WHERE FechaInicio>=#$fecha_query# AND FechaInicio<=#$fecha_queryT# AND Operario=$noper";

			$result = odbc_do($connectstring, $query);
		
			/************ IMPRESION *****************/
			print("<center>");
			if (!odbc_result_all($result, "border=1 cellspacing=0 cellpadding=1")){
				echo '<center><h3>Numero de Operador NO valido</h3></center>';
				exit();
			}
			
			odbc_close($connectstring);
		}
	}else //fin !isset(noper)
		echo '<center><b>DATO de tipo NO VALIDO</b></center>';
		
}//fin session
?>
