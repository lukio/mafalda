<?php
if (!isset($_SESSION['user_autenticado'])) {
    die("Usuario no autenticado");
}


function mostrar_select($data,$bloque, $it){

    for($i=0 ; $i < count($data);) {
        $it->setCurrentBlock($bloque); //buscamos bloque

        $it->setVariable('NAME_DATO',$data[$i++]);

        $it->parseCurrentBlock($bloque); //generamos la parte del bloque analizado
    }

}

function mostrar_inputs($data,$bloque, $it){

    for($i=0 ; $i < count($data);) {
        $it->setCurrentBlock($bloque); //buscamos bloque

        $it->setVariable('DATO', $data[$i++]);
        $it->setVariable('TYPE_DATO', $data[$i++]);
        $it->setVariable('NAME_DATO',$data[$i++]);
        $it->setVariable('SIZE_DATO',$data[$i++]);
        $it->setVariable('ID_DATO',$data[$i++]);
        if ($bloque == "input_cen")
        $it->setVariable('DATO_AJAX',$data[$i++]);

        $it->parseCurrentBlock($bloque); //generamos la parte del bloque analizado
    }

}


function pagina_consulta_gerencia ($action){
    /* Depende de que action nos llega hacemos:
    * alta, baja, modificacion, procesa. 
    *
    * Pagina de Consulta para la gerencia 
    *
    * */

    require_once 'include/pear/Sigma.php'; //insertamos la libreria
    $it = new HTML_Template_Sigma('themes'); //declaramos el objeto
    $it->loadTemplatefile('consulta_gerencia.html'); //seleccionamos la plantilla

    $data_der = array (
                      "Numero serie:  ", "text", "celda", "10", "celda_id",
                      "Numero OT: ", "text", "numero_ot", "10", "numero_ot_id"
                    );

    $data_cen = array (
                       "Nombre Operario: ", "text", "nombre_operario", "10", "nombre_operario_id", "busco_operario",
                       "Sector: ", "text", "sector", "10", "sector_id", "bussco_sector"
                    );
    $data_fecha = array (
                   "Fecha Inicio: ", "text", "fecha_inicio", "12", "fecha_inicio_id",
                   "Fecha Final: ", "text", "fecha_final", "12", "fecha_final_id"
            );
    $data_busqueda = array (
                   "Embalado por fecha", "Probatuti por operario y fecha", "Probatuti por sector y fecha", 
                   "Ensayos por operario y fecha", "Cableado (OT Asignada)", "Lima (OT Asignada)", 
                   "Num OT por fecha", "OT por operario"
            );

    mostrar_inputs($data_der,"input_der", $it);
    mostrar_inputs($data_cen,"input_cen", $it);
    mostrar_inputs($data_fecha,"FECHAS", $it);
    mostrar_select($data_busqueda,"BUSQUEDA", $it);
    
    $it->show(); //mostramos el resultado
}

function cual_action($action){

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



?>
