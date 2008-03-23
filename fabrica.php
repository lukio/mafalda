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

    if ( !is_numeric($ncelda) ){
	        print "DATO de tipo NO VALIDO";

    }else{

            $modelo="";

        // Conecto a DB Flexar
        $mdb2 =& MDB2::singleton($dsn, $options);
        if (PEAR::isError($mdb2)) {
                 die($mdb2->getMessage());
        }

        $query = "select lote, ROUND(imprb,3), ROUND(impsg,3), ROUND(imprs,3)
                  FROM impedancias 
                  where serie=".$mdb2->quote($ncelda,'integer')."";

        //SI NO OBTENGO NINGUN RESULTADO EN ESTA CONSULTA ENTONCES NO EXISTE el NUMERO DE SERIE DADO
        $res =& $mdb2->query($query);

        if (PEAR::isError($res)) {
                    die($res->getMessage());
        }
        $res_impedancias = $res->fetchrow();
        if (!$res_impedancias['0']){
            echo 'Número de serie inexistente';
            exit();
        }

        $query = "SELECT ROUND(ENSAYOS.RANGO_FIN,3), LEFT(ENSAYOS.FECHA,11), ENSAYOS.VSC_INI, ENSAYOS.VSC_FIN, ENSAYOS.GOLPES, ENSAYOS.ESPEC 
                  FROM ENSAYOS 
                  WHERE NRO_SERIE=".$mdb2->quote($ncelda,'integer')."";

        //tomo resultados de ensayos 
        $res_ensayos = $mdb2->queryAll($query);

        if (PEAR::isError($res_ensayos)) {
                    die($res_ensayos->getMessage());
        }

       
        $query = "SELECT DataHorno.Cero, ROUND(DataHorno.Pendiente,2), ROUND(DataHorno.R2,2), ROUND(DataHorno.H,2), DataHorno.Horno, LEFT(HornoResumen.Fecha, 11)
                  FROM DataHorno INNER JOIN HornoResumen ON DataHorno.Horneada = HornoResumen.Horneada
                  WHERE (((DataHorno.Serie)=".$mdb2->quote($ncelda,'integer').")) AND DataHorno.Horno=HornoResumen.Horno";

        //tomo el numero de registros totales
        $res_horno = $mdb2->queryAll($query);

        if (PEAR::isError($res_horno)) {
                    die($res_horno->getMessage());
        }

        //traigo el lote de embalado de la celda. chequeo que este el campo abierto en falso para chequear que no fue abiert
        // PREGUNTA: si fue abierto, como se cual es el nuevo lote de embalado?
        $query = "SELECT EMBALADO.ID_Grupo FROM EMBALADO WHERE EMBALADO.serie=".$mdb2->quote($ncelda,'integer')." AND EMBALADO.abierto=0";

        $lote_emba = $mdb2->queryOne($query);
        
        $query = "SELECT Lotes.Modelo, Lotes.Msg, Lotes.Mrb, Lotes.OCMecanizado, Operaciones.Operacion, LEFT(Lotes.FechaPeg,11)
                  FROM Operaciones INNER JOIN Lotes ON Operaciones.IdOperacion = Lotes.Area
                  WHERE (((Lotes.Lote)=".$mdb2->quote($res_impedancias['0'],'integer')."))";

        $res_lotes = $mdb2->queryRow($query);

        if (PEAR::isError($res_lotes)) {
                    die($res_lotes->getMessage());
        }

        //Calculo de la sensibilidad

    	$query = "SELECT Sensibilidad, Modelos.CapNominal, Modelos.TolSens FROM Modelos WHERE Modelos.Modelo=".$mdb2->quote($res_lotes['0'],'text')."";
    
        $res_estadistica= $mdb2->queryRow($query);

        if (PEAR::isError($res_estadistica)) {
                    die($res_estadistica->getMessage());
        }

        //hago uso de esta variable a lo ultimo
        $num_col = count($res_ensayos);
        $rfinal = $res_ensayos[$num_col-1]['0'];
        $espec = $res_ensayos[$num_col-1]['5'];

    	$sensibilidad = $res_estadistica['0'];
	    $capnom = $res_estadistica['1'];
    	$tolsens = $res_estadistica['2'];

	    $sensi_real = ($capnom*$sensibilidad)/(($sensibilidad/$espec)*$rfinal);
    	$desv_est_porce = (($rfinal/$capnom) -1 )*100;
        /* Impresion
         * archivo template: fabrica.html
         * */

        require_once 'include/pear/Sigma.php'; //insertamos la libreria
        $it = new HTML_Template_Sigma('themes'); //declaramos el objeto
        $it->loadTemplatefile('fabrica.html', true, true); //seleccionamos la plantilla
        
        // Tabla de Ensayos
        foreach($res_ensayos as $name) {
            // Assign data to the inner block
            foreach($name as $cell) {
                $it->setCurrentBlock("ENSAYO");
                $it->setVariable("DATO", $cell);
                $it->parseCurrentBlock("ENSAYO");
            }
            $it->parse("row");
        }

        // Tabla de Maquina Horno
        foreach($res_horno as $name) {
            // Assign data to the inner block
            foreach($name as $cell) {
                $it->setCurrentBlock("HORNO");
                $it->setVariable("DATO", $cell);
                $it->parseCurrentBlock("HORNO");
            }
            $it->parse("row_horno");
        }

        // Tabla Datos generales
        $it->setCurrentBlock("CELDA");
        $it->setVariable("MODELO", $res_lotes['0']);
        $it->setVariable("LOTE_PRODUCCION", $res_impedancias['0']);
        $it->setVariable("LOTE_EMBALADO", $lote_emba);
        $it->setVariable("AREA", $res_lotes['4']);
        $it->setVariable("OM", $res_lotes['3']);
        $it->setVariable("OMMP", "link tango");
        $it->setVariable("FECHA_PEGADO", $res_lotes['5']);
        $it->parseCurrentBlock("CELDA");
        $it->parse("row_celda");
        
        // Tabla Datos generales
        $it->setCurrentBlock("IMPE");
        $it->setVariable("MSG", $res_lotes['1']);
        $it->setVariable("MRB", $res_lotes['2']);
        $it->setVariable("IMPE_RB", $res_impedancias['1']);
        $it->setVariable("IMPE_SG", $res_impedancias['2']);
        $it->setVariable("IMPE_RS", $res_impedancias['3']);
        $it->parseCurrentBlock("IMPE");

        // Tabla Datos estadistica
        $it->setCurrentBlock("ESTADISTICA");
        $it->setVariable("SR", round($sensi_real,3));
        $it->setVariable("DEP", round($desv_est_porce,3));
        $it->setVariable("TS", $tolsens);
        $it->setVariable("CN", $capnom);
        $it->parseCurrentBlock("ESTADISTICA");
        


        $it->show();
    }

}

function buscar_lote_embalado($q){
    print "BUSCAR POR LOTE EMABALADO";
}


function buscar_lote_produccion($q){
    print "BUSCAR POR LOTE PRODUCCION";
}
            

?>
