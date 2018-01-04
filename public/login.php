<?php
//if (isset($_GET['destroy'])) {
//if(isset($args[0]) && $args[0] == 'destroy'){
//}

$error = false;

//if (isset($_POST['cedula']) && !empty($_POST['cedula']) && isset($_POST['password']) && !empty($_POST['password'])) {
if (isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['password']) && !empty($_POST['password'])) {
    //var_dump($_POST);
    //die();
    //$cedula = $_POST['cedula'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $md5_password = md5($password);

    $usuario = q("
        SELECT * 
        FROM sai_usuario
            , sai_rol  
        WHERE
            usu_borrado IS NULL 
            AND usu_rol = rol_id 
            AND usu_username='$username'
            AND usu_password='$md5_password'
    ");
            //AND usu_cedula='$cedula' 
    //$usuario = q("SELECT * FROM sai_usuario AS usu, sai_rol AS rol WHERE usu.usu_rol = rol.rol_id AND usu.usu_cedula='$cedula' AND usu.usu_cedula<>'1713175071'");
    //
    //$usuario = q("SELECT * FROM sai_usuario AS usu, sai_rol AS rol WHERE usu.usu_rol = rol.rol_id AND usu.usu_cedula='$cedula' AND usu.usu_password=md5($password)");
    //$usuario = q("SELECT * FROM sai_usuario AS usu, sai_rol AS rol WHERE usu.usu_rol = rol.rol_id AND usu.usu_cedula='$cedula' AND usu.usu_password='".md5($password)."'");
    // echo count($usuario);

    //var_dump($usuario);
    if (true && is_array($usuario) && count($usuario) == 1){
        //echo "<hr>";
        //
        $usu_id = $usuario[0]['usu_id'];
        $cedula = $usuario[0]['usu_cedula'];
        $usu_nombre = $usuario[0]['usu_nombres'] . ' '. $usuario[0]['usu_apellidos'] ;
        $rol = $usuario[0]['usu_rol'];
        $rol_version = $usuario[0]['rol_version'];
        $rol_nombre = $usuario[0]['rol_nombre'];
        $rol_codigo = $usuario[0]['rol_codigo'];

        $seguridades = q("SELECT * FROM sai_permiso, sai_objeto WHERE per_objeto = obj_id AND per_rol=$rol");
        $_SESSION['seguridades'] = $seguridades;
        $_SESSION['cedula'] = $cedula;
        $_SESSION['username'] = $username;
        $_SESSION['usu_id'] = $usu_id;
        $_SESSION['usu_nombre'] = $usu_nombre;
        $_SESSION['rol'] = $rol;
        $_SESSION['rol_version'] = $rol_version;
        l("Ingreso de usuario $cedula con rol $rol");

        if (isset($_POST['rememberme']) && !empty($_POST['rememberme'])) {
            $params = session_get_cookie_params();
            setcookie(session_name(), $_COOKIE[session_name()], time() + 60*60*24*30, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }

        //$destino = $rol_nombre;
        $destino = (empty($rol_codigo)) ? $rol_nombre : $rol_codigo;
        header("Location: /$destino");
    } else {
        $error = true;
        $_SESSION = array();
    }
} else {
    //si no manda intento de login, destruye sesion:
    //
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    session_start();
}

?>

<div class="container">

      <form action = "/login" method="POST" class="form-signin">
<img src="/img/sait-logo.png" style="width:300px;height:100px;">
        <h2 class="form-signin-heading">Ingreso al sistema</h2>
        <label for="username" class="sr-only">Nombre de usuario</label>
        <input type="text" id="username" name="username" class="form-control" placeholder="Usuario" required autofocus>
        <!--label for="cedula" class="sr-only">Número de cédula</label>
        <input type="text" id="cedula" name="cedula" class="form-control" placeholder="Usuario" required autofocus-->
        <label for="inputPassword" class="sr-only">Contraseña</label>
        <input type="password" id="password" name="password" class="form-control" placeholder="Contraseña" required>

        <div class="checkbox">
          <label>
         <input type="checkbox" name="rememberme" value="rememberme"> Recordar en esta computadora
          </label>
        </div>
        <button class="btn btn-lg btn-primary btn-block" type="submit">Ingresar</button>
      <?php if($error): ?>
<div class="alert alert-danger alert-dismissible" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
  <strong>Error:</strong><br> No se encuentra al usuario
</div>
<?php l("Intento fallido de ingreso de usuario $cedula al Establecimiento de Salud $ess_id -$ess_nombre-"); ?>
      <?php endif; ?>

      </form>


    </div> <!-- /container -->
<script src="/js/bootstrap3-typeahead.min.js"></script>
<script type="text/javascript">
var escogido = {id:"",name:""};
var es =[<?php
/*
$glue = '';
foreach($es as $e){
    echo $glue.'{id:"'.$e['ess_id'].'",name:"' . str_replace('"', "'", $e['ess_nombre'].' ('.$e['canton'].', '.$e['provincia']) . ') - '.$e['ess_unicodigo'].'"}';
    $glue = ',';
}
 */
?>];
$(document).ready(function() {
    $('#establecimiento_salud_typeahead').typeahead({
        //source:es,
        source:function(query, process){
            $.get('/_listarEstablecimientoSalud/' + query, function(data){
                data = JSON.parse(data);
                process(data.lista);
            });
        },
        displayField:'name',
        valueField:'id',
        highlighter:function(name){
            //console.log(item);
            var ficha = '';
            ficha +='<div>';
            ficha +='<h4>'+name+'</h4>';
            ficha +='</div>';
            return ficha;

        },
        updater:function(item){
            console.log(item);
            $('#establecimiento_salud').val(item.id);
            escogido.id = item.id;
            escogido.name = item.name;

            return item.name;

        }
    });
})

function p_validar_es(){
    console.log('on blur')
    if ($('#establecimiento_salud').val() == ''){
        $('#establecimiento_salud_typeahead').val('');
    }
}
</script>
