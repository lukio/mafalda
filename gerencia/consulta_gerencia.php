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
    
    sort($data_busqueda); // ordenamos el listado de busquedas

    mostrar_inputs($data_der,"input_der", $it);
    mostrar_inputs($data_cen,"input_cen", $it);
    mostrar_inputs($data_fecha,"FECHAS", $it);
    mostrar_select($data_busqueda,"BUSQUEDA", $it);
    
    $it->show(); //mostramos el resultado
}

function cual_action($action, $q){
/*
    En $action viene el tipo de busqueda
    En $q viene todas las variables en orden:
    $q = serie, ot, nom_operario, sector, fecha_inicio, fecha_final
*/

    $q = explode (',', $q); 

    switch($action){
        case "Probatuti por operario y fecha": proba_oper_fecha($q); break;
        case "baja": baja(); break;
        case "modificacion": modificacion(); break;
        case "procesa": procesa($action[1]); break;
        case "borrarmodelo": borra_modelo($action[1]); break;
        case "modificamodelo":modifica_modelo($action[1]); break;
        default: print "No existe tal acción"; 
    }

}

function proba_oper_fecha($q){
/*
    En $q viene todas las variables en orden:
    $q = serie, ot, nom_operario, sector, fecha_inicio, fecha_final
*/
$nom_operario = $q['2'];
$fecha_inicio = $q['4'];
$fecha_inicio = $q['5'];
//require_once('validaciones.php');
    /* Chequear si nom_operario es alpha, si las fechas son validas y no se superponen. */
    if (!ctype_alpha($nom_operario)){
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

        // Enlaces Tabla Probatuti y num ot por lote
        $it->setCurrentBlock("LINKS");
        $it->setVariable("LOTE_EMBA", $lote_embalado);
        $it->parseCurrentBlock("LINKS");

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


?>
