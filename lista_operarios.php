<?php
require 'dbinfo.php';
session_start();

if (!array_key_exists('username', $_SESSION)) {
	print "session ha sido finalizada";
	print '<a href="login.html">Pagina Login</a>';
}else {
	if (!($connectstring = odbc_connect($sql_db_flexar,$sql_usuarioflexar, $sql_pwdflexar))){
		echo 'Fallo conexion base de datos flexar';
		exit();
	}
	
	//cambiar el & por || cuando lo pase a una base de datos SQL
	$query = "SELECT IdOperario, Nombre AS Nombre, Apellido AS Apellido 
		  FROM Operarios WHERE Inactivo=0 
		  ORDER BY Operarios.Nombre";
			
	if (!($result = odbc_do($connectstring, $query))){
		echo 'Fallo query tabla: lista operarios';
		exit();
	}

	if(!(odbc_result_all($result, "BORDER=1 cellspacing=0 cellpadding=1"))){
		echo '<center><b>NO se muestran resultados</b></center>';
		exit();
	}

	odbc_close($connectstring);
}
?>
