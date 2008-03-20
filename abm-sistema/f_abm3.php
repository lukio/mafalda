<?php

/**
* Altas, Bajas, Modificaciones (ABM) de Modelos - Flexar SRL - Enero 2006
* TipoABM :
*			Alta = 1
*			Baja = 2 
*			Modificaciones = 3 
**/
require 'validaciones.php';
require 'dbinfo.php';
require 'DB.php';
ini_set('session.gc_probability', '100');
ini_set('session.gc_maxlifetime', '600');
session_start();

//require 'DB_Table.php';
//#define NUEVO_MODELO 1;
//#define NO_HAY_MODELO 0;
#define CANT_CHECKBOX 5;
//#define CANT_CAMPOS  41;
// se hacen las conexiones al principio. Si falla no continua
 //conectar a la db flexar
 $id_db_flexar = DB::connect("$program_db_flexar://$usuarioflexar:$pwdflexar@$host/$db_flexar");
 if (DB::isError($id_db_flexar)) { die("No se puede conectar: " . $id_db_flexar ->getMessage()); }
 //establezco gestion automatica de errores
 $id_db_flexar -> setErrorHandling(PEAR_ERROR_DIE);

 //conectar a la db Tango
 $id_db_tango = DB::connect("$program_db_tango://$usuariotango:$pwdtango@$host/$db_tango");
 if (DB::isError($id_db_tango)) { die("No se puede conectar: " . $id_db_tango ->getMessage()); }
 //establezco gestion automatica de errores
 $id_db_tango -> setErrorHandling(PEAR_ERROR_DIE);


 
 $tipoabm = $_GET['tipoabm'];
$tipoabm=trim($tipoabm);
	if (!is_number($tipoabm) ){
			impresion_error("Dato de TIPO Invalido");
			exit ();
	}else {
			switch($tipoabm){
	/** Cuando sepa como Tango deja inactivos las celdas cargadas en sus sistema implemento 
	    lo de chequear inconsistencias entre flexar y tango	
		    case 1: chequear_inconsistencias() ? show_or_not() : impresion_error("NO hay nuevos Modelos a Ingresar"); */					
				case 1:	show_or_not(); 
					break;
			   	case 2:	show_or_bmodelos();
					//b_modelos();
					break;
				case 3: m_modelos();
					break;
			}
				
	}

