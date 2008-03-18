<?php
$data = array (
        array('Azul',      'Cielo'),
        array('Rojo',      'Cereza'),
        array('Verde',    'Aceituna')
);

//creamos el codigo puente
require_once 'include/pear/Sigma.php'; //insertamos la libreria
$it = new HTML_Template_Sigma('themes'); //declaramos el objeto
$it->loadTemplatefile('theme.html'); //seleccionamos la plantilla
/*foreach ($data as $name) { //recorremos el array
    $it->setCurrentBlock('tabla'); //buscamos el bloque
    $it->setVariable('DETALLE', $data[0][0]); //declaramos la variable COLOR
    $it->setVariable('VALOR', $data[0][1]); //declaramos la variable DESCRIPCION
    $it->parseCurrentBlock(); //generamos la parte del bloque analizado
}*/
$it->show(); //mostramos el resultado

?>

