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

switch ($funcion){
    case 'buscar_nserie_csv':buscar_nserie_csv($dato); break;
}



function buscar_nserie_csv($ncelda){

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
        */ 

/*        require_once 'include/pear/Sigma.php'; //insertamos la libreria
        $it = new HTML_Template_Sigma('themes'); //declaramos el objeto
        $it->loadTemplatefile('fabrica.html', true, true); //seleccionamos la plantilla
*/
        $csv_file = "NUMERO DE SERIE: ".$ncelda."\n";

        // Tabla de Ensayos
        $csv_file .="\nMaquina de Ensayos\n";
        $csv_file .="rango fin;fecha ensayo;cero inicial;cero final;golpe;espec\n";

        foreach($res_ensayos as $name) {
            // Assign data to the inner block
            foreach($name as $cell) {
                $csv_file .=$cell.";";
            }
            $csv_file .="\n";
        }
        $csv_file.="\nMaquina HORNO\n";
        

        $csv_file .="cero horno;pendiente;r2;H;Hor;fecha horno\n";
        // Tabla de Maquina Horno
        foreach($res_horno as $name) {
            // Assign data to the inner block
            foreach($name as $cell) {
                $csv_file .=$cell.";";
            }
            $csv_file .="\n";
        }

        $csv_file .="\nDatos generales\n";
        $csv_file .="Modelo;Lote produccion;Lote embalado;Area;Orden meca;Orden meca-mp;Fecha pegado\n";
        // Tabla Datos generales
        $csv_file .=$res_lotes['0'].";";
        $csv_file .=$res_impedancias['0'].";";
        $csv_file .=$lote_emba.";";
        $csv_file .=$res_lotes['4'].";";
        $csv_file .=$res_lotes['3'].";";
        $csv_file .="link tango;";
        $csv_file .=$res_lotes['5'].";";
        $csv_file .="\n"; 
        // Tabla Datos generales

        $csv_file .="\nDatos msg, etc...\n";
        $csv_file .="MSG;MRB;Impe RB;Impe SG;Impe RS\n";

        $csv_file .=$res_lotes['1'].";";
        $csv_file .=$res_lotes['2'].";";
        $csv_file .=$res_impedancias['1'].";";
        $csv_file .=$res_impedancias['2'].";";
        $csv_file .=$res_impedancias['3'].";";
        $csv_file .="\n";

        $csv_file .="\nTabla Datos estadistica\n";
        $csv_file .="sensibilidad real;desviacion estandar porcentual;tolerancia sens.;cap. nominal\n";
        $csv_file .=round($sensi_real,4).";";
        $csv_file .=round($desv_est_porce,4).";";
        $csv_file .=$tolsens.";";
        $csv_file .=$capnom.";";
        $csv_file .="\n"; 

        Header("Content-Description: File Transfer");
        header("Content-Type: application/force-download");
        header("Content-Disposition: attachment; filename=exportar.csv");
        echo $csv_file;
    }

}

?>