function chequear_inconsistencias(){
	/** SE DECIDE DEJAR INHABILITADO ESTE CHEQUEO HASTA QUE PODAMOS SABER COMO EL TANGO DEJA
	* INACTIVAS LAS CELDAS CARGADAS EN SU SISTEMA
	*
	* 1) Chequear contra la db de Tango si se ha agregado un nuevo
	*	modelo. De ser asi que de la posibilidad de dar nuevas altas. Sino AVISO de que NO hay nuevos Modelos 
	* 2) Si hay inconsistencias entonces aparece el front-end con nuevo modelo a insertar  los inputs correspondientes
	Todos los nombres de la db_flexar, db_tango, etc se encuentran en dbinfo.php
	**/
/*
	//las conexion se abren al principio	
	//armamos query de lista de Modelos (Tablas Produccion)
	$query = 'SELECT Modelos.Modelo FROM Modelos';

	/**
	 * en este bloque traemos la variable global de db_flexar 
	 * traemos fila por fila y la copiamos a un array local (tanto para flexar
	 * como para Tango), luego se comparan los arrays. 
	 **/
/*	
	global $id_db_flexar;
	$array_flexar = array();
	$f=0;
	$q = $id_db_flexar -> query($query);
	
	while ($row_flexar = $q -> fetchRow()){
		 $array_flexar[$f++] = $row_flexar[0];
	}
	$f--;
	
	$nro_querestringe='1001';
	$query = "SELECT SUBSTRING(dbo.STA11.COD_ARTICU,5,100) FROM dbo.STA11 WHERE dbo.STA11.COD_ARTICU LIKE '$nro_querestringe%'";
	
	global $id_db_tango;
	$array_tango = array();
	$q = $id_db_tango -> query($query);
	$t = 0;
	while ($row_tango = $q -> fetchRow()){
		 $array_tango[$t++] = $row_tango[0];
	}
	$t--;
	
	// fin obtencion de Modelos de Flexar y de Tango. 

	/** Comienza la comparacion
	* Puedo hacerles un sort a cada uno, y luego hacer la busqueda
	* SI -> encuentro modelo => corto busqueda y retorno OK
	* NO encuentro ningun modelo, retorno "pulgar abajo"
	* f = cantidad de registros de Flexar
	* t = cantida de registros de Tango
	*
	* hago for . uala !
	* array_modelos = lista de modelos que no existen en Flexar y si existen en Tango
	**/
/*	$array_modelos = array();
	
	sort($array_flexar, SORT_STRING);		
	sort($array_tango, SORT_STRING);

//la de tango es la que tiene que estar mas llena, sino tiene que haber la misma cantidad de registros !
/*	for ($i =0, $n=0, $s=0 ; $i < $t; $i++, $s++)
		if ($array_tango[$i] != $array_flexar[$s]){
			$array_modelos[$n++] = $array_tango[$i];
			$s--;
		//	return NUEVO_MODELO;			
		}
		$n--;
		
		for ($fin =0 ; $fin<$n; $fin++)
			echo "$array_modelos[$fin] <br>";

*/
/*		
echo '<div style="position:absolute; left:60; top:140;">';
echo "flexar<br>";
		for ($fin =0 ; $fin<$f; $fin++)
			echo "$array_flexar[$fin] <br>";
echo '</div>';

echo '<div style="position:absolute; left:300; top:140;">';
echo "tango<br>";
		for ($fin =0 ; $fin<$t; $fin++)
			echo "$array_tango[$fin] <br>";
echo '</div>';


			
/*	busqueda para retornar el primer modelo con diferencias
	for ($i =0; $i < $t; $i++)
		if ($array_tango[$i] != $array_flexar[$i]){
		//	$array_modelos[$n++] = $array_tango[$i++];
			return NUEVO_MODELO;			
		}
		
*/
		
//	return NO_HAY_MODELO;				
   //for ($j=0; $j<$f; $t++) //flexar
	//if ($array_tango[$i] == $array_flexar[$j]){
		//$flag = 1;
	 //}


//for ($i =0; $i < $t; $i++)
	//print "$array_tango[$i]<br>";

//for ($i =0; $i < $f; $i++)
	//print "$array_flexar[$i]<br>";


}

function impresion_error($error){
echo '<center><h2>'.$error.'</h2></center>';
}


function show_or_not() {	
		if (isset($_GET['_submit_check'])){
		  if ($_SESSION['estado'] == "on"){	
			if ($form_errors = validamos_formulario()) { //si validamos_formulario() devuelve errores entonces se lo pasamos a imprimir_datos()
					imprimir_entrada_datos($form_errors);
				}else { //los datos son validos entonces se procesan
					a_modelos();				
				}
			 }else{
				//limpiar GET
				limpiarGet();
				imprimir_entrada_datos();
				}
			}else{ //el formulario no se ha enviado entonces se muestra
				imprimir_entrada_datos();
		}	

}

function show_or_bmodelos() {	
		if (isset($_GET['_submit_check'])){
				b_modelos();				
				}
			else{ //el formulario no se ha enviado entonces se muestra
				show_baja_modelos();
		}	

}

function validamos_formulario(){
$errores = array();
for ($i=5; $i < 40; $i++){
if (!is_number(trim($_GET[$i]))){
	if(!empty($_GET[$i]))
		$errores[$i] = '<font color=red>Invalido</font>';
//	elseif(!($i=17 || $i=18 || $i=19 || $i=20 || $i=21 || $i=22 || $i=23 || $i=24 || $i=25 || $i=26 || $i=27 || $i=28 || $i=29 || $i=30))
//		$errores[$i] = '<font color=red>Invalido</font>';
	}
	

if ( !(trim($_GET[40])=='Etiqueta1' || trim($_GET[40])=='Etiqueta2') ){
		$errores[40] = '<font color=red>Invalido</font>';
	}
if ( !is_modelo(trim($_GET['modelo_nuevo']))) {
		$errores[41] = '<font color=red>Invalido</font>';
	}
}
// se retorna un arreglo de errores desde el input 5 hasta el 40
return $errores;
}

