<?php


/* Si le ponemos login a esta page ...
session_start();
	if (!array_key_exists('username', $_SESSION)) {
		print 'session ha sido finalizada';
		print '<a href="login.html">Pagina Login</a>';
		
<html>
<head>
	 <script language="Javascript">
		setTimeout("location.replace('consulta.html')",2000);
	</script>
	</head>
<html>
		print "Hello, $_SESSION[username].";
		}else {
 */

require 'dbinfo.php';

/* orden de mecanizado = lotes que salieron con ese orden de mecanizado. mostrar observaciones */
$nomecanizado = $_GET['nomecanizado'];

//conecta a una dbms ... usar persistente? odbc_pconnect
if (!($connectstring = odbc_connect($sql_db_flexar,$sql_usuarioflexar, $sql_pwdflexar))){
	echo 'Fallo conexion: server sql';
	exit();
}


$query = "SELECT Lote, Modelo, Cantidad, LEFT(Fecha,11) AS FechaIni 
	  FROM Lotes WHERE OCMecanizado=$nomecanizado
	  ORDER BY Fecha";

if(!( $result = odbc_do($connectstring, $query))){
	echo 'fallo query a tabla Lotes';
	exit();
} 
			 
/**********IMPRESION*************/
print '<center><br>';
print 'Orden de mecanizado:<b>' . $nomecanizado .'</b><br><br>';

if(!odbc_result_all($result, "border=1 cellspacing=0 cellpadding=1")){
	echo 'Num. Orden Mecanizado INCORRECTO';
	exit();
}
//} fin session
?>
