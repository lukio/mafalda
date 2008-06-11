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
    require_once('dbinfo.php');
    require_once('MDB2.php');

    // Conecto a DB Flexar
    $mdb2 =& MDB2::singleton($dsn, $options);
    if (PEAR::isError($mdb2)) {
             die($mdb2->getMessage());
    }
    $query = "SELECT operaciones.operacion from operaciones order by operacion"; 
    $operaciones =& $mdb2->queryCol($query);

    require_once 'include/pear/Sigma.php'; //insertamos la libreria
    $it = new HTML_Template_Sigma('themes'); //declaramos el objeto
    $it->loadTemplatefile('consulta_gerencia.html'); //seleccionamos la plantilla

    $data_der = array (
                      "Numero serie:  ", "text", "celda", "12", "celda_id",
                      "Numero OT: ", "text", "numero_ot", "12", "numero_ot_id"
                    );
    
    $data_fecha = array (
                   "Fecha Inicio: ", "text", "fecha_inicio", "12", "fecha_inicio_id",
                   "Fecha Final: ", "text", "fecha_final", "12", "fecha_final_id"
            );
    $data_busqueda = array (
                    "Embalado por fecha", 
                    "Cantidad embalado por modelo", 
                    "Cantidad embalado por marca", 
                    "Probatuti por operario y fecha", 
                    "Probatuti por sector y fecha", 
                    "Ensayos por operario y fecha",
                    "Ensayos por modelo y fecha",
                    "Ensayos por fecha",
                    "OT por operario y fecha", 
                    "OT por sector y fecha",
                    "OT por sector, modelo y fecha",
                    "Pendientes por sector",
                    "Reparaciones por modelo y fecha",
            );
    $query = "SELECT marcas.marca from marcas order by marca"; 
    $marcas =& $mdb2->queryCol($query);
    $query = "SELECT Modelos.Modelo from Modelos order by modelo"; 
    $modelos =& $mdb2->queryCol($query);
    sort($data_busqueda); // ordenamos el listado de busquedas
    mostrar_inputs($data_der,"input_der", $it);
    mostrar_select($operaciones, "select_sector", $it);
    mostrar_inputs($data_fecha,"FECHAS", $it);
    mostrar_select($data_busqueda,"BUSQUEDA", $it);
    mostrar_select($marcas, "select_marca", $it);
    mostrar_select($modelos, "select_modelo", $it);
    
    $it->show(); //mostramos el resultado
}

function cual_action($action, $q){
/*
    En $action viene el tipo de busqueda
    El array tipo de busqueda se completa en $data_busqueda en la funcion pagina_consulta_gerencia()
    En $q viene todas las variables en orden:
    $q = serie, ot, sector, nom_operario, fecha_inicio, fecha_final
*/
ini_set("session.gc_maxlifetime", "18000");
    $q = explode (':', $q);

    switch($action){
        case "Probatuti por operario y fecha": proba_oper_fecha($q); break;
        case "Probatuti por sector y fecha": proba_sector_fecha($q); break;
        case "Cantidad embalado por modelo": cant_embalada_modelo($q); break;
        case "Cantidad embalado por marca": cant_embalada_marca($q); break;
        case "Embalado por fecha": embalada_fecha($q); break;
        case "Ensayos por operario y fecha": ensayos_fecha_operador($q); break;
        case "Ensayos por modelo y fecha": ensayos_fecha_modelo($q); break;
        case "Ensayos por fecha": ensayos_fecha($q); break;
        case "OT por operario y fecha": ot_operario_fecha($q); break;
        case "OT por sector y fecha": ot_sector_fecha($q); break;
        case "OT por sector, modelo y fecha": ot_sector_modelo_fecha($q); break;
        case "Pendientes por sector": pendientes_sector($q); break;
        case "Reparaciones por modelo y fecha": reparaciones_modelo_fecha($q); break;
        case "serie": require_once('fabrica.php');  buscar_nserie('consulta_gerencia', $q[0] ); break;
        case "tabla_probatuti": require_once('fabrica.php'); buscar_tabla_probatuti('consulta_gerencia',$q[0]); break;
        case "ot_por_lote": require_once('fabrica.php'); buscar_ot_por_lote('consulta_gerencia',$q[0]); break;
        case "lote_produccion": require_once('fabrica.php'); buscar_lote_produccion('consulta_gerencia',$q[0]); break;
        case "lote_embalado": require_once('fabrica.php'); buscar_lote_embalado('consulta_gerencia',$q[0]); break;
        case "p_orden": require_once('fabrica.php'); buscar_p_orden_trabajo('consulta_gerencia',$q[0]); break;
        case "orden_mecanizado": require_once('fabrica.php'); buscar_orden_mecanizado('consulta_gerencia',$q[0]); break;
        default: print "No existe tal acción: consulta gerencia"; 
    }

}

