<?php
//echo ($_solo_lectura ? 'SI solo lectura' : 'NO solo lectura');
//echo '<pre>';
//var_dump($_SESSION['seguridades']);
//echo '</pre>';
    $result_destinatarios = q("
        SELECT des_nombre FROM sai_destinatario
    ");
    //$destinatarios = array('cliente', 'proveedor', 'usuario');
    $destinatarios = array();
    if ($result_destinatarios) {
        foreach($result_destinatarios  as $r) {
            $destinatarios[] = $r['des_nombre'];
        }
    }
    $result_provincias = q("
        SELECT *
        FROM sai_provincia
        WHERE prv_borrado IS NULL
        ORDER BY prv_nombre
    ");
    $provincias = array();
    if ($result_provincias) {
        foreach ($result_provincias as $r) {
            $provincias[] = $r;
        }
    }
    $tipos_contactos = array();
    $result_tipos_contactos = q("
        SELECT *
        FROM sai_tipo_contacto
        WHERE tco_borrado IS NULL
    ");
    if ($result_tipos_contactos) {
        foreach ($result_tipos_contactos as $r) {

            $nombre = ($r['tco_nombre']);
            $nombre = strtolower($nombre);
            $nombre = str_replace(' ', '_', $nombre);
            $nombre = limpiar_nombre_archivo($nombre);
            $tipos_contactos[$nombre] = $r['tco_id'];
        }
    }
    //var_dump($tipos_contactos);
?>
<html style="height: auto; min-height: 100%;">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>AdminLTE 2 | Starter</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="/css/AdminLTE.min.css">
  <!-- AdminLTE Skins. We have chosen the skin-blue for this starter
        page. However, you can choose any other skin. Make sure you
        apply the skin class to the body tag so the changes take effect. -->
  <link rel="stylesheet" href="/css/skins/skin-blue.min.css">

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->

  <!-- Google Font -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
<!--script type="text/javascript" src="chrome-extension://aggiiclaiamajehmlfpkjmlbadmkledi/lib/popup.js" async=""></script><script type="text/javascript" src="chrome-extension://aggiiclaiamajehmlfpkjmlbadmkledi/lib/tat_popup.js" async=""></script><script src="chrome-extension://hbhhpaojmpfimakffndmpmpndcmonkfa/generated/eval.js"></script--></head>
<!--
BODY TAG OPTIONS:
=================
Apply one or more of the following classes to get the
desired effect
|---------------------------------------------------------|
| SKINS         | skin-blue                               |
|               | skin-black                              |
|               | skin-purple                             |
|               | skin-yellow                             |
|               | skin-red                                |
|               | skin-green                              |
|---------------------------------------------------------|
|LAYOUT OPTIONS | fixed                                   |
|               | layout-boxed                            |
|               | layout-top-nav                          |
|               | sidebar-collapse                        |
|               | sidebar-mini                            |
|---------------------------------------------------------|
-->
<!-- body class="skin-blue sidebar-mini" style="height: auto; min-height: 100%;" -->
<body class="skin-blue-light sidebar-mini" style="height: auto; min-height: 100%;">
<?php

?>
<div class="wrapper" style="height: auto; min-height: 100%;">

  <!-- Main Header -->
  <!-- Left side column. contains the logo and sidebar -->
  <aside class="main-sidebar">

    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">

      <!-- Sidebar user panel (optional) -->
      <!--div class="user-panel">
        <div class="pull-left image">
          <img src="/img/user2-160x160.jpg" class="img-circle" alt="User Image">
        </div>
        <div class="pull-left info">
          <p>Alexander Pierce</p>
          <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
        </div>
      </div-->

      <!-- search form (Optional) -->
      <form action="#" method="POST" id="busqueda" onsubmit="p_enviar_busqueda(this)" class="sidebar-form">
        <div class="input-group">
          <input type="text" name="busqueda_query" id="busqueda_query" class="form-control" placeholder="Buscar...">
          <span class="input-group-btn">
              <button type="submit" name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i>
              </button>
            </span>
        </div>
      </form>
      <!-- /.search form -->

      <!-- Sidebar Menu -->
      <ul class="sidebar-menu tree" data-widget="tree">
        <li class="header">ESTADOS DE ATENCIONES</li>
<?php
$result = q("
    SELECT * 
    FROM sai_estado_atencion 
    WHERE esa_borrado IS NULL
    AND esa_nombre <> 'Fin'
    ORDER BY esa_padre, esa_orden, esa_id
");
    $tree = array();
    $estados = array();
    foreach($result as $r){
        $id = $r['esa_id'];
        $padre = $r['esa_padre'];
        $tree[$id] = $r;
        $tree[$id]['padre'] = null;
        $tree[$id]['hijos'] = array();
    }
    foreach($result as $r){
        $id = $r['esa_id'];
        $padre = $r['esa_padre'];
        $tree[$id]['padre'] = & $tree[$padre];
        $tree[$padre]['hijos'][$id] = & $tree[$id];
    }
    function p_tree($hijos, $texto = null, $esa_codigo_padre = null) {
        if (!empty($texto)) {
            echo <<<EOF
            <li class="treeview">
              <a href="#"><span class="glyphicon glyphicon-th-list" aria-hidden="true"></span> <span>$texto</span>
                <span class="pull-right-container">
                    <i class="fa fa-angle-left pull-right"></i>
                  </span>
              </a>
              <ul class="treeview-menu">
EOF;
        }
        foreach ($hijos as $id => $hijo) {
            if (isset($hijo['hijos']) && !empty($hijo['hijos'])) {
                //no es hoja, tiene hijos
                $esa_codigo = (!empty($hijo['esa_codigo'])) ? $hijo['esa_codigo'] : $esa_codigo_padre;
                p_tree($hijo['hijos'], $hijo['esa_nombre'], $esa_codigo);
            } else {
                //es hoja
                echo <<<EOF
                    <li><a href="/{$esa_codigo_padre}/{$hijo['esa_id']}">{$hijo['esa_nombre']}</a></li>
EOF;
            }
        }
        if (!empty($texto)) {
            echo <<<EOF
                <li><a href="/{$esa_codigo_padre}">TODOS</a></li>
              </ul>
            </li>
EOF;
        }
    }
    p_tree($tree[""]['hijos']);
    //echo "<pre>";
    //var_dump($tree[""]);

    //VERIFICA FILTRO DE DATOS, Y TITULO:
$filtro = isset($filtro) ? " AND tea_estado_atencion_actual IN $filtro" : '';
$filtro_raw = isset($filtro_raw) ? $filtro_raw : '';
$esa_id = isset($esa_id) ? intval($esa_id) : null;

if (isset($args[0]) && !empty($args[0])) {
    $esa_id = intval($args[0]);
    $filtro = " AND tea_estado_atencion_actual = $esa_id";
}

if (isset($args[1]) && !empty($args[1])) {
    $busqueda = pg_escape_string($args[1]);
    $busqueda = strtolower($busqueda);
    $ate_secuencial_busqueda = intval($busqueda);
    $filtro_busqueda = " AND ate_secuencial = $ate_secuencial_busqueda OR ate_codigo ILIKE '%{$busqueda}%'";
}

if (!empty($esa_id)) {
    $esa_nombre = q("SELECT esa_nombre FROM sai_estado_atencion WHERE esa_borrado IS NULL AND esa_id = $esa_id")[0]['esa_nombre'];
}
?>
      <!-- /.sidebar-menu -->
    </section>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper" style="min-height: 368px;">
    <!--div style="padding: 20px 30px; background: rgb(243, 156, 18); z-index: 999999; font-size: 16px; font-weight: 600;"><a class="pull-right" href="#" data-toggle="tooltip" data-placement="left" title="Never show me this again!" style="color: rgb(255, 255, 255); font-size: 20px;">×</a><a href="https://themequarry.com" style="color: rgba(255, 255, 255, 0.9); display: inline-block; margin-right: 10px; text-decoration: none;">Ready to sell your theme? Submit your theme to our new marketplace now and let over 200k visitors see it!</a><a class="btn btn-default btn-sm" href="https://themequarry.com" style="margin-top: -5px; border: 0px; box-shadow: none; color: rgb(243, 156, 18); font-weight: 600; background: rgb(255, 255, 255);">Let's Do It!</a>
</div-->
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
      <?=(isset($titulo_proceso) ? $titulo_proceso : 'Resultados de búsqueda')?>
      <span class="badge"><?=$esa_nombre?></span>
        <!--small>Optional description</small-->
      </h1>
      <!--ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
      </ol-->
    </section>

    <!-- Main content -->
    <section class="content container-fluid">

      <!--------------------------
        | Your Page Content Here |
        -------------------------->

<?php
//if (isset($_POST['estado']) && !empty($_POST['estado'])) {

$sql = ("
    SELECT * 
    ,e1.esa_nombre AS estado_actual
    ,e1.esa_id AS estado_actual_id
    ,e2.esa_nombre AS estado_siguiente
    ,e2.esa_id AS estado_siguiente_id
    ,e2.esa_orden AS estado_siguiente_orden
    ,(usu_tecnico.usu_nombres || ' ' || usu_tecnico.usu_apellidos) AS usu_tecnico_nombre
    ,(usu_comercial.usu_nombres || ' ' || usu_comercial.usu_apellidos) AS usu_comercial_nombre
    ,(
        SELECT to_char(paa_creado, 'YYYY-MM-DD ')
        FROM sai_paso_atencion
        WHERE paa_borrado IS NULL
        AND paa_atencion = ate_id
        ORDER BY paa_creado DESC
        LIMIT 1 
    ) AS fecha_vigencia

    FROM sai_atencion

    LEFT OUTER JOIN sai_servicio
        ON ser_borrado IS NULL
        AND ate_servicio = ser_id

    LEFT OUTER JOIN sai_cuenta
        ON cue_borrado IS NULL
        AND cue_id = ate_cuenta

    LEFT OUTER JOIN sai_cliente
        ON cli_borrado IS NULL
        AND cli_id = ate_cliente

    LEFT OUTER JOIN sai_pertinencia_proveedor
        ON pep_borrado IS NULL
        AND ate_pertinencia_proveedor = pep_id

    LEFT OUTER JOIN sai_proveedor
        ON pro_borrado IS NULL
        AND pep_proveedor = pro_id

    LEFT OUTER JOIN sai_contacto
        ON con_borrado IS NULL
        AND ate_contacto = con_id

    LEFT OUTER JOIN sai_usuario AS usu_tecnico
        ON usu_tecnico.usu_borrado IS NULL
        AND usu_tecnico.usu_id = ate_usuario_tecnico

    LEFT OUTER JOIN sai_usuario AS usu_comercial
        ON usu_comercial.usu_borrado IS NULL
        AND usu_comercial.usu_id = ate_usuario_comercial


    LEFT OUTER JOIN sai_transicion_estado_atencion
        ON tea_borrado IS NULL
        AND tea_pertinencia_proveedor = pep_id
        AND tea_estado_atencion_actual = ate_estado_atencion

    LEFT OUTER JOIN sai_estado_atencion AS e1 
        ON e1.esa_borrado IS NULL
        AND tea_estado_atencion_actual = e1.esa_id

    LEFT OUTER JOIN sai_estado_atencion AS e2 
        ON e2.esa_borrado IS NULL
        AND tea_estado_atencion_siguiente = e2.esa_id

    WHERE ate_borrado IS NULL
        $filtro
        $filtro_busqueda

    ORDER BY 
        ate_id DESC, estado_actual_id DESC, estado_siguiente_orden
        ,ate_creado DESC
");
$result = q($sql);
//echo $sql;
if ($result) {
    $estado_actual = null;
    $estado_siguiente = null;
    $atenciones = array();
    foreach ($result as $r) {
        if (!isset($atenciones[$r[ate_id]])) {
            $atenciones[$r[ate_id]] = $r;
            $atenciones[$r[ate_id]]['estados_siguientes'] = array();
        }
        $atenciones[$r[ate_id]]['estados_siguientes'][$r[estado_siguiente_id]] = $r;

    }

    echo '<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">';
    foreach ($atenciones as $ate_id => $atencion) {
        $estados_siguentes = $atencion['estados_siguientes'];
        $tea_id_actual = $atencion['tea_id'];
        $r = $atencion;

        $fecha_formateada = p_formatear_fecha($r['ate_creado']);
        $estado_actual = empty($r[estado_actual]) ? 'ATENCION SIN ESTADO': $r[estado_actual];
        $codigo = empty($r[ate_codigo]) ? '' : " , ID: <strong>{$r[ate_codigo]}</strong>";
        echo <<<EOT
      <a name="atencion_{$r[ate_secuencial]}"></a>
<div class="panel panel-info panel-atencion" id="panel_atencion_{$r[ate_id]}"  xxxstyle="width:500px;">
  <div class="panel-heading">
    <div class="pull-right">
      $fecha_formateada 
    </div>
    <h3 class="panel-title">
      <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse_{$r[ate_secuencial]}" aria-expanded="false" aria-controls="collapse_{$r[ate_secuencial]}" >
        {$r[ate_secuencial]}. <strong>{$estado_actual}</strong> para servicio de {$r[ser_nombre]} ({$r[pro_nombre_comercial]}) a {$r[cli_razon_social]} $codigo
      </a>
    </h3>
  </div>

  <div id="collapse_{$r[ate_secuencial]}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading_{$r[ate_secuencial]}">
EOT;

        $es_fin = false;
        foreach ($estados_siguentes as $estado_siguiente_id => $estado_siguiente) {
            if (strtolower($estado_siguiente[estado_siguiente]) === 'fin') {
                $es_fin = true;
            }
        }

        if (!$_solo_lectura && !$es_fin) {
            echo <<<EOT
      <div class="pull-right well" style="padding:20px;margin:20px;">
      <h4>Pasar a un siguiente estado:</h4>
EOT;
            foreach ($estados_siguentes as $estado_siguiente_id => $estado_siguiente) {
                $rsig = $estado_siguiente;
                echo <<<EOT
<form method="POST" onsubmit="return p_validar_transicion(this, {$rsig['tea_id']}, {$rsig['ate_id']}, {$rsig['estado_siguiente_id']})">
<input type="hidden" name="estado" value="{$rsig['estado_siguiente_id']}">
<input type="hidden" name="tea_id" value="{$rsig['tea_id']}">
<input type="hidden" name="id" value="{$rsig['ate_id']}">
<button class="btn btn-success">
<span class="glyphicon glyphicon-arrow-right" aria-hidden="true"></span>
 {$rsig['estado_siguiente']}
</button>
</form>
EOT;

            }
            echo '</div>';
        }

        $glue = '';
        $contacto_empresa = '';
        if (!empty($r[con_telefono])) {
            $contacto_empresa .= $glue . $r[con_telefono];
            $glue = ', ';
        }

        if (!empty($r[con_celular])) {
            $contacto_empresa .= $glue . $r[con_celular];
            $glue = ', ';
        }

        if (!empty($r[con_correo_electronico])) {
            $contacto_empresa .= $glue . "<a href='mailto:{$r[con_correo_electronico]}'>{$r[con_correo_electronico]}</a>";
            $glue = ', ';
        }

        echo <<<EOT
    <div class="panel-body">
      <strong>Dependencia de empresas:</strong> {$r[cue_codigo]}
      <br>
      <strong>Contacto de la empresa:</strong> {$r[con_nombres]} {$r[con_apellidos]} ({$contacto_empresa})
      <br>
      <strong>Usuario técnico:</strong> {$r[usu_tecnico_nombre]}
      <br>
      <strong>Usuario comercial:</strong> {$r[usu_comercial_nombre]}
      <br>
      <strong>Fecha de transición:</strong> {$r[fecha_vigencia]}
<div id="campos_estado_vigente_{$r[ate_id]}"></div>
      <div>&nbsp;</div>
<a class="btn btn-info" href="#" onclick="p_toggle_historico({$r[ate_id]}, {$r[ate_secuencial]});return false;">Mostrar historial</a>
<!--
        <table id="tabla_historico_{$r[ate_id]}" style="width:400px;display:none;" class="table table-striped table-condensed table-hover">
        <tbody id="valores_historicos_{$r[ate_id]}">
EOT;

        /*
        $sql = ("
            SELECT *
            , concat(
                vae_texto
                ,vae_numero
                ,to_char(vae_fecha, 'yyyy-MM-DD hh:mm')
            ) AS valor
            , (
                SELECT nod_codigo
                FROM 
                 sai_nodo
                , sai_ubicacion
                WHERE 
                nod_borrado IS NULL
                AND ubi_borrado IS NULL
                AND nod_id = vae_nodo
                AND ubi_id = nod_ubicacion
            ) AS nodo
            , (
                SELECT ciu_nombre 
                FROM 
                 sai_ciudad
                WHERE 
                ciu_borrado IS NULL
                AND ciu_id = vae_ciudad
            ) AS ciudad
            FROM sai_campo_extra
            ,sai_paso_atencion
            ,sai_valor_extra
            ,sai_usuario
            ,sai_transicion_estado_atencion
            ,sai_estado_atencion
            WHERE cae_borrado IS NULL
            AND vae_borrado IS NULL
            AND paa_borrado IS NULL
            AND usu_borrado IS NULL
            AND tea_borrado IS NULL
            AND esa_borrado IS NULL
            AND vae_campo_extra = cae_id
            AND vae_paso_atencion = paa_id
            AND paa_creado_por = usu_id
            AND paa_transicion_estado_atencion = tea_id
            AND tea_estado_atencion_actual = esa_id
            AND paa_atencion={$r[ate_id]}
            AND NOT paa_confirmado IS NULL
            ORDER BY paa_id DESC, cae_orden
        ");
            //AND NOT paa_paso_anterior IS NULL//reemplazado por confirmado

            //AND paa_borrado IS NULL // ya agregado...
        //$result_campos = q($sql);
        if ($result_campos) {
            $paa = null;
            foreach($result_campos as $rdato){
                if ($paa != $rdato['paa_id']) {
                    $paa = $rdato['paa_id'];
                    $usuario = $rdato['usu_nombres'] . ' ' . $rdato['usu_apellidos'];
                    $estado = $rdato['esa_nombre'];
                    $fecha_formateada = p_formatear_fecha($rdato['paa_creado']);
                    echo <<<EOT
            <tr>
              <td class="bg-info" colspan=2>
                <strong>$estado</strong> por $usuario
                <br>
                {$fecha_formateada}
            </td>
            </tr>
EOT;
                }
                $label = ucfirst($rdato['cae_texto']);
                //$dato = $rdato['vae_texto'] . $rdato['vae_numero'] . $rdato['vae_fecha'] . $rdato['nodo'] .$rdato['ciudad'];
                if (empty($rdato['nodo'])) {
                    $dato = $rdato['valor'] . $rdato['ciudad'] ;
                } else {
                    $nod_id = $rdato['vae_nodo'];
                    $dato = <<<EOT
                    <a href="#" onclick="p_abrir_detalle_nodo($nod_id);return false;">{$rdato['nodo']}</a>
EOT;
                }
                echo <<<EOT
            <tr>
              <th style="width:50%;text-align:right;">$label:</th>
              <td style="text-align:center;" id="campo_historico_{$r[ate_id]}_{$rdato[cae_id]}">$dato</td>
            </tr>
EOT;
            }
        }
         */
        //echo "$sql";
        echo <<<EOT
        </tbody></table>
-->
      <div>&nbsp;</div>
    </div>
  </div>
</div>
EOT;
    }
    echo '</div>';
}
?>


    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->


  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Create the tabs -->
    <ul class="nav nav-tabs nav-justified control-sidebar-tabs">
      <li class="active"><a href="#control-sidebar-home-tab" data-toggle="tab"><i class="fa fa-home"></i></a></li>
      <li><a href="#control-sidebar-settings-tab" data-toggle="tab"><i class="fa fa-gears"></i></a></li>
    </ul>
    <!-- Tab panes -->
    <div class="tab-content">
      <!-- Home tab content -->
      <div class="tab-pane active" id="control-sidebar-home-tab">
        <h3 class="control-sidebar-heading">Recent Activity</h3>
        <ul class="control-sidebar-menu">
          <li>
            <a href="javascript:;">
              <i class="menu-icon fa fa-birthday-cake bg-red"></i>

              <div class="menu-info">
                <h4 class="control-sidebar-subheading">Langdon's Birthday</h4>

                <p>Will be 23 on April 24th</p>
              </div>
            </a>
          </li>
        </ul>
        <!-- /.control-sidebar-menu -->

        <h3 class="control-sidebar-heading">Tasks Progress</h3>
        <ul class="control-sidebar-menu">
          <li>
            <a href="javascript:;">
              <h4 class="control-sidebar-subheading">
                Custom Template Design
                <span class="pull-right-container">
                    <span class="label label-danger pull-right">70%</span>
                  </span>
              </h4>

              <div class="progress progress-xxs">
                <div class="progress-bar progress-bar-danger" style="width: 70%"></div>
              </div>
            </a>
          </li>
        </ul>
        <!-- /.control-sidebar-menu -->

      </div>
      <!-- /.tab-pane -->
      <!-- Stats tab content -->
      <div class="tab-pane" id="control-sidebar-stats-tab">Stats Tab Content</div>
      <!-- /.tab-pane -->
      <!-- Settings tab content -->
      <div class="tab-pane" id="control-sidebar-settings-tab">
        <form method="post">
          <h3 class="control-sidebar-heading">General Settings</h3>

          <div class="form-group">
            <label class="control-sidebar-subheading">
              Report panel usage
              <input type="checkbox" class="pull-right" checked="">
            </label>

            <p>
              Some information about this general settings option
            </p>
          </div>
          <!-- /.form-group -->
        </form>
      </div>
      <!-- /.tab-pane -->
    </div>
  </aside>
  <!-- /.control-sidebar -->
  <!-- Add the sidebar's background. This div must be placed
  immediately after the control sidebar -->
  <div class="control-sidebar-bg"></div>
</div>
<!-- ./wrapper -->

<?php if (isset($mostrar_nuevo) && $mostrar_nuevo && !$_solo_lectura): ?>
<a href="#" onclick="p_nuevo();return false;" style="position:fixed;bottom:50px;right:10px;"><img src="/img/plus.png" alt="Crear nuevo registro" title="Crear nuevo registro" ></img></a>
<?php endif; ?>
<!-- REQUIRED JS SCRIPTS -->

<!-- AdminLTE App -->
<script src="/js/adminlte.min.js"></script>

<!-- Optionally, you can add Slimscroll and FastClick plugins.
     Both of these plugins are recommended to enhance the
     user experience. -->

<div id="modal_nodo" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog xxx-modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-nuevo-title">Nuevo punto</h4>
      </div>
      <div class="modal-body">

<form id="formulario_nodo" class="form-horizontal">
<input type="hidden" id="nod_cae_id" name="nod_cae_id" value="">
<input type="hidden" id="nod_atencion" name="nod_atencion" value="">
<?php

$cae_texto = 'Ubicación';
$cae_id = '0';
$cae_validacion = ' required ';
$col1 = 3;
$col2 = 8;

$result_provincias = q("
    SELECT *
    FROM sai_provincia
    ORDER BY prv_nombre
");
$provincias = array();
if ($result_provincias) {
    foreach ($result_provincias as $r) {
        $provincias[] = $r;
    }
}
$opciones = '<option value="">&nbsp;</option>';

foreach($provincias as $provincia) {
    $codigo = $provincia['prv_id'];
    $nombre = $provincia['prv_nombre'];
    $opciones .= '<option value="'. $codigo . '">'.$nombre.' </option>';
}

?>
    <div class="form-group">
      <label for="nod_codigo" class="col-sm-<?=$col1?> control-label">Código:</label>
      <div class="col-sm-<?=$col2?>">
        <input <?=$cae_validacion?> class="form-control" id="nod_codigo" name="nod_codigo" placeholder="" value="" onblur="p_validar(this)">
      </div>
    </div>

    <div class="form-group">
      <label for="nod_descripcion" class="col-sm-<?=$col1?> control-label">Descripción:</label>
      <div class="col-sm-<?=$col2?>">
        <input <?=$cae_validacion?> class="form-control" id="nod_descripcion" name="nod_descripcion" placeholder="" value="" onblur="p_validar(this)">
      </div>
    </div>

<div class="panel panel-default">
  <div class="panel-heading">
    <strong><?=$cae_texto?></strong>
  </div>
  <div class="panel-body">
    <input type="hidden" class="form-control" id=" $cae_validacionextra_<?=$cae_id?>" name=" $cae_validacionextra_<?=$cae_id?>" value="">

    <div class="form-group">
    <label for="nod_provincia" class="col-sm-<?=$col1?>  control-label">Provincia:</label>
      <div class="col-sm-<?=$col2?>">
        <select <?=$cae_validacion?> class="form-control combo-select2" id="nod_provincia" name="nod_provincia" placeholder="" value="" onblur="p_validar(this)" onchange="p_cargar_cantones_ciudades(this, <?=$cae_id?>)">
    <?=$opciones?> 
        </select>
      </div>
    </div>
   
    <div class="form-group">
    <label for="nod_canton" class="col-sm-<?=$col1?>  control-label">Cantón:</label>
      <div class="col-sm-<?=$col2?>">
        <select disabled <?=$cae_validacion?> class="form-control combo-select2" id="nod_canton" name="nod_canton" placeholder="" value="" onblur="p_validar(this)" onchange="p_cargar_parroquias(this, <?=$cae_id?>)">
          <option>Escoja primero la provincia</option>
        </select>
      </div>
    </div>

    <div class="form-group">
      <label for="nod_parroquia" class="col-sm-<?=$col1?> control-label">Parroquia:</label>
      <div class="col-sm-<?=$col2?>">
        <select disabled <?=$cae_validacion?> class="form-control combo-select2" id="nod_parroquia" name="nod_parroquia" placeholder="" value="" onblur="p_validar(this)" >
          <option>Escoja primero la provincia y el cantón</option>
        </select>
      </div>
    </div>

    <div class="form-group">
      <label for="nod_ciudad" class="col-sm-<?=$col1?> control-label">Ciudad:</label>
      <div class="col-sm-<?=$col2?>">
        <select disabled <?=$cae_validacion?> class="form-control combo-select2" id="nod_ciudad" name="nod_ciudad" placeholder="" value="" onblur="p_validar(this)" >
          <option>Escoja primero la provincia</option>
        </select>
      </div>
    </div>
       
    <div class="form-group">
      <label for="nod_sector" class="col-sm-<?=$col1?> control-label">Sector:</label>
      <div class="col-sm-<?=$col2?>">
        <!--input <?=$cae_validacion?> class="form-control" id="nod_sector" name="nod_sector" placeholder="" value="" onblur="p_validar(this)"-->
        <input class="form-control" id="nod_sector" name="nod_sector" placeholder="" value="" onblur="p_validar(this)">
      </div>
    </div>

    <div class="form-group">
      <label for="nod_direccion" class="col-sm-<?=$col1?> control-label">Dirección:</label>
      <div class="col-sm-<?=$col2?>">
        <input <?=$cae_validacion?> class="form-control" id="nod_direccion" name="nod_direccion" placeholder="" value="" onblur="p_validar(this)">
      </div>
    </div>

    <div class="panel panel-default">
      <div class="panel-heading">
        <strong>Coordenadas</strong>
      </div>
      <div class="panel-body">
        <div class="form-group">
          <label for="nod_longitud" class="col-sm-<?=$col1?> control-label">Longitud:</label>
          <div class="col-sm-<?=$col2?>">
            <input <?=$cae_validacion?> class="form-control" id="nod_longitud" name="nod_longitud" placeholder="" value="" onblur="p_validar(this)">
          </div>
        </div>
        <div class="form-group">
          <label for="nod_latitud" class="col-sm-<?=$col1?> control-label">Latitud:</label>
          <div class="col-sm-<?=$col2?>">
            <input <?=$cae_validacion?> class="form-control" id="nod_latitud" name="nod_latitud" placeholder="" value="" onblur="p_validar(this)">
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
    function p_cargar_cantones_ciudades(target, cae_id){
        console.log('En p_cargar_cantones_ciudades', $(target).val(), cae_id);
        var prv_id = $(target).val();
        $('#nod_canton').prop('disabled', true);
        $('#nod_canton').html('<option value="">Escoja primero la provincia</option>');
        $('#nod_ciudad').prop('disabled', true);
        $('#nod_ciudad').html('<option value="">Escoja primero la provincia</option>');
        $('#nod_parroquia').prop('disabled', true);
        $('#nod_parroquia').html('<option value="">Escoja primero el cantón</option>');

        if (prv_id != '') {
            $.ajax({
                'url':'/_listar/canton/provincia/' + prv_id
            }).done(function(data){
                console.log('Respuesta /_listar/canton/provincia/' + prv_id, data);
                data = JSON.parse(data);
                console.log('data',data);
                var opciones = '';
                Array.from(data).forEach(function(canton){
                    opciones += '<option value="'+canton['id']+'">'+canton['nombre']+'</option>';
                });

                $('#nod_canton').prop('disabled', false);
                $('#nod_canton').html('<option value="">&nbsp;</option>' + opciones);

            });

            $.ajax({
                'url':'/_listar/ciudad/provincia/' + prv_id
            }).done(function(ciudades){
                console.log('Respuesta /_listar/ciudad/provincia/' + prv_id, ciudades);
                ciudades = JSON.parse(ciudades);
                console.log('ciudades',ciudades);
                var opciones = '';
                Array.from(ciudades).forEach(function(ciudad){
                    opciones += '<option value="'+ciudad['id']+'">'+ciudad['nombre']+'</option>';
                });

                $('#nod_ciudad').prop('disabled', false);
                $('#nod_ciudad').html('<option value="">&nbsp;</option>' + opciones);
            });
        }
    }

    function p_cargar_parroquias(target, cae_id){
        console.log('En p_cargar_parroquias', $(target).val(), cae_id);
        var can_id = $(target).val();
        $('#nod_parroquia').prop('disabled', true);
        $('#nod_parroquia').html('<option value="">Escoja primero el cantón</option>');
        if (can_id != '') {
            $.ajax({
                'url':'/_listar/parroquia/canton/' + can_id
            }).done(function(data){
                console.log('Respuesta /_listar/parroquia/canton/' + can_id, data);
                data = JSON.parse(data);
                console.log('data',data);
                var opciones = '';
                Array.from(data).forEach(function(canton){
                    opciones += '<option value="'+canton['id']+'">'+canton['nombre']+'</option>';
                });

                $('#nod_parroquia').prop('disabled', false);
                $('#nod_parroquia').html('<option value="">&nbsp;</option>' + opciones);
            });
        }
    }

</script>
</form>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success" onclick="p_crear_nodo()" id="boton_crear_nodo">Crear punto y regresar</button>
      </div>
    </div>
  </div>
</div>




<div id="modal_historial" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-nuevo-title">Historial de <?=isset($titulo_proceso_singular)?$titulo_proceso_singular:'atención'?> <span id="historial_titulo"></span></h4>
      </div>
      <div class="modal-body">
        <div class="form-horizontal" id="contenido_historial">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>


<style>
.autoalto .form-control{
height:auto;
min-height: 30px;
overflow: hidden;
}
</style>

<div id="modal_detalle_nodo" class="modal fade autoalto" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-nuevo-title"><span id="detalle_nodo_titulo"></span></h4>
      </div>
      <div class="modal-body">
        <div class="form-horizontal" id="detalle_nodo_contenido">

          <?php $col1=2;$col2=4; ?>


          <div class="form-group">
            <label class="col-sm-<?=$col1?> control-label">Dirección:</label>
            <div class="col-sm-<?=$col2?>">
              <span class="form-control" id="detalle_nodo_direccion"></span>
            </div>

            <label class="col-sm-<?=$col1?> control-label">Sector:</label>
            <div class="col-sm-<?=$col2?>">
              <span class="form-control" id="detalle_nodo_sector"></span>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-<?=$col1?> control-label">Longitud:</label>
            <div class="col-sm-<?=$col2?>">
              <span class="form-control" id="detalle_nodo_longitud"></span>
            </div>

            <label class="col-sm-<?=$col1?> control-label">Latitud:</label>
            <div class="col-sm-<?=$col2?>">
              <span class="form-control" id="detalle_nodo_latitud"></span>
            </div>
          </div>

          <table class="table table-bordered bg-info">
            <thead>
            <tr>
            <th>Provincia</th>
            <th>Cantón</th>
            <th>Parroquia</th>
            <th>Ciudad</th>
            </tr>
            </thead>
            <tbody>
            <tr>
            <td><span id="detalle_nodo_provincia"></span></td>
            <td><span id="detalle_nodo_canton"></span></td>
            <td><span id="detalle_nodo_parroquia"></span></td>
            <td><span id="detalle_nodo_ciudad"></span></td>
            </tr>
            </tbody>
          </table>

<hr>

          <div class="form-group">
            <label class="col-sm-<?=$col1?> control-label">Código:</label>
            <div class="col-sm-<?=$col2?>">
              <span class="form-control" id="detalle_nodo_codigo"></span>
            </div>

            <label class="col-sm-<?=$col1?> control-label">Descripción:</label>
            <div class="col-sm-<?=$col2?>">
              <span class="form-control" id="detalle_nodo_descripcion"></span>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-<?=$col1?> control-label">Tipo de última milla:</label>
            <div class="col-sm-<?=$col2?>">
              <span class="form-control" id="detalle_nodo_tipo_ultima_milla"></span>
            </div>

            <label class="col-sm-<?=$col1?> control-label">Responsable de última milla:</label>
            <div class="col-sm-<?=$col2?>">
              <span class="form-control" id="detalle_nodo_responsable_ultima_milla"></span>
            </div>
          </div>

          <!--div class="form-group">
            <label class="col-sm-<?=$col1?> control-label">Costo de instalación del proveedor:</label>
            <div class="col-sm-<?=$col2?>">
              <span class="form-control" id="detalle_nodo_costo_instalacion_proveedor"></span>
            </div>

            <label class="col-sm-<?=$col1?> control-label">Costo de instalación del cliente:</label>
            <div class="col-sm-<?=$col2?>">
              <span class="form-control" id="detalle_nodo_costo_instalacion_cliente"></span>
            </div>
          </div-->

          <div class="form-group">
            <label class="col-sm-<?=$col1?> control-label">Distancia:</label>
            <div class="col-sm-<?=$col2?>">
              <span class="form-control" id="detalle_nodo_distancia"></span>
            </div>

            <label class="col-sm-<?=$col1?> control-label">Fecha de término:</label>
            <div class="col-sm-<?=$col2?>">
              <span class="form-control" id="detalle_nodo_fecha_termino"></span>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-<?=$col1?> control-label">Creado por:</label>
            <div class="col-sm-<?=$col2?>">
              <span class="form-control" id="detalle_nodo_creado_por"></span>
            </div>

            <label class="col-sm-<?=$col1?> control-label">Fecha de creación:</label>
            <div class="col-sm-<?=$col2?>">
              <span class="form-control" id="detalle_nodo_creado"></span>
            </div>
          </div>

        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>



<div id="modal_nodo_completo" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog xxx-modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-nuevo-title">Completar datos del punto</h4>
      </div>
      <div class="modal-body">

          <?php $col1=3;$col2=8; ?>

<form id="formulario_nodo_completo" class="form-horizontal">
<input type="hidden" id="nod_completo_cae_id" name="cae_id" value="">
<input type="hidden" id="nod_completo_id" name="nod_id" value="">
    <div class="form-group">
      <label class="col-sm-<?=$col1?> control-label">Código:</label>
      <div class="col-sm-<?=$col2?>">
        <span id="nod_completo_codigo"></span>
      </div>
    </div>

    <div class="form-group">
      <label class="col-sm-<?=$col1?> control-label">Descripción:</label>
      <div class="col-sm-<?=$col2?>">
        <span id="nod_completo_descripcion"></span>
      </div>
    </div>

    <!--div class="form-group">
      <label for="nod_costo_instalacion_proveedor" class="col-sm-<?=$col1?> control-label">Costo de instalación del proveedor:</label>
      <div class="col-sm-<?=$col2?>">
        <input type='number' <?=$cae_validacion?> class="form-control" id="nod_costo_instalacion_proveedor" name="nod_costo_instalacion_proveedor" placeholder="" value="" onblur="p_validar(this)">
      </div>
    </div>

    <div class="form-group">
      <label for="nod_costo_instalacion_cliente" class="col-sm-<?=$col1?> control-label">Costo de instalación del cliente:</label>
      <div class="col-sm-<?=$col2?>">
        <input type='number' <?=$cae_validacion?> class="form-control" id="nod_costo_instalacion_cliente" name="nod_costo_instalacion_cliente" placeholder="" value="" onblur="p_validar(this)">
      </div>
    </div-->

    <div class="form-group">
      <label for="nod_tipo_ultima_milla" class="col-sm-<?=$col1?> control-label">Tipo de última milla:</label>
      <div class="col-sm-<?=$col2?>">
        <select <?=$cae_validacion?> class="form-control combo-select2" id="nod_tipo_ultima_milla" name="nod_tipo_ultima_milla" onblur="p_validar(this)">
          <option value="">&nbsp;</option>
<?php
$result_tum = q("
    SELECT * 
    FROM sai_tipo_ultima_milla
    WHERE tum_borrado IS NULL
");
if ($result_tum) {
    foreach ($result_tum as $r) {
        echo "<option value='{$r[tum_id]}'>{$r[tum_nombre]}</option>";
    }
}
?>
        </select>
      </div>
    </div>

    <div class="form-group">
      <label for="nod_responsable_ultima_milla" class="col-sm-<?=$col1?> control-label">Responsable de última milla:</label>
      <div class="col-sm-<?=$col2?>">
        <input <?=$cae_validacion?> class="form-control" id="nod_responsable_ultima_milla" name="nod_responsable_ultima_milla" placeholder="" value="" onblur="p_validar(this)">
      </div>
    </div>

    <div class="form-group">
      <label for="nod_distancia" class="col-sm-<?=$col1?> control-label">Distancia:</label>
      <div class="col-sm-<?=$col2?>">
        <input type='number' <?=$cae_validacion?> class="form-control" id="nod_distancia" name="nod_distancia" placeholder="" value="" onblur="p_validar(this)">
      </div>
    </div>

    <div class="form-group">
      <label for="nod_fecha_termino" class="col-sm-<?=$col1?> control-label">Fecha de término:</label>
      <div class="col-sm-<?=$col2?>">
                <div class="input-group date">
                    <input <?=$cae_validacion?> class="form-control datetimepicker" id="nod_fecha_termino" name="nod_fecha_termino" placeholder="" value="" onblur="p_validar(this)">
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
      </div>
    </div>

    <div class="form-group">
      <label for="nod_nodo" class="col-sm-<?=$col1?> control-label">Nodo:</label>
      <div class="col-sm-<?=$col2?>">
        <input type='text' class="form-control" id="nod_nodo" name="nod_nodo" placeholder="" value="" onblur="p_validar(this)">
      </div>
    </div>

</form>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success" onclick="p_completar_nodo()" id="boton_crear_nodo">Guardar datos del punto y regresar</button>
      </div>
    </div>
  </div>
</div>



<div id="modal_confirmacion" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-nuevo-title">Confirmar acciones de cambio de estado</h4>
      </div>
      <div class="modal-body">

<form id="formulario_accion" class="form-horizontal">
<input type="hidden" id="ate_id_accion" name="ate_id">
<input type="hidden" id="estado_siguiente_id_accion" name="estado_siguiente_id">
      <?php foreach($destinatarios as $destinatario): ?>
<input type="hidden" id="tea_id_accion_<?=$destinatario?>" name="tea_id_<?=$destinatario?>">
<div id="panel_accion_<?=$destinatario?>" class="panel panel-default">
  <div class="panel-heading">
    <strong><?=ucfirst($destinatario)?></strong>
  </div>
  <div class="panel-body">



  <div class="form-group">
    <label for="email_<?=$destinatario?>" class="col-sm-3 control-label">Destinatarios:</label>
    <div class="col-sm-9">
      <input class="form-control" id="email_<?=$destinatario?>" name="email_<?=$destinatario?>">
    </div>
  </div>

  <div class="form-group">
    <label for="cc_<?=$destinatario?>" class="col-sm-3 control-label">Con copia a:</label>
    <div class="col-sm-9">
      <input class="form-control" id="cc_<?=$destinatario?>" name="cc_<?=$destinatario?>">
    </div>
  </div>

  <div class="form-group">
    <label for="asunto_<?=$destinatario?>" class="col-sm-3 control-label">Asunto:</label>
    <div class="col-sm-9">
      <input class="form-control" id="asunto_<?=$destinatario?>" name="asunto_<?=$destinatario?>">
    </div>
  </div>

  <div class="form-group">
    <label for="mensaje_<?=$destinatario?>" class="col-sm-3 control-label">Mensaje:</label>
    <div class="col-sm-9">
      <textarea class="form-control" id="mensaje_<?=$destinatario?>" name="mensaje_<?=$destinatario?>"></textarea>
    </div>
  </div>

  <div class="form-group">
    <label for="adjunto_<?=$destinatario?>" class="col-sm-3 control-label">Adjuntos:</label>
    <div class="col-sm-9">
      <input type="file" class="form-control" id="adjunto_<?=$destinatario?>" name="adjunto_<?=$destinatario?>" onchange="p_cargar_adjunto(this, '<?=$destinatario?>')">
      <div id="adjuntos_lista_<?=$destinatario?>"></div>
    </div>
  </div>


  </div>
</div>

<?php endforeach; ?>

</form>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-warning" onclick="p_abrir_campos_llenos()" id="boton_modificar_datos_recopilados">Modificar datos recopilados</button>
        <button type="button" class="btn btn-success" onclick="p_ejecutar_transicion()" id="boton_ejecutar_transicion">Ejecutar Transición</button>
      </div>
    </div>
  </div>
</div>

<div id="modal-nuevo" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-nuevo-title">Nueva <?=isset($titulo_proceso_singular)?$titulo_proceso_singular:'atención'?></h4>
      </div>
      <div class="modal-body">

<form id="formulario_nuevo" class="form-horizontal">
  <input type="hidden" id="ate_id_nuevo" name="ate_id" value="">


  <div class="form-group">
    <label for="cliente" class="col-sm-4 control-label">Empresa:</label>
    <div class="col-sm-8">
      <select required class="form-control combo-select2" style="width: 50%" id="cliente" name="cliente" tabindex="-1" aria-hidden="true" onchange="p_cargar_contactos_cuentas(this)">
        <option value="">&nbsp;</option>
      <?php
$result = q("
    SELECT *
    FROM sai_cliente
    WHERE cli_borrado IS NULL
");
if ($result) {
    foreach($result as $r) {
        $value = $r['cli_id'];
        $label = $r['cli_razon_social'];
        echo "<option value='$value'>$label</option>";
    }
}
        ?>
      </select>
    </div>
  </div>

  <div class="form-group">
    <label for="contacto" class="col-sm-4 control-label">Contacto de la empresa:</label>
    <div class="col-sm-8">
      <select required class="form-control combo-select2" style="width: 50%" id="contacto" name="contacto" tabindex="-1" aria-hidden="true">

        <option value="">&nbsp;</option>
      </select> 
    </div>
  </div>

  <div class="form-group">
    <label for="contacto_en_sitio" class="col-sm-4 control-label">Contacto en sitio:</label>
    <div class="col-sm-8">
      <select required class="form-control combo-select2" style="width: 50%" id="contacto_en_sitio" name="contacto_en_sitio" tabindex="-1" aria-hidden="true">

        <option value="">&nbsp;</option>
      </select> 
    </div>
  </div>


  <div class="form-group">
    <label for="cuenta" class="col-sm-4 control-label">Dependencia de empresas:</label>
    <div class="col-sm-8">
      <select required class="form-control combo-select2" style="width: 50%" id="cuenta" name="cuenta" tabindex="-1" aria-hidden="true">

        <option value="">&nbsp;</option>
<?php
/*
$result = q("
    SELECT *
    FROM sai_cuenta
    WHERE cue_borrado IS NULL
");
if ($result) {
    foreach ($result as $r) {
        $value = $r['cue_id'];
        $label = $r['cue_codigo'];
        echo "<option value='$value'>$label</option>";
    }
}
 */
        ?>
      </select> 
    </div>
  </div>


  <div class="form-group">
    <label for="servicio" class="col-sm-4 control-label">Servicio</label>
    <div class="col-sm-8">
      <select required class="form-control combo-select2" style="width: 50%" id="servicio" name="servicio" tabindex="-1" aria-hidden="true" onchange="p_cargar_proveedores(this)">

        <option value="">&nbsp;</option>
      <?php
$result = q("
    SELECT *
    FROM sai_servicio
    WHERE ser_borrado IS NULL
");
if ($result) {
    foreach($result as $r) {
        $value = $r['ser_id'];
        $label = $r['ser_nombre'];
        echo "<option value='$value'>$label</option>";
    }
}
        ?>
      </select> 
    </div>
  </div>




  <div class="form-group">
    <label for="proveedor" class="col-sm-4 control-label">Proveedor</label>
    <div class="col-sm-8">
      <select required multiple class="form-control combo-select2" style="width: 50%" id="proveedor" name="proveedor[]" tabindex="-1" aria-hidden="true">
        <option value="">&nbsp;</option>
      <?php
/*
$result = q("
    SELECT *
    FROM sai_proveedor
    WHERE pro_borrado IS NULL
");
if ($result) {
    foreach($result as $r) {
        $value = $r['pro_id'];
        $label = $r['pro_nombre_comercial']; 
        echo "<option value='$value'>$label</option>";
    }
}
 */
        ?>
      </select> 
    </div>
  </div>


  <div class="form-group">
    <label for="usuario_tecnico" class="col-sm-4 control-label">Usuario técnico</label>
    <div class="col-sm-8">

      <!--pre>FOREIGN KEY
                  </pre-->
      <select required class="form-control combo-select2" style="width: 50%" id="usuario_tecnico" name="usuario_tecnico" tabindex="-1" aria-hidden="true">
        <option value="">&nbsp;</option>
      <?php
$result = q("
    SELECT *
    ,(usu_nombres || ' ' || usu_apellidos) AS nombre
    FROM sai_usuario
    ,sai_rol
    WHERE  usu_borrado IS NULL
    AND rol_borrado IS NULL
    AND usu_rol = rol_id
    AND rol_codigo = 'tecnico'
");
if ($result) {
    $nombres = array();
    foreach($result as $r) {
        $value = $r['usu_id'];
        $label = $r['nombre'];
        if (!isset($nombres[$label])) {
            echo "<option value='$value'>$label</option>";
            $nombres[$label] = $value;
        }
    }
}
        ?>

      </select> 
    </div>
  </div>



  <div class="form-group">
    <label for="usuario_comercial" class="col-sm-4 control-label">Usuario comercial</label>
    <div class="col-sm-8">

      <!--pre>FOREIGN KEY
                  </pre-->
      <select required class="form-control combo-select2" style="width: 50%" id="usuario_comercial" name="usuario_comercial" tabindex="-1" aria-hidden="true">
        <option value="">&nbsp;</option>
      <?php
$result = q("
    SELECT *
    FROM sai_usuario
    ,sai_rol
    WHERE 
    usu_borrado IS NULL
    AND rol_id = usu_rol
    AND rol_codigo = 'comercial'
");
if ($result) {
    foreach($result as $r) {
        $value = $r['usu_id'];
        $label = $r['usu_nombres'] . ' ' .$r['usu_apellidos'];
        echo "<option value='$value'>$label</option>";
    }
}
        ?>

      </select> 
    </div>
  </div>

  <div class="form-group">
    <label for="cantidad_extremos" class="col-sm-4 control-label">Cantidad de Extremos</label>
    <div class="col-sm-8">
      <input type="number" min="1" max="999" step="1" class="form-control" id="cantidad_extremos" name="cantidad_extremos">
    </div>
  </div>


</form>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-success" onclick="p_crear()" id="formulario_nuevo_crear">Crear <?=isset($titulo_proceso_singular)?$titulo_proceso_singular:'atención'?></button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->



<div id="modal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Ingrese los siguientes datos <span id="formulario_titulo"></span></h4>
      </div>
      <div class="modal-body">

<!--div id="map" style=" height: 400px;width: 100%;"></div-->
<form id="formulario" class="form-horizontal">
  <input type="hidden" id="ate_id" name="ate_id" value="">

<div id="campos"></div>
</form>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-success" onclick="p_guardar()" id="formulario_guardar">Guardar</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
</body></html>


<script src="/js/ckeditor/ckeditor.js"></script>
<script src="/js/bootstrap3-typeahead.min.js"></script>

  <link rel="stylesheet" href="/css/bootstrap-toggle.min.css">
  <script src="/js/bootstrap-toggle.min.js"></script>

<script>
var tipos_contactos = <?=json_encode($tipos_contactos)?>;
$(document).ready(function() {
    $('.panel-atencion').on('shown.bs.collapse', function() {
        console.log("shown", $(this).prop('id'));
    }).on('show.bs.collapse', function() {
        console.log("show", $(this).prop('id'));

        ate_id = parseInt($(this).prop('id').replace('panel_atencion_', ''));
        $.get('/_obtenerValoresVigentes/'+ate_id, function(data){
            console.log('/_obtenerValoresVigentes/'+ate_id, data);
            data = JSON.parse(data);
            console.log('data', data);
            var campos_estado_vigente = '';
            campos_estado_vigente += '<table style="width:400px;" class="table table-striped table-condensed table-hover"><tbody>';
            data.forEach(function(campo){
                var valor_detallado = (campo['valor_detallado']  == null) ? '' : campo['valor_detallado'];
                console.log('CAMPO', campo);

                if (campo['nodo']) {
                    var nod_id = campo['valor'];
                    valor_detallado = '<a href="#" onclick="p_abrir_detalle_nodo('+nod_id+');return false;">'+valor_detallado+'</a>';
                }
                campos_estado_vigente += ''
                    + '<tr>'
                    + '<th style="width:50%;text-align:right;">' + campo['etiqueta'] + ':</th>'
                    + '<td style="text-align:center;">' + valor_detallado + '</td>'
                    + '</tr>'
                    ;
            });
            campos_estado_vigente += '</tbody></table>';
            $('#campos_estado_vigente_'+ate_id).html(campos_estado_vigente);

        });
    });

    $('.combo-select2').select2({
        language: "es"
        ,width: '100%'
    });
    $('textarea').each(function(){
         CKEDITOR.replace(this);
    });
    $('.datetimepicker').datetimepicker({
        locale: 'es',
        format: 'YYYY-MM-DD'
    });
    var hash = window.location.hash.substr(1);

    console.log('HASH:', "["+hash+"]");
    if (hash != '') {
        //hace el scroll hasta el elemento:
        var $anchor = $(':target'),
            fixedElementHeight = 50;

        if ($anchor.length > 0) {

            $('html, body')
                .stop()
                .animate({
                scrollTop: $anchor.offset().top - fixedElementHeight
            }, 200);
        }

        // abre el acordeon adecuado:
        ate_id = parseInt(hash.replace('atencion_', ''));
        console.log('ate_id', ate_id);
        $('#collapse_' + ate_id).collapse('show');
    }

    $("#modal").on("shown.bs.modal", function () {
        //google.maps.event.trigger(map, "resize");
        $("#modal").off("shown.bs.modal");
    });
});

function p_abrir_detalle_nodo_desde_historial(ate_id, nod_id){
    $('#modal_historial').modal('hide');
    $('#modal_historial').on('hidden.bs.modal', function () {

        $('#modal_historial').off('hidden.bs.modal');
        $('#modal_detalle_nodo').on('hidden.bs.modal', function () {
            $('#modal_historial').modal('show');
            $('#modal_detalle_nodo').off('hidden.bs.modal');
        });
        p_abrir_detalle_nodo(nod_id);
    });
}

function p_abrir_detalle_nodo(nod_id){
    console.log('En p_abrir_detalle_nodo', nod_id);
    $.get('/_obtenerNodo/' + nod_id, function(data){
        console.log('/_obtenerNodo/' + nod_id, data);
        data = JSON.parse(data);
        console.log('data:', data);
        if (data) {
            var nodo = data[0];
            var contenido = '';

            contenido += '<>';

            //$('#detalle_nodo_contenido').html(contenido);
            //var titulo = nodo['nod_codigo'] + ': ' + nodo['nod_descripcion'] + ' ('+nodo['ubi_direccion']+')'
            //var titulo = nodo['nod_codigo'];
            //var titulo = 'de atención ' + nodo['ate_secuencial'] +'. '+(nodo['ate_codigo'] == null ? '(sin ID)' : nodo['ate_codigo']);
            //var titulo = 'de servicio ' + nodo['ate_secuencial'] +' '+(nodo['ate_codigo'] == null ? '(sin ID)' : nodo['ate_codigo']) + ', punto ' + nodo['nod_codigo'];
            var titulo = '';
            if (false && nodo['nod_no_diferencia_puntos'] == 1 && nodo['nod_atencion'] != nodo['nod_atencion_referenciada']) {
                titulo = 'Servicio activo '+ nodo['ate_secuencial'] +' '+ nodo['ate_codigo'];
            } else if (nodo['nod_no_diferencia_puntos'] == 0 && nodo['nod_atencion'] != nodo['nod_atencion_referenciada']) {
                titulo = 'Servicio activo '+ nodo['ate_secuencial'] +' '+ nodo['ate_codigo']  + ', punto ' + nodo['nod_codigo'];
            } else {
                titulo = 'Punto ' + nodo['nod_codigo'];
            }

            if (false && nodo['nod_no_diferencia_puntos'] == 1 && nodo['nod_atencion'] != nodo['nod_atencion_referenciada']) {
                var url = '/proceso/0/' + nodo['ate_codigo'] + '#atencion_' + nodo['ate_secuencial'];
                var win = window.open(url, '_blank');
                win.focus();
            } else {
                $('#detalle_nodo_titulo').text(titulo);

                $('#detalle_nodo_codigo').text(nodo['nod_codigo']);
                $('#detalle_nodo_descripcion').text(nodo['nod_descripcion']);

                $('#detalle_nodo_tipo_ultima_milla').text(nodo['tum_nombre']);
                $('#detalle_nodo_responsable_ultima_milla').text(nodo['nod_responsable_ultima_milla']);

                //$('#detalle_nodo_costo_instalacion_proveedor').text(nodo['nod_costo_instalacion_proveedor']);
                //$('#detalle_nodo_costo_instalacion_cliente').text(nodo['nod_costo_instalacion_cliente']);

                $('#detalle_nodo_distancia').text(nodo['nod_distancia']);
                $('#detalle_nodo_fecha_termino').text(nodo['fecha_termino']);

                $('#detalle_nodo_creado_por').text(nodo['usuario']);
                $('#detalle_nodo_creado').text(nodo['fecha_creacion']);

                $('#detalle_nodo_provincia').text(nodo['prv_nombre']);
                $('#detalle_nodo_canton').text(nodo['can_nombre']);
                $('#detalle_nodo_parroquia').text(nodo['par_nombre']);
                $('#detalle_nodo_ciudad').text(nodo['ciu_nombre']);

                $('#detalle_nodo_direccion').text(nodo['ubi_direccion']);
                $('#detalle_nodo_sector').text(nodo['ubi_sector'] == 'null' ? '' : nodo['ubi_sector']);
                $('#detalle_nodo_longitud').text(nodo['ubi_longitud']);
                $('#detalle_nodo_latitud').text(nodo['ubi_latitud']);

                $('#modal_detalle_nodo').modal('show');
            }
        }
    });
}

function p_enviar_busqueda(target) {
    var busqueda = $('#busqueda_query').val();
    $(target).prop('action', '/proceso/0/' + busqueda);
}

function p_toggle_historico(ate_id, ate_secuencial){
    $('#tabla_historico_' + ate_id).toggle();
    $.get('/_obtenerValoresHistoricos/' + ate_id, function(data){
        console.log('/_obtenerValoresHistoricos/' + ate_id, data);
        data = JSON.parse(data);
        console.log('data', data);
        if (data && data.length > 0) {
            var contenido = '';
            contenido += '' +
                '<table id="tabla_dinamica_historial" class="table">' +
                '<thead><tr>'+
                '<th>Fecha</th>'+
                '<th>Usuario</th>'+
                '<th>Estado</th>'+
                '<th>Destinatario</th>'+
                '<th>Campo</th>'+
                '<th>Valor</th>'+
                '</tr></thead>'+
                '<tbody>'+
                '';
            data.forEach(function(d){
                var label = [d['cae_texto']].join('');
                var dato = '';
                if (d['nodo']) {
                    var nod_id = d['vae_nodo'];
                    dato += ''+
                        '<a href="#" onclick="p_abrir_detalle_nodo_desde_historial('+ate_id+','+nod_id+');return false;">'+d['nodo']+'</a>'+
                        '';
                } else {
                    dato = [d['valor'], d['ciudad']].join('') ;
                }
                contenido += '' +
                    '<tr>'+
                    '<td style="text-align:center;">'+d['fecha']+'</td>'+
                    '<td style="text-align:center;">'+d['usuario']+'</td>'+
                    '<td style="text-align:center;">'+d['esa_nombre']+'</td>'+
                    '<td style="text-align:center;">'+d['des_nombre']+'</td>'+
                    '<td style="text-align:center;">'+label+'</td>'+
                    '<td style="text-align:center;">'+dato+'</td>'+
                    '</tr>'+
                    '';
                console.log(d['vae_creado']);
            });
            contenido += '' +
                '</tbody>' +
                '</table>' +
                '';

            $('#contenido_historial').html(contenido);
            $('#historial_titulo').text(ate_secuencial);

            $('#tabla_dinamica_historial').DataTable({ 
                language: {
                    "sProcessing":     "Procesando...",
                    "sLengthMenu":     "Mostrar _MENU_ registros",
                    "sZeroRecords":    "No se encontraron resultados",
                    "sEmptyTable":     "Ningún dato disponible en esta tabla",
                    "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                    "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
                    "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
                    "sInfoPostFix":    "",
                    "sSearch":         "Buscar:",
                    "sUrl":            "",
                    "sInfoThousands":  ",",
                    "sLoadingRecords": "Cargando...",
                    "oPaginate": {
                        "sFirst":    "Primero",
                        "sLast":     "Último",
                        "sNext":     "Siguiente",
                        "sPrevious": "Anterior"
                    },
                    "oAria": {
                        "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                        "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                    },
                },
                "order": [[ 0, "desc" ]]
            });
            $('#modal_historial').modal('show');
        }
    });
}

function p_inicializar_autocompletar(id){
    //$('#campo_extra_typeahead_'+id).typeahead({
    $('.typeahead-ciudad').typeahead({
        source:function(query, process){
            $.get('/_listarCiudades/' + query, function(data){
                console.log(data);
                data = JSON.parse(data);
                process(data.lista);
            });
        },
        displayField:'name',
        valueField:'id',
        highlighter:function(name){
            var ficha = '';
            ficha +='<div>';
            ficha +='<h4>'+name+'</h4>';
            ficha +='</div>';
            return ficha;
        },
        updater:function(item){
            var id = $(this.$element[0]).prop('id').split('_').pop();

            console.log('typeahead ID:' , id);

            $('#campo_extra_'+id).val(item.id);
            $('#campo_extra_detalle_valor_'+id).text(item.name);
            $('#campo_extra_grupo_'+id).hide();
            $('#campo_extra_detalle_'+id).show();
            return item.name;
        }
    });
    $('.typeahead-servicio-activo').each(function() {
        var $this = $(this);
        $this.typeahead({
            source:function(query, process){
                var ate_id = $('#ate_id').val();
                var cae_id = $this.attr('id').replace('campo_extra_typeahead_sa_', '');
                $.get('/_listarNodosServiciosActivos/' + ate_id + '/' + cae_id + '/' + query, function(data){
                    console.log(data);
                    data = JSON.parse(data);
                    process(data.lista);
                });
            },
            displayField:'name',
            valueField:'id',
            highlighter:function(name){
                var ficha = '';
                ficha +='<div>';
                ficha +='<h4>'+name+'</h4>';
                ficha +='</div>';
                return ficha;
            },
            updater:function(item){
                var id = $(this.$element[0]).prop('id').split('_').pop();

                console.log('typeahead ID:' , id);

                $('#campo_extra_'+id).val(item.id);
                $('#campo_extra_detalle_valor_'+id).text(item.name);
                $('#campo_extra_grupo_'+id).hide();
                $('#campo_extra_detalle_'+id).show();
                return item.name;
            }
        });
    });
    $('.typeahead-nodo').typeahead({
        source:function(query, process){
            $.get('/_listarNodos/' + query, function(data){
                console.log(data);
                data = JSON.parse(data);
                process(data.lista);
            });
        },
        displayField:'name',
        valueField:'id',
        highlighter:function(name){
            var ficha = '';
            ficha +='<div>';
            ficha +='<h4>'+name+'</h4>';
            ficha +='</div>';
            return ficha;
        },
        updater:function(item){
            var id = $(this.$element[0]).prop('id').split('_').pop();

            console.log('typeahead ID:' , id);

            $('#campo_extra_'+id).val(item.id);
            $('#campo_extra_detalle_valor_'+id).text(item.name);
            $('#campo_extra_grupo_'+id).hide();
            $('#campo_extra_detalle_'+id).show();
            return item.name;
        }
    });
}


function p_completar_nodo() {
    var cae_id = $('#nod_completo_cae_id').val();
    var nod_id = $('#nod_completo_id').val();
    console.log('en p_completar_nodo', nod_id);

    if (p_validar($('#formulario_nodo_completo'))) {
        var dataset = $('#formulario_nodo_completo').serialize();
        console.log('dataset: ', dataset);

        $.post('/_completarNodo', dataset, function(data){
            console.log('Respuesta /_completarNodo:', data);
            data = JSON.parse(data);
            console.log('data:', data);
            if (data) {
                var nod_id = $('#nod_completo_id').val();
                $('#campo_extra_' + cae_id).val(nod_id);
                $('#boton_nodo_completo_' + cae_id).removeClass('btn-warning');
                $('#boton_nodo_completo_' + cae_id).addClass('btn-success');

                $('#modal_nodo_completo').modal('hide');
                $('#modal_nodo_completo').on('hidden.bs.modal', function () {
                    data = data[0];
                    //direccion = $('#nod_direccion').val();
                    //var item = {id:data['nod_id'], name:data['nod_codigo'] + ': ' + data['nod_descripcion'] + ' ('+direccion+')'};
                    //$('#campo_extra_'+id).val(item.id);
                    //$('#campo_extra_detalle_valor_'+id).text(item.name);
                    //$('#campo_extra_grupo_'+id).hide();
                    //$('#campo_extra_detalle_'+id).show();

                    $('#modal').modal('show');
                    $('#modal_nodo_completo').off('hidden.bs.modal');
                });
            } else {
                alert('No se ha podido completar la información del punto, inténtelo más tarde.');
            }
        });
    }
}
function p_crear_nodo() {
    var id = $('#nod_cae_id').val();
    console.log('en p_crear_nodo', id);

    if (p_validar($('#formulario_nodo'))) {
        var dataset = $('#formulario_nodo').serialize();

        $.post('/_crearNodo', dataset, function(data){
            console.log('Respuesta /_crearNodo:', data);
            data = JSON.parse(data);
            console.log('data:', data);

            $('#modal_nodo').modal('hide');
            $('#modal_nodo').on('hidden.bs.modal', function () {
                data = data[0];
                direccion = $('#nod_direccion').val();
                //var item = {id:data['nod_id'], name:data['nod_codigo'] + ': ' + data['nod_descripcion'] + ' ('+direccion+')'};
                var item = {id:data['nod_id'], name:data['nod_codigo']};
                $('#campo_extra_'+id).val(item.id);
                $('#campo_extra_detalle_valor_'+id).text(item.name);
                $('#campo_extra_grupo_'+id).hide();
                $('#campo_extra_detalle_'+id).show();

                $('#modal').modal('show');
                $('#modal_nodo').off('hidden.bs.modal');
            });
        });
    }
}

function p_abrir_nodo_completo(id) {

    console.log('en p_abrir_nodo_completo', id);
    var ate_id = $('#ate_id').val();

    $.get('/_obtenerNodoDeAtencion/' + id + '/' + ate_id, function(data){
        console.log('Obteniendo detalle de atencion ate_id:' + ate_id + ', campo cae_id:'+ id, data);
        data = JSON.parse(data);
        console.log('data:', data);
        if (data) {
            data = data[0];
            $('#nod_completo_codigo').text(data['nod_codigo']);
            $('#nod_completo_descripcion').text(data['nod_descripcion']);
            $('#modal').modal('hide');
            $('#modal').on('hidden.bs.modal', function () {
                $('#modal_nodo_completo').find(':input').each(function() {
                    switch(this.type) {
                    case 'password':
                    case 'text':
                    case 'textarea':
                    case 'file':
                    case 'select-one':
                    case 'select-multiple':
                    case 'date':
                    case 'number':
                    case 'tel':
                    case 'email':
                    case 'hidden':
                        $(this).val('');
                        break;
                    case 'checkbox':
                    case 'radio':
                        this.checked = false;
                        break;
                    }
                });
                $('#modal_nodo_completo').find('.panel-collapse.in').each(function() {
                    $(this).collapse('hide');
                });
                $('#nod_completo_cae_id').val(id);
                $('#nod_completo_id').val(data['nod_id']);
                console.log('CAMPO #nod_completo_id:', $('#nod_completo_id').val());

                //$('#nod_costo_instalacion_proveedor').val(data['nod_costo_instalacion_proveedor']);
                //$('#nod_costo_instalacion_cliente').val(data['nod_costo_instalacion_cliente']);
                $('#nod_tipo_ultima_milla').val(data['nod_tipo_ultima_milla']);
                $('#nod_tipo_ultima_milla').trigger('change');
                $('#nod_responsable_ultima_milla').val(data['nod_responsable_ultima_milla']);

                $('#nod_distancia').val(data['nod_distancia']);

                $('#nod_fecha_termino').val(data['nod_fecha_termino']);
                $('#nod_nodo').val(data['nod_nodo']);

                $('#modal_nodo_completo').modal('show');
                $('#modal').off('hidden.bs.modal');
            });
        } else {
            alert('No hay punto relacionado');
        }
    });
}

function p_abrir_nuevo_nodo(id){
    console.log('en p_abrir_nuevo_nodo', id);
    $('#modal').modal('hide');
    $('#modal').on('hidden.bs.modal', function () {
        $('#modal_nodo').find(':input').each(function() {
            switch(this.type) {
            case 'password':
            case 'text':
            case 'textarea':
            case 'file':
            case 'select-one':
            case 'select-multiple':
            case 'date':
            case 'number':
            case 'tel':
            case 'email':
            case 'hidden':
                $(this).val('');
                break;
            case 'checkbox':
            case 'radio':
                this.checked = false;
                break;
            }
        });
        $('#modal_nodo').find('.panel-collapse.in').each(function() {
            $(this).collapse('hide');
        });
        $('#nod_cae_id').val(id);
        $('#nod_atencion').val($('#ate_id').val());
        $('#modal_nodo').modal('show');
        $('#modal').off('hidden.bs.modal');
    });
}

function p_quitar_opcion_typeahead(id){
    console.log('en p_quitar_opcion_typeahead', id);
            $('#campo_extra_'+id).val('');
            $('#campo_extra_typeahead_'+id).typeahead('val', '');
            $('#campo_extra_typeahead_'+id).val('');
            $('#campo_extra_typeahead_sa_'+id).typeahead('val', '');
            $('#campo_extra_typeahead_sa_'+id).val('');
            $('#campo_extra_detalle_valor_'+id).text('');
            $('#campo_extra_grupo_'+id).show();
            $('#campo_extra_detalle_'+id).hide();
}


var provincias = <?=json_encode($provincias)?>;

var destinatarios = <?=json_encode($destinatarios)?>;
var marker = null;

function initMap() {
    latitud = 2.4403747; //-0.7323406; 
    longitud = -85.1904362; //-82.7581854; 
    var uluru = {lat: latitud, lng: longitud};
    var map = new google.maps.Map(document.getElementById('map'), {
        zoom: 6,
        center: uluru
    });
    google.maps.event.addListener(map, 'click', function(e) {
        console.log(e);
        if (marker != null) {
            marker.setMap(null);
        }

        marker = new google.maps.Marker({
            position: {lat:e.latLng.lat(), lng:e.latLng.lng()},
            map: map
        });
    });
   /* 
    var marker = new google.maps.Marker({
        position: uluru,
        map: map
    });
    */
}

var formulario_de_transicion = [];
function p_validar_transicion(target, tea_id, ate_id, estado_siguiente_id, abrir_ventana_campos){
    formulario_de_transicion['target'] = target;
    formulario_de_transicion['tea_id'] = tea_id;
    formulario_de_transicion['ate_id'] = ate_id;
    formulario_de_transicion['estado_siguiente_id'] = estado_siguiente_id;

    abrir_ventana_campos = typeof(abrir_ventana_campos) === 'undefined' ? true : false;
    console.log('En p_validar_transicion', tea_id, ate_id);

    var traer_campos_extra = 1;
    $.get('/_obtenerCampos/' + tea_id + '/' + ate_id + '/' + traer_campos_extra, function(data){
        console.log(data);
        data = JSON.parse(data);
        console.log(data);

        var completo = true;
        if (data) {
            var campos = [];
            data.forEach(function(d){
                var id = d['cae_id'];
                campos[id] = d;
                campos[id]['padre'] = null;
                campos[id]['hijos'] = [];
            });
            campos.forEach(function(campo){
                var id = campo['cae_id'];
                var padre = campo['cae_padre'];
                if (typeof(campos[padre]) != 'undefined') {
                    campos[padre]['hijos'][id] = campos[id];
                    campos[id]['padre'] = campos[padre];
                }
            });
            campos.forEach(function(campo){
                if (campo['hijos'].length == 0 && (campo['valor'] == null || campo['valor'].trim() == '')) {
                    completo = false;
                    console.log('NO ESTA COMPLETO POR:', campo['hijos'].length, campo['valor'], campo);
                }
            });
            $('#boton_modificar_datos_recopilados').show();
        } else {
            console.log('No hay campos');
            $('#boton_modificar_datos_recopilados').hide();
        }

        if (completo){
            //target.submit();
            p_abrir_confirmacion(target, tea_id, ate_id, estado_siguiente_id);
            //console.log('submit');
        } else {
            //alert('Faltan de completar campos.');
            console.log('tea_id:',tea_id, 'ate_id:',ate_id);

            if (abrir_ventana_campos) {
                p_abrir(tea_id, ate_id);
            }
        }
    });
    return false;
}

function p_abrir_confirmacion(target, tea_id, ate_id, estado_siguiente_id) {
    console.log('p_abrir_confirmacion');
    //$('#formulario_titulo_hacia').text(fila_titulo_y);

    $('#modal_confirmacion').find(':input').each(function() {
        switch(this.type) {
        case 'password':
        case 'text':
        case 'textarea':
        case 'file':
        case 'select-one':
        case 'select-multiple':
        case 'date':
        case 'number':
        case 'tel':
        case 'email':
        case 'hidden':
            $(this).val('');
            break;
        case 'checkbox':
        case 'radio':
            this.checked = false;
            break;
        }
    });
    $('#modal_confirmacion').find('.panel-collapse.in').each(function() {
        $(this).collapse('hide');
    });
    for ( instance in CKEDITOR.instances ) {
        CKEDITOR.instances[instance].setData('');
    }
    destinatarios.forEach(function(destinatario){
        $('#adjuntos_lista_'+destinatario).html('');
        $('#panel_accion_'+destinatario).hide();
    });

    var dataset = $(target).serialize();
    $.post('/_calcularPreTransicion/' + ate_id, dataset, function(data){
        console.log('Respuesta _calcularPreTransicion', data);
        data = JSON.parse(data);
        console.log('data', data);
        if (data != '' && data != null && typeof(data['ERROR']) === 'undefined') {
        $('#ate_id_accion').val(ate_id);
        $('#estado_siguiente_id_accion').val(estado_siguiente_id);

        var emails = data.emails;
        var plantillas = data.plantillas;
        Array.from(data.contenido).forEach(function(contenido){
            console.log('CONTENIDO:', contenido);
            var destinatario = contenido.destinatario;
            var pla_id = contenido.pla_id;
            var plantilla = plantillas[pla_id];
            var tea_id = contenido.tea_id;

            $('#panel_accion_'+destinatario).show();

            $('#tea_id_accion_'+destinatario).val(tea_id);
            $('#email_'+destinatario).val(emails[destinatario]);
            $('#cc_'+destinatario).val(emails['cc']);
            console.log('CC', '#cc_'+destinatario, emails['cc'], emails);
            CKEDITOR.instances['mensaje_'+destinatario].setData(plantilla.textos[0]);
            $('#asunto_'+destinatario).val(plantilla.textos[1]);
            console.log('PLANTILLA:', plantilla);
            if (plantilla.xls_generado) {
                var icono = '<span class="glyphicon glyphicon-download" aria-hidden="true"></span> ';
                Array.from(plantilla.adjuntos_generados).forEach(function(archivo){
                    console.log('ARCHIVO:', archivo);

                    var hidden = '<input type="hidden" name="adjunto_' + destinatario + '[]" value="' + archivo + '">';
                    var boton_borrar = '<button class="btn btn-danger" onclick="p_quitar_adjunto(this)"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></button>';
                    var nombre_archivo = archivo.split('/').pop();
                    //$('#adjuntos_lista_'+destinatario).append(hidden + '<div><a class="btn btn-default" href="/' + plantilla.textos[2] + '">' + icono + plantilla.textos[2] + '</a></div>');
                    $('#adjuntos_lista_'+destinatario).append(hidden + '<div><a class="btn btn-default" href="/' + archivo + '">' + icono + nombre_archivo + '</a> '+boton_borrar+'</div>');
                });
            }
        });
        $('#modal_confirmacion').modal('show');
        } else {
            console.log('No se puede procesar respuesta: ', data);
            alert('Sin respuesta del servidor, intentelo nuevamente en unos minutos.');
        }
    });
}

function p_quitar_adjunto (target) {
    $(target).parent().remove();
}

function p_cargar_adjunto(target, destinatario) {
    console.log('En p_cargar_adjunto', target.files[0]);
    var formData = new FormData();
    var ate_id = $('#ate_id_accion').val();
    formData.append('adjunto', target.files[0]);
    formData.append('ate_id', ate_id);

    $.ajax({
           url : '/_cargarAdjunto/',
           type : 'POST',
           data : formData,
           processData: false,  // tell jQuery not to process the data
           contentType: false,  // tell jQuery not to set contentType
           success : function(data) {
               console.log('/_cargarAdjunto', data);
               data = JSON.parse(data);
               if (data && data['message'] == 'OK') {
                   console.log('OK');
                   var archivo = data['nombre'];
                   var ruta = data['ruta'];

                   var icono = '<span class="glyphicon glyphicon-download" aria-hidden="true"></span> ';
                   var hidden = '<input type="hidden" name="adjunto_' + destinatario + '[]" value="' + ruta + '">';
                   var boton_borrar = '<button class="btn btn-danger" onclick="p_quitar_adjunto(this)"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></button>';
                   //$('#adjuntos_lista_'+destinatario).append(hidden + '<div><a class="btn btn-default" href="/' + plantilla.textos[2] + '">' + icono + plantilla.textos[2] + '</a></div>');
                   $('#adjuntos_lista_'+destinatario).append(hidden + '<div><a class="btn btn-default" href="/' + ruta + '">' + icono + archivo + '</a> '+boton_borrar+'</div>');
                   $(target).val('');
               }
           }
    });
}


function p_abrir_campos_llenos() {
    console.log('En p_abrir_campos_llenos');
    var ate_id = $('#ate_id_accion').val();

    var tea_id_destinatario = null, tea_id = null;
    destinatarios.forEach(function(destinatario){
        tea_id_destinatario = $('#tea_id_accion_' + destinatario).val();
        console.log('Buscando valor en ', '#tea_id_accion_' + destinatario, $('#tea_id_accion_' + destinatario).val());
        if (tea_id_destinatario != '' && tea_id_destinatario != null && !isNaN(tea_id_destinatario)) {
        //if (Number.isInteger(tea_id_destinatario)) {
            tea_id = tea_id_destinatario;
            console.log('Encontrado tea_id:', '['+tea_id+']');
        }
    });
    if (tea_id != null) {
        console.log('tea_id:', tea_id,'ate_id:', ate_id);
        $('#modal_confirmacion').modal('hide');
        $('#modal_confirmacion').on('hidden.bs.modal', function () {
            p_abrir(tea_id, ate_id);
            $('#modal_confirmacion').off('hidden.bs.modal');
        });
    } else {
        console.log('No se puede abrir, no se encuentra el tea_id (NULL):', tea_id);
    }
}

function p_ejecutar_transicion(){
    console.log('p_ejecutar_transicion');
    for ( instance in CKEDITOR.instances ) {
        CKEDITOR.instances[instance].updateElement();
    }

    var dataset = $('#formulario_accion').serialize(); 
    console.log('dataset: ', dataset   );
    $.post('/_confirmarTransicion', dataset, function(data){

        console.log('_confirmarTransicion: ', data);
        data = JSON.parse(data);
        console.log(data);
        if (typeof(data['ERROR']) !== 'undefined') {
            alert (data['ERROR']);
        } else {
            $('#modal_confirmacion').modal('hide');
            location.reload();
        }
    })
}

var nodos_completos = [];
var nodos_codigos = [];
function p_abrir(tea_id, ate_id) {
    console.log('En p_abrir', tea_id, ate_id);

    console.log('abrir', tea_id, ate_id);
    var traer_campos_extra = 1;
    $.get('/_obtenerCampos/'+tea_id + '/'+ate_id + '/' + traer_campos_extra, function(data){
        console.log(data);
        data = JSON.parse(data);
        console.log(data);

        $('#campos').html("");
        $('#ate_id').val(ate_id);
        nodos_completos = [];
        nodos_codigos = [];
        if (data) {
            var campos = [];
            data.forEach(function(d){
                var id = d['cae_id'];
                campos[id] = d;
                campos[id]['padre'] = null;
                campos[id]['hijos'] = [];
            });
            campos.forEach(function(campo){
                var id = campo['cae_id'];
                var padre = campo['cae_padre'];
                if (typeof(campos[padre]) != 'undefined') {
                    campos[padre]['hijos'][id] = campos[id];
                    campos[id]['padre'] = campos[padre];
                }
            });

            fechas_enlazadas = [];
            $('#campos').append(p_desplegar_campos(campos));
            //inicializar checkbox-toggle:
            $('.checkbox-toggle').bootstrapToggle({
                on: 'Existe servicio activo'
                ,off: 'No existe servicio activo'
                ,width: "200"

            });
            $('.checkbox-toggle').change(function() {
                var cae_id = $(this).attr('id').replace('cambiar_nodo_existe_', '');
                p_cambiar_nodo_existe(this, cae_id);
            });
            //inicializar codigos de nodos (login):
            nodos_codigos.forEach(function(campo){
                var cae_id = campo['cae_id'];
                $.get('/_obtenerNodoDeAtencion/' + cae_id + '/' + ate_id, function(nodo_completo){
                    console.log('/_obtenerNodoDeAtencion/' + cae_id + '/' + ate_id, nodo_completo);
                    nodo_completo = JSON.parse(nodo_completo);
                    if (nodo_completo) {
                        nodo_completo = nodo_completo[0];
                        console.log('nodo_completo', nodo_completo);


                        $('#campo_extra_grupo_'+cae_id).removeClass('has-warning');
                        if (nodo_completo) {
                            $('#campo_extra_grupo_'+cae_id).show();
                            if (nodo_completo['nod_atencion_referenciada'] != null && nodo_completo['nod_atencion_referenciada'] != nodo_completo['nod_atencion']) {
                                $('#campo_extra_grupo_'+cae_id).hide();
                                $('#campo_extra_'+cae_id).val(nodo_completo['nod_codigo']);
                            } else if (false && nodo_completo['nod_no_diferencia_puntos'] == 1) {
                                $('#campo_extra_grupo_'+cae_id).hide();
                                $('#campo_extra_'+cae_id).val(nodo_completo['nod_codigo']);
                            } else {
                                $('#campo_extra_'+cae_id).val(nodo_completo['nod_codigo']).blur();
                            }
                        }
                    }
                });
            });
            //inicializar nodos completos:
            nodos_completos.forEach(function(campo){
                var cae_id = campo['cae_id'];
                $.get('/_obtenerNodoDeAtencion/' + cae_id + '/' + ate_id, function(nodo_completo){
                    console.log('/_obtenerNodoDeAtencion/' + cae_id + '/' + ate_id, nodo_completo);
                    nodo_completo = JSON.parse(nodo_completo);
                    if (nodo_completo) {
                        nodo_completo = nodo_completo[0];
                        console.log('nodo_completo', nodo_completo);
                        $('#boton_nodo_completo_'+cae_id).removeClass('btn-default');
                        if (nodo_completo && nodo_completo['nod_fecha_termino'] !== null && nodo_completo['nod_nodo'] !== null && nodo_completo['nod_tipo_ultima_milla'] !== null && nodo_completo['nod_responsable_ultima_milla'] !== null && nodo_completo['nod_distancia'] !== null && nodo_completo['nod_id'] != null) {
                            console.log('el nodo parece completo...', '#campo_extra_'+cae_id, nodo_completo['nod_id']);
                            $('#campo_extra_'+cae_id).val(nodo_completo['nod_id']);
                            //if (nodo_completo['nod_atencion'] == $('#ate_id').val()) {
                            if (nodo_completo['nod_atencion_referenciada'] == $('#ate_id').val()) {
                                $('#boton_nodo_completo_'+cae_id).removeClass('btn-warning');
                                $('#boton_nodo_completo_'+cae_id).addClass('btn-success');
                            } else {
                                $('#boton_nodo_completo_'+cae_id).hide();
                            }

                        } else {
                            $('#campo_extra_'+cae_id).val('');
                            $('#boton_nodo_completo_'+cae_id).removeClass('btn-success');
                            $('#boton_nodo_completo_'+cae_id).addClass('btn-warning');
                        }
                    }
                });
            });
            //configurar fechas enlazadas:
            if (fechas_enlazadas.length > 0) {
                var count = 0;
                var cae_id_anterior = '';
                var cae_id_actual = '';
                var cae_id_primera = '';
                fechas_enlazadas.forEach(function(cae_id){
                    cae_id_actual = cae_id;
                    if (count == 0) {
                        $('#campo_extra_' + cae_id_actual).datetimepicker({
                            locale: 'es',
                            format: 'YYYY-MM-DD'
                        });
                        cae_id_primera = cae_id;
                    } else {
                        $('#campo_extra_' + cae_id_actual).datetimepicker({
                            useCurrent: false, //Important! See issue #1075
                            locale: 'es',
                            format: 'YYYY-MM-DD'
                        });
                        $('#campo_extra_' + cae_id_anterior).on("dp.change", function (e) {
                            (function(cae_id){
                                $('#campo_extra_' + cae_id).data("DateTimePicker").minDate(e.date);
                                console.log('dp.change->#campo_extra_' + cae_id, 'minDate', e.date );
                            })(cae_id);
                        });
                    }
                    cae_id_anterior = cae_id;
                    count++;
                });
                $('#campo_extra_' + cae_id_actual).on("dp.change", function (e) {
                    var cae_id = cae_id_primera;
                    $('#campo_extra_' + cae_id).data("DateTimePicker").maxDate(e.date);
                    console.log('dp.change->#campo_extra_' + cae_id, 'maxDate', e.date );
                });
            }
            $('.datetimepicker').datetimepicker({
            locale: 'es',
                format: 'YYYY-MM-DD'
            });
            $('.combo-select2').select2({
                language: "es"
                ,width: '100%'
            });
            p_inicializar_autocompletar();
            $('textarea').each(function(){
                try {
                    CKEDITOR.replace(this);
                } catch(e) {
                    console.log('ERROR en CKEDITOR:', e);
                }
            });

            $('#modal').modal('show');
        } else {
            alert('No hay campos asociados');
        }
    });
}

function p_cargar_canton(cae_id) {
    console.log('En p_cargar_canton', cae_id);
}

var fechas_enlazadas;
function p_desplegar_campos(campos, padre_id) {
    var respuesta = '';
    padre_id = padre_id || null;
    console.log('En p_desplegar_campos: ', campos, padre_id) ;
    var col1 = (padre_id == null) ? 2 : 3;
    var col2 = (padre_id == null) ? 10 : 8;

    campos.sort(function(a, b){
        return a['cae_orden'] - b['cae_orden'];
    });

    campos.forEach(function(campo){
        var valor_historico = (campo['valor_historico'] == 'null' || campo['valor_historico'] == null) ? '' : campo['valor_historico'];
        var valor_por_defecto = (campo['valor_por_defecto'] == 'null' || campo['valor_por_defecto'] == null  || campo['valor_por_defecto'] == '') ? valor_historico : campo['valor_por_defecto'];
        //var valor_por_defecto = valor_historico;
        var valor = (campo['valor'] == 'null' || campo['valor'] == null || campo['valor'] == '') ? valor_por_defecto : campo['valor'];

        var menor_que = (campo['menor_que'] == 'null' || campo['menor_que'] == null || campo['menor_que'] == '') ? null : campo['menor_que'];
        var mayor_que = (campo['mayor_que'] == 'null' || campo['mayor_que'] == null || campo['mayor_que'] == '') ? null : campo['mayor_que'];

        var contenido = '';
        console.log('CAMPO:', campo);
        //if (padre_id == null || padre_id == campo['cae_padre']) {
        if (padre_id == campo['cae_padre']) {
            if (campo['hijos'].length == 0 ) {
                //if (campo['padre'] != null) {
                if (campo['tipo_dato'] == 'fecha') {
                    contenido += ''+
                        '<div class="form-group">'+
                            '<label for="campo_extra_'+campo['cae_id']+'" class="col-sm-' + col1 + ' control-label">'+campo['cae_texto']+ ':</label>' +
                            '<div class="col-sm-' + col2 + '">' +
                                '<div class="input-group date" id="datetimepicker2-'+campo['cae_id']+'">'+
                                    '<input type="text" class="form-control datetimepicker" name="campo_extra_'+campo['cae_id']+'" id="campo_extra_'+campo['cae_id']+'" value="'+valor+'" />'+
                                    '<span class="input-group-addon">'+
                                        '<span class="glyphicon glyphicon-calendar"></span>'+
                                    '</span>'+
                                '</div>'+
                            '</div>'+
                        '</div>'+
                        '';
                } else if (campo['tipo_dato'] == 'fecha_enlazada') {
                    fechas_enlazadas[fechas_enlazadas.length] = campo['cae_id'];
                    contenido += ''+
        '<div class="form-group">'+
        '<label for="campo_extra_'+campo['cae_id']+'" class="col-sm-' + col1 + ' control-label">'+campo['cae_texto']+ ':</label>' +
        '<div class="col-sm-' + col2 + '">' +
        '<div class="input-group date" id="datetimepicker2-'+campo['cae_id']+'">'+
        '<input type="text" class="form-control" name="campo_extra_'+campo['cae_id']+'" id="campo_extra_'+campo['cae_id']+'" value="'+valor+'" />'+
        '<span class="input-group-addon">'+
        '<span class="glyphicon glyphicon-calendar"></span>'+
        '</span>'+
        '</div>'+
        '</div>'+
        '</div>'+
        '';


                } else if (campo['tipo_dato'] == 'numero') {
                    var validacion_mayor_que = (mayor_que == null) ? '' : 'max="'+mayor_que+'"'; 
                    var validacion_menor_que = (menor_que == null) ? 'min=""' : 'min="'+menor_que+'"'; 

                    var validacion_html5 = campo['cae_validacion'];
                    var funcion_validar = 'p_validar(this)';

                    if (campo['cae_validacion'] !== null && campo['cae_validacion'].indexOf('capacidad')  !== -1) {
                        //el numero es capacidad
                        //validacion_html5 = 'required';
                        //funcion_validar = 'p_validar_capacidad(this, \'' + campo['cae_validacion'] + '\')';
                    }

                    valor = ((valor == null || valor == '') && (campo['cae_codigo'] !== null && campo['cae_codigo'].indexOf('COSTO_INSTALACION')  !== -1)) ? '0' : valor;
                    contenido += ''+
                        '<div class="form-group">' +
                        '<label for="campo_extra_'+campo['cae_id']+'" class="col-sm-' + col1 + ' control-label">'+campo['cae_texto']+ ':</label>' +
                        '<div class="col-sm-' + col2 + '">' +
                        '<input type="number" ' + validacion_html5 + ' '+validacion_mayor_que+' '+validacion_menor_que+' class="form-control" id="campo_extra_'+campo['cae_id']+'" name="campo_extra_'+campo['cae_id']+'" placeholder="" value="' + valor + '" onblur="' + funcion_validar + '">' +
                        '</div>' +
                        '</div>'+
                        '';

                } else if (campo['tipo_dato'] == 'codigo_atencion') {

                    contenido += ''+
                        '<div class="form-group" id="campo_extra_grupo_'+campo['cae_id']+'">' +
                        '<label for="campo_extra_typeahead_'+campo['cae_id']+'" class="col-sm-' + col1 + ' control-label">'+campo['cae_texto']+ ':</label>' +
                        '<div class="col-sm-' + col2 + '">' +
                        '<input type="text" class="form-control" required id="campo_extra_' + campo['cae_id'] + '" name="campo_extra_' + campo['cae_id'] + '" value="' + valor + '" onblur="p_validar_codigo_atencion(this)">' +
                        '</div>' +
                        '</div>'+

                        '';
                } else if (campo['tipo_dato'] == 'ciudad') {
                    var descripcion = '';
                    var grupo_style = '';
                    var detalle_style = 'style="display:none;"';

                    if (campo['ciudad']) {
                        descripcion = campo['ciudad'];
                        grupo_style = 'style="display:none;"';
                        detalle_style = '';
                    }

                    contenido += ''+
                        '<div class="form-group" '+grupo_style+' id="campo_extra_grupo_'+campo['cae_id']+'">' +
                        '<label for="campo_extra_typeahead_'+campo['cae_id']+'" class="col-sm-' + col1 + ' control-label">'+campo['cae_texto']+ ':</label>' +
                        '<div class="col-sm-' + col2 + '">' +
                        '<input type="hidden" id="campo_extra_' + campo['cae_id'] + '" name="campo_extra_' + campo['cae_id'] + '" value="' + valor + '">' +
                        '<input type="text" '+campo['cae_validacion']+' class="form-control typeahead-ciudad" id="campo_extra_typeahead_'+campo['cae_id']+'" xxxname="campo_extra_typeahead_'+campo['cae_id']+'" data-provide="typeahead" autocomplete="off" placeholder="Ingrese al menos 2 caracteres" value="' + valor + '" onblur="p_validar(this)">' +
                        '</div>' +
                        '</div>'+

                        '<div class="form-group" '+detalle_style+' id="campo_extra_detalle_'+campo['cae_id']+'">' +
                        '<label class="col-sm-' + col1 + ' control-label">'+campo['cae_texto']+ ':</label>' +
                        '<div class="col-sm-' + (col2 - 2) + '">' +
                        '<span id="campo_extra_detalle_valor_' + campo['cae_id'] + '">' + descripcion + '</span>' +
                        '</div>' +
    '<div class="col-sm-1">' +
    '<button type="button" class="btn btn-danger boton-quitar" id="campo_extra_quitar_'+campo['cae_id']+'" onclick="p_quitar_opcion_typeahead('+campo['cae_id']+')"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></button>' +
    '</div>' +
                        '</div>'+
                        '';
                } else if (campo['tipo_dato'] == 'nodo') {
                    var descripcion = '';
                    var grupo_style = '';
                    var detalle_style = 'style="display:none;"';

                    if (campo['nodo']) {
                        descripcion = campo['nodo'];
                        grupo_style = 'style="display:none;"';
                        detalle_style = '';
                    }

                    var contenido_nodo = '';
                    var punto = ''+
                        '<div class="col-sm-' + (col2 ) + '">' +
                            '<input type="text" '+campo['cae_validacion']+' class="form-control typeahead-nodo" id="campo_extra_typeahead_'+campo['cae_id']+'" xxxname="campo_extra_typeahead_'+campo['cae_id']+'" data-provide="typeahead" autocomplete="off" placeholder="Ingrese código del punto" value="' + valor + '" onblur="p_validar(this)">' +
                        '</div>' +
                        '<div class="col-sm-1">' +
                            '<button type="button" class="btn btn-success boton-nuevo" id="campo_extra_nuevo_'+campo['cae_id']+'" onclick="p_abrir_nuevo_nodo('+campo['cae_id']+')"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span></button>' +
                        '</div>' +
                        '';

                    if (campo['cae_validacion'] == 'concentrador' || campo['cae_validacion'] == 'extremo') {
                        var servicio_activo = ''+
                            '<div class="col-sm-' + (col2 ) + '">' +
                                '<input type="text" '+campo['cae_validacion']+' class="form-control typeahead-servicio-activo" id="campo_extra_typeahead_sa_'+campo['cae_id']+'" xxxname="campo_extra_typeahead_'+campo['cae_id']+'" data-provide="typeahead" autocomplete="off" placeholder="Ingrese ID del servicio activo" value="' + valor + '" onblur="p_validar(this)">' +
                            '</div>' +

                            '';

                        contenido_nodo = ''+
                            '<div class="checkbox">'+
                                '<label>'+
                                    '<input type="checkbox" class="checkbox-toggle" id="cambiar_nodo_existe_'+campo['cae_id']+'" >' +
                                    '<!-- Existe el servicio activo -->'+
                                '</label>'+
                            '</div>'+
                            '<div style="display:none;" id="nodo_existe_'+campo['cae_id']+'">'+
                                servicio_activo+
                            '</div>'+
                            '<div id="nodo_no_existe_'+campo['cae_id']+'">'+
                                punto +
                            '</div>'+
                            '';
                    } else {
                        contenido_nodo = punto;
                    }
                    contenido += ''+
                        '<div class="form-group" '+grupo_style+' id="campo_extra_grupo_'+campo['cae_id']+'">' +
                            '<label for="campo_extra_typeahead_'+campo['cae_id']+'" class="col-sm-' + col1 + ' control-label">'+campo['cae_texto']+ ':</label>' +
                            '<input type="hidden" id="campo_extra_' + campo['cae_id'] + '" name="campo_extra_' + campo['cae_id'] + '" value="' + valor + '">' +
                            '<div class="col-sm-' + col2 + '">'+
                                contenido_nodo +
                            '</div>'+
                        '</div>'+
                        '<div class="form-group" '+detalle_style+' id="campo_extra_detalle_'+campo['cae_id']+'">' +
                            '<label class="col-sm-' + col1 + ' control-label">'+campo['cae_texto']+ ':</label>' +
                            '<div class="col-sm-' + (col2 - 2) + '">' +
                                '<span id="campo_extra_detalle_valor_' + campo['cae_id'] + '">' + descripcion + '</span>' +
                            '</div>' +
                            '<div class="col-sm-1">' +
                                '<button type="button" class="btn btn-danger boton-quitar" id="campo_extra_quitar_'+campo['cae_id']+'" onclick="p_quitar_opcion_typeahead('+campo['cae_id']+')"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></button>' +
                            '</div>' +
                        '</div>'+
                        '';
                } else if (campo['tipo_dato'] == 'nodo_codigo') {
                    nodos_codigos.push(campo);

                    contenido += ''+
                        '<div class="form-group has-warning" id="campo_extra_grupo_'+campo['cae_id']+'">' +
                        '<label for="campo_extra_typeahead_'+campo['cae_id']+'" class="col-sm-' + col1 + ' control-label">'+campo['cae_texto']+ ':</label>' +
                        '<div class="col-sm-' + col2 + '">' +
                        '<input type="text" class="form-control" required id="campo_extra_' + campo['cae_id'] + '" name="campo_extra_' + campo['cae_id'] + '" value="' + valor + '" onblur="p_validar_nodo_codigo(this)">' +
                        '</div>' +
                        '</div>'+

                        '';
                } else if (campo['tipo_dato'] == 'nodo_completo') {
                    nodos_completos.push(campo);

                    contenido += ''+
                        '<div class="form-group" id="nodo_completo_grupo_'+campo['cae_id']+'">' +
                        '<div class="col-sm-' + col2 + '">' +
                        '<input type="number" required ' + campo['cae_validacion'] + ' style="display:none;" id="campo_extra_' + campo['cae_id'] + '" name="campo_extra_' + campo['cae_id'] + '" value="">' +
                        '<button class="btn btn-default" id="boton_nodo_completo_'+campo['cae_id']+'" onclick="p_abrir_nodo_completo(' + campo['cae_id'] + ');return false;" >Completar datos de punto</button>' +
                        '</div>' +
                        '</div>'+
                        '';
                } else {
                    contenido += '<div class="form-group">' +
                        '<label for="campo_extra_' + campo['cae_id'] + '" class="col-sm-' + col1 + ' control-label">' + campo['cae_texto'] + ':</label>' +
                        '<div class="col-sm-' + col2 + '">' +
                        '<input '+campo['cae_validacion']+' class="form-control" id="campo_extra_'+campo['cae_id']+'" name="campo_extra_'+campo['cae_id']+'" placeholder="" value="' + valor + '" onblur="p_validar(this)">' +
                        '</div>' +
                        '</div>';

                }
            } else if(campo['hijos'].length > 0) {

                var contenidohijos = p_desplegar_campos(campo['hijos'], campo['cae_id']);

                contenido += '<div class="panel panel-default"><div class="panel-heading"><strong>' + campo['cae_texto'] + '</strong></div><div class="panel-body">' + contenidohijos + '</div></div>';
                console.log('grupo', campo['cae_texto'], campo['cae_padre'], padre_id);

            }
        }
        //respuesta += ('<div class="form-group">' + contenido + '</div>');
        respuesta += contenido;
    });
    return respuesta;
}


function p_cambiar_nodo_existe(target, cae_id) {
    if ($(target).is(":checked")) {
        $('#nodo_existe_' + cae_id).show('fast');
        $('#nodo_no_existe_' + cae_id).hide('fast');
    } else {
        $('#nodo_existe_' + cae_id).hide('fast');
        $('#nodo_no_existe_' + cae_id).show('fast');
    }
}


function p_validar_capacidad(target, tipo_capacidad) {
    if (p_validar(target)) {
        var ate_id = $('#ate_id').val();
        var capacidad = $(target).val();
        $.get('/_registrarCapacidad/' + ate_id + '/' + tipo_capacidad + '/' + capacidad, function(data){
            console.log('/_registrarCapacidad/' + ate_id + '/' + tipo_capacidad + '/' + capacidad, data);
            data = JSON.parse(data);
            console.log('data:', data);
        });
    }
}


function p_validar_nodo_codigo(target){
    console.log('En p_validar_nodo_codigo', target);
    var cae_id = $(target).attr('id').replace('campo_extra_', '');
    var ate_id = $('#ate_id').val();
    var nod_codigo = $(target).val();
    $(target).parent().parent().removeClass('has-error');
    $(target).parent().parent().removeClass('has-success');
    $(target).parent().parent().removeClass('has-warning');
    if (nod_codigo != '') {
        $(target).val('');
        $.get('/_validarNodoCodigo/' + ate_id + '/' + cae_id + '/' + nod_codigo, function(data){
            console.log('/_validarNodoCodigo/' + ate_id + '/' + cae_id + '/' + nod_codigo, data);
            data = JSON.parse(data);
            console.log('data', data);
            if (data.length > 0) {
                console.log('ERROR de validacion');
                $(target).parent().parent().addClass('has-error');
                $(target).parent().parent().removeClass('has-success');

                $(target).popover('hide');
                $(target).popover('destroy');
                $(target).popover({
                placement:'auto top',
                    trigger:'manual',
                    html:true,
                    content:'El valor "' + nod_codigo + '" ya está siendo usado por un punto. Por favor ingrese otro valor.'
                });
                $(target).popover('show');
                setTimeout(function () {
                    $(target).popover('hide');
                    $(target).popover('destroy');
                }, 4000);
            } else {
                $(target).parent().parent().removeClass('has-error');
                $(target).parent().parent().addClass('has-success');
                $(target).val(nod_codigo);
            }
        });
    }
}

function p_validar_codigo_atencion(target){
    console.log('En p_validar_codigo_atencion', target);
    var ate_id = $('#ate_id').val();
    var ate_codigo = $(target).val();
    $(target).parent().parent().removeClass('has-error');
    $(target).parent().parent().removeClass('has-success');
    if (ate_codigo != '') {
        $(target).val('');
        $.get('/_validarCodigoAtencion/' + ate_id + '/' + ate_codigo, function(data){
            console.log('/_validarCodigoAtencion/' + ate_id + '/' + ate_codigo, data);
            data = JSON.parse(data);
            console.log('data', data);
            if (data.length > 0) {
                console.log('ERROR de validacion');
                $(target).parent().parent().addClass('has-error');
                $(target).parent().parent().removeClass('has-success');

                $(target).popover('hide');
                $(target).popover('destroy');
                $(target).popover({
                placement:'auto top',
                    trigger:'manual',
                    html:true,
                    content:'El valor "'+ate_codigo+'" ya está siendo usado por otra atención. Ingrese otro valor.'
                });
                $(target).popover('show');
                setTimeout(function () {
                    $(target).popover('hide');
                    $(target).popover('destroy');
                }, 4000);
            } else {
                $(target).parent().parent().removeClass('has-error');
                $(target).parent().parent().addClass('has-success');
                $(target).val(ate_codigo);
            }
        });
    }
}


function p_validar(target){
    var id = $(target).attr('id');
    console.log('validando', target, id, $(target)[0].checkValidity());
    var resultado = true;
    if (!$(target)[0].checkValidity()) {
        console.log('no valida...', target);
        $(target).popover('hide');
        $(target).popover('destroy');
        $(target).popover({
            placement:'auto top',
            trigger:'manual',
            html:true,
            content:target.validationMessage
        });
        $(target).popover('show');
        setTimeout(function () {
            $(target).popover('hide');
            $(target).popover('destroy');
        }, 4000);

        $('<input type="submit">').hide().appendTo('#' + id).click().remove();
        resultado = false;
    }
    return resultado;
}

function p_guardar(){
    if (p_validar($('#formulario'))) {
        $('#formulario').find('.typeahead').each(function(){$(this).prop('disabled', true)});
        var dataset = $('#formulario').serialize();
        $('#formulario').find('.typeahead').each(function(){$(this).prop('disabled', false)});

        console.log('dataset: ', dataset   );
        $.post('/_guardarValoresExtra', dataset, function(data){

            console.log('OK guardado', data);
            $('#modal').modal('hide');

            $("#modal").on("hidden.bs.modal", function () {

                p_validar_transicion(formulario_de_transicion['target'], formulario_de_transicion['tea_id'], formulario_de_transicion['ate_id'], formulario_de_transicion['estado_siguiente_id'], false);

                $("#modal").off("hidden.bs.modal");
            });
        })
    }
}
function p_nuevo(){
    $('#modal-nuevo').find(':input').each(function() {
        switch(this.type) {
        case 'password':
        case 'text':
        case 'textarea':
        case 'file':
        case 'select-one':
        case 'select-multiple':
        case 'date':
        case 'number':
        case 'tel':
        case 'email':
        case 'hidden':
            $(this).val('');
            break;
        case 'checkbox':
        case 'radio':
            this.checked = false;
            break;
        }
        $(this).trigger('change');
    });

    $('#cuenta').html('<option value="">Seleccione el cliente primero</option>');
    $('#cuenta').prop('disabled', true);
    $('#contacto').html('<option value="">Seleccione el cliente primero</option>');
    $('#contacto').prop('disabled', true);
    $('#contacto_en_sitio').html('<option value="">Seleccione el cliente primero</option>');
    $('#contacto_en_sitio').prop('disabled', true);
    $('#proveedor').html('<option value="">Seleccione el servicio primero</option>');
    $('#proveedor').prop('disabled', true);
    $('#proveedor').prop('multiple', false);

    $('#cantidad_extremos').prop('disabled', true);
    $('#cantidad_extremos').parent().parent().hide();

    $('#modal-nuevo').modal('show');
}

function p_cargar_proveedores(target) {
    console.log('En p_cargar_proveedores', target);

    $('#cantidad_extremos').prop('disabled', true);
    $('#cantidad_extremos').parent().parent().hide();
    $('#proveedor').html('<option value="">Seleccione el servicio primero</option>');
    $('#proveedor').prop('disabled', true);
    $('#proveedor').prop('multiple', false);
    $('#proveedor').val('');
    $('#proveedor').trigger('change');

    var ser_id = $(target).val();
    if (ser_id != '') {
        $.get('/_listarProveedores/' + ser_id, function(data){
            console.log('/_listarProveedores/'+ser_id, data);
            data = JSON.parse(data);
            console.log('data:', data);
            var opciones = '';
            if (data) {
                var count = 0;
                Array.from(data).forEach(function(proveedor){
                    opciones += '<option value="'+proveedor['pro_id']+'">'+proveedor['pro_nombre_comercial']+'</option>';
                    count++;
                });
                
                $('#proveedor').html(opciones);
                $('#proveedor').prop('disabled', false);
                $('#proveedor').prop('multiple', true);
                if (count > 1) {
                    $('#proveedor').val([]);
                }
                //$('#proveedor').trigger('change');

            }
        });
        $.get('/_listar/servicio/'+ser_id, function(data){
            console.log('/_listar/servicio/'+ser_id, data);
            data = JSON.parse(data);
            console.log('data', data);
            if (data) {
                var servicio = data[0];
                if (servicio['nombre'].toLowerCase() == 'datos') {
                    $('#cantidad_extremos').prop('disabled', false);
                    $('#cantidad_extremos').parent().parent().show();
                }
            }
        });
    }
}

function p_cargar_contactos_cuentas(target) {
    console.log('En p_cargar_contactos_cuentas', target);

    $('#cuenta').html('<option value="">Seleccione el cliente primero</option>');
    $('#cuenta').prop('disabled', true);
    $('#contacto').html('<option value="">Seleccione el cliente primero</option>');
    $('#contacto').prop('disabled', true);
    $('#contacto_en_sitio').html('<option value="">Seleccione el cliente primero</option>');
    $('#contacto_en_sitio').prop('disabled', true);

    var cli_id = $(target).val();
    if (cli_id != '') {
        $.get('/_listar/contacto/cliente/' + cli_id, function(data){
            console.log('/_listar/contacto/cliente/'+cli_id, data);
            data = JSON.parse(data);
            console.log('data:', data);
            console.log('tipos_contactos:', tipos_contactos);

            var opciones = '';
            var opciones_en_sitio = '';
            var opciones_comercial = '';

            if (data) {
                var count = 0;
                var count_comercial = 0;
                var count_en_sitio = 0;

                Array.from(data).forEach(function(contacto){
                    opciones += '<option value="'+contacto['id']+'">'+contacto['nombres']+' '+contacto['apellidos']+'</option>';

                    if (contacto['tipo_contacto'] == tipos_contactos['contacto_en_sitio']) {
                        opciones_en_sitio += '<option value="'+contacto['id']+'">'+contacto['nombres']+' '+contacto['apellidos']+'</option>';
                        count_en_sitio++;
                    }
                    if (contacto['tipo_contacto'] == tipos_contactos['comercial']) {
                        opciones_comercial += '<option value="'+contacto['id']+'">'+contacto['nombres']+' '+contacto['apellidos']+'</option>';
                        count_comercial++;
                    }
                    count++;
                });
                if (count_en_sitio > 1) {
                    opciones_en_sitio = '<option value="">&nbsp;</option>'+ opciones_en_sitio;
                }
                if (count_comercial > 1) {
                    opciones_comercial = '<option value="">&nbsp;</option>'+ opciones_comercial;
                }
                if (count > 1) {
                    opciones = '<option value="">&nbsp;</option>'+ opciones;
                }
                opciones_comercial = (opciones_comercial == '') ? opciones : opciones_comercial;
                opciones_en_sitio = (opciones_en_sitio == '') ? opciones : opciones_en_sitio;

                $('#contacto').html(opciones_comercial);
                $('#contacto').prop('disabled', false);
                $('#contacto_en_sitio').html(opciones_en_sitio);
                $('#contacto_en_sitio').prop('disabled', false);
            }
        });
        $.get('/_listar/cuenta/cliente/' + cli_id, function(data){
            console.log('/_listar/cuenta/cliente/'+cli_id, data);
            data = JSON.parse(data);
            console.log('data:', data);
            var opciones = '';
            if (data) {
                var count = 0;
                Array.from(data).forEach(function(cuenta){
                    opciones += '<option value="'+cuenta['id']+'">'+cuenta['codigo']+'</option>';
                    count++;
                });
                if (count > 1) {
                    opciones = '<option value="">&nbsp;</option>'+ opciones;
                }
                $('#cuenta').html(opciones);
                $('#cuenta').prop('disabled', false);

            }
        });
    }
}


function p_crear(){
    console.log('p_crear');
    if (p_validar($('#formulario_nuevo'))) {
        var dataset = $('#formulario_nuevo').serialize();
        console.log('dataset: ', dataset   );
        $.post('/_crearAtencion', dataset, function(data){

            console.log('OK creacion de atencion', data);
            $('#modal').modal('hide');
            location.reload();
        })
    }
}
</script>
<!--
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB8FSFiwwDeuKMWXOZpPuL1v6s9PnWNsFQ&callback=initMap"></script>
-->
