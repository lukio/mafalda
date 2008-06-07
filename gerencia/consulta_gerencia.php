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
                      "Numero serie:  ", "text", "celda", "18", "celda_id",
                      "Numero OT: ", "text", "numero_ot", "18", "numero_ot_id"
                    );

    $data_cen = array (
                       "Nombre Operario: ", "text", "nombre_operario", "18", "nombre_operario_id", "busco_operario",
                       "Sector: ", "text", "sector", "18", "sector_id", "bussco_sector"
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

    $q = explode (':', $q); 

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

$nom_operario = $q[2];
$nomyapellido = explode (",",$nom_operario);

$fechai = explode("/", $q[4]);
$fechaf  = explode ("/", $q[5]);
/* Chequear si nom_operario es alpha, si las fechas son validas y no se superponen. */

$chequeo = array();
$chequeo['nom_operario'] = ctype_alpha(trim($nomyapellido[0])) ? true: false;
$chequeo['apellido_operario'] = ctype_alpha(trim($nomyapellido[1])) ? true: false;
/* 
 funcionamiento: bool checkdate  ( int $month  , int $day  , int $year  ) 
*/
$chequeo['fechai'] = checkdate($fechai[1],$fechai[0],$fechai[2]);
$chequeo['fechaf'] = checkdate($fechaf[1],$fechaf[0],$fechaf[2]);

// es true si fecha_inicio es menor a la segunda
// compara_fechas('yyyy/mm/dd','yyyy/mm/dd');
if (compara_fechas($fechai[2]."/".$fechai[1]."/".$fechai[0], $fechaf[2]."/".$fechaf[1]."/".$fechaf[0]) > 0)
    $chequeo['comp_fechas'] = false;
else
    $chequeo['comp_fechas'] = true;

    if (!$chequeo['nom_operario'] or !$chequeo['apellido_operario'] or !$chequeo['fechai'] or !$chequeo['fechaf'] or !$chequeo['comp_fechas']){
            print "Dato de tipo NO VALIDO<br />";
            /*Decir cuales valores son incorrectos*/
            var_dump($chequeo); 
    }else{
        require_once('dbinfo.php');
        require_once('MDB2.php');

        // Conecto a DB Flexar
        $mdb2 =& MDB2::singleton($dsn, $options);
        if (PEAR::isError($mdb2)) {
                 die($mdb2->getMessage());
        }
        /*
        La fecha la espera en el orden mm/dd/yyyy
        */
        $fecha_inicio = $fechai[1]."/".$fechai[0]."/".$fechai[2];
        $fecha_final = $fechaf[1]."/".$fechaf[0]."/".$fechaf[2];
        $a = trim($nomyapellido[1]);
        print $fecha_inicio." ".$fecha_final;


        $query = "SELECT Operarios.Nombre, Probatuti.Fecha, Probatuti.hora AS hora, Operaciones.Operacion, 
                         Lotes.Modelo, Probatuti.serie
                 FROM (((Probatuti LEFT JOIN Operaciones ON Probatuti.Area = Operaciones.IdOperacion) 
                      LEFT JOIN Impedancias ON Probatuti.serie = Impedancias.Serie) LEFT JOIN Lotes ON Impedancias.Lote = Lotes.Lote)
                      RIGHT JOIN Operarios ON Probatuti.operador = Operarios.OperProba
                WHERE Operarios.Nombre='$a' AND Probatuti.Fecha>='$fecha_inicio' And Probatuti.Fecha<='$fecha_final'";

/*        $query = "SELECT embalado.serie, (SELECT Lote FROM Impedancias WHERE embalado.serie=impedancias.serie) as lotepro,
                  LEFT(embalado.fecha,20) as fecha
                  FROM embalado
                  WHERE ID_Grupo=".$mdb2->quote($lote_embalado,'integer')." order by embalado.serie";
*/

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
            foreach ($name as $cell){

            // Assign data to the inner block
/*                $it->setCurrentBlock("LOTEEM");
                $it->setVariable("N_SERIE", $name['serie']);
                $it->setVariable("LOTE_PRODUCCION", $name['lotepro']);
                $it->setVariable("FECHA", $name['fecha']);
                $it->parseCurrentBlock("LOTEEM");
*/              print $cell."<br />";
            $it->parse("row_lemba");
            }
        }
       $it->show(); 
    }
}

/*
Devuelve:
 numero positivo si la primera fecha es mayor que la segunda
 número negativo si la primera es menor que la seguna 
 0 si son iguales
 Funciona con ambos formatos: 'yyyy-mm-dd' y 'yyyy/mm/dd'

 http://www.forosdelweb.com/f18/validar-fechas-php-408134/
*/
function compara_fechas($fecha1,$fecha2)
{
if (preg_match("/[0-9]{1,2}\/[0-9]{1,2}\/([0-9][0-9]){1,2}/",$fecha1))
list($dia1,$mes1,$año1)=split("/",$fecha1);
if (preg_match("/[0-9]{1,2}-[0-9]{1,2}-([0-9][0-9]){1,2}/",$fecha1))
list($dia1,$mes1,$año1)=split("-",$fecha1);
if (preg_match("/[0-9]{1,2}\/[0-9]{1,2}\/([0-9][0-9]){1,2}/",$fecha2))
list($dia2,$mes2,$año2)=split("/",$fecha2);
if (preg_match("/[0-9]{1,2}-[0-9]{1,2}-([0-9][0-9]){1,2}/",$fecha2))
list($dia2,$mes2,$año2)=split("-",$fecha2);
$dif = mktime(0,0,0,$mes1,$dia1, $año1) - mktime(0,0,0, $mes2,$dia2,$año2);
return ($dif);
}



?>
