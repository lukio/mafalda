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

        $query = "select lote, left(imprb,6), left(impsg,6), left(imprs,6)
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

        $query = "SELECT round(ENSAYOS.RANGO_FIN,4), LEFT(ENSAYOS.FECHA,11), ENSAYOS.VSC_INI, ENSAYOS.VSC_FIN, ENSAYOS.GOLPES, ENSAYOS.ESPEC 
                  FROM ENSAYOS 
                  WHERE NRO_SERIE=".$mdb2->quote($ncelda,'integer')."";

        //tomo resultados de ensayos 
        $res_ensayos = $mdb2->queryAll($query);

        if (PEAR::isError($res_ensayos)) {
                    die($res_ensayos->getMessage());
        }

       
        $query = "SELECT DataHorno.Cero, left(DataHorno.Pendiente,6), left(DataHorno.R2,6), left(DataHorno.H,6), DataHorno.Horno, LEFT(HornoResumen.Fecha, 11)
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

        print "NUMERO DE SERIE: ".$ncelda."<br />";

        // Enlaces Tabla Probatuti y num ot por lote
        $it->setCurrentBlock("LINKS");
        $it->setVariable("N_SERIE", $ncelda);
        $it->setVariable("LOTEPRO", $res_impedancias['0']);
        $it->parseCurrentBlock("LINKS");
        
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
        $it->setVariable("SR", round($sensi_real,4));
        $it->setVariable("DEP", round($desv_est_porce,4));
        $it->setVariable("TS", $tolsens);
        $it->setVariable("CN", $capnom);
        $it->parseCurrentBlock("ESTADISTICA");
        


        $it->show();
    }

}