function proba_oper_fecha($q){
/*
    En $q viene todas las variables en orden:
    $q = serie, ot, sector, nom_operario, fecha_inicio, fecha_final
*/

$nom_operario = $q[3];
$sector = $q[2];
$nomyapellido = explode (",",$nom_operario);

$fechai = explode("/", $q[4]);
$fechaf  = explode ("/", $q[5]);
/* Chequear si nom_operario es alpha, si las fechas son validas y no se superponen. */

$chequeo = array();
//$chequeo['nom_operario'] = ctype_alpha(trim($nomyapellido[0])) ? true: false;
//$chequeo['apellido_operario'] = ctype_alpha(trim($nomyapellido[1])) ? true: false;
$chequeo['nom_operario'] = true;
$chequeo['apellido_operario'] = true;
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
            //var_dump($chequeo); 
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

        $query = "SELECT Probatuti.Fecha as fecha, Lotes.Modelo as modelo, Probatuti.serie as serie
                 FROM (((Probatuti LEFT JOIN Operaciones ON Probatuti.Area = Operaciones.IdOperacion) 
                      LEFT JOIN Impedancias ON Probatuti.serie = Impedancias.Serie) LEFT JOIN Lotes ON Impedancias.Lote = Lotes.Lote)
                      RIGHT JOIN Operarios ON Probatuti.operador = Operarios.OperProba
                WHERE Operarios.Nombre=".$mdb2->quote($a,'text')." AND Probatuti.Fecha>=".$mdb2->quote($fecha_inicio,'date')." And Probatuti.Fecha<=".$mdb2->quote($fecha_final,'date')." 
                order by Lotes.Modelo, Probatuti.serie";

//                  WHERE ID_Grupo=".$mdb2->quote($lote_embalado,'integer')." order by embalado.serie";

        $res =& $mdb2->query($query);

        if (PEAR::isError($res)) {
                    die($res->getMessage());
        }
        $rows = $res->fetchAll(MDB2_FETCHMODE_ASSOC);
        require_once 'include/pear/Sigma.php'; //insertamos la libreria
        $it = new HTML_Template_Sigma('themes'); //declaramos el objeto
        $it->loadTemplatefile('probatuti_oper_fecha_gerencia.html', true, true); //seleccionamos la plantilla

        foreach($rows as $name) {
            // Assign data to the inner block
                $it->setCurrentBlock("PROBA_GERENCIA");
                $it->setVariable("OPERADOR",$nom_operario);
                $it->setVariable("SECTOR", $sector);
                $it->setVariable("MODELO", $name['modelo']);
                $it->setVariable("N_SERIE", $name['serie']);
                $it->setVariable("FECHA", $name['fecha']);
                $it->parseCurrentBlock("PROBA_GERENCIA");
            $it->parse("row_lemba");
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

function proba_sector_fecha($q){
/*
    En $q viene todas las variables en orden:
    $q = serie, ot, sector, nom_operario, fecha_inicio, fecha_final
*/

$nom_operario = $q[3];
$sector = $q[2];
$nomyapellido = explode (",",$nom_operario);

$fechai = explode("/", $q[4]);
$fechaf  = explode ("/", $q[5]);
/* Chequear si nom_operario es alpha, si las fechas son validas y no se superponen. */

$chequeo = array();
//$chequeo['nom_operario'] = ctype_alpha(trim($nomyapellido[0])) ? true: false;
//$chequeo['apellido_operario'] = ctype_alpha(trim($nomyapellido[1])) ? true: false;
$chequeo['sector'] = ctype_alpha(trim($sector)) ? true: false;
$chequeo['nom_operario'] = true;
$chequeo['apellido_operario'] = true;
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

    if (!$chequeo['sector'] or !$chequeo['fechai'] or !$chequeo['fechaf'] or !$chequeo['comp_fechas']){
            print "Dato de tipo NO VALIDO<br />";
            /*Decir cuales valores son incorrectos*/
            //var_dump($chequeo); 
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

        $query = "SELECT Operarios.Nombre, Operarios.Apellido, Probatuti.Fecha as fecha, Lotes.Modelo as modelo, Probatuti.serie as serie
                 FROM (((Probatuti LEFT JOIN Operaciones ON Probatuti.Area = Operaciones.IdOperacion) 
                      LEFT JOIN Impedancias ON Probatuti.serie = Impedancias.Serie) LEFT JOIN Lotes ON Impedancias.Lote = Lotes.Lote)
                      RIGHT JOIN Operarios ON Probatuti.operador = Operarios.OperProba
                WHERE Operaciones.Operacion=".$mdb2->quote($sector,'text')." AND Probatuti.Fecha>=".$mdb2->quote($fecha_inicio,'date')." And Probatuti.Fecha<=".$mdb2->quote($fecha_final,'date')." 
                order by Lotes.Modelo, Probatuti.serie";

//                  WHERE ID_Grupo=".$mdb2->quote($lote_embalado,'integer')." order by embalado.serie";

        $res =& $mdb2->query($query);

        if (PEAR::isError($res)) {
                    die($res->getMessage());
        }
        $rows = $res->fetchAll(MDB2_FETCHMODE_ASSOC);
        require_once 'include/pear/Sigma.php'; //insertamos la libreria
        $it = new HTML_Template_Sigma('themes'); //declaramos el objeto
        $it->loadTemplatefile('probatuti_sector_fecha_gerencia.html', true, true); //seleccionamos la plantilla

        foreach($rows as $name) {
            // Assign data to the inner block
                $it->setCurrentBlock("PROBA_GERENCIA");
                $it->setVariable("OPERADOR",$name['nombre']." ".$name['apellido']);
                $it->setVariable("SECTOR", $sector);
                $it->setVariable("MODELO", $name['modelo']);
                $it->setVariable("N_SERIE", $name['serie']);
                $it->setVariable("FECHA", $name['fecha']);
                $it->parseCurrentBlock("PROBA_GERENCIA");
            $it->parse("row_lemba");
        }
       $it->show(); 
    }
}

function cant_embalada_modelo($q){
/*
    En $q viene todas las variables en orden:
    $q = serie, ot, sector, nom_operario, fecha_inicio, fecha_final, marca, modelo
*/
$modelo = $q[7];
$chequeo_mod = true; //harcoding hasta que evalue el modelo

    if (!$chequeo_mod){
            print "Dato de tipo NO VALIDO<br />";
            /*Decir cuales valores son incorrectos*/
            //var_dump($chequeo); 
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
        $query ="SELECT Modelos.Modelo, Marcas.Marca, ModeloMarcaEmb.CantEmb
                 FROM Modelos INNER JOIN (ModeloMarcaEmb INNER JOIN Marcas ON ModeloMarcaEmb.ID_Marca = Marcas.Id) ON Modelos.Id = ModeloMarcaEmb.ID_Modelo
                 where Modelos.Modelo = ".$mdb2->quote($modelo,'text')."
                ORDER BY Modelos.Modelo";

        $res =& $mdb2->queryAll($query);

        if (PEAR::isError($res)) {
                    die($res->getMessage());
        }
        require_once 'include/pear/Sigma.php'; //insertamos la libreria
        $it = new HTML_Template_Sigma('themes'); //declaramos el objeto
        $it->loadTemplatefile('cantidad_embalado_modelo_gerencia.html', true, true); //seleccionamos la plantilla

        $it->setCurrentBlock("LINKS");
        $it->setVariable("MODOMARCA", "modelo");
        $it->setVariable("MODELO", $modelo);
        $it->parseCurrentBlock("LINKS");

        foreach($res as $name) {
            foreach ($name as $cell){
            // Assign data to the inner block
                $it->setCurrentBlock("CANT_EMB_MOD");
                $it->setVariable("DATO", $cell);
                $it->parseCurrentBlock("CANT_EMB_MOD");
        }
            $it->parse("row");
        }
       $it->show(); 
    }
}
function cant_embalada_marca($q){
/*
    En $q viene todas las variables en orden:
    $q = serie, ot, sector, nom_operario, fecha_inicio, fecha_final, marca, modelo
*/
$marca = $q[6];
$chequeo['marca'] = ctype_alpha(trim($marca)) ? true: false;

    if (!$chequeo['marca']){
            print "Dato de tipo NO VALIDO<br />";
            /*Decir cuales valores son incorrectos*/
            //var_dump($chequeo); 
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
        $query ="SELECT Modelos.Modelo, Marcas.Marca, ModeloMarcaEmb.CantEmb
                 FROM Modelos INNER JOIN (ModeloMarcaEmb INNER JOIN Marcas ON ModeloMarcaEmb.ID_Marca = Marcas.Id) ON Modelos.Id = ModeloMarcaEmb.ID_Modelo
                 where Marcas.Marca = ".$mdb2->quote($marca,'text')."
                ORDER BY Modelos.Modelo";

        $res =& $mdb2->queryAll($query);

        if (PEAR::isError($res)) {
                    die($res->getMessage());
        }
        require_once 'include/pear/Sigma.php'; //insertamos la libreria
        $it = new HTML_Template_Sigma('themes'); //declaramos el objeto
        $it->loadTemplatefile('cantidad_embalado_modelo_gerencia.html', true, true); //seleccionamos la plantilla

        $it->setCurrentBlock("LINKS");
        $it->setVariable("MODOMARCA", "marca");
        $it->setVariable("MODELO", $marca);
        $it->parseCurrentBlock("LINKS");

        foreach($res as $name) {
            foreach ($name as $cell){
            // Assign data to the inner block
                $it->setCurrentBlock("CANT_EMB_MOD");
                $it->setVariable("DATO", $cell);
                $it->parseCurrentBlock("CANT_EMB_MOD");
        }
            $it->parse("row");
        }
       $it->show(); 
}
}

function embalada_fecha($q){
/*
    En $q viene todas las variables en orden:
    $q = serie, ot, sector, nom_operario, fecha_inicio, fecha_final, marca, modelo
*/

$fechai = explode("/", $q[4]);
$fechaf  = explode ("/", $q[5]);
/* Chequear si nom_operario es alpha, si las fechas son validas y no se superponen. */

$chequeo = array();
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

    if (!$chequeo['fechai'] or !$chequeo['fechaf'] or !$chequeo['comp_fechas']){
            print "Dato de tipo NO VALIDO<br />";
            /*Decir cuales valores son incorrectos*/
            //var_dump($chequeo); 
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

        $query = "SELECT embalado.serie as serie, lotes.modelo, embalado.id_grupo, embalado.fecha
                  FROM (EMBALADO LEFT JOIN Impedancias ON EMBALADO.serie = Impedancias.Serie) LEFT JOIN Lotes ON Impedancias.Lote = Lotes.Lote
                  WHERE embalado.fecha>=".$mdb2->quote($fecha_inicio,'date')." and embalado.fecha<=".$mdb2->quote($fecha_final,'date')."
                  order by embalado.fecha, lotes.modelo";

        $res =& $mdb2->query($query);

        if (PEAR::isError($res)) {
                    die($res->getMessage());
        }
        require_once 'include/pear/Sigma.php'; //insertamos la libreria
        $it = new HTML_Template_Sigma('themes'); //declaramos el objeto
        $it->loadTemplatefile('embalado_fecha_gerencia.html', true, true); //seleccionamos la plantilla

        $rows = $res->fetchAll(MDB2_FETCHMODE_ASSOC);

        foreach($rows as $name) {
            // Assign data to the inner block
                $it->setCurrentBlock("PROBA_GERENCIA");
                $it->setVariable("N_SERIE", $name['serie']);
                $it->setVariable("MODELO", $name['modelo']);
                $it->setVariable("LOTEEMBALADO", $name['id_grupo']);
                $it->setVariable("FECHA", $name['fecha']);
                $it->parseCurrentBlock("PROBA_GERENCIA");
            $it->parse("row_lemba");
        }
       $it->show(); 
    }
}

function ensayos_fecha($q){
/*
    En $q viene todas las variables en orden:
    $q = serie, ot, sector, nom_operario, fecha_inicio, fecha_final, marca, modelo
*/

$nom_operario = $q[3];
$modelo = $q[7];
$nomyapellido = explode (",",$nom_operario);

$fechai = explode("/", $q[4]);
$fechaf  = explode ("/", $q[5]);
/* Chequear si nom_operario es alpha, si las fechas son validas y no se superponen. */

$chequeo = array();
//$chequeo['nom_operario'] = ctype_alpha(trim($nomyapellido[0])) ? true: false;
//$chequeo['apellido_operario'] = ctype_alpha(trim($nomyapellido[1])) ? true: false;
$chequeo['nom_operario'] = true;
$chequeo['apellido_operario'] = true;
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
            //var_dump($chequeo); 
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

        $query = "SELECT ENSAYOS.MODELO, ENSAYOS.NRO_SERIE, ENSAYOS.RANG_NOM, ENSAYOS.RANGO_INI, ENSAYOS.FECHA
                  FROM ENSAYOS
                  WHERE ENSAYOS.FECHA>=".$mdb2->quote($fecha_inicio,'date')." And ENSAYOS.FECHA<=".$mdb2->quote($fecha_final,'date')."
                  ORDER BY ENSAYOS.MODELO, ensayos.fecha";

        $res =& $mdb2->query($query);

        if (PEAR::isError($res)) {
                    die($res->getMessage());
        }
        $rows = $res->fetchAll(MDB2_FETCHMODE_ASSOC);
        require_once 'include/pear/Sigma.php'; //insertamos la libreria
        $it = new HTML_Template_Sigma('themes'); //declaramos el objeto
        $it->loadTemplatefile('ensayo_fecha_gerencia.html', true, true); //seleccionamos la plantilla

        foreach($rows as $name) {
            // Assign data to the inner block
                $it->setCurrentBlock("PROBA_GERENCIA");
                $it->setVariable("N_SERIE", $name['nro_serie']);
                $it->setVariable("MODELO", $name['modelo']);
                $it->setVariable("RANGO_NOM", $name['rang_nom']);
                $it->setVariable("RANGO_INI", $name['rango_ini']);
                $it->setVariable("FECHA", $name['fecha']);
                $it->parseCurrentBlock("PROBA_GERENCIA");
            $it->parse("row_lemba");
        }
       $it->show(); 
    }
}

function ensayos_fecha_operador($q){
/*
    En $q viene todas las variables en orden:
    $q = serie, ot, sector, nom_operario, fecha_inicio, fecha_final, marca, modelo
*/

$nom_operario = $q[3];
$modelo = $q[7];
$nomyapellido = explode (",",$nom_operario);

$fechai = explode("/", $q[4]);
$fechaf  = explode ("/", $q[5]);
/* Chequear si nom_operario es alpha, si las fechas son validas y no se superponen. */

$chequeo = array();
//$chequeo['nom_operario'] = ctype_alpha(trim($nomyapellido[0])) ? true: false;
//$chequeo['apellido_operario'] = ctype_alpha(trim($nomyapellido[1])) ? true: false;
$chequeo['nom_operario'] = true;
$chequeo['apellido_operario'] = true;
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
            //var_dump($chequeo); 
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

        $query = "SELECT ENSAYOS.MODELO, ENSAYOS.NRO_SERIE, ENSAYOS.RANG_NOM, ENSAYOS.RANGO_INI, ENSAYOS.FECHA
                  FROM ENSAYOS
                  WHERE ENSAYOS.FECHA>=".$mdb2->quote($fecha_inicio,'date')." And ENSAYOS.FECHA<=".$mdb2->quote($fecha_final,'date')." and ensayos.operador=".$mdb2->quote($a,'text')."
                  ORDER BY ENSAYOS.MODELO, ensayos.fecha";

        $res =& $mdb2->query($query);

        if (PEAR::isError($res)) {
                    die($res->getMessage());
        }
        $rows = $res->fetchAll(MDB2_FETCHMODE_ASSOC);
        require_once 'include/pear/Sigma.php'; //insertamos la libreria
        $it = new HTML_Template_Sigma('themes'); //declaramos el objeto
        $it->loadTemplatefile('ensayo_fecha_gerencia.html', true, true); //seleccionamos la plantilla

        foreach($rows as $name) {
            // Assign data to the inner block
                $it->setCurrentBlock("PROBA_GERENCIA");
                $it->setVariable("N_SERIE", $name['nro_serie']);
                $it->setVariable("MODELO", $name['modelo']);
                $it->setVariable("RANGO_NOM", $name['rang_nom']);
                $it->setVariable("RANGO_INI", $name['rango_ini']);
                $it->setVariable("FECHA", $name['fecha']);
                $it->parseCurrentBlock("PROBA_GERENCIA");
            $it->parse("row_lemba");
        }
       $it->show(); 
    }
}

