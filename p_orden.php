<?php
require 'validaciones.php';
require 'dbinfo.php';
/*  Si necesita de un login para ver esta page...
	session_start();
	if (!array_key_exists('username', $_SESSION)) {
		print 'session ha sido finalizada';
		print '<a href="login.html">Pagina Login</a>';
		}else {
 */
$norden = $_GET['norden'];
//$ncelda = $_POST['ncelda'];

if (is_number($norden) && !is_text($norden)){
		//conecto a db Flexar
		if (!($connectstring = odbc_connect($sql_db_flexar,$sql_usuarioflexar, $sql_pwdflexar))){
			echo 'Fallo conexion base de datos flexar';
			exit();
		}
	 
		/* Como hacemos el query
		 * dbc_result_all($result, "BORDER=1 cellspacing=0 cellpadding=3 witdth=300");
		 * consulta a dbms  . Selecciona nroserie, dia embalado segun el nro lote embalado
		 * 2 querys , con ANULADA y sin ANULADA
		 */
	 
	 $query = "SELECT LEFT(FechaInicio,20) AS Fecha_Inicio, Operario, Terminada, Anulada 
		   FROM OrdenesDeTrabajo 
		   WHERE NroOrden=$norden";

	 //obtengo resultados
	 if(!($result   = odbc_do($connectstring, $query))){
		 echo 'Fallo query tabla OrdenesDeTrabajo';
		 exit();
	 }

	$rfecha_ini  = odbc_result($result,1);
	$roperario  = odbc_result($result,2);
	$rterminada  = odbc_result($result,3);
	$ranulada =  odbc_result($result,4);

	//print ("terminada? " . $rterminada . "");
	//print ("anulada? " . $ranulada . "");
	 if ($ranulada == 1)
		print ("Numero de Orden :" . $norden . " ANULADA");
	else{
		$query = "SELECT Lote, Cantidad, LEFT(FechaDeTerminacion,20) AS Fecha_Termi
			  FROM DatosOrden 
			  WHERE NroOrd=$norden";
		$result   = odbc_do($connectstring, $query);

		//hay mucha !!!!! forrr!!!!!
		 $cont_fila_DO=0;
		 while (odbc_fetch_row($result)){
			$rlote[]  = odbc_result($result,1);
			$rcantidad[]  = odbc_result($result,2);	
			$rfecha_ter  = odbc_result($result,3); 
			$cont_fila_DO++;
		}
		/* query en desuso
		 * $query = "SELECT opera.Operacion AS Area, op.Nombre, op.Apellido
		 * FROM Probatuti prob, Operaciones opera, Operarios op 
		 * WHERE prob.Area=opera.IdOperacion AND prob.operador=op.OperProba AND prob.serie=$ncelda
		 * ORDER BY prob.fecha, prob.Area";
		 */
		$query = "SELECT op.Nombre, op.Apellido, opera.Operacion AS Area
			  FROM Operaciones opera, Operarios op
			  WHERE op.Area=opera.IdOperacion AND op.IdOperario=$roperario";

		$result   = odbc_do($connectstring, $query);
		$rnombre  = odbc_result($result,1);
		$rapellido  = odbc_result($result,2);
		$rarea  = odbc_result($result,3);
		
		/********* IMPRIMIR *************/
		print("<center><br /><b>Numero de Orden de Trabajo:" . $norden . "</b><br /><br />");

		print("<table border=1 cellspacing=0 cellpadding=2 width=600>");
		print("<tr align=center><td><b>Nom.Apell</b></td><td><b>Area</b></td><td><b>Termin</b></td><td><b>Fech.Inici</b></td><td><b>Fech.Termi</b></td></tr>");
		print "<tr align=center><td><b>" . $rnombre . " " . $rapellido . "</b></td>";
		print "<td><b>" . $rarea . "</b></td>";	
		print "<td><b>" . $rterminada . "</b></td>";	
		print "<td><b>" . $rfecha_ini . "</b></td>";	
		print "<td><b>" . $rfecha_ter . "</b></td>";	
		print("</tr>");
		print("</table>");

		print("<br><br>");

		print("<table border=1 cellspacing=0 cellpadding=2 width=300>");
		print("<tr align=center><td><b> Lote.Pro </b></td><td><b>Modelo</b></td><td><b>Cantidad</b></td></tr>");
		for($i=0; $i<$cont_fila_DO; $i++){
			print "<tr align=center><td><b>" . $rlote[$i] . "</b></td>";
		
		$query ="SELECT Modelo FROM Lotes WHERE Lote=$rlote[$i]";
		$result   = odbc_do($connectstring, $query);
		$rmodelo = odbc_result($result,1);
		 
		print "<td><b>" . $rmodelo . "</b></td>";	
		print "<td><b>" . $rcantidad[$i] . "</b></td>";	
		print("</tr>");
		}
		print("</table>");
		odbc_close($connectstring);
	}

 }else //fin dato de tipo no valido
	 echo 'Dato de tipo NO VALIDO';

//}//fin session

?>
