<?php

/**
 * Funciones para hacer la exportacion
 * Busquedas para nivel de fabrica. 
 * Funciones declaradas:
 buscar_nserie_csv();
 buscar_lote_embalado();
 buscar_lote_produccion();
 * */
$funcion = $_GET['f'];
$dato = $_GET['q'];
$separador = ",";

switch ($funcion){
    case 'buscar_nserie_csv':buscar_nserie_csv($dato, $separador); break;
    case 'buscar_tabla_probatuti_csv':buscar_tabla_probatuti_csv($dato, $separador); break;
    case 'buscar_ot_por_lote_csv':buscar_ot_por_lote_csv($dato, $separador); break;
    case 'buscar_lote_embalado_csv':buscar_lote_embalado_csv($dato, $separador); break;
    case 'buscar_lote_produccion_csv':buscar_lote_produccion_csv($dato, $separador); break;
}



function buscar_nserie_csv($ncelda, $separador){

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

        $query = "SELECT round(ENSAYOS.RANGO_FIN,2), LEFT(ENSAYOS.FECHA,11), ENSAYOS.VSC_INI, ENSAYOS.VSC_FIN, ENSAYOS.GOLPES, ENSAYOS.ESPEC 
                  FROM ENSAYOS 
                  WHERE NRO_SERIE=".$mdb2->quote($ncelda,'integer')."";

        //tomo resultados de ensayos 
        $res_ensayos = $mdb2->queryAll($query);

        if (PEAR::isError($res_ensayos)) {
                    die($res_ensayos->getMessage());
        }

       
        $query = "SELECT DataHorno.Cero, ROUND(DataHorno.Pendiente,3), ROUND(DataHorno.R2,3), ROUND(DataHorno.H,3), DataHorno.Horno, LEFT(HornoResumen.Fecha, 11)
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
        $sensi_real = round($sensi_real,4);
        $desv_est_porce= round($desv_est_porce,4);

        /* Impresion
         * archivo template: fabrica.html
        */ 
        /*
        require_once 'include/pear/Sigma.php'; //insertamos la libreria
        $it = new HTML_Template_Sigma('themes'); //declaramos el objeto
        $it->loadTemplatefile('fabrica.html', true, true); //seleccionamos la plantilla
        */
        $separador_texto =""; 
        $csv_file = "NUMERO DE SERIE: ".$ncelda."\n";

        // Tabla de Ensayos
        $csv_file .="\nMaquina de Ensayos\n";
        $csv_file .="rango fin".$separador."fecha ensayo".$separador."cero inicial".$separador."cero final".$separador."golpe".$separador."espec\n";

        foreach($res_ensayos as $name) {
            // Assign data to the inner block
            foreach($name as $cell) {
                $csv_file .="\"$cell\"".$separador;
            }
            $csv_file .="\n";
        }
        $csv_file.="\nMaquina HORNO\n";
        

        $csv_file .="cero horno".$separador."pendiente".$separador."r2".$separador."H".$separador."Hor".$separador."fecha horno\n";
        // Tabla de Maquina Horno
        foreach($res_horno as $name) {
            // Assign data to the inner block
            foreach($name as $cell) {
                $csv_file .="\"$cell\"".$separador;
            }
            $csv_file .="\n";
        }

        $csv_file .="\nDatos generales\n";
        $csv_file .="Modelo".$separador."Lote produccion".$separador."Lote embalado".$separador."Area".$separador."Orden meca".$separador."Orden meca-mp".$separador."Fecha pegado\n";
        // Tabla Datos generales
        $csv_file .="\"$res_lotes[0]\"".$separador;

        $csv_file .="\"$res_impedancias[0]\"".$separador;
        $csv_file .="\"$lote_emba\"".$separador;
        $csv_file .="\"$res_lotes[4]\"".$separador;
        $csv_file .="\"$res_lotes[3]\"".$separador;
        $csv_file .="link tango;";
        $csv_file .="\"$res_lotes[5]\"".$separador;
        $csv_file .="\n"; 
        // Tabla Datos generales

        $csv_file .="\n\"Datos msg etc...\"\n";
        $csv_file .="MSG".$separador."MRB".$separador."Impe RB".$separador."Impe SG".$separador."Impe RS\n";

        $csv_file .="\"$res_lotes[1]\"".$separador;
        $csv_file .="\"$res_lotes[2]\"".$separador;
        $csv_file .="\"$res_impedancias[1]\"".$separador;
        $csv_file .="\"$res_impedancias[2]\"".$separador;
        $csv_file .="\"$res_impedancias[3]\"".$separador;
        $csv_file .="\n";

        $csv_file .="\n\"Tabla Datos estadistica\"\n";
        $csv_file .="sensibilidad real".$separador."desviacion estandar porcentual".$separador."tolerancia_sens".$separador."cap_nominal\n";
        $csv_file .="\"$sensi_real\"".$separador;
        $csv_file .="\"$desv_est_porce\"".$separador;
        $csv_file .="\"$tolsens\"".$separador;
        $csv_file .="\"$capnom\"".$separador;
        $csv_file .="\n"; 

        Header("Content-Description: File Transfer");
        header("Content-Type: application/force-download");
        header("Content-Disposition: attachment; filename=exportar_serie_".$ncelda.".csv");
        $csv_file = str_replace(".",",",$csv_file);
        echo $csv_file;
            
    }
    

}

