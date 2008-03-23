<?php

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
        case "borrarmodelo": borra_modelo($action[1]); break;
        case "modificamodelo":modifica_modelo($action[1]); break;
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
    $data_13 = array (
            "Apareo: ", "checkbox", "37", "10", "sensibilidad_id",
            "Car Lat: ", "checkbox", "38", "10", "impedancia_id",
            "Certificado: ", "checkbox", "39", "10", "grupocorrhorno_id",
        );
    $data_14 = array (
            "Chequeo: ", "checkbox", "40", "10", "sensibilidad_id",
            "Modelo nuevo: ", "text", "modelo_nuevo", "10", "impedancia_id",
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
        imprimirfila_alta($data_13, "13", $it);
        imprimirfila_alta($data_14, "14", $it);

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
    
    require_once ('MDB2.php');
    require_once('dbinfo.php');
    $mdb2 =& MDB2::singleton($dsn, $options);

    if (PEAR::isError($mdb2)) {
         die($mdb2->getMessage());
    }

    $query = "SELECT Modelo FROM Modelos where Inactivo='0' order by Modelo";

    $res = $mdb2->queryCol($query);
    if (PEAR::isError($res)) {
         die($mdb2->getMessage());
    }

    for($i=0 ; $i < count($res); ) {
    $it->setCurrentBlock("MODELOS");
    $it->setVariable('MODELO',$res[$i++]);
    $it->parseCurrentBlock("MODELOS");
    }
    $it->show();
    $mdb2->disconnect();
}

function modificacion(){
    require_once 'include/pear/Sigma.php'; //insertamos la libreria
    $it = new HTML_Template_Sigma('themes'); //declaramos el objeto
    $it->loadTemplatefile('abm_modificacion.html'); //seleccionamos la plantilla
    
    require_once ('MDB2.php');
    require_once('dbinfo.php');
    $mdb2 =& MDB2::singleton($dsn, $options);

    if (PEAR::isError($mdb2)) {
         die($mdb2->getMessage());
    }

    $query = "SELECT Modelo FROM Modelos where Inactivo='0' order by Modelo";

    $res = $mdb2->queryCol($query);
    if (PEAR::isError($res)) {
         die($mdb2->getMessage());
    }

    for($i=0 ; $i < count($res); ) {
    $it->setCurrentBlock("MODELOS");
    $it->setVariable('MODELO',$res[$i++]);
    $it->parseCurrentBlock("MODELOS");
    }
    $it->show();
    $mdb2->disconnect();
}

function procesa($action){
    $paso_validacion = 1;

    switch($action){
        case "alta": $paso_validacion = validar_datos($action); break;
        case "modificacion": modificacion(); break;
        default: print "No existe tal acción"; 
    }

    if (!$paso_validacion){
    /* Cargo la Alta de nuevo */
    // Hago la conexion. Sin usar globales, por favor :)
    //    global $id_db_flexar;
        carga_modelo();



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


//    if ($flag_error > 0){
    //    limpiarGet();
    //    print $error;
//    }
    return 0;
    }
}