function es_error($i, $errores, $lefte, $tope ){

	if (isset($errores[$i])){
	echo "<div style='position: absolute; left:$lefte; top:$tope;'>";
	 echo $errores[$i];
	 echo "</div>";
	}
}

function imprimir_entrada_datos($errores =''){

		//puedo poner los valores por defecto .... 

//esto es para el go back del browser :) espero que funque!
 //if( $_SESSION['estado'] == "off" ) 
	//unset($_GET['modelo_nuevo']);

$nro = 40;
for ($i = 5; $i < 15; $i++){
	es_error($i,$errores, 100, 90+$nro);
	$nro +=40;
}
$nro = 40;
for ($i = 15; $i < 25; $i++){
	es_error($i,$errores, 250, 90+$nro);
	$nro +=40;
}
$nro = 40;
for ($i = 25; $i < 35; $i ++){
	es_error($i,$errores, 400, 90+$nro);
	$nro +=40;
}
$nro = 40;
for ($i = 35; $i < 41; $i ++){
	es_error($i,$errores, 550, 90+$nro);
	$nro +=40;
}
es_error(41, $errores, 300,10); //NO FUNCIONA el error para el modelo !

echo "<div style='position:absolute; left:0; top:0;'><form method='GET' action='f_abm3.php' target='resultados'></div>";
//echo "<div style='position: absolute; left:100; top:10;'><b>Modelo a Ingresar: </b><input type='text' name='modelo_nuevo' size='8'></div>";

echo "<div style='position: absolute; left:100; top:10;'>Modelo a Ingresar:<input type='text' name='modelo_nuevo' size=8";
if(isset($_GET['modelo_nuevo'])) echo " value='".$_GET['modelo_nuevo']."'></div>"; else echo " ></div>";
		//if(isset($_GET['modelo_nuevo'])) echo " value='$_GET[modelo_nuevo]' ></div>"; else echo " ></div>";


echo "<div style='position: absolute; left:40; top:50;'>Apareo: <input type='checkbox' name='1' /></div>";
echo "<div style='position: absolute; left:140; top:50;'>CarLat: <input type='checkbox' name='2'></div>";
echo "<div style='position: absolute; left:220; top:50;'>Certificado: <input type='checkbox' name='3' ></div>";
echo "<div style='position: absolute; left:340; top:50;'>Chequeo: <input type='checkbox' name='4' ></div>";
/*
* funcion input_form () es para type=text le paso el value y la posicion adonde quiero que se coloque
* function input_form($name, value, $maxlen, posicion x, posicion y){
*/

input_form('Sensibilidad',5, 7, 20, 110);
input_form('Impedancia',6, 7, 20, 150);
input_form('GrupoCorrHorno',7, 7, 20, 190);
input_form('ImpEnt',8, 7, 20, 230);
input_form('ImpSal',9, 7, 20, 270);
input_form('TolImpEnt',10, 7, 20, 310);
input_form('TolImpSal',11, 7, 20, 350);
input_form('Cero',12, 7, 20, 390);
input_form('TolCero',13, 7, 20, 430);
input_form('TolSens',14, 7, 20, 470);
input_form('CapNominal',15, 7, 170, 110);
input_form('Ruta',16, 20, 170, 150);
input_form('Alin',17, 7, 170, 190);
input_form('Hister',18, 7, 170, 230);
input_form('Rep',19, 7, 170, 270);
input_form('Creep',20, 7, 170, 310);
input_form('CorCeroTemp',21, 7, 170, 350);
input_form('CorSpanTemp',22, 7, 170, 390);
input_form('VMaxAlim',23, 7, 170, 430);
input_form('RangTemp',24, 7, 170, 470);
input_form('Sobrecarga',25, 7, 320, 110);
input_form('LimRot',26, 7, 320, 150);
input_form('Cable',27, 7, 320, 190);
input_form('TolR2',28, 7, 320, 230);
input_form('TolPendHorno',29, 7, 320, 270);
input_form('TolH',30, 7, 320, 310);
input_form('CantPorLot',31, 7, 320, 350);
input_form('pSg',32, 7, 320, 390);
input_form('CantSg',33, 7, 320, 430);
input_form('pRb',34, 7, 320, 470);
input_form('CantRb',35, 7, 470, 110);
input_form('pPrensa',36, 7, 470, 150);
input_form('pCable',37, 7, 470, 190);
input_form('pArnes',38, 7, 470, 230);
input_form('DeltaRb',39, 30, 470, 270);
input_form('Etiqueta',40, 10, 470, 310);


echo '<div style="position:absolute; left:465; top:400;"><input type="submit" name="Submit" value="Ingresar Modelo"><input type="hidden" name="_submit_check" value="1"><input type="hidden" name="tipoabm" value="1"></form></div>';

  //comenzamos la session! (esto es para el go back del browser)
  $_SESSION['estado'] = "on";
}