function ensayos_fecha_modelo($q){
/*
    En $q viene todas las variables en orden:
    $q = serie, ot, sector, nom_operario, fecha_inicio, fecha_final, marca, modelo
*/

$nom_operario = $q[3];
$modelo = $q[7];
$nomyapellido = explode (",",$nom_operario);

$fechai = explode("/", $q[4]);
$fechaf  = explode ("/", $q[5]);
/* Chequear si nom_operario es alpha, si las fechas son validas y no se superponen. */

$chequeo = array();
//$chequeo['nom_operario'] = ctype_alpha(trim($nomyapellido[0])) ? true: false;
//$chequeo['apellido_operario'] = ctype_alpha(trim($nomyapellido[1])) ? true: false;
$chequeo['nom_operario'] = true;
$chequeo['apellido_operario'] = true;
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
            //var_dump($chequeo); 
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

        $query = "SELECT ENSAYOS.MODELO, ENSAYOS.NRO_SERIE, ENSAYOS.RANG_NOM, ENSAYOS.RANGO_INI, ENSAYOS.FECHA
                  FROM ENSAYOS
                  WHERE ENSAYOS.FECHA>=".$mdb2->quote($fecha_inicio,'date')." And ENSAYOS.FECHA<=".$mdb2->quote($fecha_final,'date')." and ensayos.modelo=".$mdb2->quote($modelo,'text')."
                  ORDER BY ENSAYOS.MODELO, ensayos.fecha";

        $res =& $mdb2->query($query);

        if (PEAR::isError($res)) {
                    die($res->getMessage());
        }
        $rows = $res->fetchAll(MDB2_FETCHMODE_ASSOC);
        require_once 'include/pear/Sigma.php'; //insertamos la libreria
        $it = new HTML_Template_Sigma('themes'); //declaramos el objeto
        $it->loadTemplatefile('ensayo_fecha_gerencia.html', true, true); //seleccionamos la plantilla

        foreach($rows as $name) {
            // Assign data to the inner block
                $it->setCurrentBlock("PROBA_GERENCIA");
                $it->setVariable("N_SERIE", $name['nro_serie']);
                $it->setVariable("MODELO", $name['modelo']);
                $it->setVariable("RANGO_NOM", $name['rang_nom']);
                $it->setVariable("RANGO_INI", $name['rango_ini']);
                $it->setVariable("FECHA", $name['fecha']);
                $it->parseCurrentBlock("PROBA_GERENCIA");
            $it->parse("row_lemba");
        }
       $it->show(); 
    }
}