function buscar_tabla_probatuti_csv($ncelda, $separador){

    if (!is_numeric($ncelda)){
            print "Dato de tipo NO VALIDO";
    }else{
        require_once('dbinfo.php');
        require_once('MDB2.php');
        //print "TABLA PROBATUTI DE CELDA: ".$ncelda."<br />";

        // Conecto a DB Flexar
        $mdb2 =& MDB2::singleton($dsn, $options);
        if (PEAR::isError($mdb2)) {
                 die($mdb2->getMessage());
        }
       $query = "SELECT opera.Operacion, op.Nombre, op.Apellido, prob.MedTerminada, 
                 round(prob.impsalida,3), round(prob.impentrada,3), round(prob.tenssalida,3),
                 prob.dircarga, prob.aiscuerpo, LEFT(prob.fecha, 11) AS Fecha 

                 FROM Probatuti prob, Operaciones opera, Operarios op 

                 WHERE prob.Area=opera.IdOperacion AND prob.operador=op.OperProba AND prob.serie=$ncelda 

                 ORDER BY prob.fecha, prob.Area";

        $res =& $mdb2->query($query);

        if (PEAR::isError($res)) {
                    die($res->getMessage());
        }
        $rows = $res->fetchAll();
        $separador_texto =""; 

        $csv_file = "\"TABLA PROBATUTI DE CELDA:\"".$ncelda."\n";
        $csv_file .= "\narea".$separador."nombre".$separador."apellido".$separador."medter".$separador."imp-sal".$separador."imp-ent".$separador."tensal".$separador."dircarga".$separador."A-cuerpo".$separador."fecha\n";

        foreach($rows as $name) {
            // Assign data to the inner block
            foreach($name as $cell) {
                $csv_file .="\"$cell\"".$separador;
            }
            $csv_file .="\n";
        }
       
        Header("Content-Description: File Transfer");
        header("Content-Type: application/force-download");
        header("Content-Disposition: attachment; filename=exportar_tabla_proba_".$ncelda.".csv");
        $csv_file = str_replace(".",",",$csv_file);
        echo $csv_file;

        }

}