function input_form($name, $num, $maxlen, $left, $top){
///	echo '<div style="position: absolute; left:'.$left.'; top:'.$top.';">'.$name.':<br><input type="text" name='.$num.' size=8 maxlength='.$maxlen.'';
echo "<div style='position: absolute; left:$left; top:$top;'>$name:<br><input type='text' name='$num' size='8' maxlength='$maxlen'";
//if(isset($_GET[$num])) echo " value=''$left=100 echo $left' ></div>"; else echo " ></div>";
if(isset($_GET[$num])) echo " value='".$_GET[$num]."'></div>"; else echo " ></div>";
}

function a_modelos(){
/** 1 - chequear que el modelo no exista en tablas produccion. Se chequea el nombre
* aunque el modelo este inactivo. 
*  2 - si el modelo no existe se inserta el modelo en tablas produccion
*  3 - Se muestran los datos insertados - desde aca, si se quiere hacer algo, 
*      debe ir a modificar con un cartel, ingreso OK
*   -  si hubo algun problema, no se inserta nada rollback, se muestra cartel 
*	de que hubo un fallo. 
*  4 - se bloquea algo al hacer la insercion? 
* 
* Insertar valores por defecto
* se arman dos arrays 
* 1 - que sea el de los campos 
* 2 -  que sea el de los valores. 
* Como se dividen dentro del insert ? usando la funcion explode que te los separa en comas. 
que debo hacer antes? chequear si la variable ha sido seteada o no. Si la variable 
no ha sido seteada entonces no se pone dentro de los arrays. Si ha sido seteada se pone
dentro del array. 
* 
*/

/**
* en este bloque traemos la variable global de db_flexar 
* traemos fila por fila y la copiamos a un array local
**/
global $id_db_flexar;

$_SESSION['estado'] ="off";

$array_nombre_campos = array('', 'Apareo', 'CarLat', 'Certificado', 'Chequeo', 'Sensibilidad', 'Impedancia', 'GrupoCorrHorno', 'ImpEnt', 'ImpSal', '[Tol ImpEnt]', 'TolImpSal', 'Cero', 'TolCero', 'TolSens', 'CapNominal', 'Ruta', 'Alin', 'Hister', 'Rep', 'Creep', 'CorCeroTemp', 'CorSpanTemp', 'VMaxAlim', 'RangTemp', 'Sobrecarga', 'LimRot', 'Cable', 'TolR2', 'TolPendHorno', 'TolH', 'CantPorLote', 'pSg', 'CantSg', 'pRb', 'CantRb', 'pPrensa', 'pCable', 'pArnes', 'DeltaRb', 'Etiqueta'); 


$array_campos_insert = array();
$array_values = array();


$query = "SELECT Modelos.Modelo FROM Modelos";

$q = $id_db_flexar -> query($query);
while ($row_flexar = $q -> fetchRow()){
	if ( strtoupper($_GET['modelo_nuevo'])== strtoupper($row_flexar[0])){
		impresion_error("El Modelo que desea ingresar ya existe");
		exit();
	}
}
$q -> free();
//echo "o sino dale pa adelante !";
//	$query_insertado =  ;
//Nos preparamos para el insert ! pasamos los checkboxes a algo que el sql me entienda,
// modelo le pasamos las letras a upper !
// armamos el query para los valores que van o no por defecto
checkbox_to_SQL();
$_GET['modelo_nuevo'] =	strtoupper($_GET['modelo_nuevo']); 

// tengo un array con el nombre de todos los campos. 
// contador para el array = j 
for ($i = 1, $j=0; $i < 40; $i++)
	if (!empty($_GET[$i])){
		$array_campos_insert[$j] = $array_nombre_campos[$i];
		$array_values[$j] = $_GET[$i];
		$array_interrogacion[$j++]='?';
		}
	
$modelonuevo = $_GET['modelo_nuevo'];
$etiqueta = $_GET['40'];
//$query = "INSERT INTO Modelos (Modelo, '".implode ("', '", $array_campos_insert)."') VALUES(?, ".implode ("', '", $array_interrogacion)."), array('$modelonuevo', '".implode ("', '", $array_values)."')";
// build query...

$query = "INSERT INTO Modelos (Modelo, ".implode(', ',$array_campos_insert).", Etiqueta) VALUES ('".$modelonuevo."', ".implode(', ', $array_values).", '".$etiqueta."')";

//echo '<br />'.$query.'<br />';

$id_db_flexar -> query($query);

//	if (empty($_GET['15'])) $_GET['15'] ="5";
//super insert ! 
//$id_db_flexar -> query('INSERT INTO Modelos (Modelo, Apareo, CarLat, Sensibilidad, Impedancia, GrupoCorrHorno, Certificado,  ImpEnt, [Tol ImpEnt], ImpSal, TolImpSal, Cero, TolCero, TolSens, CapNominal, Chequeo, Etiqueta, Alin, Hister, Rep, Creep, CorCeroTemp, CorSpanTemp, VMaxAlim, RangTemp, Sobrecarga, LimRot, Cable, TolR2, TolPendHorno, TolH, CantPorLote, pSg, CantSg, pRb, CantRb, pPrensa, pCable, pArnes, DeltaRb, Ruta ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', array($_GET['modelo_nuevo'], $_GET['1'], $_GET['2'],$_GET['5'],$_GET['6'],$_GET['7'],$_GET['3'],$_GET['8'],$_GET['10'],$_GET['9'],$_GET['11'], $_GET['12'],$_GET['13'],$_GET['14'],$_GET['15'],$_GET['4'],$_GET['40'],$_GET['17'],$_GET['18'],$_GET['19'],$_GET['20'],$_GET['21'],$_GET['22'],$_GET['23'],$_GET['24'],$_GET['25'],$_GET['26'],$_GET['27'],$_GET['28'],$_GET['29'],$_GET['30'],$_GET['31'],$_GET['32'],$_GET['33'],$_GET['34'],$_GET['35'],$_GET['36'],$_GET['37'],$_GET['38'],$_GET['39'],$_GET['16']));

//	$valor = $_GET['17'];
print "<h3><center>El Modelo ha sido cargado</center></h3>";

	// se muestran los datos y somos felices, uso los div
	//tomo los datos de nuevo y los muestro. :)
	
	$id_db_flexar -> setFetchMode(DB_FETCHMODE_OBJECT);
	$queryselect = "SELECT * FROM Modelos WHERE Modelos.Modelo='$modelonuevo'";
//	$querymodelos = "SHOW COLUMNS FROM Modelos";	
	$q = $id_db_flexar -> query($queryselect);
//	$qm = $id_db_flexar -> query($querymodelos);
	$row_flexar = $q -> fetchRow();

//print "<tr><td>";
echo '<div style="position: absolute; left:50; top:100;">';
print "<table border=1>";
        print "<tr><td>Modelo: ".$row_flexar -> Modelo."</td></tr>";
	echo "<tr><td>Apareo: ".$row_flexar -> Apareo."</td></tr>";
	print "<tr><td>CarLat: ".$row_flexar -> CarLat."</td></tr>";
	print "<tr><td>Sensibilidad: ".$row_flexar -> Sensibilidad."</td></tr>";
	print "<tr><td>Impedancia: ".$row_flexar -> Impedancia."</td></tr>";
	print "<tr><td>GrupoCorrHorno: ".$row_flexar -> GrupoCorrHorno."</td></tr>";
	print "<tr><td>Certificado: ".$row_flexar -> Certificado."</td></tr>";
	print "<tr><td>ImpEnt: ".$row_flexar -> ImpEnt."</td></tr>";
//	print "\nTol ImpEnt: ".$row_flexar -> Tol ImpEnt;
	print "<tr><td>ImpSal: ".$row_flexar -> ImpSal."</tr></td>";
	print "<tr><td>Cero: ".$row_flexar -> Cero."</td></tr>";
	print "<tr><td>TolCero: ".$row_flexar -> TolCero."</td></tr>";
	print "<tr><td>TolSens: ".$row_flexar -> TolSens."</td></tr>";
	print "<tr><td>CapNominal: ".$row_flexar -> CapNominal."</td></tr>";
 print "</table>";
echo "</div>";
echo '<div style="position: absolute; left:230; top:100;">';
	 print "<table border=1>";
	print "<tr><td>Chequeo: ".$row_flexar -> Chequeo."</td></tr>";
	print "<tr><td>Etiqueta: ".$row_flexar -> Etiqueta."</td></tr>";
	print "<tr><td>Alin: ".$row_flexar -> Alin."</td></tr>";
	print "<tr><td>Hister: ".$row_flexar -> Hister."</td></tr>";
	print "<tr><td>Rep: ".$row_flexar -> Rep."</td></tr>";
	print "<tr><td>Creep: ".$row_flexar -> Creep."</td></tr>";
	print "<tr><td>CorCeroTemp: ".$row_flexar -> CorCeroTemp."</td></tr>";
	print "<tr><td>VMaxAlim: ".$row_flexar -> VMaxAlim."</td></tr>";
	print "<tr><td>RangTemp: ".$row_flexar -> RangTemp."</td></tr>";
	print "<tr><td>SobreCarga: ".$row_flexar -> Sobrecarga."</td></tr>";
	print "<tr><td>LimRot: ".$row_flexar -> LimRot."</td></tr>";
	print "<tr><td>Cable: ".$row_flexar -> Cable."</td></tr>";
	print "<tr><td>TolR2: ".$row_flexar -> TolR2."</td></tr>";
	print "</table>";
print "</div>";
echo '<div style="position: absolute; left:420; top:100;">';
  print "<table border=1>";
	print "<tr><td>TolPendHorno: ".$row_flexar -> TolPendHorno."</td></tr>";
	print "<tr><td>TolH: ".$row_flexar -> TolH."</td></tr>";
	print "<tr><td>CantPorLote: ".$row_flexar -> CantPorLote."</td></tr>";
	print "<tr><td>pSg: ".$row_flexar -> pSg."</td></tr>";
	print "<tr><td>CantSg: ".$row_flexar -> CantSg."</td></tr>";
	print "<tr><td>pRb: ".$row_flexar -> pRb."</td></tr>";
	print "<tr><td>CantRb: ".$row_flexar -> CantRb."</td></tr>";
	print "<tr><td>pPrensa: ".$row_flexar -> pPrensa."</td></tr>";
	print "<tr><td>pCable: ".$row_flexar -> pCable."</td></tr>";
	print "<tr><td>pArnes: ".$row_flexar -> pArnes."</td></tr>";
	print "<tr><td>DeltaRb: ".$row_flexar -> DeltaRb."</td></tr>";
	print "<tr><td>Ruta: ".$row_flexar -> Ruta."</td></tr>";
print "</table>";
print "</div>";
$q -> free();

 /* Y ahora, se limpian los GET[*], para el go back del browser! */		

/*
	for ($i = 1, $nro=40; $i < 41; $i++, $nro+=20){
		if ($i>=1 && $i<15)
			imprimir_valores($i,$array_nombre_campos, 100, 90+$nro, $row_flexar);
			if ($i >=15 && $i<25){
				//$nro = 40;
				imprimir_valores($i,$array_nombre_campos, 250, 90+$nro-280, $row_flexar);
			}
			if($i>=25 && $i<35){
//				$nro=40;
				imprimir_valores($i,$array_nombre_campos, 400, 90+$nro-480, $row_flexar);
			}
			if($i>=35 && $i<41){
//				$nro=40;
				imprimir_valores($i,$array_nombre_campos, 250, 90+$nro-450, $row_flexar);
			}
	}
	/*
	$nro=40;		
	for ($i = 15; $i < 25; $i++){

		$nro +=20;
	}
	$nro=40;		
	for ($i = 25; $i < 35; $i++){

		$nro +=20;
	}
	$nro=40;		
	for ($i = 35; $i < 41; $i++){
		imprimir_valores($i,$array_nombre_campos, 500, 90+$nro);
		$nro +=20;
	}
	*/
}


