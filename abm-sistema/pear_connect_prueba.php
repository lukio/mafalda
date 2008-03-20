<?php
require 'DB.php';
require_once 'dbinfo.php';

//conectamos a db flexar
//	if (!($id_db_flexar = odbc_connect($db_flexar, $usuarioflexar, $pwdflexar))){
//		echo 'Fallo conexion base de datos produccion.dsn';
//		exit();
//		}

	//	$retorno = odbc_autocommit($id_db_flexar, FALSE);
	//	echo $retorno;

		
//		$query = "SELECT Modelo FROM Modelos";

//	if(!($resultflexar = odbc_do($id_db_flexar, $query))){
//		echo 'Fallo conexion odbc_do: Ensayos';
//		exit();
//		}

	//	  odbc_commit($id_db_flexar);
	//	echo odbc_cursor($resultflexar);
//echo odbc_procedures($id_db_flexar);

 $id_db_flexar = DB::connect("$program_db_flexar://$usuarioflexar:$pwdflexar@$host/$db_flexar");
 if (DB::isError($id_db_flexar)) { die("No se puede conectar: " . $id_db_flexar ->getMessage()); }

 	$q = $id_db_flexar-> query ('SELECT Modelos.Modelo FROM Modelos');

	while ($row = $q -> fetchRow()) {
		print "$row[0] \n";
	}

?>
