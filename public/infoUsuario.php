<?php
$usu = q("SELECT * FROM sai_usuario WHERE usu_cedula='{$_SESSION[cedula]}'")[0];
?>

<h1>Información del usuario</h1>

<div class="row">
    <div class="col-md-2">
      <label  for="">Nombre:</label>
    </div>
    <div class="col-md-4">
        <?="{$usu[usu_nombres]} {$usu[usu_apellidos]}"?>
    </div>
</div>
<div class="row">
    <div class="col-md-2">
      <label  for="">Nombre de usuario:</label>
    </div>
    <div class="col-md-4">
        <?="{$usu[usu_username]}"?>
    </div>
</div>
<div class="row">
    <div class="col-md-2">
      <label  for="">N&uacute;mero de C&eacute;dula:</label>
    </div>
    <div class="col-md-4">
        <?php echo $_SESSION['cedula']; ?>
    </div>
</div>
<div class="row">
    <div class="col-md-2">
      <label  for="oldpassword">Rol:</label>
    </div>
    <div class="col-md-4">
        <?php echo q("SELECT rol_nombre FROM sai_rol WHERE rol_id=" .$_SESSION['rol'])[0]['rol_nombre']; ?>
    </div>
</div>

