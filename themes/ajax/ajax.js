var Conexion=false; // Variable que manipula la conexion.
var Servidor="consulta.php"; // Determina la pagina donde buscar
var Palabra=""; //Determina la ultima palabra buscada.

// funcion que realiza la conexion con el objeto XMLHTTP...
function Conectar()
{
	if(window.XMLHttpRequest)
		Conexion=new XMLHttpRequest(); //mozilla
	else if(window.ActiveXObject)
		Conexion=new ActiveXObject("Microsoft.XMLHTTP"); //microsoft
}

function Contenido(idContenido)
{
	/* readyState devuelve el estado de la conexion. puede valer:
	 *	0- No inicializado (Es el valor inicial de readyState)
	 *	1- Abierto (El método "open" ha tenido éxito)
	 *	2- Enviado (Se ha completado la solicitud pero ningun dato ha sido recibido todavía)
	 *	3- Recibiendo
	 *	4- Respuesta completa (Todos los datos han sido recibidos)
	 */

	// En espera del valor 4
	if(Conexion.readyState!=4) return;
	/* status: contiene un codigo enviado por el servidor
	 *	200-Completado con éxito
	 *	404-No se encontró URL
	 *	414-Los valores pasados por GET superan los 512
	 * statusText: contiene el texto del estado
	 */
	if(Conexion.status==200) // Si conexion HTTP es buena !!!
	{
		//si recibimos algun valor a mostrar...
		if(Conexion.responseText)
		{
			/* Modificamos el identificador temp con el valor recibido por la consulta
			*	Podemos recibir diferentes tipos de datos:
			*	responseText-Datos devueltos por el servidor en formato cadena
			*	responseXML-Datos devueltos por el servidor en forma de documento XML
			*/
			document.getElementById(idContenido).style.display="block";
			document.getElementById(idContenido).innerHTML=Conexion.responseText;
		}else
			document.getElementById(idContenido).style.display="none";
	}else{
		document.getElementById(idContenido).innerHTML=Conexion.status+"-"+Conexion.statusText;
	}

	// Deshabilitamos la visualización del reloj
	document.getElementById("reloj").style.visibility="hidden";

	Conexion=false;
}

function Solicitud(idContenido,Cadena)
{
	// si no recibimos cadena, no hacemos nada.
	// Cadena=la cadena a buscar en la base de datos
	/* Si cadena es igual a Palabra, no se realiza la busqueda. Puede ser que pulsen la tecla tabulador,
	 * y no interesa que vuelva a verificar...*/
	if(Cadena && Cadena!=Palabra)
	{
		// Si ya esta conectado, cancela la solicitud en espera de que termine
		if(Conexion) return; // Previene uso repetido del boton.
		
		// Realiza la conexion
		Conectar();
		
		// Si la conexion es correcta...
		if(Conexion)
		{
			// Habilitamos la visualización del reloj
			document.getElementById("reloj").style.visibility="visible";

			// Esta variable, se utiliza para igualar con la cadena a buscar.
			Palabra=Cadena;

			/* Preparamos una conexion con el servidor:
			*	POST|GET - determina como se envian los datos al servidor
			*	true - No sincronizado. Ello significa que la página WEB no es interferida en su funcionamiento
			*	por la respuesta del servidor. El usuario puede continuar usando la página mientras el servidor
			*	retorna una respuesta que la actualizará, usualmente, en forma parcial.
			*	false - Sincronizado */
			Conexion.open("POST",Servidor,true);

			// Añade un par etiqueta/valor a la cabecera HTTP a enviar. Si no lo colocamos, no se pasan los parametros.
			Conexion.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
	
			// Cada vez que el estado de la conexión (readyState) cambie se ejecutara el contenido de esta "funcion()"
			Conexion.onreadystatechange=function()
			{
				Contenido(idContenido);
			}
			
			date=new Date();
			/* Realiza la solicitud al servidor. Puede enviar una cadena de caracteres, o un objeto del tipo XML
			 * Si no deseamos enviar ningun valor, enviariamos null */
			Conexion.send("idContenido="+idContenido+"&word="+Cadena+"&"+date.getTime());
		}else
			document.getElementById(idContenido).innerHTML="No disponible";
	}
}

// Funcion que inicia la busqueda.
// Tiene que recibir el identificador donde mostrar el listado, y la cadena a buscar
function autocompletar(idContenido,Cadena)
{
	// Comprovamos que la longitud de la cadena sea superior o igual a 1 caracteres
	if(Cadena.length>=1)
	{
		if(Conexion!=false)
		{
			// Deshabilitamos la visualización del reloj
			document.getElementById("reloj").style.visibility="hidden";
			//si esta en medio de una conexion, la cancelamos
			Conexion.abort();
			Conexion=false;
		}
		Solicitud(idContenido,Cadena);
	}else
		document.getElementById(idContenido).style.display="none";
}

// Funcion que se ejecuta cuando seleccionamos un valor del desplegable
function selectItem(idContenido,value)
{
	// Cuando pulsamos sobre el desplegable, colocamos el valor en el cuadro de texto
	document.getElementById("input").value=value;
	//volvemos a indicar que actualice el listado con el nuevo valor
	autocompletar(idContenido,value);
}
