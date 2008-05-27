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


function pagina_consulta_planos (){
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

function cual_action($action, $q){

    //$action = explode (':', $action); // tomo la accion de procesa

    switch($action){
        case "plano_buscado": plano_buscado($q); break;
        default: break; 
    }

}
function plano_buscado($q){
    $listado_archivos = scandir("/usr/share");
    rsort($listado_archivos);
    foreach ($listado_archivos as $file)
        //echo "<p>".strpbrk($file,$q)."<p />";
        //echo "<p>".similar_text($file,$q)."<p />";
        //echo "<p>".stristr($file,$q);
        /*
        Valores retornados
        Devuelve una cadena que empieza desde el caracter encontrado, o FALSE si no se encuentra.
        usar condicional "?" 
        no va a fucionar :P
        */
        print "<p>".strpbrk($file,$q)."<a href=file:\\home/lukio/tmp/flexar/$file>\t$file</a>";
}

?>
