<?php
session_start();
require_once('../private/config.php');
require_once('../private/utils.php');
require_once('../private/bdd.php');

$path = $_GET['path'];

$path = explode('/', $path);

$nedetel_objeto = (empty($path[0])) ? 'main' : array_shift($path);
if ($nedetel_objeto[0] == '_' || isset($_SESSION['cedula'])){
} else {
    $nedetel_objeto = 'login';
}
$args = $path;
$template_temporal_no_persistente = false;
$template = 'default';
//$template = 'default';

function p_preparar_buffer($buffer) {
    return $buffer;
}

//inicia petición
ob_start('p_preparar_buffer');
if (file_exists($nedetel_objeto . '.php')) {
    //verifica seguridades
    $con_permiso = false;

    if ($nedetel_objeto === 'login') {
        $con_permiso = true;
    } else {
        $rol_id = (isset($_SESSION['rol']) ? $_SESSION['rol'] : 0);
        $rol_version = q("SELECT rol_version FROM sai_rol WHERE rol_id=$rol_id");

        if ($rol_version) {
            $rol_version = $rol_version[0]['rol_version'];

            $seguridades = array();
            if (isset($_SESSION['rol_version']) && $rol_version == $_SESSION['rol_version']) {
                $seguridades = $_SESSION['seguridades'];
            } else {
                $seguridades = q("SELECT * FROM sai_permiso, sai_objeto WHERE per_objeto = obj_id AND per_rol=$rol_id");
                $_SESSION['seguridades'] = $seguridades;
                $_SESSION['rol_version'] = $rol_version;
            }
            if (!empty($seguridades)) {
                foreach($seguridades as $seguridad){
                    if ($seguridad['obj_nombre'] == $nedetel_objeto) {
                        $con_permiso = true;
                    } 
                }
            }
        }

        if (!$con_permiso) {
            $count_nedetel_objetos = q("SELECT COUNT(*) FROM sai_objeto WHERE obj_nombre='$nedetel_objeto'")[0]['count'];
            $con_permiso = ($count_nedetel_objetos == 0);
        }



/*
        $count_permisos = q("SELECT COUNT(*) FROM sai_seguridad, sai_objeto, sai_rol WHERE seg_objeto=mod_id AND seg_rol=rol_id AND mod_texto='$nedetel_objeto' AND rol_id=$rol_id")[0]['count'];
        //echo $count_permisos;
        //echo ("SELECT COUNT(*) FROM sai_seguridad, sai_objeto, sai_rol WHERE seg_objeto=mod_id AND seg_rol=rol_id AND mod_texto='$nedetel_objeto' AND rol_id=$rol_id");
        if ($count_permisos  == 0){
            $count_nedetel_objetos = q("SELECT COUNT(*) FROM sai_objeto WHERE mod_texto='$nedetel_objeto'")[0]['count'];
            $con_permiso = ($count_nedetel_objetos == 0);
        } else {
            $con_permiso = true;
        }
 */
    }

    //if ($con_permiso || true) {
    if ($con_permiso) {
        //carga nedetel_objeto
        require_once($nedetel_objeto . '.php');
        l("Acceso a módulo -$nedetel_objeto-, parámetros: " . implode(',', $args));
    } else {
        echo "ERROR: No tiene permisos para acceder al módulo <strong>$nedetel_objeto</strong>.";
        l("ERROR: intento de acceso no autorizado al módulo $nedetel_objeto");
    }
} else {
    echo "ERROR: Módulo <strong>$nedetel_objeto</strong> no instanciado.";
    l("ERROR:  módulo $nedetel_objeto no instanciado.");
}
$content = ob_get_contents();
ob_end_clean();

if($nedetel_objeto[0] == '_') {
    $template = 'ws_rest';
    $template_temporal_no_persistente = true;
} else if (isset($_SESSION['template']) && !empty($_SESSION['template'])) {
    $template = $_SESSION['template']; 
}

//if($nedetel_objeto[0] == '_' && $template == 'default') {
//    require_once("../private/templates/ws_rest.template.php");
//} else {
    if ($template != 'default' && $template != 'login' && file_exists("../private/templates/$template.template.php")) {

    } else if (file_exists("../private/templates/$nedetel_objeto.template.php")) {
        $template = $nedetel_objeto;
    } else if (!file_exists("../private/templates/$template.template.php")) {
        $template = 'default';
    }


if (!$template_temporal_no_persistente) {
    $_SESSION['template'] = $template;
}

//echo '<pre>';
//var_dump($_SESSION);
//echo '</pre>';
    require_once("../private/templates/$template.template.php");
//}