function ot_operario_fecha($q){
/*
    En $q viene todas las variables en orden:
    $q = serie, ot, sector, nom_operario, fecha_inicio, fecha_final, marca, modelo
*/

$nom_operario = $q[3];
$nomyapellido = explode (",",$nom_operario);

$fechai = explode("/", $q[4]);
$fechaf  = explode ("/", $q[5]);
/* Chequear si nom_operario es alpha, si las fechas son validas y no se superponen. */

$chequeo = array();
//$chequeo['nom_operario'] = ctype_alpha(trim($nomyapellido[0])) ? true: false;
//$chequeo['apellido_operario'] = ctype_alpha(trim($nomyapellido[1])) ? true: false;
$chequeo['nom_operario'] = true;
$chequeo['apellido_operario'] = true;
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
            //var_dump($chequeo); 
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
        $nombre = utf8_decode($a);

        $query = "SELECT Operarios.Apellido, Operarios.Nombre, operaciones.operacion, OrdenesDeTrabajo.FechaInicio, OrdenesDeTrabajo.NroOrden, DatosOrden.Lote,
                         Lotes.Modelo, DatosOrden.Cantidad
                  FROM Operarios INNER JOIN OrdenesDeTrabajo ON Operarios.IdOperario = OrdenesDeTrabajo.Operario
                                 INNER JOIN operaciones on ordenesdetrabajo.operacion = operaciones.idoperacion
                                 INNER JOIN DatosOrden ON OrdenesDeTrabajo.NroOrden = DatosOrden.NroOrd 
                                 INNER JOIN Lotes ON DatosOrden.Lote = Lotes.Lote
                  WHERE Operarios.Nombre=".$mdb2->quote($nombre,'text')." and OrdenesDeTrabajo.FechaInicio>=".$mdb2->quote($fecha_inicio,'date')." And OrdenesDeTrabajo.FechaInicio<=".$mdb2->quote($fecha_final,'date')."
                  order by ordenesdetrabajo.nroorden";
                                 /*INNER JOIN Impedancias ON Impedancias.Lote = Lotes.Lote*/
        $res =& $mdb2->query($query);

        if (PEAR::isError($res)) {
                    die($res->getMessage());
        }
        $rows = $res->fetchAll(MDB2_FETCHMODE_ASSOC);
        require_once 'include/pear/Sigma.php'; //insertamos la libreria
        $it = new HTML_Template_Sigma('themes'); //declaramos el objeto
        $it->loadTemplatefile('ot_gerencia.html', true, true); //seleccionamos la plantilla

        foreach($rows as $name) {
            // Assign data to the inner block
                $it->setCurrentBlock("OT_GERENCIA");
                $it->setVariable("OPERADOR",utf8_encode($name['nombre'])." ".utf8_encode($name['apellido']));
                $it->setVariable("SECTOR", $name['operacion']);
                $it->setVariable("MODELO", $name['modelo']);
                $it->setVariable("N_SERIE", $name['serie']);
                $it->setVariable("NROORDEN", $name['nroorden']);
                $it->setVariable("LOTEPRO", $name['lote']);
                $it->setVariable("OTCANTIDAD", $name['cantidad']);
                $it->setVariable("FECHA", substr($name['fechainicio'], 0, 11));
                $it->parseCurrentBlock("OT_GERENCIA");
            
            $it->parse("row_lemba");
        }
       $it->show(); 
    }
}