function imprimir_valores($i, $array_nombre_campos, $lefte, $tope, $rowflexar ){
		echo "<div style='position: absolute; left:$lefte; top:$tope;'>";
		 echo $array_nombre_campos[$i].": ".$rowflexar[$i];
		 echo "</div>";
}


function b_modelos(){
	/**
	* Muestro los distintos modelos para seleccionar el modelo en cuestion
	* Lo unico que aca se hace es modificar en la Tabla Modelos el campo
	* Inactivo a true. Los demas datos NO se tocan.  
	* ¿se muestran los datos ? ?
	**/
	global $id_db_flexar;	
	$modelo_a_bajar = $_GET['bajamodelo'];
	echo $modelo_a_bajar;
	$query = "UPDATE Modelos SET Inactivo=true WHERE Modelos.Modelo = '$modelo_a_bajar'";
	$q = $id_db_flexar -> query($query);

	echo 'el modelo fue voleta :P';
}

function show_baja_modelos(){

	global $id_db_flexar;
	//selecciono todos los modelos menos los que estan Inactivos
	$query = "SELECT Modelos.Modelo FROM Modelos WHERE Modelos.Inactivo <> true ORDER BY Modelos.Modelo"; 
	$q = $id_db_flexar -> query($query);
	
echo "<form method='GET' action='f_abm3.php' target='resultados'>";
	print '<center><b>Modelo a dar de baja:</b>';
	 print '<select name="bajamodelo">';
	   while ($row_flexar = $q -> fetchRow()){
		   echo " <option value=$row_flexar[0]>$row_flexar[0]</div>";
	   }
	   print "</select>";		
echo "<input type='submit' name='Submit' value='Bajar Modelo'><input type='hidden' name='_submit_check' value='1'><input type='hidden' name='tipoabm' value='2'></form></center>";
 
}

function m_modelos(){
//	show_or_not(3);
		echo 'modificacion de modelos';
	}
function checkbox_to_SQL(){
		for ($i = 1; $i < 5; $i++)
			if($_GET[$i] == "on") $_GET[$i] = 'true'; else $_GET[$i] = 'false';				
}
function limpiarGet(){
		
	unset($_GET['modelo_nuevo']);
	for ($i = 0; $i < 41; $i++)
		unset($_GET[$i]);


}

?>
