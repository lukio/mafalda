<?php

if (isset($_POST['bajamodelo'])){
    procesa('baja');
}


function cual_action ($action){
    /* Depende de que action nos llega hacemos:
    * alta, baja, modificacion, procesa. 
    */

    $action = explode (':', $action); // tomo la accion de procesa

    switch($action[0]){
        case "alta": alta(); break;
        case "baja": baja(); break;
        case "modificacion": modificacion(); break;
        case "procesa": procesa($action[1]); break;
        default: print "No existe tal acción"; 
    }
}

function imprimirfila_alta($data, $nombre_bloque, $it){
/*
 * Imprime una fila de 3 campos para armar la tabla de altas
*/

    for($i=0 ; $i < count($data);) {

        $it->setCurrentBlock($nombre_bloque);
        $it->setVariable('DATO', $data[$i++]);
        $it->setVariable('TYPE_DATO', $data[$i++]);
        $it->setVariable('NAME_DATO',$data[$i++]);
        $it->setVariable('SIZE_DATO',$data[$i++]);
        $it->setVariable('ID_DATO',$data[$i++]);
        $it->parseCurrentBlock("'$nombre_bloque");
    }
}

function alta(){

    require_once 'include/pear/Sigma.php'; //insertamos la libreria
    $it = new HTML_Template_Sigma('themes'); //declaramos el objeto
    $it->loadTemplatefile('abm.html'); //seleccionamos la plantilla

    $data_1 = array (
            "Sensibilidad: ", "text", "1", "10", "sensibilidad_id",
            "Impedancia: ", "text", "2", "10", "impedancia_id",
            "Grupo Corr Horno: ", "text", "3", "10", "grupocorrhorno_id",
        );
    $data_2 = array (
            "Imp. Entrada: ", "text", "4", "10", "sensibilidad_id",
            "Imp. Salida: ", "text", "5", "10", "impedancia_id",
            "Tol. Imp. Entrada: ", "text", "6", "10", "grupocorrhorno_id",
        );

    $data_3 = array (
            "Tol. Imp. Salida: ", "text", "7", "10", "sensibilidad_id",
            "Cero: ", "text", "8", "10", "impedancia_id",
            "Tol. Cero: ", "text", "9", "10", "grupocorrhorno_id",
        );
    $data_4 = array (
            "Tol. Sensibilidad: ", "text", "10", "10", "sensibilidad_id",
            "Capacidad Nominal: ", "text", "11", "10", "impedancia_id",
            "Ruta: ", "text", "12", "10", "grupocorrhorno_id",
        );
    $data_5 = array (
            "Alineacion: ", "text", "13", "10", "sensibilidad_id",
            "Histeresis: ", "text", "14", "10", "impedancia_id",
            "Rep: ", "text", "15", "10", "grupocorrhorno_id",
        );
    $data_6 = array (
            "Creep: ", "text", "16", "10", "sensibilidad_id",
            "Correccion Cero Temp.: ", "text", "17", "10", "impedancia_id",
            "Correccion Span Temp.: ", "text", "18", "10", "grupocorrhorno_id",
        );
    $data_7 = array (
            "V Max. Alim: ", "text", "19", "10", "sensibilidad_id",
            "Rango Temp.: ", "text", "20", "10", "impedancia_id",
            "Sobrecarga: ", "text", "21", "10", "grupocorrhorno_id",
        );
    $data_8 = array (
            "Limite Rot: ", "text", "22", "10", "sensibilidad_id",
            "Cable: ", "text", "23", "10", "impedancia_id",
            "Tol. R2: ", "text", "24", "10", "grupocorrhorno_id",
        );
    $data_9 = array (
            "Tol. Pendiente Horno: ", "text", "25", "10", "sensibilidad_id",
            "Tol. H: ", "text", "26", "10", "impedancia_id",
            "Cantidad Por Lote: ", "text", "27", "10", "grupocorrhorno_id",
        );
    $data_10 = array (
            "pSg: ", "text", "28", "10", "sensibilidad_id",
            "Cantidad Sg: ", "text", "29", "10", "impedancia_id",
            "pRb: ", "text", "30", "10", "grupocorrhorno_id",
        );
    $data_11 = array (
            "Cant Rb: ", "text", "31", "10", "sensibilidad_id",
            "pPrensa: ", "text", "32", "10", "impedancia_id",
            "pCablea: ", "text", "33", "10", "grupocorrhorno_id",
        );
    $data_12 = array (
            "pArnes: ", "text", "34", "10", "sensibilidad_id",
            "DeltaRb: ", "text", "35", "10", "impedancia_id",
            "Etiqueta: ", "text", "36", "10", "grupocorrhorno_id",
        );

/** Nose porque. Pero no funciona si escribo el form con el template
        $it->setCurrentBlock("FORM");
        $it->setVariable('MODULO', 'abm_modelos');
        $it->setVariable('ACTION', 'alta');
        $it->parseCurrentBlock("FORM");
*/
        imprimirfila_alta($data_1, "1", $it);
        imprimirfila_alta($data_2, "2", $it);
        imprimirfila_alta($data_3, "3", $it);
        imprimirfila_alta($data_4, "4", $it);
        imprimirfila_alta($data_5, "5", $it);
        imprimirfila_alta($data_6, "6", $it);
        imprimirfila_alta($data_7, "7", $it);
        imprimirfila_alta($data_8, "8", $it);
        imprimirfila_alta($data_9, "9", $it);
        imprimirfila_alta($data_10, "10", $it);
        imprimirfila_alta($data_11, "11", $it);
        imprimirfila_alta($data_12, "12", $it);

        $it->setCurrentBlock("FIN_FORM");
        $it->setVariable('TIPO',"submit");
        $it->setVariable('NOMBRE',"Submit");
        $it->setVariable('VALUE',"Cargar Modelo");
        $it->parseCurrentBlock("FIN_FORM");
    
    $it->show();

}
function baja(){

    require_once 'include/pear/Sigma.php'; //insertamos la libreria
    $it = new HTML_Template_Sigma('themes'); //declaramos el objeto
    $it->loadTemplatefile('abm_baja.html'); //seleccionamos la plantilla
    
    $select_baja = array ("CD", "CD-10", "CD-20");

    for($i=0 ; $i < count($select_baja); ) {
    $it->setCurrentBlock("MODELOS");
    $it->setVariable('MODELO',$select_baja[$i++]);
    $it->parseCurrentBlock("MODELOS");
    }
    $it->show();
}