function ot_sector_modelo_fecha($q){
/*
    En $q viene todas las variables en orden:
    $q = serie, ot, sector, nom_operario, fecha_inicio, fecha_final, marca, modelo
*/

$nom_operario = $q[3];
$nomyapellido = explode (",",$nom_operario);
$sector = $q[2];
$modelo = $q[7];

$fechai = explode("/", $q[4]);
$fechaf  = explode ("/", $q[5]);
/* Chequear si nom_operario es alpha, si las fechas son validas y no se superponen. */

$chequeo = array();
//$chequeo['nom_operario'] = ctype_alpha(trim($nomyapellido[0])) ? true: false;
//$chequeo['apellido_operario'] = ctype_alpha(trim($nomyapellido[1])) ? true: false;
$chequeo['nom_operario'] = true;
$chequeo['apellido_operario'] = true;
$chequeo['sector'] = true;
$chequeo['modelo'] = true;
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

    if (!$chequeo['modelo'] or !$chequeo['sector'] or !$chequeo['nom_operario'] or !$chequeo['apellido_operario'] or !$chequeo['fechai'] or !$chequeo['fechaf'] or !$chequeo['comp_fechas']){
            print "Dato de tipo NO VALIDO<br />";
            /*Decir cuales valores son incorrectos*/
            //var_dump($chequeo); 
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
        $nombre = utf8_decode(trim($sector));

        $query = "SELECT Operarios.Apellido, Operarios.Nombre, operaciones.operacion, OrdenesDeTrabajo.FechaInicio, OrdenesDeTrabajo.NroOrden, DatosOrden.Lote,
                         Lotes.Modelo, DatosOrden.Cantidad
                  FROM Operarios INNER JOIN OrdenesDeTrabajo ON Operarios.IdOperario = OrdenesDeTrabajo.Operario
                                 INNER JOIN operaciones on ordenesdetrabajo.operacion = operaciones.idoperacion
                                 INNER JOIN DatosOrden ON OrdenesDeTrabajo.NroOrden = DatosOrden.NroOrd 
                                 INNER JOIN Lotes ON DatosOrden.Lote = Lotes.Lote
                  WHERE Lotes.Modelo=".$mdb2->quote($modelo,'text')." and Operaciones.operacion=".$mdb2->quote($nombre,'text')." and OrdenesDeTrabajo.FechaInicio>=".$mdb2->quote($fecha_inicio,'date')." And OrdenesDeTrabajo.FechaInicio<=".$mdb2->quote($fecha_final,'date')."
                  order by ordenesdetrabajo.nroorden";
                                 /*INNER JOIN Impedancias ON Impedancias.Lote = Lotes.Lote*/
        $res =& $mdb2->query($query);

        if (PEAR::isError($res)) {
                    die($res->getMessage());
        }
        $rows = $res->fetchAll(MDB2_FETCHMODE_ASSOC);
        require_once 'include/pear/Sigma.php'; //insertamos la libreria
        $it = new HTML_Template_Sigma('themes'); //declaramos el objeto
        $it->loadTemplatefile('ot_gerencia.html', true, true); //seleccionamos la plantilla

        foreach($rows as $name) {
            // Assign data to the inner block
                $it->setCurrentBlock("OT_GERENCIA");
                $it->setVariable("OPERADOR",utf8_encode($name['nombre'])." ".utf8_encode($name['apellido']));
                $it->setVariable("SECTOR", $name['operacion']);
                $it->setVariable("MODELO", $name['modelo']);
                $it->setVariable("N_SERIE", $name['serie']);
                $it->setVariable("NROORDEN", $name['nroorden']);
                $it->setVariable("LOTEPRO", $name['lote']);
                $it->setVariable("OTCANTIDAD", $name['cantidad']);
                $it->setVariable("FECHA", substr($name['fechainicio'], 0, 11));
                $it->parseCurrentBlock("OT_GERENCIA");
            
            $it->parse("row_lemba");
        }
       $it->show(); 
    }
}

