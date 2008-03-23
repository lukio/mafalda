<?php

/**
* Busquedas para nivel de fabrica. 
* Funciones declaradas:
 buscar_nserie();
 buscar_lote_embalado();
 buscar_lote_produccion();
*/

function buscar_nserie($ncelda){

    require_once('dbinfo.php');
    require_once ('MDB2.php');

    /* variables
     * $ncelda
     *
     **/

    if ( !is_numeric($ncelda) ){
	        print "DATO de tipo NO VALIDO";

    }else{

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

        //Haciendo calculos de las estadisticas
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

function buscar_lote_produccion($lote_produccion){
    print "LOTE PRODUCCION: ".$lote_produccion."<br />";

    if (!is_numeric($lote_produccion)){
            print "Dato de tipo NO VALIDO";
    }else{
        require_once('dbinfo.php');
        require_once('MDB2.php');

        /**
         * Se va a agregar dos querys. Uno para el header y el otro para el resto !
         * header : + merma + modelo + fecha
         */

        //Conecto a DB Flexar
        // Conecto a DB Flexar
        $mdb2 =& MDB2::singleton($dsn, $options);
        if (PEAR::isError($mdb2)) {
                 die($mdb2->getMessage());
        }
        
        //devuelve una fila
        $query = "SELECT l.modelo, l.merma,  LEFT(l.fecha, 11), m.tolsens,ROUND(m.tolcero,3), m.tolimpsal, m.[tol impent]
                  FROM Modelos m, Lotes l
                  WHERE l.lote=? and l.modelo=m.modelo";

        // sanitizamos la el select
        $type = array ('integer');
        $statement= $mdb2->prepare($query, $type, MDB2_PREPARE_RESULT);
        $data = array($lote_produccion);
        $result_header = $statement->execute($data);
        if(PEAR::isError($result_header)) {
             die($mdb2->getMessage());
         }
        $statement->Free();
        $row_header = $result_header->fetchrow();
        if (!isset($row_header['0'])){
            print "<br>No existe número de producción";
            exit();
        }
        // devuelve varias filas
        $query = "SELECT Impedancias.Serie AS NroSerie, round(Impedancias.ImpSG,3), ROUND(Impedancias.ImpRB,3), round(Impedancias.ImpRs,3) 
                  FROM Impedancias
                  WHERE Impedancias.Lote=? order by impedancias.serie";

        // sanitizamos el select
        $type = array ('integer');
        $statement= $mdb2->prepare($query, $type, MDB2_PREPARE_RESULT);
        $data = array($lote_produccion);
        $result_impedancias = $statement->execute($data);
        if(PEAR::isError($result_impedancias)) {
             die($mdb2->getMessage());
         }
        $statement->Free();
        $row_impedancias = $result_impedancias->fetchAll();

        $mdb2->disconnect();       
        
        // impresion!!       
        require_once 'include/pear/Sigma.php'; //insertamos la libreria
        $it = new HTML_Template_Sigma('themes'); //declaramos el objeto
        $it->loadTemplatefile('lote_produccion_fabrica.html', true, true); //seleccionamos la plantilla
        
        // Datos varios del Lote
        foreach($row_header as $name) {
            // Assign data to the inner block
                $it->setCurrentBlock("LOTES");
                $it->setVariable("DATO", $name);
                $it->parseCurrentBlock("LOTES");
        }
        // Datos de los numero de serie de ese Lote
        foreach($row_impedancias as $name) {
            // Assign data to the inner block
            foreach($name as $cell) {
                $it->setCurrentBlock("IMPE");
                $it->setVariable("DATO", $cell);
                $it->parseCurrentBlock("IMPE");
            }
            $it->parse("row_imp");
        }
            $it->show();
    }


}

function buscar_lote_embalado($lote_embalado){

    print "LOTE EMBALADO: ".$lote_embalado."<br />";

    if (!is_numeric($lote_embalado)){
            print "Dato de tipo NO VALIDO";
    }else{
        require_once('dbinfo.php');
        require_once('MDB2.php');

        // Conecto a DB Flexar
        $mdb2 =& MDB2::singleton($dsn, $options);
        if (PEAR::isError($mdb2)) {
                 die($mdb2->getMessage());
        }

    //consulta a dbms  . Selecciona nroserie, dia embalado segun el nro lote embalado
        $query = "SELECT embalado.serie, (SELECT Lote FROM Impedancias WHERE embalado.serie=impedancias.serie) as lotepro,
                  LEFT(embalado.fecha,20) as fecha
                  FROM embalado
                  WHERE ID_Grupo=".$mdb2->quote($lote_embalado,'integer')." order by embalado.serie";

        $res =& $mdb2->query($query);

        if (PEAR::isError($res)) {
                    die($res->getMessage());
        }
        $rows = $res->fetchAll(MDB2_FETCHMODE_ASSOC);
        require_once 'include/pear/Sigma.php'; //insertamos la libreria
        $it = new HTML_Template_Sigma('themes'); //declaramos el objeto
        $it->loadTemplatefile('lote_embalado_fabrica.html', true, true); //seleccionamos la plantilla

        foreach($rows as $name) {
            // Assign data to the inner block
                $it->setCurrentBlock("LOTEEM");
                $it->setVariable("N_SERIE", $name['serie']);
                $it->setVariable("LOTE_PRODUCCION", $name['lotepro']);
                $it->setVariable("FECHA", $name['fecha']);
                $it->parseCurrentBlock("LOTEEM");
            $it->parse("row_lemba");
        }
       $it->show(); 
    }
}

function buscar_tabla_probatuti($ncelda){
    print "TABLA PROBATUTI DE CELDA: ".$ncelda;

    if (!is_numeric($ncelda)){
            print "Dato de tipo NO VALIDO";
    }else{
        require_once('dbinfo.php');
        require_once('MDB2.php');

        // Conecto a DB Flexar
        $mdb2 =& MDB2::singleton($dsn, $options);
        if (PEAR::isError($mdb2)) {
                 die($mdb2->getMessage());
        }
       $query = "SELECT opera.Operacion AS Area, op.Nombre, op.Apellido, prob.MedTerminada AS MedTer, 
                 prob.impsalida AS ImpSa, prob.impentrada AS ImpEn, prob.tenssalida AS TensSa,
                 prob.dircarga AS DirCarga, prob.aiscuerpo AS AislaCuore, LEFT(prob.fecha, 11) AS Fecha 

                 FROM Probatuti prob, Operaciones opera, Operarios op 

                 WHERE prob.Area=opera.IdOperacion AND prob.operador=op.OperProba AND prob.serie=$ncelda 

                 ORDER BY prob.fecha, prob.Area";

        }

}
            

?>
