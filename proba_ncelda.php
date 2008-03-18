<?php
require 'dbinfo.php';
$ncelda = $_POST['ncelda'];

//conecto a db Flexar
if (!($idbase = odbc_connect($sql_db_flexar,$sql_usuarioflexar, $sql_pwdflexar))){
	echo 'Fallo conexion base de datos flexar';
	exit();
}
		


//consulta a dbms  . Selecciona * A Impedancias
/* querys que se pueden borrar
 *
 * $query = "SELECT serie, Area, MedTerminada, impsalida, impentrada, tensalida, dircarga, aiscuerpo, fecha FROM Probatuti WHERE serie=$ncelda";
 * $query = "SELECT prob.serie AS "Nro. Serie", prob.Area AS "Area", prob.MedTerminada AS "Med. Terminada", prob.impsalida AS "Impedancia Salida", prob.impentrada AS "Impedancia Entrada", prob.tenssalida AS "Ten. Salida", prob.dircarga AS "Dir. Carga", prob.aiscuerpo AS "Aislacion del Cuerpo", prob.fecha AS "Fecha" FROM Probatuti WHERE serie=$ncelda ORDER BY fecha, Area";
 * toma OperProba
 * $query = "SELECT prob.serie AS Serie, opera.Operacion AS Area, op.Nombre, op.Apellido, prob.MedTerminada AS MedTer, prob.impsalida AS ImpSa, prob.impentrada AS ImpEn, prob.tenssalida AS TensSa, prob.dircarga AS DirCarga, prob.aiscuerpo AS AislaCuore, prob.fecha AS Fecha FROM Probatuti prob, Operaciones opera, Operarios op WHERE prob.Area=opera.IdOperacion AND prob.operador=op.OperProba AND prob.serie=$ncelda ORDER BY prob.fecha, prob.Area";
 * toma idoperario
 * $query = "SELECT prob.serie AS Serie, opera.Operacion AS Area, op.Nombre, op.Apellido, prob.MedTerminada, prob.impsalida AS ImpeSalida, prob.impentrada AS ImpeEntrada, prob.tenssalida AS TensionSalida, prob.dircarga AS DirCarga, prob.aiscuerpo AS AislacionCuerpo, prob.fecha AS Fecha FROM Probatuti prob, Operaciones opera, Operarios op WHERE prob.Area=opera.IdOperacion AND prob.operador=op.IdOperario AND prob.serie=$ncelda ORDER BY prob.fecha, prob.Area";
 * prob JOIN Operaciones opera ON (prob.Area = opera.IdOperacion) WHERE serie=$ncelda";// ORDER BY fecha, Area";
 **/

$query = "SELECT opera.Operacion AS Area, op.Nombre, op.Apellido, prob.MedTerminada AS MedTer, 
	  prob.impsalida AS ImpSa, prob.impentrada AS ImpEn, prob.tenssalida AS TensSa,
	  prob.dircarga AS DirCarga, prob.aiscuerpo AS AislaCuore, LEFT(prob.fecha, 11) AS Fecha 

	  FROM Probatuti prob, Operaciones opera, Operarios op 

	  WHERE prob.Area=opera.IdOperacion AND prob.operador=op.OperProba AND prob.serie=$ncelda 

	  ORDER BY prob.fecha, prob.Area";


if(!($result = odbc_do($idbase,$query))){
	echo 'Fallo query tabla Probatuti, Operaciones u Operarios';
	exit();
}

print("<br /><br /><center><b>Tabla Probatuti - Especificaciones de la celda: $ncelda <br /><br /></b>");


if(!odbc_result_all($result,"BORDER=1 cellspacing=0 cellpadding=1 witdth=745")){
	echo 'Fallo impresion odbc_result_all';
	exit();
}

$query = "SELECT opera.Operacion AS Area, op.Nombre, op.Apellido, Lata.MedTerminada AS MedTer, 
	  Lata.impsalida AS ImpSa, Lata.impentrada AS ImpEn, Lata.tenssalida AS TensSa, 
	  Lata.dircarga AS DirCarga, Lata.aiscuerpo AS AislaCuore, LEFT(Lata.fecha, 20) AS Fecha 

	  FROM Lata, Operaciones opera, Operarios op 
	  
	  WHERE Lata.Area=opera.IdOperacion AND Lata.operador=op.OperProba AND Lata.serie=$ncelda
	  AND Lata.Embalada=0
	  
	  ORDER BY Lata.fecha, Lata.Area";

if(!($result = odbc_do($idbase,$query))){
	echo 'Fallo query tabla Lata, Operarios u Operaciones';
	exit();
}

print("<br /><br /><center><b>Tabla Lata - Especificaciones de la celda: $ncelda <br /><br /></b>");


if(!odbc_result_all($result,"BORDER=1 cellspacing=0 cellpadding=1 witdth=745")){
	echo '<b>No tenemos nada en Tabla Lata</b>';
	exit();
	}
?>
