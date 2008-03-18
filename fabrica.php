<?php

/**
* Busquedas para nivel de fabrica. 
* Funciones declaradas:
 buscar_nserie();
 buscar_lote_embalado();
 buscar_lote_produccion();
*/

function buscar_nserie($ncelda){
    print "BUSCAR POR SERIE";

    require_once('include/validaciones.php');
    require_once('dbinfo.php');
    /**
    Variables para conectarme al tango

    $program_db_tango;
    $db_tango;
    $usuariotango;
    $pwdtango;

    Variables para conectarme al flexar
    $sql_program_db_flexar;
    $sql_db_flexar;
    $sql_usuarioflexar;
    $sql_pwdflexar;

    */

    // Inicializo variables
    $modelo="";

    if (is_number($ncelda) && !is_text($ncelda)){

	// Conecto a DB Flexar
	if (!($connectstring = odbc_connect($sql_db_flexar,$sql_usuarioflexar, $sql_pwdflexar))){
		echo 'Fallo conexion base de datos flexar';
		exit();
		}

//consulta a dbms  . Selecciona campo NRO_SERIE from tabla ENSAYOS
$_query_ = "SELECT e.RANGO_FIN, e.MODELO, i.ImpSG, i.ImpRB, i.Lote, dh.Cero, dh.Pendiente, dh.R2, em.ID_Grupo 
            FROM ENSAYOS e, Impedancias i, DataHorno dh, EMBALADO em 
           WHERE e.NRO_SERIE=$ncelda AND i.Serie=$ncelda AND dh.Serie=$ncelda";

	  $query = "SELECT ENSAYOS.RANGO_FIN, Left (ENSAYOS.FECHA,11), ENSAYOS.VSC_INI, ENSAYOS.VSC_FIN, ENSAYOS.GOLPES, ENSAYOS.ESPEC FROM ENSAYOS WHERE NRO_SERIE=$ncelda";

	//tomo resultados de ensayos 
	if(!($result = odbc_do($connectstring, $query))){
		echo 'Fallo query a ensayos';
		exit();
		}

	$rfinal = array();
	$ien=0;
	 while (odbc_fetch_row($result)){
		$rfinal[]  = odbc_result($result,1);
		$fecha_ensayo[]  = odbc_result($result,2);
		$vsc_ini[]  = odbc_result($result,3);
		$vsc_fin[]  = odbc_result($result,4);
		$golpes[]  = odbc_result($result,5);
		$ien++;
		}
	$espec = odbc_result($result, 6);

	/** quiero seleccionar una fila donde el nro de serie sera nrocelda (proveniente de consulta.html)
	 * entonces el query debe ser = "SELECT * from ENSAYOS where (NRO_SERIE='nroceda')
	 * tomo resultados
	 * tomo el numero de registros totales
	 * $nroregistro = odbc_num_rows ($result);
	 * me paro en el ultimo registro (que es el que me interesa)
	 * odbc_fetch_row($result, $nroregistro);
	 *
	 * $rfinal = odbc_result($result, 1);
	 * nuevo query Impedancias (grupo 2)
	*/
	
	$query = "SELECT Impedancias.ImpSG, Impedancias.ImpRB, Impedancias.Lote, Impedancias.ImpRs FROM Impedancias WHERE Impedancias.Serie=$ncelda";
	 
	if(!($result = odbc_do($connectstring, $query))){
		echo 'Fallo query tabla: Impedancias';
		exit ();
	}
		
	//SI NO OBTENGO NINGUN RESULTADO EN ESTA CONSULTA ENTONCES NO EXISTE el NUMERO DE SERIE DADO
	if (!($lote_pro= odbc_result($result, 3))){
		echo '<center><b>Num. de Serie INCORRECTO</b></center>';
		exit();
	}
	$impsg = odbc_result($result, 1);
	$imprb = odbc_result($result, 2);
	$imprs = odbc_result($result, 4);
		

	/** nuevo query DataHorno (grupo 1)
	* $query = "SELECT dh.Cero AS Cero, dh.Pendiente AS Pendiente, dh.R2 AS R2, dh.H AS H, dh.Horno AS Horno,  (SELECT hr.Fecha FROM HornoResumen hr WHERE hr.Horneda=dh.Horneada) AS Fecha FROM DataHorno dh WHERE dh.Serie=$ncelda";
	 */

$query = "SELECT DataHorno.Cero, DataHorno.Pendiente, DataHorno.R2, DataHorno.H, DataHorno.Horno, LEFT(HornoResumen.Fecha, 11)
	FROM DataHorno INNER JOIN HornoResumen ON DataHorno.Horneada = HornoResumen.Horneada
       	WHERE (((DataHorno.Serie)=$ncelda)) AND DataHorno.Horno=HornoResumen.Horno";

	if(!($result = odbc_do($connectstring, $query))){
		echo 'Fallo conexion odbc_do: DataHorno';
		exit ();
	}
	//tomo el numero de registros totales
			
	$idh=0;
	 while (odbc_fetch_row($result)){ 
		$cero[] = odbc_result($result, 1);
		$pendiente[] = odbc_result($result, 2); //castear este numero
		$r2[] = odbc_result($result, 3);  //se castea
		$h[] = odbc_result($result, 4);  //se castea
		$horno[] = odbc_result($result, 5);  //se castea
		$fechah[] = odbc_result($result, 6);  //se castea
		$idh++;
	 }
	//traigo el lote de embalado de la celda. chequeo que este el campo abierto en falso para chequear que no fue abiert
	// PREGUNTA: si fue abierto, como se cual es el nuevo lote de embalado?
	$query = "SELECT EMBALADO.ID_Grupo FROM EMBALADO WHERE EMBALADO.serie=$ncelda AND EMBALADO.abierto=0";

	if(!($result = odbc_do($connectstring, $query))){
		echo 'Fallo tabla: EMBALADO';
		exit();
	}

	$lote_emba = odbc_result($result, 1);	

	if (isset($lote_pro)){
	//$query = "SELECT Lotes.Modelo, Lotes.Msg, Lotes.OCMecanizado, Lotes.Mrb, LEFT(Lotes.FechaPeg,11) FROM Lotes WHERE Lotes.Lote=$lote_pro";

$query = "SELECT Lotes.Modelo, Lotes.Msg, Lotes.OCMecanizado, Lotes.Mrb, LEFT(Lotes.FechaPeg,11), Operaciones.Operacion
	  FROM Operaciones INNER JOIN Lotes ON Operaciones.IdOperacion = Lotes.Area
	  WHERE (((Lotes.Lote)=$lote_pro))";

	   
	   if(!($result = odbc_do($connectstring, $query))){
		   echo 'Fallo query tabla: Lotes';
		   exit();
	   }
	   $modelo = odbc_result($result,1);
	   $msg = odbc_result($result,2);
	   $ocmecanizado = odbc_result($result, 3);
	   $mrb = odbc_result($result, 4);
	   $fpegado = odbc_result($result, 5);
	   $area = odbc_result($result, 6);

	  }

	/************** IMPRIMIR **********************/
	//formato : Header, grupo1, grupo2, grupo3

	if (isset($lote_pro)){

	echo '<center><b>Especificaciones - Celda de Carga :' . $ncelda . '</b><br /><br />';

	//Se imprime el header !! Hay una tabla ahi adentro  

	//echo "<a href='pagina2.php?nombrevariable=$variable1' > segunda pagina </a>";

	//comienzo tabla global y tabla de Horno
	echo '<center><table border=0 cellspacing=0 cellpadding=1><tr><td valign=top><tr><td> <b>Ensayos - HORNO</b><br><table border=1 cellspacing=0 cellpadding=1 width=350><tr align=center><td><b> Cero Horno</b></td><td><b> Pendiente </b></td><td><b> R2 </b></td><td><b> H </b></td><td><b> Hor </b></td><td><b> FechaHor </b></td></tr>';

	for($cont=0; $cont<$idh; $cont++){
		print "<tr align=center><td><b>" . $cero[$cont] . "</b></td><td><b>" . round($pendiente[$cont],4) . "</b></td><td><b>" . round($r2[$cont],3) . "</b></td><td><b>" . round($h[$cont],3). "<b></td><td><b>" . $horno[$cont]. "<b></td><td><b>" . $fechah[$cont]. "<b></td></tr>";
	}
	echo '</table></td><td valign=top>';
	//fin tabla horno se abre otro item tabla global


	//se imprime grupo2 - Maquina Ensayos
	echo '<b>Maquina ENSAYOS </b><table border=1 cellspacing=0 cellpadding=1><tr align=center><td><b> Rango Fin </b></td><td><b> Fecha aa/mm/dd</b></td><td><b> Cero ini</b></td><td><b> Cero fin</b></td><td><b> Golp</b></td></tr>';
	  for($cont=0; $cont<$ien; $cont++)
			print "<tr align=center><td><b>" . $rfinal[$cont] . "</b></td><td><b>" . $fecha_ensayo[$cont] . "</b></td><td><b>" . $vsc_ini[$cont] . "</b></td><td><b>" . $vsc_fin[$cont] . "</b></td><td><b>" . $golpes[$cont] . "</b></td></tr>";

	echo '</table></td></tr></table>';
	//fin impresion ensayos

echo '<BR>';
  echo '<table border=0>';
	echo '<tr><td align=right><b>Modelo : </td><td align=left><b>'.$modelo.' </td></tr>';
	echo '<tr><td align=right><b>Lote Produccion : </td><td align=left><b><a href=lote_pro3.php?nlotepro='.$lote_pro.'&modelo='.$modelo.'>'.$lote_pro.' </a></td><td><b>Area: '.$area.'</td></tr>';
	echo '<tr><td align=right><b>Lote Embalado : </td><td align=left><b><a href=lote_emb2.php?nlote_emba='.$lote_emba.'>'.$lote_emba.' </a></td></tr>'; echo '<tr><td align=right><b>Orden Mecanizado : </td><td align=left><b><a href=por_omeca.php?nomecanizado='.$ocmecanizado.'>'.$ocmecanizado.' </a></td>';
	echo '<td><b><a href=sql_prueba.php?nomecanizado='.$ocmecanizado.'><h5>OM-Materia Prima<h5> </a></td></tr>';
	echo '<tr><td align=right><b>Fecha Pegado : </td><td align=left><b>' . $fpegado . '</td></tr> </table>';
  echo '<br> <table border=0>';
	echo '<tr><td align=right><b>   MSG    : </td><td align=left><b>' . $msg . '</td></tr>';
	echo '<tr><td align=right><b>   MRB    : </td><td align=left><b>' . $mrb . '</td></tr>';
	echo '<tr><td align=right><b>Impedancia RB : </td><td align=left><b>' . $imprb . '</td></tr>';
	echo '<tr><td align=right><b>Impedancia SG : </td><td align=left><b>' . $impsg . '</td></tr>';
	echo '<tr><td align=right><b>Impedancia RS : </td><td align=left><b>' . $imprs . '</tr>';
//Calculo de la sensibilidad

	$query = "SELECT Modelos.Sensibilidad, Modelos.CapNominal, Modelos.TolSens FROM Modelos WHERE Modelos.Modelo='$modelo'";

	if(!($result = odbc_do($connectstring, $query))){
			echo 'Fallo conexion odbc_do: Modelos';
			exit ();
			}
	$sensibilidad = odbc_result($result,1);
	$capnom = odbc_result($result, 2);
	$tolsens = odbc_result($result, 3);
	$sensi_real = ($capnom*$sensibilidad)/(($sensibilidad/$espec)*$rfinal[$ien-1]);
	$desv_est_porce = (($rfinal[$ien-1]/$capnom) -1 )*100;
	
	echo '<tr><td align=right><b>Sensibilidad Real : </td><td align=left><b>'.round($sensi_real,3).'</tr>';
	echo '<tr><td align=right><b>Desviacion estandar porcentual : </td><td align=left><b>'.round($desv_est_porce,3).'</tr>';
	echo '<tr><td align=right><b>Tolerancia Sens : </td><td align=left><b>'.round($tolsens,3).'</tr>';
	echo '<tr><td align=right><b>Capacidad Nominal : </td><td align=left><b>'.round($capnom,3).'</tr></table><br><br>';	
	/**
	 * COLOCAR LA DESVIACION ESTANDAR porcentual= 1 - (capnom/capreal)*100  (pa los de arriba)
	 * colocar la cap. nominal a pedido del mila !
	 * se puede decir que este bloque se muestre o no si el usuario esta logueado?
	 session_start();
	 if (!array_key_exists('username', $_SESSION))
	 	;
	 else
		 echo '<br><a href="login.html">Pagina Login</a>';
	
	*/
	echo '<div style="position: absolute; left:40; top:15;">';
		print("<center><form method=POST action=ordentrabajo_ncelda.php>");
		print("<input type=hidden name=lotepro value=$lote_pro>");
		print("<input type=hidden name=ncelda value=$ncelda>");
		print("<input type=submit name=Submit value='Num. de OT por lote'>");
		print "</form>";
	echo '</div>';
	//}


	//echo '</td><td>")';
	echo '<div style="position: absolute; left:580; top:15;">';
		print "<form method=POST action=proba_ncelda.php>";
		print "<input type=hidden name=lotepro value=$lote_pro>";
		print "<input type=hidden name=ncelda value=$ncelda>";
		print "<input type=submit name=Submit value='Tabla Probatuti'>";
		print "</form>";
	echo '</div>';
	//echo '</td></tr></table>';
	} //fin if (isset($lote_pro))
	else echo '<b>Lote de produccion NO existe, Numero de serie NO existe</b>';

}else
	echo '<center><b>DATO de tipo NO VALIDO</b></center>';

}
function buscar_lote_embalado($q){
    print "BUSCAR POR LOTE EMABALADO";
}
function buscar_lote_produccion($q){
    print "BUSCAR POR LOTE PRODUCCION";
}
            

?>