function ot_sector_fecha($q){
/*
    En $q viene todas las variables en orden:
    $q = serie, ot, sector, nom_operario, fecha_inicio, fecha_final, marca, modelo
*/

$nom_operario = $q[3];
$nomyapellido = explode (",",$nom_operario);
$sector = $q[2];

$fechai = explode("/", $q[4]);
$fechaf  = explode ("/", $q[5]);
/* Chequear si nom_operario es alpha, si las fechas son validas y no se superponen. */

$chequeo = array();
//$chequeo['nom_operario'] = ctype_alpha(trim($nomyapellido[0])) ? true: false;
//$chequeo['apellido_operario'] = ctype_alpha(trim($nomyapellido[1])) ? true: false;
$chequeo['nom_operario'] = true;
$chequeo['apellido_operario'] = true;
$chequeo['sector'] = true;
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

    if (!$chequeo['sector'] or !$chequeo['nom_operario'] or !$chequeo['apellido_operario'] or !$chequeo['fechai'] or !$chequeo['fechaf'] or !$chequeo['comp_fechas']){
            print "Dato de tipo NO VALIDO<br />";
            /*Decir cuales valores son incorrectos*/
            //var_dump($chequeo); 
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
        $nombre = utf8_decode(trim($sector));

        $query = "SELECT Operarios.Apellido, Operarios.Nombre, operaciones.operacion, OrdenesDeTrabajo.FechaInicio, OrdenesDeTrabajo.NroOrden, DatosOrden.Lote,
                         Lotes.Modelo, DatosOrden.Cantidad
                  FROM Operarios INNER JOIN OrdenesDeTrabajo ON Operarios.IdOperario = OrdenesDeTrabajo.Operario
                                 INNER JOIN operaciones on ordenesdetrabajo.operacion = operaciones.idoperacion
                                 INNER JOIN DatosOrden ON OrdenesDeTrabajo.NroOrden = DatosOrden.NroOrd 
                                 INNER JOIN Lotes ON DatosOrden.Lote = Lotes.Lote
                  WHERE Operaciones.operacion=".$mdb2->quote($nombre,'text')." and OrdenesDeTrabajo.FechaInicio>=".$mdb2->quote($fecha_inicio,'date')." And OrdenesDeTrabajo.FechaInicio<=".$mdb2->quote($fecha_final,'date')."
                  order by ordenesdetrabajo.nroorden";
                                 /*INNER JOIN Impedancias ON Impedancias.Lote = Lotes.Lote*/
        $res =& $mdb2->query($query);

        if (PEAR::isError($res)) {
                    die($res->getMessage());
        }
        $rows = $res->fetchAll(MDB2_FETCHMODE_ASSOC);
        require_once 'include/pear/Sigma.php'; //insertamos la libreria
        $it = new HTML_Template_Sigma('themes'); //declaramos el objeto
        $it->loadTemplatefile('ot_gerencia.html', true, true); //seleccionamos la plantilla

        foreach($rows as $name) {
            // Assign data to the inner block
                $it->setCurrentBlock("OT_GERENCIA");
                $it->setVariable("OPERADOR",utf8_encode($name['nombre'])." ".utf8_encode($name['apellido']));
                $it->setVariable("SECTOR", $name['operacion']);
                $it->setVariable("MODELO", $name['modelo']);
                $it->setVariable("N_SERIE", $name['serie']);
                $it->setVariable("NROORDEN", $name['nroorden']);
                $it->setVariable("LOTEPRO", $name['lote']);
                $it->setVariable("OTCANTIDAD", $name['cantidad']);
                $it->setVariable("FECHA", substr($name['fechainicio'], 0, 11));
                $it->parseCurrentBlock("OT_GERENCIA");
            
            $it->parse("row_lemba");
        }
       $it->show(); 
    }
}