function buscar_lote_produccion($lote_produccion){
    require_once('dbinfo.php');
    require_once('MDB2.php');

    if (!is_numeric($lote_produccion)){
            print "Dato de tipo NO VALIDO";
    }else{
        print "LOTE PRODUCCION: ".$lote_produccion."<br />";


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

        // Enlaces Tabla Probatuti y num ot por lote
        $it->setCurrentBlock("LINKS");
        $it->setVariable("LOTEPRO", $lote_produccion);
        $it->parseCurrentBlock("LINKS");
        
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
        print "LOTE EMBALADO: ".$lote_embalado."<br />";

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

    if (!is_numeric($ncelda)){
            print "Dato de tipo NO VALIDO";
    }else{
        require_once('dbinfo.php');
        require_once('MDB2.php');
        print "TABLA PROBATUTI DE CELDA: ".$ncelda."<br />";

        // Conecto a DB Flexar
        $mdb2 =& MDB2::singleton($dsn, $options);
        if (PEAR::isError($mdb2)) {
                 die($mdb2->getMessage());
        }
       $query = "SELECT opera.Operacion, op.Nombre, op.Apellido, prob.MedTerminada, 
                 prob.impsalida, prob.impentrada, prob.tenssalida,
                 prob.dircarga, prob.aiscuerpo, LEFT(prob.fecha, 11) AS Fecha 

                 FROM Probatuti prob, Operaciones opera, Operarios op 

                 WHERE prob.Area=opera.IdOperacion AND prob.operador=op.OperProba AND prob.serie=$ncelda 

                 ORDER BY prob.fecha, prob.Area";

        $res =& $mdb2->query($query);

        if (PEAR::isError($res)) {
                    die($res->getMessage());
        }
        $rows = $res->fetchAll();
        require_once 'include/pear/Sigma.php'; //insertamos la libreria
        $it = new HTML_Template_Sigma('themes'); //declaramos el objeto
        $it->loadTemplatefile('tabla_probatuti_fabrica.html', true, true); //seleccionamos la plantilla

        foreach($rows as $name) {
            // Assign data to the inner block
            foreach($name as $cell) {
                $it->setCurrentBlock("TABLA_PROBA");
                $it->setVariable("DATO", $cell);
                $it->parseCurrentBlock("TABLA_PROBA");
            }
            $it->parse("row_proba");
        }
       $it->show(); 


        }

}

function buscar_ot_por_lote($lote_produccion){ 


    if (!is_numeric($lote_produccion)){
            print "Dato de tipo NO VALIDO";
    }else{
        require_once('dbinfo.php');
        require_once('MDB2.php');

        print "LOTE PRODUCCION BUSCADO: ".$lote_produccion."<br />";

        // Conecto a DB Flexar
        $mdb2 =& MDB2::singleton($dsn, $options);
        if (PEAR::isError($mdb2)) {
                 die($mdb2->getMessage());
        }

        $query = "select do.nroord, do.cantidad, 
        (select operaciones.operacion from operaciones
        where operaciones.idoperacion=ot.operacion) as area,
        (select operarios.nombre from operarios 
        where operarios.idoperario=ot.operario) as nombre,
        (select operarios.apellido from operarios 
        where operarios.idoperario=ot.operario) as apellido,
        left(ot.fechainicio,11) as fechainicio, left(do.fechadeterminacion,11) as fechadeterminacion, ot.comentarios, ot.observaciones
        from datosorden do, ordenesdetrabajo ot
        where do.lote='$lote_produccion' and do.nroord=ot.nroorden and ot.terminada='1'
        and ot.anulada='0' order by ot.nroorden";

        $res =& $mdb2->query($query);

        if (PEAR::isError($res)) {
                    die($res->getMessage());
        }
        $rows = $res->fetchAll(MDB2_FETCHMODE_ASSOC);
        require_once 'include/pear/Sigma.php'; //insertamos la libreria
        $it = new HTML_Template_Sigma('themes'); //declaramos el objeto
        $it->loadTemplatefile('ot_por_lote_fabrica.html', true, true); //seleccionamos la plantilla


        foreach($rows as $name) {
            // Assign data to the inner block
                $it->setCurrentBlock("OTL");
                $it->setVariable("NROORDEN", $name['nroord']);
                $it->setVariable("CANTIDAD", $name['cantidad']);
                $it->setVariable("AREA", $name['area']);
                $it->setVariable("NOMBRE", $name['nombre']);
                $it->setVariable("APELLIDO", $name['apellido']);
                $it->setVariable("FECHA_INI", $name['fechainicio']);
                $it->setVariable("FECHA_FIN", $name['fechadeterminacion']);
                $it->setVariable("COME", $name['comentarios']);
                $it->setVariable("OBS", $name['observaciones']);
                $it->parseCurrentBlock("OTL");
            $it->parse("row_ot");
        }
       $it->show(); 
    }
}
       
function buscar_p_orden_trabajo($nroorden){ 

    if (!is_numeric($nroorden)){
            print "Dato de tipo NO VALIDO";
    }else{
        require_once('dbinfo.php');
        require_once('MDB2.php');

        print "NRO ORDEN BUSCADO: ".$nroorden."<br />";

        // Conecto a DB Flexar
        $mdb2 =& MDB2::singleton($dsn, $options);
        if (PEAR::isError($mdb2)) {
                 die($mdb2->getMessage());
        }

        $query = "select distinct ot.terminada, ot.anulada,
                (select operaciones.operacion from operaciones
                where operaciones.idoperacion=ot.operacion) as area,
                (select operarios.nombre from operarios 
                where operarios.idoperario=ot.operario) as nombre,
                (select operarios.apellido from operarios 
                where operarios.idoperario=ot.operario) as apellido,
                left(ot.fechainicio,11) as fechainicio, left(do.fechadeterminacion,11) as fechadeterminacion
                from ordenesdetrabajo ot, datosorden do
                where ot.nroorden='$nroorden' and do.nroord='$nroorden'
                and ot.anulada<>'1'";
        $res =& $mdb2->query($query);

        if (PEAR::isError($res)) {
                    die($res->getMessage());
        }
        $rows = $res->fetchAll(MDB2_FETCHMODE_ASSOC);

        $query = "select do.lote, do.cantidad, lo.modelo
                 from ordenesdetrabajo ot, datosorden do, lotes lo
                 where ot.nroorden='$nroorden' and do.nroord='$nroorden'
                 and ot.anulada<>'1' and do.lote=lo.lote order by do.lote";

        $res =& $mdb2->query($query);

        if (PEAR::isError($res)) {
                    die($res->getMessage());
        }
        $rows_1 = $res->fetchAll(MDB2_FETCHMODE_ASSOC);

        require_once 'include/pear/Sigma.php'; //insertamos la libreria
        $it = new HTML_Template_Sigma('themes'); //declaramos el objeto
        $it->loadTemplatefile('p_orden_fabrica.html', true, true); //seleccionamos la plantilla

        foreach($rows as $name) {
            // Assign data to the inner block
                $it->setCurrentBlock("OTL");
                $it->setVariable("NOMBRE", $name['nombre']);
                $it->setVariable("APELLIDO", $name['apellido']);
                $it->setVariable("AREA", $name['area']);
                $it->setVariable("TERMINO", $name['terminada']);
                $it->setVariable("FECHA_INI", $name['fechainicio']);
                $it->setVariable("FECHA_FIN", $name['fechadeterminacion']);
                $it->parseCurrentBlock("OTL");
            $it->parse("row_ot");
        }

        foreach($rows_1 as $name) {
            // Assign data to the inner block
                $it->setCurrentBlock("OT_L");
                $it->setVariable("LOTE", $name['lote']);
                $it->setVariable("MODELO", $name['modelo']);
                $it->setVariable("CANTIDAD", $name['cantidad']);
                $it->parseCurrentBlock("OT_L");
            $it->parse("row_ot_l");
//                $it->setVariable("CANTIDAD", $name['cantidad']);
        }
       $it->show(); 
    }
}
            
function buscar_orden_mecanizado($nro_ordenmecanizado){ 


    if (!is_numeric($nro_ordenmecanizado)){
            print "Dato de tipo NO VALIDO";
    }else{
        require_once('dbinfo.php');
        require_once('MDB2.php');

        print "NRO ORDEN MECANIZADO: ".$nro_ordenmecanizado."<br />";

        // Conecto a DB Flexar
        $mdb2 =& MDB2::singleton($dsn, $options);
        if (PEAR::isError($mdb2)) {
                 die($mdb2->getMessage());
        }
        
        $query = "SELECT lo.lote, lo.modelo, lo.cantidad, LEFT(lo.Fecha,11) as fecha
                  FROM Lotes lo WHERE OCMecanizado=$nro_ordenmecanizado
                  ORDER BY lo.Fecha";

        $res =& $mdb2->query($query);

        if (PEAR::isError($res)) {
                    die($res->getMessage());
        }
        $rows = $res->fetchAll(MDB2_FETCHMODE_ASSOC);
  //      die (print_r($rows));

        require_once ('include/pear/Sigma.php'); //insertamos la libreria
        $it = new HTML_Template_Sigma('themes'); //declaramos el objeto
        $it->loadTemplatefile('om_fabrica.html', true, true); //seleccionamos la plantilla

        foreach($rows as $name) {
            // Assign data to the inner block
                $it->setCurrentBlock("OTL_");
                $it->setVariable("LOTE", $name['lote']);
                $it->setVariable("MODELO", $name['modelo']);
                $it->setVariable("CANTIDAD", $name['cantidad']);
                $it->setVariable("FECHA", $name['fecha']);
                
                $it->parseCurrentBlock("OTL_");
                $it->parse("row_ot_");
                
                
        }
       $it->show(); 
    }

}

?>
