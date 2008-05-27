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


function pagina_consulta_planos ($action){
    /* Depende de que action nos llega hacemos:
    * alta, baja, modificacion, procesa. 
    *
    * Pagina de Consulta para la gerencia 
    *
    * */

    require_once 'include/pear/Sigma.php'; //insertamos la libreria
    $it = new HTML_Template_Sigma('themes'); //declaramos el objeto
    $it->loadTemplatefile('consulta_planos.html'); //seleccionamos la plantilla

    $data_cen = array (
                       "Plano Buscado: ", "text", "plano_buscado", "10", "plano_buscado_id", "plano_buscado",
                    );

    mostrar_inputs($data_cen,"input_cen", $it);
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
