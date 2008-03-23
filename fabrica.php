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

        if (PEAR::isError($res_ensayos)) {
                    die($res_ensayos->getMessage());
        }

        //hago uso de esta variable a lo ultimo
//        $espec = $row['5'];

        $query = "SELECT DataHorno.Cero, ROUND(DataHorno.Pendiente,2), ROUND(DataHorno.R2,2), ROUND(DataHorno.H,2), DataHorno.Horno, LEFT(HornoResumen.Fecha, 11)
                  FROM DataHorno INNER JOIN HornoResumen ON DataHorno.Horneada = HornoResumen.Horneada
                  WHERE (((DataHorno.Serie)='$ncelda')) AND DataHorno.Horno=HornoResumen.Horno";

        //tomo el numero de registros totales
        $res_horno = $mdb2->queryAll($query);

        if (PEAR::isError($res_horno)) {
                    die($res_horno->getMessage());
        }

        //traigo el lote de embalado de la celda. chequeo que este el campo abierto en falso para chequear que no fue abiert
        // PREGUNTA: si fue abierto, como se cual es el nuevo lote de embalado?
        $query = "SELECT EMBALADO.ID_Grupo FROM EMBALADO WHERE EMBALADO.serie='$ncelda' AND EMBALADO.abierto=0";

        $lote_emba = $mdb2->queryOne($query);
        
        $query = "SELECT Lotes.Modelo, Lotes.Msg, Lotes.Mrb, Lotes.OCMecanizado, Operaciones.Operacion, LEFT(Lotes.FechaPeg,11)
                  FROM Operaciones INNER JOIN Lotes ON Operaciones.IdOperacion = Lotes.Area
                  WHERE (((Lotes.Lote)=$lote_pro))";

        $res_lotes = $mdb2->queryAll($query);

        if (PEAR::isError($res_lotes)) {
                    die($res_lotes->getMessage());
        }
/*        $row = $res->fetchrow();
           
        $modelo = $row['0'];
        $msg = $row['1'];
        $ocmecanizado = $row['2'];
        $mrb = $row['3'];
        $fpegado = $row['4'];
        $area = $row['5'];
*/
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

        foreach($res_horno as $name) {
            // Assign data to the inner block
            foreach($name as $cell) {
                $it->setCurrentBlock("HORNO");
                $it->setVariable("DATO", $cell);
                $it->parseCurrentBlock("HORNO");
            }
            $it->parse("row_horno");
        }

            $it->setCurrentBlock("CELDA");
            $it->setVariable("MODELO", $res_lotes['0']['0']);
            $it->setVariable("LOTE_PRODUCCION", $lote_pro);
            $it->setVariable("LOTE_EMBALADO", $lote_emba);
            $it->setVariable("AREA", $res_lotes['0']['4']);
            $it->setVariable("OM", $res_lotes['0']['3']);
            $it->setVariable("OMMP", "link tango");
            $it->setVariable("FECHA_PEGADO", $res_lotes['0']['5']);
            $it->parseCurrentBlock("CELDA");

            $it->parse("row_celda");
        

        $it->show();

}else
	print "DATO de tipo NO VALIDO";

}

function buscar_lote_embalado($q){
    print "BUSCAR POR LOTE EMABALADO";
}


function buscar_lote_produccion($q){
    print "BUSCAR POR LOTE PRODUCCION";
}
            

?>