function modificacion(){
    print "1º MOSTRAR MODELOS A MODIFICAR 2º  MOSTRAR DATOS DEL MODELO A MODIFICAR";
}

function procesa($action){
    $paso_validacion = 1;

    switch($action){
        case "alta": $paso_validacion = validar_datos($action); break;
        case "baja": borra_modelo(); break;
        case "modificacion": modificacion(); break;
        default: print "No existe tal acción"; 

    if (!$paso_validacion){
    /* Cargo la Alta de nuevo */
    // Hago la conexion. Sin usar globales, por favor :)
    //    global $id_db_flexar;
        carga_modelo();



     }
    }
}

function validar_datos($action){

    if ( $action == "alta"){
        require_once('include/validaciones.php');
        
        $flag_error = 0;
        for ($i=1; $i < 35; $i++){
        if (!is_numeric($_GET[$i])){
                $flag_error++;
            }
        }
/*
        if ( !(trim($_GET[40])=='Etiqueta1' || trim($_GET[40])=='Etiqueta2') ){
                $flag_error++;
            }
        if ( !is_modelo(trim($_GET['modelo_nuevo']))) {
                $flag_error++;
            }
*/
//    $error = "<h5>Hubo un error en alguno de los campos. Vuelva a cargar<h5>";
    $error = "<html><head><script language='Javascript'>
                function cargarindex(){
                    setTimeout(\"location.replace('index.php')\",2000);
                }
                </script>
            </head>
            <body onload='cargarindex()'>
                <div align='center'>
                    <h3>Alguno de los campos tienen datos de tipo NO valido. Vuelva a cargar el Alta</h3>
                </div>
            </body></html>";


    if ($flag_error > 0){
    /*  require_once 'include/pear/Sigma.php'; //insertamos la libreria
        $it = new HTML_Template_Sigma('themes'); //declaramos el objeto
        $it->loadTemplatefile('gerencia.html'); //seleccionamos la plantilla
        $it->setCurrentBlock("ERROR");
        $it->setVariable('DATO',$error);
        $it->parseCurrentBlock("ERROR");
        $it->show();
    */
    print $error;
    }
    return 0;


    }
}

function borra_modelo(){
    $bajamodelo = $_POST['bajamodelo'];
    unset($_POST['bajamodelo']);

    require_once '../include/pear/Sigma.php'; //insertamos la libreria
    $it = new HTML_Template_Sigma('../themes'); //declaramos el objeto
    $it->loadTemplatefile('baja_gerencia.html'); //seleccionamos la plantilla
    print "borra modelo".$bajamodelo;
    $it->show();

}


function carga_modelo(){

    $array_campos_insert = array();
    $array_values = array();

    $array_nombre_campos = array(
                                '',
                                'Apareo', 
                                'CarLat', 
                                'Certificado',
                                'Chequeo',
                                'Sensibilidad',
                                'Impedancia',
                                'GrupoCorrHorno',
                                'ImpEnt',
                                'ImpSal',
                                '[Tol ImpEnt]',
                                'TolImpSal',
                                'Cero',
                                'TolCero',
                                'TolSens',
                                'CapNominal',
                                'Ruta',
                                'Alin',
                                'Hister',
                                'Rep',
                                'Creep',
                                'CorCeroTemp',
                                'CorSpanTemp', 
                                'VMaxAlim', 
                                'RangTemp',
                                'Sobrecarga',
                                'LimRot', 
                                'Cable', 
                                'TolR2', 
                                'TolPendHorno',
                                'TolH',
                                'CantPorLote',
                                'pSg',
                                'CantSg',
                                'pRb',
                                'CantRb',
                                'pPrensa',
                                'pCable',
                                'pArnes',
                                'DeltaRb', 
                                'Etiqueta'
                            );


    $query = "SELECT Modelos.Modelo FROM Modelos";

    $q = $id_db_flexar -> query($query);
    while ($row_flexar = $q -> fetchRow()){
        if ( strtoupper($_GET['modelo_nuevo'])== strtoupper($row_flexar[0])){
            impresion_error("El Modelo que desea ingresar ya existe");
            exit();
        }
    }
    $q -> free();

    checkbox_to_SQL(); //paso el valor check a valores true o false
    $_GET['modelo_nuevo'] = strtoupper($_GET['modelo_nuevo']); 

    // tengo un array con el nombre de todos los campos. 
    // contador para el array = j 
    for ($i = 1, $j=0; $i < 40; $i++)
        if (!empty($_GET[$i])){
            $array_campos_insert[$j] = $array_nombre_campos[$i];
            $array_values[$j] = $_GET[$i];
            $array_interrogacion[$j++]='?';
        }
    
        $modelonuevo = $_GET['modelo_nuevo'];
        $etiqueta = $_GET['40'];

        // Armamos el query usando el implode
        $query = "INSERT INTO Modelos (Modelo, ".implode(', ',$array_campos_insert).", Etiqueta) VALUES ('".$modelonuevo."', ".implode(', ', $array_values).", '".$etiqueta."')";

        // Hacemos el insert
        $id_db_flexar -> query($query);

}

function checkbox_to_SQL(){
        for ($i = 1; $i < 5; $i++)
            if($_GET[$i] == "on") $_GET[$i] = 'true'; else $_GET[$i] = 'false';
}

function limpiarGet(){

    unset($_GET['modelo_nuevo']);
    for ($i = 0; $i < 41; $i++)
        unset($_GET[$i]);
}



?>
