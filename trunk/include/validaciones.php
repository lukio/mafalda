<?php
//if (!defined("__HEADER__")) {
//   echo "Acesso NO VALIDO";
//   exit();
//}

# funciones de validacion mediante expresiones regulares y derivadas

function is_mail($string){
    return ereg("^([a-z0-9_]|\-|\.)+[a-z0-9]+@(([a-z0-9_]|\-)+\.)+[a-z]{2,4}$",trim($string));
}

function is_name($string) {
    return ereg("[[:space:][:alpha:]\']+",trim($string));
}
function is_modelo($string){
   return ereg("[[a-z][a-z]+-[:digit:]]+",trim($string));
}
function no_es_raro($string){
	return ereg("\?\>\<\.\,\'\"\;\:\[\{\}\/\|\!\@\#\$\%\^\&\*\(\)\_\=\+\~\`]+",trim($string));
}
function is_text($string) {
    return ereg("[[:space:][:alpha:]\?\>\<\.\,\'\"\;\:\[\{\}\/\|\!\@\#\$\%\^\&\*\(\)\-\_\=\+\~\`]+",trim($string));
}

function is_text2($string) {
    return ereg("[[:alpha:]]+",trim($string));
}

function is_number($string) {
    return (ereg("[[:digit:]]+", trim($string)));
}

function is_login($string) {
    return ereg("[a-z]", trim($string));
}

function is_password($string) {
    return ereg("[[:alpha:]]+", trim($string));
}

function is_linkpag($string) {
    return (ereg("[a-z0-9_]+", trim($string)));
}

function login_exist($string) {
    global $pg_conn;
    $SQL = "SELECT COUNT(usr_id) FROM sysweb_usuarios WHERE usr_login='".$string."'";
    $rc  = pg_query($SQL);
    $ret = (pg_num_rows($rc)==1) ? true : false;
}


function group_exist($string) {
    global $pg_conn;
    $SQL = "SELECT COUNT(grp_id) FROM sysweb_grupos WHERE grp_id='".$int."'";
    $rc  = pg_query($SQL);
    $ret = (pg_num_rows($rc)==1) ? true : false;
}

/*function control_login($str_login,&$errores,$verify=false,$exist=true) {
    //Por defecto no verifica la existencia del login
    //msg de error en caso de que el login exista $exist=true
    //msg de error en caso de que el login no exista $exist=false
    
    global $pg_conn;
    
    if (empty($str_login)) {
        agregar_msg($errores,'1000');
        $return = false;
    }
    elseif (!is_login($str_login)) {
        agregar_msg($errores,'1001');
        $return = false;
    }
    elseif($verify) {
        if (($exist) && (login_exist($str_login)) {
            agregar_msg($errores,'1002');
            $return = false;
        }
        elseif ((!$exist) && (!login_exist($str_login)) {
            agregar_msg($errores,'1003');
            $return = false;        
        }
    }
    else $ret = true;
    
    return $return;
}

function control_password($str_password,&$errores) {

    if (empty($str_password)) {
        agregar_msg($errores,'1004');
        $ret = false;
    }
    elseif (!is_password($str_password)) {
        agregar_msg($errores,'1005');
        $ret = false;
    }
    else
        $ret = true;
        
    return $ret;
}

function control_group($int_grp_id,&$errores,$verify=false,$exist=true) {
    //Por defecto no verifica la existencia del grupo
    //msg de error en caso de que el grupo exista $exist=true
    //msg de error en caso de que el grupo no exista $exist=false

    global $pg_conn;
    
    if (empty($int_grp_id)) {
        agregar_msg($errores,'1006');
        $ret = false;
    }
    elseif (!is_int($int_grp_id)) {
        agregar_msg($errores,'1007');
        $ret = false;
    }
    elseif($verify) {
        if (($exist) && (group_exist($int_grp_id)) {
            agregar_msg($errores,'1008');
            $return = false;
        }
        elseif ((!$exist) && (!group_exist($int_grp_id)) {
            agregar_msg($errores,'1009');
            $return = false;        
        }
    }
    else $ret = true;
    
    return $return;
}

function control_nombre($str_nombre,&$errores) {
    
    if (is_empty($str_nombre)) {
        agregar_msg($errores,'1010');
        $ret = false;
    }
    elseif (!is_name($str_nombre)) {
        agregar_msg($errores,'1011');
        $ret = false;
    }
    else 
        $ret = true;
    
    return $ret;
}

function control_apellido($str_apellido,&$errores) {
    
    if (is_empty($str_apellido)) {
        agregar_msg($errores,'1012');
        $ret = false;
    }
    elseif (!is_name($str_apellido)) {
        agregar_msg($errores,'1013');
        $ret = false;
    }
    else
        $ret = true;
    
    return $ret;
}

*/
// function control_link($string,&$errores) {
// 
//     if (empty($string)) {
//         $errores['pag'] = obtener_msg_aviso('0200');
//         $ret = false;
//     }
//     elseif(!is_linkpag($string)) {
//         $errores['pag'] = obtener_msg_aviso('0200');
//     }
//     else
//         $ret = true;
//     }
//     return $ret;
// }

// function control_parametros($parametros) { //solo alfanumericos ! no funca bien :s
// 
//     return ereg("[[:alnum:]]+", trim($parametros));
// }
?>
