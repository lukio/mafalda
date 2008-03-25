<?php
// Fichero que realiza la consulta en la base de datos y devuelve los resultados
if(isset($_POST["word"]))
{
	// Conectamos con la base de datos
	$link=mysql_connect("localhost", "all", "all");
	mysql_select_db("table", $link);

	if($_POST["word"]{0}=="*")
		$result=mysql_query("SELECT * FROM Diccionario WHERE Palabra LIKE '%".substr($_POST["word"],1)."%' and Palabra<>'".$_POST["word"]."' ORDER BY Palabra LIMIT 10",$link);
	else
		$result=mysql_query("SELECT * FROM Diccionario WHERE Palabra LIKE '".$_POST["word"]."%' and Palabra<>'".$_POST["word"]."' ORDER BY Palabra LIMIT 10",$link);

	while($row=mysql_fetch_array($result))
	{
		// Mostramos las lineas que se mostraran en el desplegable. Cada enlace
		// tiene una funcion javascript que pasa los parametros necesarios a la
		// funcion selectItem
		echo "<a href=\"javascript:selectItem('".$_POST["idContenido"]."','".$row["Palabra"]."')\">".$row["Palabra"]."</a>";
	}
}
?>
