<?php
if(!isset($_SESSION['user_autenticado'])) header('Location:../index.php');

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
            "Sensibilidad: ", "text", "sensibilidad", "10", "sensibilidad_id",
            "Impedancia: ", "text", "impedancia", "10", "impedancia_id",
            "Grupo Corr Horno: ", "text", "grupocorrhorno", "10", "grupocorrhorno_id",
        );
    $data_2 = array (
            "Imp. Entrada: ", "text", "sensibilidad", "10", "sensibilidad_id",
            "Imp. Salida: ", "text", "impedancia", "10", "impedancia_id",
            "Tol. Imp. Entrada: ", "text", "grupocorrhorno", "10", "grupocorrhorno_id",
        );

    $data_3 = array (
            "Tol. Imp. Salida: ", "text", "sensibilidad", "10", "sensibilidad_id",
            "Cero: ", "text", "impedancia", "10", "impedancia_id",
            "Tol. Cero: ", "text", "grupocorrhorno", "10", "grupocorrhorno_id",
        );
    $data_4 = array (
            "Tol. Sensibilidad: ", "text", "sensibilidad", "10", "sensibilidad_id",
            "Capacidad Nominal: ", "text", "impedancia", "10", "impedancia_id",
            "Ruta: ", "text", "grupocorrhorno", "10", "grupocorrhorno_id",
        );
    $data_5 = array (
            "Alineacion: ", "text", "sensibilidad", "10", "sensibilidad_id",
            "Histeresis: ", "text", "impedancia", "10", "impedancia_id",
            "Rep: ", "text", "grupocorrhorno", "10", "grupocorrhorno_id",
        );
    $data_6 = array (
            "Creep: ", "text", "sensibilidad", "10", "sensibilidad_id",
            "Correccion Cero Temp.: ", "text", "impedancia", "10", "impedancia_id",
            "Correccion Span Temp.: ", "text", "grupocorrhorno", "10", "grupocorrhorno_id",
        );
    $data_7 = array (
            "V Max. Alim: ", "text", "sensibilidad", "10", "sensibilidad_id",
            "Rango Temp.: ", "text", "impedancia", "10", "impedancia_id",
            "Sobrecarga: ", "text", "grupocorrhorno", "10", "grupocorrhorno_id",
        );
    $data_8 = array (
            "Limite Rot: ", "text", "sensibilidad", "10", "sensibilidad_id",
            "Cable: ", "text", "impedancia", "10", "impedancia_id",
            "Tol. R2: ", "text", "grupocorrhorno", "10", "grupocorrhorno_id",
        );
    $data_9 = array (
            "Tol. Pendiente Horno: ", "text", "sensibilidad", "10", "sensibilidad_id",
            "Tol. H: ", "text", "impedancia", "10", "impedancia_id",
            "Cantidad Por Lote: ", "text", "grupocorrhorno", "10", "grupocorrhorno_id",
        );
    $data_10 = array (
            "pSg: ", "text", "sensibilidad", "10", "sensibilidad_id",
            "Cantidad Sg: ", "text", "impedancia", "10", "impedancia_id",
            "pRb: ", "text", "grupocorrhorno", "10", "grupocorrhorno_id",
        );
    $data_11 = array (
            "Cant Rb: ", "text", "sensibilidad", "10", "sensibilidad_id",
            "pPrensa: ", "text", "impedancia", "10", "impedancia_id",
            "pCablea: ", "text", "grupocorrhorno", "10", "grupocorrhorno_id",
        );
    $data_12 = array (
            "pArnes: ", "text", "sensibilidad", "10", "sensibilidad_id",
            "DeltaRb: ", "text", "impedancia", "10", "impedancia_id",
            "Etiqueta: ", "text", "grupocorrhorno", "10", "grupocorrhorno_id",
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
    print "MOSTRAR MODELOS A DAR DE BAJA";
}

function modificacion(){
    print "1º MOSTRAR MODELOS A MODIFICAR
          2º  MOSTRAR DATOS DEL MODELO A MODIFICAR";
}

function procesa($action){
    print "PROCESA LAS ALTAS, BAJAS O MODIFICACIONES. (QUERYS SQL)".$action;

}

?>
