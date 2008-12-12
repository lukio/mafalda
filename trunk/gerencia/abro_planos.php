<?php

$file = $_GET['q'];
$diryfile = explode (":",$file);

$archivo = file_get_contents ($diryfile[0].$diryfile[1]); 

Header("Content-Description: File Transfer");
header("Content-Type: application/force-download");
header('Content-Disposition: attachment; filename="'.$diryfile[1].'"');
echo $archivo;

?>
