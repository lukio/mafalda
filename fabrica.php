<?php

/**
* Busquedas para nivel de fabrica. 
* Funciones declaradas:
 buscar_nserie();
 buscar_lote_embalado();
 buscar_lote_produccion();
*/

function buscar_nserie($ncelda){

    require_once('include/validaciones.php');
    require_once('dbinfo.php');
    require_once ('MDB2.php');

    /* variables
     * $modelo
     * $ncelda
     *
     **/

    if ( is_numeric($ncelda) ){

        $modelo="";

        // Conecto a DB Flexar
        $mdb2 =& MDB2::singleton($dsn, $options);
        if (PEAR::isError($mdb2)) {
                 die($mdb2->getMessage());
        }

        $query = "select impsg, imprb, lote, imprs
                  FROM impedancias 
                  where serie='$ncelda'";

        //SI NO OBTENGO NINGUN RESULTADO EN ESTA CONSULTA ENTONCES NO EXISTE el NUMERO DE SERIE DADO
        $res =& $mdb2->query($query);

        if (PEAR::isError($res)) {
                    die($res->getMessage());
        }

        $row = $res->fetchrow();
        if (!($lote_pro= $row['2'])){
            echo 'Número de serie inexistente';
            exit();
        }
        $impsg = $row['0'];
        $imprb = $row['1'];
        $imprs = $row['3'];

        $query = "SELECT ENSAYOS.RANGO_FIN, LEFT(ENSAYOS.FECHA,11), ENSAYOS.VSC_INI, ENSAYOS.VSC_FIN, ENSAYOS.GOLPES, ENSAYOS.ESPEC 
                  FROM ENSAYOS 
                  WHERE NRO_SERIE='$ncelda'";

        //tomo resultados de ensayos 
        $res_ensayos = $mdb2->queryAll($query);

        if (PEAR::isError($res)) {
                    die($res->getMessage());
        }

        //hago uso de esta variable a lo ultimo
//        $espec = $row['5'];

        $query = "SELECT DataHorno.Cero, DataHorno.Pendiente, DataHorno.R2, DataHorno.H, DataHorno.Horno, LEFT(HornoResumen.Fecha, 11)
                  FROM DataHorno INNER JOIN HornoResumen ON DataHorno.Horneada = HornoResumen.Horneada
                  WHERE (((DataHorno.Serie)='$ncelda')) AND DataHorno.Horno=HornoResumen.Horno";

        //tomo el numero de registros totales
        $res =& $mdb2->query($query);

        if (PEAR::isError($res)) {
                    die($res->getMessage());
        }

        $idh=0;
         while ($row = $res->fetchrow()){
            $cero[] = $row['0'];
            $pendiente[] = $row['1']; //castear este numero
            $r2[] = $row['2'];  //se castea
            $h[] = $row['3'];  //se castea
            $horno[] = $row['4'];  //se castea
            $fechah[] = $row['5'];  //se castea
            $idh++;
         }
        //traigo el lote de embalado de la celda. chequeo que este el campo abierto en falso para chequear que no fue abiert
        // PREGUNTA: si fue abierto, como se cual es el nuevo lote de embalado?
        $query = "SELECT EMBALADO.ID_Grupo FROM EMBALADO WHERE EMBALADO.serie='$ncelda' AND EMBALADO.abierto=0";

        $lote_emba = $mdb2->queryOne($query);
        
        $query = "SELECT Lotes.Modelo, Lotes.Msg, Lotes.OCMecanizado, Lotes.Mrb, LEFT(Lotes.FechaPeg,11), Operaciones.Operacion
                  FROM Operaciones INNER JOIN Lotes ON Operaciones.IdOperacion = Lotes.Area
                  WHERE (((Lotes.Lote)=$lote_pro))";

        $res =& $mdb2->query($query);

        if (PEAR::isError($res)) {
                    die($res->getMessage());
        }

        $row = $res->fetchrow();
           
        $modelo = $row['0'];
        $msg = $row['1'];
        $ocmecanizado = $row['2'];
        $mrb = $row['3'];
        $fpegado = $row['4'];
        $area = $row['5'];
        //Calculo de la sensibilidad

    	$query = "SELECT Modelos.Sensibilidad, Modelos.CapNominal, Modelos.TolSens FROM Modelos WHERE Modelos.Modelo='$modelo'";
    
        $res =& $mdb2->query($query);

        if (PEAR::isError($res)) {
                    die($res->getMessage());
        }

        $row = $res->fetchrow();
    	$sensibilidad = $row['0'];
	    $capnom = $row['1'];
    	$tolsens = $row['2'];
	//    $sensi_real = ($capnom*$sensibilidad)/(($sensibilidad/$espec)*$rfinal[$ien-1]);
    	$desv_est_porce = (($rfinal[$ien-1]/$capnom) -1 )*100;

        /* Impresion
         * archivo template: fabrica.html
         * */

        require_once 'include/pear/Sigma.php'; //insertamos la libreria
        $it = new HTML_Template_Sigma('themes'); //declaramos el objeto
        $it->loadTemplatefile('fabrica.html', true, true); //seleccionamos la plantilla

        foreach($res_ensayos as $name) {
            // Assign data to the inner block
            foreach($name as $cell) {
                $it->setCurrentBlock("ENSAYO");
                $it->setVariable("DATO", $cell);
                $it->parseCurrentBlock("ENSAYO");
            }
            $it->parse("row");
        }

        $it->show();




	/*echo '<table class="fabrica" border=0 cellspacing=0 cellpadding=1><tr><td valign=top><tr><td> <b>Ensayos - HORNO</b><br><table border=1 cellspacing=0 cellpadding=1 width=350><tr align=center><td><b> Cero Horno</b></td><td><b> Pendiente </b></td><td><b> R2 </b></td><td><b> H </b></td><td><b> Hor </b></td><td><b> FechaHor </b></td></tr>';

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

}else
	echo 'DATO de tipo NO VALIDO';

}
function buscar_lote_embalado($q){
    print "BUSCAR POR LOTE EMABALADO";
}
function buscar_lote_produccion($q){
    print "BUSCAR POR LOTE PRODUCCION";
}
            

?>
