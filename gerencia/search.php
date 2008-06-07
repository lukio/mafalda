<?php

$q = strtolower($_GET["q"]);
$id = strtolower($_GET["id"]);
$sector = strtolower($_GET["sector"]);

if (!$q) return;
$items = array();
$ruta = '../include';
set_include_path(get_include_path() . PATH_SEPARATOR . $ruta);
require_once('../dbinfo.php');
require_once('MDB2.php');

// Conecto a DB Flexar
$mdb2 =& MDB2::singleton($dsn, $options);
if (PEAR::isError($mdb2)) {
         die($mdb2->getMessage());
}
//consulta a dbms  . Selecciona nroserie, dia embalado segun el nro lote embalado
if ($id == "operario"){
    if (!$sector)
        $query = "SELECT idoperario, nombre, apellido FROM operarios";
    else
        $query  = "SELECT idoperario, nombre, apellido FROM operarios";
}elseif ($id == "sector"){
    $query = "SELECT idoperacion, operacion FROM operaciones";
}else{
    return;
}

$res =& $mdb2->query($query);

if (PEAR::isError($res)) {
        die($res->getMessage());
}
$rows = $res->fetchAll(MDB2_FETCHMODE_ASSOC);

if ($id == "operario"){
foreach ($rows as $name) {
	if (strpos(strtolower(utf8_encode($name['nombre'])), $q) !== false or strpos(strtolower(utf8_encode($name['apellido'])), $q) !== false) {
		//echo $name['idoperario']."_".utf8_encode($name['apellido']).", ".utf8_encode($name['nombre'])."\n";
		echo utf8_encode($name['apellido']).", ".utf8_encode($name['nombre'])."\n";
	}
}
}elseif ($id == "sector" ){
foreach ($rows as $name) {
	if (strpos(strtolower(utf8_encode($name['operacion'])), $q) !== false) {
		echo $name['idoperacion']."_".utf8_encode($name['operacion'])."\n";
	}
}
}else
    return;

?>