function borra_modelo($borrarmodelo){
    /* Hacer un query que ponga el valor Inactivo=1 */
    // global $id_db_flexar; Ubicar el conector sin usar globales
    require_once ('MDB2.php');
    require_once('dbinfo.php');
    $mdb2 =& MDB2::singleton($dsn, $options);

    if (PEAR::isError($mdb2)) {
         die($mdb2->getMessage());
    }

    $query = "UPDATE Modelos SET Inactivo='1' WHERE Modelos.Modelo = '$borrarmodelo'";

    $res = $mdb2->query($query);

    if (PEAR::isError($res)) {
         die($mdb2->getMessage());
    }
    $res->free();
    $mdb2->disconnect();
    print "Se ha dado de baja el modelo: ".$borrarmodelo;
    
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
                                'DeltaRb'
                            );

    $error_modelo = "<html><head><script language='Javascript'>
                function cargarindex(){
                    setTimeout(\"location.replace('index.php')\",2000);
                }
                </script>
            </head>
            <body onload='cargarindex()'>
                <div align='center'>
                    <h3>Nombre de modelo existente. Vuelva a realizar la carga</h3>
                </div>
            </body></html>";
    /**
     * 1º incluimos el archivo de MDB2.php
     * 2º Hacemos el conect con singleton.
     * */
    require_once ('MDB2.php');
    require_once ('dbinfo.php');
    
    /*Bloque para conectarme a la DB*/
    $mdb2 =& MDB2::singleton($dsn, $options);
    if (PEAR::isError($mdb2)) {
         die($mdb2->getMessage());
    }
    
    $query = "SELECT Modelo FROM Modelos where Inactivo='0' order by Modelo";

    $res = & $mdb2->query($query);

    // Si hay algun error finaliza el programa. 
    if (PEAR::isError($res)) {
        die($res->getMessage());
    }

    while ($row_flexar = $res->fetchRow()){
        if ( strtoupper($_GET['modelo_nuevo'])== strtoupper($row_flexar[0])){
            print ($error_modelo); // Si existe el modelo se redirecciona al index.php
            exit();
        }
    }
    $res->free();
    
    // paso el valor check a valores 1 o 0
   checkbox_to_SQL();

    // nombre de modelo en mayusculas
   $_GET['modelo_nuevo'] = strtoupper($_GET['modelo_nuevo']); 

    /**
     * Armamos el query usando el implode
     * $query = "INSERT INTO Modelos (Modelo, ".implode(', ',$array_campos_insert).", Etiqueta) VALUES ('".$modelonuevo."', ".implode(', ', $array_values).", '".$etiqueta."')";
     *
     * Resulta que hacer el query sin usar el implode va a ser mejor :)
    */ 

    $res = $mdb2->prepare('INSERT INTO Modelos (
            Sensibilidad, Impedancia, GrupoCorrHorno, ImpEnt, ImpSal, [Tol ImpEnt], TolImpSal, Cero, TolCero, TolSens, 
            CapNominal, Ruta, Alin, Hister, Rep, Creep, CorCeroTemp, CorSpanTemp, VMaxAlim, RangTemp, 
            Sobrecarga, LimRot, Cable, TolR2, TolPendHorno, TolH, CantPorLote, pSg, CantSg, pRb,
            CantRb, pPrensa, pCable, pArnes, DeltaRb, Etiqueta, Apareo, CarLat, Certificado, Chequeo, 
            Modelo, Inactivo ) VALUES  (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
            ?,?)', true, MDB2_PREPARE_MANIP);

     $data = array(
       $_GET['1'], $_GET['2'], $_GET['3'], $_GET['4'], $_GET['5'], $_GET['6'], $_GET['7'], $_GET['8'], $_GET['9'],$_GET['10'], 
       $_GET['11'], $_GET['12'], $_GET['13'], $_GET['14'], $_GET['15'], $_GET['16'], $_GET['17'], $_GET['18'], $_GET['19'], $_GET['20'], 
       $_GET['21'], $_GET['22'], $_GET['23'], $_GET['24'], $_GET['25'], $_GET['26'], $_GET['27'], $_GET['28'], $_GET['29'], $_GET['30'],
       $_GET['31'], $_GET['32'], $_GET['33'], $_GET['34'], $_GET['35'], $_GET['36'], $_GET['37'], $_GET['38'], $_GET['39'], $_GET['40'],
       $_GET['modelo_nuevo'],'0' );
              
        // Hacemos el insert
       $res->execute($data);

        if (PEAR::isError($res)) {
            die($res->getMessage());
        }
        $res->free();
        $mdb2->disconnect();
        // liberar los GET
        limpiarGet();
        $_GET['modulo']="abm_modelos";
        $_GET['action']="alta";
        header('location:index.php');
}

function modifica_modelo($modelo){
    /*Query buscando todos los datos del modelo
     * Mostrar campos del modelo con sus respectivos valores
     * */
    //$query = "select * from modelos where modelo='.$modelo.'";
    //$resultado = $id_db_flexar -> query($query);
    print "modifico el modelo".$modelo;

}


function checkbox_to_SQL(){
        for ($i = 37; $i < 41; $i++){
            if($_GET[$i] == "on") $_GET[$i] = '1'; else $_GET[$i] = '0';
        }
}

function limpiarGet(){

    unset($_GET['modelo_nuevo']);

    for ($i = 1; $i < 41; $i++)
        unset($_GET[$i]);
}



?>
