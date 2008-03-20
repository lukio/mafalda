<?php
if(!isset($_SESSION['user_autenticado'])) header('Location:../index.php');

function cual_action ($action){
    /* Depende de que action nos llega hacemos:
    * alta, baja, modificacion, procesa. 
    */

    switch($action){
        case "alta": alta(); break;
        case "baja": baja(); break;
        case "modificacion": modificacion(); break;
        case "procesa": procesa($action); break;
        default: print "No existe tal acción"; 
    }
}

function alta(){

    require_once 'include/pear/Sigma.php'; //insertamos la libreria
    $it = new HTML_Template_Sigma('themes'); //declaramos el objeto
    $it->loadTemplatefile('abm.html'); //seleccionamos la plantilla

    $it->setCurrentBlock('alta'); //bu
    $it->setVariable('DATO', "dato de alta");
    $it->parseCurrentBlock('alta');

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
    print "PROCESA LAS ALTAS, BAJAS O MODIFICACIONES. (QUERYS SQL)";

}

?>