function pendientes_sector($q){
/*
    En $q viene todas las variables en orden:
    $q = serie, ot, sector, nom_operario, fecha_inicio, fecha_final, marca, modelo
*/

$sector = $q[2];

/* Chequear si nom_operario es alpha, si las fechas son validas y no se superponen. */

$chequeo = array();
//$chequeo['nom_operario'] = ctype_alpha(trim($nomyapellido[0])) ? true: false;
//$chequeo['apellido_operario'] = ctype_alpha(trim($nomyapellido[1])) ? true: false;
$chequeo['sector'] = true;
/* 
 funcionamiento: bool checkdate  ( int $month  , int $day  , int $year  ) 
*/
// es true si fecha_inicio es menor a la segunda
// compara_fechas('yyyy/mm/dd','yyyy/mm/dd');

    if (!$chequeo['sector'] ){
            print "Dato de tipo NO VALIDO<br />";
            /*Decir cuales valores son incorrectos*/
            //var_dump($chequeo); 
    }else{
        require_once('dbinfo.php');
        require_once('MDB2.php');

        // Conecto a DB Flexar
        $mdb2 =& MDB2::singleton($dsn, $options);
        if (PEAR::isError($mdb2)) {
                 die($mdb2->getMessage());
        }
        $query = "SELECT Lotes.Modelo, Lotes.Lote, Lotes.Cantidad
                  FROM Lotes
                        inner join operaciones on operaciones.idoperacion=lotes.area
                  WHERE operaciones.operacion=".$mdb2->quote($sector,'text')." AND Lotes.OTAsignada=0 AND Lotes.Terminado=0 or Lotes.Terminado=NULL";

        $res =& $mdb2->query($query);

        if (PEAR::isError($res)) {
                    die($res->getMessage());
        }
        $rows = $res->fetchAll(MDB2_FETCHMODE_ASSOC);
        require_once 'include/pear/Sigma.php'; //insertamos la libreria
        $it = new HTML_Template_Sigma('themes'); //declaramos el objeto
        $it->loadTemplatefile('pendientes_gerencia.html', true, true); //seleccionamos la plantilla

        foreach($rows as $name) {
            // Assign data to the inner block
                $it->setCurrentBlock("PEND_GERENCIA");
                $it->setVariable("MODELO", $name['modelo']);
                $it->setVariable("LOTEPRO", $name['lote']);
                $it->setVariable("LOTCANTIDAD", $name['cantidad']);
                $it->parseCurrentBlock("PEND_GERENCIA");
            
            $it->parse("row_lemba");
        }
       $it->show(); 
    }
}

