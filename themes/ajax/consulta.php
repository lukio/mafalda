<?php
// Fichero que realiza la consulta en la base de datos y devuelve los resultados
if(isset($_POST["word"]))
{
	// Conectamos con la base de datos
     // Conecto a DB Flexar
    require_once('../../dbinfo.php');
    require_once ('MDB2.php');

        $mdb2 =& MDB2::singleton($dsn, $options);
        if (PEAR::isError($mdb2)) {
                 die($mdb2->getMessage());
        }

        $query = "select operacion from operaciones";
        $word = $_POST['word'];
        $query = "SELECT * FROM Operaciones WHERE operacion LIKE '".$word."' and operacion<>'".$word."' ORDER BY operacion LIMIT 10";

/*	if($_POST["word"]{0}=="*")
		$result=mysql_query("SELECT * FROM Diccionario WHERE Palabra LIKE '%".substr($_POST["word"],1)."%' and Palabra<>'".$_POST["word"]."' ORDER BY Palabra LIMIT 10",$link);
	else
*/
    /*while($res_ensayos = $mdb2->queryRow($query))
//	while($row=mysql_fetch_array($result))
	{
		// Mostramos las lineas que se mostraran en el desplegable. Cada enlace
		// tiene una funcion javascript que pasa los parametros necesarios a la
		// funcion selectItem
		//echo "<a href=\"javascript:selectItem('".$_POST["idContenido"]."','".$res_ensayos["operaciones"]."')\">".$res_ensayos["operaciones"]."</a>";
	}*/
}
?>
