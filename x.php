<?php
$data = $_GET['celda'];

//creamos el codigo puente
require_once 'include/pear/Sigma.php'; //insertamos la libreria

$it = new HTML_Template_Sigma('themes'); //declaramos el objeto

$it->loadTemplatefile('theme.html'); //seleccionamos la plantilla
    $it->setCurrentBlock('celda_div'); //buscamos bloque
    $it->setVariable('_CELDA_DIV_', $data);
    $it->parseCurrentBlock('celda_div'); //generamos la parte del bloque analizado
$it->show(); //mostramos el resultado
?>
