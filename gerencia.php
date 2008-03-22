<?php


$months = array(01 => 'Enero', 02 => 'Febrero', 03 => 'Marzo', 04 => 'Abril', 
				05 => 'Mayo', 06 => 'Junio', 07 => 'Julio', 08 => 'Agosto', 
				09 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 
				12 => 'Diciembre');


//comenzamos el formulario

echo '<div style="position:absolute; left:20; top:25;">';

print '<form method="POST" action="por_oper.php" target="_blank">';
print '<b>Numero Operario : </b><input type="text" name="noper" size=4>';
print '<br /><br /><b>Fecha Inicio: </b>';

//una para cada dia
$diaactual = date('d');
print '<select name="dia">';
for ($i =1 ; $i<= 31; $i++){
	if ($diaactual == $i)
		print '<option SELECTED value="' . $i .'">' .$i ."</option>\n";
	else
		print '<option value="' . $i .'">' .$i ."</option>\n";
}
print "</select> \n";
$mesactual= date ('m');
$mesatras = ( ($mesactual-1)==0? 12: $mesactual);
print '<select name="mes">';
//una opcion para cada elemento en $months
foreach ($months as $num => $month_name) {
	if ($mesatras == $num)
		print '<option SELECTED value=" ' . $num . '">' . $month_name . "</option>\n";
	else
		print '<option value=" ' . $num . '">' . $month_name . "</option>\n";
}
	print "</select> \n";

//una para cada anio
$anioactual = date('Y');
$anioatras = ($mesatras == 12)?$anioactual-1:$anioactual;
print '<select name="anio" value='.$anioactual.'>';
for ($year = 1998, $max_year = date ('Y') + 5; $year < $max_year; $year++){
	if ($anioatras == $year)
		print '<option SELECTED value="' . $year . '">' .$year . "</option>\n";
	else
		print '<option value="' . $year . '">' .$year . "</option>\n";
}
print "</select> \n";
echo '</div>';
echo '<div style="position:absolute; left:24; top:94;">';
	echo '<b>Fecha Final: </b>';
	//una para cada dia
	print '<select name="diaT">';
	for ($i =1 ; $i<= 31; $i++){
	if ($diaactual == $i)
			print '<option SELECTED value="' . $i .'">' .$i ."</option>\n";
		else
			print '<option value="' . $i .'">' .$i ."</option>\n";
	}
	print "</select> \n";

	print '<select name="mesT">';
	//una opcion para cada elemento en $months
	foreach ($months as $num => $month_name) {
		if ($mesactual == $num)
		print '<option SELECTED value=" ' . $num . '">' . $month_name . "</option>\n";
		else
		print '<option value=" ' . $num . '">' . $month_name . "</option>\n";
	}
	print "</select> \n";


	//una para cada anio
	print '<select name="anioT">';

	for ($year = 1999, $max_year = date ('Y') + 5; $year < $max_year; $year++){
		if ($anioactual==$year)
		print '<option SELECTED value="' . $year . '">' .$year . "</option>\n";
		else
			print '<option value="' . $year . '">' .$year . "</option>\n";
	}
	print "</select> \n";
	print '<br /><br /><input type="submit" name="Submit" value="Numeros de OT por fecha"></form>';
echo '</div>';

//segundo formulario: lista de opearios

echo '<div style="position:absolute; left:220; top:25;">';
	print '<form method="POST" action="lista_operarios.php" target="resultados">';
	print '<input type="submit" name="Submit" value="Lista de Operarios">';
	print '</form>';
echo '</div>';


//tercer formulario: nro orden de trabajo
echo '<div style="position:absolute; left:450; top:50;">';
	print '<form method="GET" action="p_orden.php" target="_blank">';
	print '<b>Numero de OT: </b><input type="text" name="norden" size=8>';
	print '<input type="submit" name="Submit" value="OT. por oper">';
	print '</form>';
echo '</div>';


//cuarto formulario: por nro orden de mecanizado
//
//echo '<div style="position:absolute; left:400; top:150;">';
//	print '<form method="POST" action="por_omeca.php" target="_blank">';
//	print '<b>Nro Orden Mecanizado: </b><input type="text" name="nomecanizado" size=8>';
//	print '<input type="submit" name="Submit" value="O. mecanizado">';
//	print '</form>';
//echo '</div>';

print '<div style="position:absolute; left:600; top:10;"><A HREF="logout.php">Log out</A></div>';

// por numero de serie !
echo '<div style="position:absolute; left:450; top:100;">';
		echo'<form method="GET" action="por_serie.php" target="resultados">';
		echo '<b>Numero Serie : </b><input type="text" name="celda" size=7>';
		echo '<input type="submit" name="Submit" value="Consulte">';
		echo '</form>';
echo '</div>';



print '<div style="position:absolute; left:0; top:190;"><hr /></div>';

print '<div style="position:absolute; left:20; top:200;"><iframe  style="width: 790px; height: 800px" name=resultados border=0 frameborder=0 ></iframe></div>';


	
?>
