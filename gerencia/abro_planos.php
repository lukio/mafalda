<html>
<head>
 <title>Planeando web</title>
 <STYLE type="text/css">
<!-- 
BODY{
   font-family: Verdana; 
   font-size: 11px; 
   font-style: normal;
   color: #000000;
}
// -->
</STYLE>
</head>
<body>

<?php
require 'dbinfo.php';
require 'DB.php';
//require 'pdf.php';

// se hacen las conexiones al principio. Si falla no continua
 //conectar a la db flexar
$id_db_flexar = DB::connect("$sql_program_db_flexar://$sql_usuarioflexar:$sql_pwdflexar@$host/$sql_db_flexar");
 if (DB::isError($id_db_flexar)) { die("No se puede conectar: " . $id_db_flexar ->getMessage()); }
 //establezco gestion automatica de errores
 $id_db_flexar -> setErrorHandling(PEAR_ERROR_DIE);

$modelo = $_POST['modelo'];
$tipo_planos = $_POST['plano'];

$query = "SELECT $tipo_planos FROM LinkPlanos WHERE Modelo='$modelo'";

$puntero_plano = $id_db_flexar->getOne($query);
$separado = explode(" ", $puntero_plano);
$junto = implode("%20", $separado);

echo '<h2>Abrir Plano</h2><br />';

if ($puntero_plano == null)
	echo 'El modelo: '.$modelo.' no tiene planos del tipo: '.$tipo_planos;
else
	echo '<a href=file:///'.$junto.'>Plano de '.$tipo_planos.'</a>';


/*
$filename="PM-CCC-100-R1.pdf";
$file = 'file:'.$junto;//nombre y ruta completa al archivo z:/micarpeta/mipdf.pdf
//$file = "prueba.pdf";
echo $file;
//if(!file_exists($file))
//exit;
$fp = file_get_contents($file);
$filesize=filesize($file);

header("Content-type: application/pdf");
header("Content-Length: ".$filesize);
header("Content-Disposition: inline; filename=$filename");
header("Content-Transfer-Encoding: binary");
echo $fp;
 */

/*
//para abrir el pdf se hace todo esto. i hope!
//$filepdf = pdf_new();
//pdf_open_file($filepdf, "firstfile.pdf");

// open second file
$pdf = pdf_new();
//pdf_open_file($pdf); 
// open open first file and read values
$pdi = pdf_open_pdi($pdf, "prueba.pdf", ""); //aca va el path al archivo

// output complete document
$data = pdf_get_buffer($pdf);
header("Content-type: application/pdf");
header("Content-disposition: inline; filename=prueba.pdf");
header("Content-length: " . strlen($data));
echo $data;

echo "que paso?";
	 */
?>
</body>
</html>
