<?php

$listado_archivos = scandir("/tmp");
//rsort($listado_archivos);
foreach ($listado_archivos as $file)
        print $file."\n";

?>
