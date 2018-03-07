<?php
?>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="SAIT">
<meta name="author" content="JYBARO">
<link rel="icon" type="image/png" href="/img/favicon.png" />
<link rel="stylesheet" href="/css/bootstrap.min.css">
<link rel="stylesheet" href="/css/bootstrap-theme.min.css"> 
<!--link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous"-->

<link rel="stylesheet" href="/css/bootstrap-datetimepicker.min.css">
<link rel="stylesheet" href="/css/select2.min.css">
<link rel="stylesheet" href="/css/jquery.dataTables.min.css">

<title><?=ucfirst(str_replace('_', ' ', $nedetel_objeto))?> - <?=$_SESSION['username']?> - SAIT</title>

<style>
html {
  position: relative;
  min-height: 100%;
}
body {
  padding-top: 70px;
  margin-bottom: 60px;
}
.footer {
  position: absolute;
  padding-top:5px;
  bottom: 0;
  width: 100%;
  height: 30px;
  background-color: #f5f5f5;
}
</style>

</head>
<body>

<nav class="navbar navbar-default  navbar-fixed-top">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="/">SAIT
      </a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav navbar-nav">
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Empresas<span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="/empresas">Empresas</a></li>
            <li><a href="/directorio">Directorio</a></li>
            <li><a href="/dependencias">Dependencias</a></li>
            <li role="separator" class="divider"></li>
            <!--li><a href="/autoadmin/sai_atencion">Atenciones</a></li>
            <li><a href="/atenciones">Flujo de las atenciones</a></li-->
            <li role="separator" class="divider"></li>
          </ul>
        </li>
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Proveedores<span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="/proveedores">Proveedores</a></li>
            <li role="separator" class="divider"></li>
          </ul>
        </li>
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Procesos<span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="/factibilidades">Factibilidades</a></li>
            <li><a href="/cotizaciones">Cotizaciones</a></li>
            <li><a href="/ordenes_servicio">Órdenes de servicio</a></li>
            <li><a href="/servicios">Servicios</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="/cambios">Solicitudes de cambios</a></li>
            <li role="separator" class="divider"></li>
          </ul>
        </li>
        <!--
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Factibilidades<span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="/factibilidades">Listar</a></li>
            <li role="separator" class="divider"></li>
          </ul>
        </li>
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Cotizaciones<span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="/cotizaciones">Listar</a></li>
            <li role="separator" class="divider"></li>
          </ul>
        </li>
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Ordenes de Servicio<span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="/ordenesServicio">Listar</a></li>
            <li role="separator" class="divider"></li>
          </ul>
        </li>
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Servicios<span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="/servicios">Listar</a></li>
            <li role="separator" class="divider"></li>
          </ul>
        </li>
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Cambios<span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="/cambios">Listar</a></li>
            <li role="separator" class="divider"></li>
          </ul>
        </li>
        -->
      </ul>
      <!--form class="navbar-form navbar-left">
        <div class="form-group">
          <input type="text" class="form-control" placeholder="Centro de salud">
        </div>
        <button type="submit" class="btn btn-default">Buscar</button>
      </form-->
      <ul class="nav navbar-nav navbar-right">
        <?php if ($_nivel <= 2): ?>
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Reportes<span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="/reporteAtenciones">Atenciones</a></li>
            <li role="separator" class="divider"></li>
          </ul>
        </li>
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Administración<span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="/autoadmin/sai_pertinencia_proveedor">Pertinencia de proveedores</a></li>
            <li><a href="/usuarios">Usuarios</a></li>
            <li><a href="/atenciones">Atenciones</a></li>
            <li><a href="/campos">Campos</a></li>
            <li><a href="/ubicaciones">Ubicaciones</a></li>
            <li><a href="/puntos">Puntos</a></li>
            <li><a href="/autoadmin/sai_tipo_ultima_milla">Tipos de última milla</a></li>
            <?php if ($_nivel <= 1): ?>
            <li role="separator" class="divider"></li>
            <li><a href="/autoadmin/sai_estado_atencion">Estados</a></li>
            <li><a href="/transiciones">Transiciones de estados</a></li>
            <li><a href="/autoadmin/sai_catalogo">Catálogos</a></li>
            <li><a href="/autoadmin">Tablas del sistema</a></li>
            <li><a href="/respaldos">Respaldos</a></li>
            <li><a href="/seguridad">Seguridad</a></li>
            <!--li><a href="/cargarForm">Cargar cat&aacute;logo de formulario</a></li-->
            <?php endif; ?>
            <li role="separator" class="divider"></li>
          </ul>
        </li>
        <?php endif; ?>
        <li class="dropdown">
          <!--a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Opciones de usuario<span class="caret"></span></a-->
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><?php
if (isset($_SESSION['usu_nombre'])){
    echo $_SESSION['usu_nombre'];
}
          ?><span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="/infoUsuario">Ver información de usuario</a></li>
            <li><a href="/cambiarClave">Cambiar contraseña</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="/login/destroy">Cerrar sesión</a></li>
            <li role="separator" class="divider"></li>
          </ul>
        </li>
      </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>


<div class="modal fade" id="vm_alerta" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Alerta</h4>
            </div>
            <div class="modal-body" id="vm_alerta_mensaje">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Aceptar</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="vm_procesando" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel"
     aria-hidden="true">

    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <!--img src="img/o.gif"-->
                <div style="width:300px;height:300px;margin:auto;padding-top:140px;background:url('/img/o.gif');background-repeat:no-repeat;background-position: center center;vertical-align: middle;text-align:center;">
                    Procesando <span id="procesando_count"></span>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="/js/jquery-3.2.1.min.js"></script>
<!--script src="https://code.jquery.com/jquery-3.2.1.min.js"></script-->
<script src="/js/form-validator/jquery.form-validator.min.js"></script>
<script src="/js/moment-with-locales.min.js"></script>
<script src="/js/bootstrap.min.js"></script>
<!--script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script-->
<script src="/js/bootstrap-datetimepicker.min.js"></script>
<script src="/js/select2.min.js"></script>
<script src="/js/jquery.dataTables.min.js"></script>
<script>
$.fn.modal.Constructor.prototype.enforceFocus = function() {};
</script>

<div style="padding:2px;">
  <?php echo $content; ?>
</div>
<div style="background:url(/img/sait-logo.png) fixed no-repeat;
background-position: center center;
opacity: 0.1;
  top: 0;
  left: 0;
  bottom: 0;
  right: 0;
  position: absolute;
  z-index: -1;
  ;">
</div>
<footer class="footer">
  <div class="container">
    <p class="text-muted text-center">
    NEDETEL - <?=date('Y')?>
    </p>
  </div>
</footer>
</body>
</html>
