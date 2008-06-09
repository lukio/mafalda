<?php

$sector = $_GET["sector"];

if (!$sector) return;
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
$query  = "SELECT operarios.nombre, operarios.apellido 
           FROM operarios, operaciones 
           WHERE operarios.area=operaciones.idoperacion and operaciones.operacion=".$mdb2->quote($sector,'text')."
           order by operarios.apellido"; 

$mdb2->setFetchMode(MDB2_FETCHMODE_ASSOC);

$operarios =& $mdb2->queryAll($query);

if (PEAR::isError($res)) {
        die($res->getMessage());
}
echo "<select name='nombre_operador' id='nombre_operario_id' tabindex='3'>";
foreach ($operarios as $name) {
		echo "<option value=\"".utf8_encode($name['apellido']).", ".utf8_encode($name['nombre'])."\">".utf8_encode($name['apellido']).", ".utf8_encode($name['nombre'])."</option>";
	}

echo "</select>";
?>
