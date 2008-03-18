<?php
require 'dbinfo.php';
// start the session
session_start();
header("Cache-control: private"); //IE 6 Fix

// Get the user's input from the form
   $username = $_POST['username'];
   $password = $_POST['password'];
//   $password= md5($password);

if (!($connectstring = odbc_connect($sql_db_flexar,$sql_usuarioflexar, $sql_pwdflexar))){
		echo 'Fallo conexion base de datos flexar';
		exit();
	}
	

$query ="SELECT ID_user FROM Usuarios WHERE login='$username' AND password='$password'";

if (!($result = odbc_do($connectstring,$query))){
	print "fallo query tabla usuarios";
	exit();
}


if ( odbc_result($result,1) ){
echo '<center><b>Usuario Validado <br>';
//     echo session_id();
     $_SESSION['username'] = $username;
}
     if (isset($_SESSION['username'])) {
     echo "<br>El usuario validado es:  ".$_SESSION['username'];
?>
<html>
	<head>
	 <script language="Javascript">
		setTimeout("location.replace('fechas.php')",2000);
	</script>
	</head>
<html>
<?php
	// echo "<br><a href=\"fechas.php\">Pagina consulta - Superior</a>";
	 }else 
	     echo '<b>Usuario o password no valido</b>';
?>