function reparaciones_modelo_fecha($q){
/*
    En $q viene todas las variables en orden:
    $q = serie, ot, sector, nom_operario, fecha_inicio, fecha_final, marca, modelo
*/

$modelo = trim($q[7]);

/* Chequear si nom_operario es alpha, si las fechas son validas y no se superponen. */

$chequeo = array();
//$chequeo['nom_operario'] = ctype_alpha(trim($nomyapellido[0])) ? true: false;
//$chequeo['apellido_operario'] = ctype_alpha(trim($nomyapellido[1])) ? true: false;

$fechai = explode("/", $q[4]);
$fechaf  = explode ("/", $q[5]);
/* Chequear si nom_operario es alpha, si las fechas son validas y no se superponen. */

$chequeo = array();
//$chequeo['nom_operario'] = ctype_alpha(trim($nomyapellido[0])) ? true: false;
//$chequeo['apellido_operario'] = ctype_alpha(trim($nomyapellido[1])) ? true: false;
/* 
 funcionamiento: bool checkdate  ( int $month  , int $day  , int $year  ) 
*/
$chequeo['fechai'] = checkdate($fechai[1],$fechai[0],$fechai[2]);
$chequeo['fechaf'] = checkdate($fechaf[1],$fechaf[0],$fechaf[2]);
$chequeo['modelo'] = true;

// es true si fecha_inicio es menor a la segunda
// compara_fechas('yyyy/mm/dd','yyyy/mm/dd');
if (compara_fechas($fechai[2]."/".$fechai[1]."/".$fechai[0], $fechaf[2]."/".$fechaf[1]."/".$fechaf[0]) > 0)
    $chequeo['comp_fechas'] = false;
else
    $chequeo['comp_fechas'] = true;

    if (!$chequeo['modelo'] or !$chequeo['fechai'] or !$chequeo['fechaf'] or !$chequeo['comp_fechas']){
            print "Dato de tipo NO VALIDO<br />";
            /*Decir cuales valores son incorrectos*/
            //var_dump($chequeo); 
    }else{
        require_once('dbinfo.php');
        require_once('MDB2.php');

        // Conecto a DB Flexar
        $mdb2 =& MDB2::singleton($dsn, $options);
        if (PEAR::isError($mdb2)) {
                 die($mdb2->getMessage());
        }

        $fecha_inicio = $fechai[1]."/".$fechai[0]."/".$fechai[2];
        $fecha_final = $fechaf[1]."/".$fechaf[0]."/".$fechaf[2];

        $query = "SELECT Lotes.Modelo, Lotes.Fecha as fechalote, ReparacionesInternas.Serie, Impedancias.Lote, ReparacionesInternas.Fecha as fechareparaciones,
                         Operaciones.Operacion, [Lista de Diagnosticos].Descripcion
                        FROM (((Lotes INNER JOIN (ReparacionesInternas INNER JOIN [Lista de Diagnosticos] ON 
                    ReparacionesInternas.Diagnostico = [Lista de Diagnosticos].Id) ON Lotes.Lote = ReparacionesInternas.Lote) 
                    INNER JOIN Operaciones ON ReparacionesInternas.AreaDeOrigen = Operaciones.IdOperacion) LEFT JOIN SubLotes ON ReparacionesInternas.Lote = SubLotes.SubLote) 
                    INNER JOIN Impedancias ON ReparacionesInternas.Serie = Impedancias.Serie
                    WHERE Lotes.Modelo=".$mdb2->quote($modelo,'text')." AND Lotes.Fecha>=".$mdb2->quote($fecha_inicio,'date')." AND Lotes.Fecha<=".$mdb2->quote($fecha_final,'date')."";
                    
                        /*GROUP BY Lotes.Modelo, Lotes.Fecha, ReparacionesInternas.Serie, Impedancias.Lote, ReparacionesInternas.Fecha, Operaciones.Operacion, [Lista de Diagnosticos].Descripcion*/
                  /*FROM Lotes INNER JOIN ReparacionesInternas INNER JOIN [Lista de Diagnosticos] ON 
                             ReparacionesInternas.Diagnostico = [Lista de Diagnosticos].Id ON 
                             Lotes.Lote = ReparacionesInternas.Lote 
                        INNER JOIN Operaciones ON ReparacionesInternas.AreaDeOrigen = Operaciones.IdOperacion LEFT JOIN SubLotes ON ReparacionesInternas.Lote = SubLotes.SubLote
                        INNER JOIN Impedancias ON ReparacionesInternas.Serie =Impedancias.Serie */

        $res =& $mdb2->query($query);

        if (PEAR::isError($res)) {
                    die($res->getMessage());
        }
        $rows = $res->fetchAll(MDB2_FETCHMODE_ASSOC);
        require_once 'include/pear/Sigma.php'; //insertamos la libreria
        $it = new HTML_Template_Sigma('themes'); //declaramos el objeto
        $it->loadTemplatefile('reparaciones_gerencia.html', true, true); //seleccionamos la plantilla

        foreach($rows as $name) {
            // Assign data to the inner block
                $it->setCurrentBlock("REPA_GERENCIA");
                $it->setVariable("SECTOR", $name['operacion']);
                $it->setVariable("MODELO", $name['modelo']);
                $it->setVariable("NRO_SERIE", $name['serie']);
                $it->setVariable("LOTEPRO", $name['lote']);
                $it->setVariable("FECHALOTE", substr($name['fechalote'],0,11));
                $it->setVariable("FECHAREPA", substr($name['fechareparaciones'],0,11));
                $it->setVariable("DESCRIPCION", $name['descripcion']);
                $it->parseCurrentBlock("REPA_GERENCIA");
            
            $it->parse("row_lemba");
        }
       $it->show(); 
    }
}
?>
