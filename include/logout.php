<?php

session_start();
unset($_SESSION['username']);

echo '<center><h2>Usted se ha deslogueado</h2>';
?>		
<html>
	<head>
	 <script language="Javascript">
		setTimeout("location.replace('consulta.html')",2000);
	</script>
	</head>
<html>
