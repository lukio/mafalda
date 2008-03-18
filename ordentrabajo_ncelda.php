<?php

require 'validaciones.php';
require 'dbinfo.php';
/* Si tuviera que pasar por un login...
	session_start();
	if (!array_key_exists('username', $_SESSION)) {
	print 'session ha sido finalizada';
	print '<a href="login.html">Pagina Login</a>';
	}else {
*/
	$lotepro = $_POST['lotepro'];
	$ncelda = $_POST['ncelda'];
	if (is_number($lotepro) && !is_text($lotepro)){

		$contador=0;
		$nroorden = array();
		$cantidad = array();
		$fechTerm = array();
		$noperacion = array();
		$noperario = array();
		$fechInic = array();
		$comentarios = array();
		$observaciones = array();
		$nombre_oper = array();
		$apellido_oper = array();

		//conecto a db Flexar
			
		if (!($idbase = odbc_connect($sql_db_flexar,$sql_usuarioflexar, $sql_pwdflexar))){
			echo 'Fallo conexion base de datos flexar';
			exit();
		}
		
		//primero DatosOrden  . segundo Ordenes de trabajo. - Enalce : Lote
		// ************ TOMO VALORES DE RESULTADOR ************/

		$query = "SELECT NroOrd, Cantidad, LEFT(FechaDeTerminacion,20) FROM DatosOrden WHERE Lote=$lotepro";

		if(!($result = odbc_do($idbase, $query))){
			echo 'Fallo query a tabla DatosOrden';
			exit();
		}
		//tomo el numero de registros totales

		//variables que me interesan

			
		//odbc_result_all($result, "BORDER=1");
			
		while (odbc_fetch_row($result)){
			$nroorden[] = odbc_result($result, 1);
			$cantidad[] = odbc_result($result, 2);
			$fechTerm[] = odbc_result($result, 3);
			$contador++; //cuenta el nro de registros
		}

		/* En desuso (no me acuerdo el porque)
		 *
		print("<table border=1 cellspacing=0 cellpadding=4>\n");
		for ($i=0; $i<$contador; $i++){
		  print("<tr>");
			print("<td>" . $nroorden[$i] . " " .$cantidad[$i] . " " . strtok($fechTerm[$i], " ") ."</td>");
			print("</tr>");
		}
		print("</table>");
		 */

	for($i=0; $i<$contador; $i++){
		$queryOR[$i] = "SELECT Operacion, Operario, LEFT(FechaInicio, 20), Comentarios, 
				Observaciones 
				FROM OrdenesDeTrabajo 
				WHERE NroOrden=$nroorden[$i] AND Terminada=1 AND ANULADA=0";

		  $resultOR[$i] = odbc_do($idbase, $queryOR[$i]);
		 }
		  

		for($i=0;$i<$contador;$i++){
			while(odbc_fetch_row($resultOR[$i])){
				$noperacion[]    = odbc_result($resultOR[$i], 1);
				$noperario[]     = odbc_result($resultOR[$i], 2);
			$fechInic[]      = odbc_result($resultOR[$i], 3);
			$comentarios[]   = odbc_result($resultOR[$i], 4);
			$observaciones[] = odbc_result($resultOR[$i], 5);
			}
		  }
		  //$fechInic = strtok($fechInic, " ");
		  
			
		for($i=0;$i<$contador;$i++){
			$queryOP[$i] = "SELECT Operacion FROM Operaciones WHERE IdOperacion=$noperacion[$i]";
			$resultOP[$i] = odbc_do($idbase, $queryOP[$i]);
		}

		for($i=0;$i<$contador;$i++){
			while(odbc_fetch_row($resultOP[$i])){
				//le pongo el area en donde se encuentra segun el ID de operacion
				$area[] = odbc_result($resultOP[$i], 1);
				}
		  }

		for($i=0;$i<$contador;$i++){	
			$queryOPS[$i] = "SELECT Nombre, Apellido FROM Operarios WHERE IdOperario=$noperario[$i]";
			$resultOPS[$i] = odbc_do($idbase, $queryOPS[$i]);
			}
			
		//nombre y apellido segun el ID de operario
		for($i=0;$i<$contador;$i++){
			while(odbc_fetch_row($resultOPS[$i])){
			$nombre_oper[]    = odbc_result($resultOPS[$i], 1); 
			$apellido_oper[]   = odbc_result($resultOPS[$i], 2);
			}
		}
		// *************** IMPRIMIR ***********/


		print("<center><br /><br /><b>Nro. OT - Especificaciones - Celda de Carga: $ncelda </b><br /><br />");
		print("<table border=1 cellspacing=0 cellpadding=2 witdth=750>");
		echo '<tr align=center><td><b> Lote.Prod </b></td><td><b> Nro. OT </b></td><td><b> CanTer </b></td><td><b> Area.Oper</b></td><td><b> Nom.Apell </b></td><td><b> Fecha.Inicia </b></td><td><b> Fecha.Term </b></td><td><b> Observ </b></td><td><b> Comen </b></td></tr>';
		 for ($i=0; $i<$contador; $i++){
				print "<tr align=center><td><b>" . $lotepro . "</b></td>";
				echo '<td><b><a target=_blank href=p_orden.php?norden='.$nroorden[$i].'>'.$nroorden[$i].'</a></b></td>';
				//<td align=right><b>Lote Produccion : </td><td align=left><b><a href=lote_pro3.php?nlotepro='.$lote_pro.'>'.$lote_pro.' </a> 
				print "<td><b>" . $cantidad[$i] . "</b></td>";
				print "<td><b>" . $area[$i] . "</b></td>";
				print "<td><b>" . $nombre_oper[$i] . " " . $apellido_oper[$i] ."</b></td>";
				print "<td><b>" . $fechInic[$i] . "</b></td>"; 
				print "<td><b>" . $fechTerm[$i] . "</b></td>";
				print "<td><b>" . $observaciones[$i] . "</b></td>";
				print "<td><b>" . $comentarios[$i] . "</b></td>";
		//print "<td><b>" . $terminada . "</b></td>";
		//print "<td><b>" . $anulada . "</b></td></tr>";
		}
		print("</table>");
	}else //fin chequeo de que lote sea numero
		echo '<h2>Tipo de DATO INVALIDO</h2>';
		

//	}//fin session

?>
