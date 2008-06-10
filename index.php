<?php
/** 
 * Nombre archivo: Index.php
 * Todos los action deberian de pasar por el index.php
 *
 * Anteriormente
 * header('location:http://fileserver/09-01-06/consulta.html');
 */

//creamos el codigo puente
define(IP_CLIENTE,$_SERVER['REMOTE_ADDR']);
session_start();
header("Cache-control: private"); //IE 6 Fix
$ruta = 'include';
set_include_path(get_include_path() . PATH_SEPARATOR . $ruta);
ini_set("session.gc_maxlifetime", "18000"); 

//Agregar si viene desde la IP que esta seteada
if(!isset($_SESSION['user_autenticado']) and $_SESSION['IP']!=IP_CLIENTE){
   /**
    * Usuario anónimo
    * Esta seteado el $_POST['action'?
    *  No -> Primera vez, entonces escribo la pagina
    *  Si -> viene de una consulta, evaluo el action.
    **/

    if(!$_GET['action']){
    /**
    * El action no esta definido.
    * Primera vez que carga la page
    **/
        require_once 'include/pear/Sigma.php'; //insertamos la libreria
        $it = new HTML_Template_Sigma('themes'); //declaramos el objeto
        $it->loadTemplatefile('theme.html'); //seleccionamos la plantilla

        $data_der = array (
                        "Numero serie:  ", "text", "celda", "10", "celda_id",
                    );

        $data_cen = array (
                       "Lote produccion:  ", "text", "lote_produccion", "10", "lote_produccion_id",
                       "Lote embalado: &nbsp;&nbsp;", "text", "lote_embalado", "10", "lote_embalado_id", 
                    );

        for($i=0 ; $i < count($data_der);) {
            $it->setCurrentBlock('input_der'); //buscamos bloque

            $it->setVariable('DATO', $data_der[$i++]);
            $it->setVariable('TYPE_DATO', $data_der[$i++]);
            $it->setVariable('NAME_DATO',$data_der[$i++]);
            $it->setVariable('SIZE_DATO',$data_der[$i++]);
            $it->setVariable('ID_DATO',$data_der[$i++]);

            $it->parseCurrentBlock('input_der'); //generamos la parte del bloque analizado
        }

        for($i=0 ; $i < count($data_cen);) {
            $it->setCurrentBlock('input_cen'); //buscamos bloque

            $it->setVariable('DATO', $data_cen[$i++]);
            $it->setVariable('TYPE_DATO', $data_cen[$i++]);
            $it->setVariable('NAME_DATO',$data_cen[$i++]);
            $it->setVariable('SIZE_DATO',$data_cen[$i++]);
            $it->setVariable('ID_DATO',$data_cen[$i++]);

            $it->parseCurrentBlock('input_cen'); //generamos la parte del bloque analizado
        }



        $it->show(); //mostramos el resultado
        
        }else{
            /**
             * Evaluo el $_GET['action']
             **/
            require_once('fabrica.php');
            require_once('exportar.php');
            require_once('include/autentifica.php');
            $action = $_GET['action'];
            $modulo = $_GET['modulo'];
            unset($_GET['action']);
            unset($_GET['modulo']);

            if ($action == "serie"){
                buscar_nserie($modulo, $_GET['q']);
                exit();
            }
            elseif ($action == "lote_embalado"){
                buscar_lote_embalado($modulo, $_GET['q']);
                exit();
            }
            elseif ($action == "lote_produccion"){
                buscar_lote_produccion($modulo, $_GET['q']);
                exit();
            }
            elseif ($action == "tabla_probatuti"){
                buscar_tabla_probatuti($modulo, $_GET['q']);
                exit();
            }
            elseif ($action == "ot_por_lote"){
                buscar_ot_por_lote($modulo, $_GET['q']);
                exit();
            }
            elseif ($action == "p_orden"){
                buscar_p_orden_trabajo($modulo, $_GET['q']);
                exit();
            }
            elseif ($action == "orden_mecanizado"){
                buscar_orden_mecanizado($modulo, $_GET['q']);
                exit();
            }
            elseif ($action == "login"){
                formulario_login();
                exit();
            }
            else
                die("action no definida");
    }

}else{
    /* Usuario autentificado
     * Lo seguimos manejando desde aca
     **/
    if(!$_GET['action'] && !$_GET['modulo']){
        require_once 'include/pear/Sigma.php'; //insertamos la libreria
        $it = new HTML_Template_Sigma('themes'); //declaramos el objeto
        $it->loadTemplatefile('gerencia.html'); //seleccionamos la plantilla
        $it->show();
    }else{
        /* Evaluo de que modulo y que action requiere*/
        $action = $_GET['action'];
        $modulo = $_GET['modulo'];
        $alta_voler = $_SESSION['alta_volver'];
        //$bajamodelo = $_GET['bajamodelo'];
        //unset($_GET['bajamodelo']);
        unset($_GET['action']);
        unset($_GET['modulo']);

         if ($action == "logout"){
             require_once('include/autentifica.php');
             logout();
             exit();
         }else{
             switch($modulo){
                 case "consulta_gerencia":  require_once('gerencia/consulta_gerencia.php');
                                            $q=$_GET['q'];
                                            unset($_GET['q']);
                                            if (isset($action)){
                                               cual_action($action, $q);
                                                break;
                                            }
                                            pagina_consulta_gerencia($action); 
                                            break;
                 case "abm_modelos":require_once('gerencia/abm_modelo.php'); pagina_abm($action); break;
                 case "consulta_planos": require_once('gerencia/consulta_planos.php'); 
                                        $q=$_GET['q']; 
                                        unset($_GET['q']); 
                                        if (isset($action)){
                                            cual_action($action, $q);
                                            break;
                                        }
                                        pagina_consulta_planos(); 
                                        break;
             }
         }
            
    }
}        
?>
