<?php
/**
 * Metodo de Autentificacion utilizado: conectarse a servidor SQL
 * 
 */

if ($_POST['action']=="postlogin")
    autentifica();

function formulario_login(){
    require_once 'pear/Sigma.php'; //insertamos la libreria
    $it = new HTML_Template_Sigma('themes'); //declaramos el objeto
    $it->loadTemplatefile('login.html'); //seleccionamos la plantilla
    $data_der = array (
                        "Usuario: &nbsp;&nbsp;&nbsp;&nbsp;", "text", "username","10","username_id",
                        "Password: ", "password", "password","10","password_id"                        
                    );

    for($i=0 ; $i < count($data_der);) {
        $it->setCurrentBlock('input'); //buscamos bloque

        $it->setVariable('DATO', $data_der[$i++]);
        $it->setVariable('TYPE_DATO', $data_der[$i++]);
        $it->setVariable('NAME_DATO',$data_der[$i++]);
        $it->setVariable('SIZE_DATO',$data_der[$i++]);
        $it->setVariable('ID_DATO',$data_der[$i++]);

        $it->parseCurrentBlock('input'); //generamos la parte del bloque analizado
    }

    $it->show(); //mostramos el resultado

}

function autentifica(){
    require_once('../dbinfo.php');
    require_once('MDB2.php');
    define(IP_CLIENTE,$_SERVER['REMOTE_ADDR']); //de esta forma siempre veremos la IP del cliente
    // start the session
    session_start();
//    header("Cache-control: private"); //IE 6 Fix

    $username = $_POST['username'];
    $password = $_POST['password'];

    $mdb2 =& MDB2::singleton($dsn, $options);
        if (PEAR::isError($mdb2)) {
            die($mdb2->getMessage());
        }

    $query = "SELECT id_user FROM Usuarios WHERE login=? AND password=?";
    $type = array ('text', 'text');
    $statement= $mdb2->prepare($query, $type, MDB2_PREPARE_RESULT);
    $data = array($username, $password);
    $result = $statement->execute($data);

    if(PEAR::isError($result)) {
             die($mdb2->getMessage());
     }
    $statement->Free();
    //Hago el query. Si el resultado es OK, entonces usuario autentificado. Sino, pues no :)
    $row = $result->fetchRow(MDB2_FETCHMODE_ASSOC);
    $mdb2->disconnect();

    unset($_POST['username']);
    unset($username);
    unset($_POST['password']);
    unset($password);

    if (!isset($row['id_user'])){
        echo "<html><head><script language='Javascript'>
                function cargarindex(){
                    setTimeout(\"location.replace('../index.php')\",2000);
                }
                </script>
            </head>
            <body onload='cargarindex()'>
                <div align='center'>
                    <h3>Usuario o password NO valido</h3>
                </div>
            </body></html>";
    }else{
        $_SESSION['user_autenticado'] = 1;
        $_SESSION['IP']=IP_CLIENTE;
        header("Location: ../index.php?modulo=consulta_gerencia");
    }

}

function logout(){
    unset($_SESSION['IP']);
    unset($_SESSION['user_autenticado']);
    session_destroy();
    header("Location:index.php");
}

?>