function buscar_ot_por_lote_csv($lote_produccion, $separador){ 


    if (!is_numeric($lote_produccion)){
            print "Dato de tipo NO VALIDO";
    }else{
        require_once('dbinfo.php');
        require_once('MDB2.php');

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

        $separador_texto =""; 

        // Enlaces Tabla Probatuti y num ot por lote
        $csv_file = "OT POR LOTE DE PRO:".$lote_produccion;
        $csv_file .="\nnro. orden".$separador."cantidad".$separador."area".$separador."nombre".$separador."apellido".$separador."fecha inicio".$separador."fecha term".$separador."coments".$separador."obs\n";


        foreach($rows as $name) {
                $nombre_utf8 = utf8_encode($name['nombre']);
                $apellido_utf8 = utf8_encode($name['apellido']);
            // Assign data to the inner block
                $csv_file .="\"$name[nroord]\"".$separador;
                $csv_file .="\"$name[cantidad]\"".$separador;
                $csv_file .="\"$name[area]\"".$separador;
                $csv_file .="\"$nombre_utf8\"".$separador;
                $csv_file .="\"$apellido_utf8\"".$separador;
                $csv_file .="\"$name[fechainicio]\"".$separador;
                $csv_file .="\"$name[fechadeterminacion]\"".$separador;
                $csv_file .="\"$name[comentarios]\"".$separador;
                $csv_file .="\"$name[observaciones]\"".$separador;
            $csv_file .="\n";
        }
       
        Header("Content-Description: File Transfer");
        header("Content-Type: application/force-download");
        header("Content-Disposition: attachment; filename=exportar_ot_por_lote_".$lote_produccion.".csv");
        echo $csv_file;
    }
}

function buscar_lote_embalado_csv($lote_embalado, $separador){


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
                  LEFT(embalado.fecha,11) as fecha
                  FROM embalado
                  WHERE ID_Grupo=".$mdb2->quote($lote_embalado,'integer')." order by embalado.serie";

        $res =& $mdb2->query($query);

        if (PEAR::isError($res)) {
                    die($res->getMessage());
        }
        $rows = $res->fetchAll(MDB2_FETCHMODE_ASSOC);

        // Enlaces Tabla Probatuti y num ot por lote
        $separador_texto =""; 
        $csv_file = "LOTE EMBALADO: ".$lote_embalado."\n";
        $csv_file .="\nnumero serie".$separador."lote produccion".$separador."fecha embalado\n";
                
        foreach($rows as $name) {
            // Assign data to the inner block
                $csv_file .="\"$name[serie]\"".$separador;
                $csv_file .="\"$name[lotepro]\"".$separador;
                $csv_file .="\"$name[fecha]\"".$separador;
            $csv_file .="\n";
        }

        Header("Content-Description: File Transfer");
        header("Content-Type: application/force-download");
        header("Content-Disposition: attachment; filename=exportar_lote_embalado_".$lote_embalado.".csv");
        echo $csv_file;

    }
}

function buscar_lote_produccion_csv($lote_produccion, $separador){
    require_once('dbinfo.php');
    require_once('MDB2.php');

    if (!is_numeric($lote_produccion)){
            print "Dato de tipo NO VALIDO";
    }else{


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
        /*
        require_once 'include/pear/Sigma.php'; //insertamos la libreria
        $it = new HTML_Template_Sigma('themes'); //declaramos el objeto
        $it->loadTemplatefile('lote_produccion_fabrica.html', true, true); //seleccionamos la plantilla
        */
        // Enlaces Tabla Probatuti y num ot por lote
        $separador_texto =""; 
        $csv = "LOTE PRODUCCION: ".$lote_produccion."\n";
        $csv_file .="\nmodelo".$separador."merma".$separador."fecha".$separador."tol sensibilidad".$separador."tol cero".$separador."imp salida".$separador."imp entrada\n";

        // Datos varios del Lote
        foreach($row_header as $name) {
                $csv_file .="\"$name\"".$separador;
        }
        $csv_file .="\n";
        // Datos de los numero de serie de ese Lote
        $csv_file .="\nnumero serie".$separador."imp sg".$separador."imp rb".$separador."imp rs\n";
        foreach($row_impedancias as $name) {
            // Assign data to the inner block
            foreach($name as $cell) {
                $cell = utf8_encode($cell);
                $csv_file .="\"$cell\"".$separador;
            }
            $csv_file .="\n";
        }
        Header("Content-Description: File Transfer");
        header("Content-Type: application/force-download");
        header("Content-Disposition: attachment; filename=exportar_lote_produccion_".$lote_produccion.".csv");
        $csv_file = str_replace(".",",",$csv_file);
        echo $csv_file;

    }

}

?>
