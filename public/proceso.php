<?php
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
      <form action="#" method="get" class="sidebar-form">
        <div class="input-group">
          <input type="text" name="q" class="form-control" placeholder="Buscar...">
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
    FROM sai_estado_atencion ORDER BY esa_padre, esa_orden, esa_id
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
                $esa_codigo_padre = (!empty($hijo['esa_codigo'])) ? $hijo['esa_codigo'] : $esa_codigo_padre;
                p_tree($hijo['hijos'], $hijo['esa_nombre'], $esa_codigo_padre);
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
      <?=(isset($titulo_proceso) ? $titulo_proceso : 'Atenciones')?>
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

$filtro = isset($filtro) ? "AND tea_estado_atencion_actual IN $filtro" : '';
if (isset($args[0]) && !empty($args[0])) {
    $filtro = "AND tea_estado_atencion_actual = {$args[0]}";
}
$sql = ("
    SELECT * 
    ,e1.esa_nombre AS estado_actual
    ,e2.esa_nombre AS estado_siguiente
    ,e2.esa_id AS estado_siguiente_id
    ,(usu_tecnico.usu_nombres || ' ' || usu_tecnico.usu_apellidos) AS usu_tecnico_nombre
    ,(usu_comercial.usu_nombres || ' ' || usu_comercial.usu_apellidos) AS usu_comercial_nombre

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

    ORDER BY 
        ate_id DESC, estado_actual, estado_siguiente
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
        echo <<<EOT
      <a name="atencion_{$r[ate_secuencial]}"></a>
<div class="panel panel-info" xxxstyle="width:500px;">
  <div class="panel-heading">
    <div class="pull-right">
      $fecha_formateada 
    </div>
    <h3 class="panel-title">
      <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse_{$r[ate_secuencial]}" aria-expanded="false" aria-controls="collapse_{$r[ate_secuencial]}" >
        {$r[ate_secuencial]}. <strong>{$r[estado_actual]}</strong> para servicio de {$r[ser_nombre]} ({$r[pro_razon_social]}) a {$r[cli_razon_social]}
      </a>
    </h3>
  </div>

  <div id="collapse_{$r[ate_secuencial]}" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading_{$r[ate_secuencial]}">
EOT;
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

        echo <<<EOT
    <div class="panel-body">
      <strong>Usuario técnico:</strong> {$r[usu_tecnico_nombre]}
      <br>
      <strong>Usuario comercial:</strong> {$r[usu_comercial_nombre]}
      <div>&nbsp;</div>
EOT;
        echo <<<EOT
        <table style="width:400px" class="table table-striped table-condensed table-hover">
        <tbody>
EOT;

        $sql = ("
            SELECT *
            FROM sai_campo_extra
            ,sai_paso_atencion
            ,sai_valor_extra
            WHERE cae_borrado IS NULL
            AND vae_borrado IS NULL
            AND vae_campo_extra = cae_id
            AND vae_paso_atencion = paa_id
            AND paa_atencion={$r[ate_id]}
            AND NOT paa_paso_anterior IS NULL
            ORDER BY vae_creado, cae_orden
        ");
            //AND paa_borrado IS NULL
        $result_campos = q($sql);
        if ($result_campos) {
            foreach($result_campos as $rdato){
                $label = ucfirst($rdato['cae_texto']);
                $dato = $rdato['vae_texto'];
                echo <<<EOT
            <tr>
              <th style="width:30%;">$label:</th>
              <td>$dato</td>
            </tr>
EOT;
            }
        }
        echo '</tbody></table>';
        //echo "$sql";
        /*
        echo <<<EOT
      <div>&nbsp;</div>
      <strong>Estado:</strong> {$r[estado_actual]}
      <div>&nbsp;</div>
      <strong>Proveedor:</strong> {$r[pro_razon_social]}
      <div>&nbsp;</div>
      <strong>Usuario:</strong> {$r[usu_nombres]} {$r[usu_apellidos]}
      <div>&nbsp;</div>

EOT;
         */
        echo <<<EOT

      <div>&nbsp;</div>

      <div>
        <button class="btn btn-info" onclick="p_abrir({$r[tea_id]}, {$r[ate_id]})"><span class="glyphicon glyphicon-list-alt" aria-hidden="true"></span> Recopilar datos</button>
      </div>
      <div>&nbsp;</div>
EOT;
        echo <<<EOT
    </div>
  </div>
</div>
EOT;
    }
    echo '</div>';
}
?>

<script src="/js/ckeditor/ckeditor.js"></script>
<script>
$(document).ready(function() {
    $('.combo-select2').select2({
        language: "es"
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
});

var destinatarios = <?=json_encode($destinatarios)?>;

function p_validar_transicion(target, tea_id, ate_id, estado_siguiente_id){
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
        }
        if (completo){
            //target.submit();
            p_abrir_confirmacion(target, tea_id, ate_id, estado_siguiente_id);
            //console.log('submit');
        } else {
            alert('Faltan de completar campos.');
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
    });

    var dataset = $(target).serialize();
    $.post('/_calcularPreTransicion/' + ate_id, dataset, function(data){
        console.log('Respuesta _calcularPreTransicion', data);
        data = JSON.parse(data);
        console.log('data', data);
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

            $('#tea_id_accion_'+destinatario).val(tea_id);
            $('#email_'+destinatario).val(emails[destinatario]);
            CKEDITOR.instances['mensaje_'+destinatario].setData(plantilla.textos[0]);
            $('#asunto_'+destinatario).val(plantilla.textos[1]);
            console.log('PLANTILLA:', plantilla);
            if (plantilla.xls_generado) {
                var icono = '<span class="glyphicon glyphicon-download" aria-hidden="true"></span> ';
                Array.from(plantilla.adjuntos_generados).forEach(function(archivo){
                    console.log('ARCHIVO:', archivo);

                    var hidden = '<input type="hidden" name="adjunto_' + destinatario + '[]" value="' + archivo + '">';
                    //$('#adjuntos_lista_'+destinatario).append(hidden + '<div><a class="btn btn-default" href="/' + plantilla.textos[2] + '">' + icono + plantilla.textos[2] + '</a></div>');
                    $('#adjuntos_lista_'+destinatario).append(hidden + '<div><a class="btn btn-default" href="/' + archivo + '">' + icono + archivo + '</a></div>');
                });
            }
        });
        $('#modal_confirmacion').modal('show');
    });
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

            $('#campos').append(p_desplegar_campos(campos));
            $('.datetimepicker').datetimepicker({
            locale: 'es',
                format: 'YYYY-MM-DD'
            });

            $('#modal').modal('show');
        } else {
            alert('No hay campos asociados');
        }
    });
}

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
        var valor = (campo['valor'] == 'null' || campo['valor'] == null) ? '' : campo['valor'];

        var contenido = '';
        console.log('CAMPO:', campo);
        //if (padre_id == null || padre_id == campo['cae_padre']) {
        if (padre_id == campo['cae_padre']) {
            if (campo['hijos'].length == 0 ) {
                //if (campo['padre'] != null) {
                if (campo['tipo_dato'] == 'fecha') {
                    contenido += ''+
'            <div class="form-group">'+
                        '<label for="campo_extra_'+campo['cae_id']+'" class="col-sm-' + col1 + ' control-label">'+campo['cae_texto']+ ':</label>' +
                        '<div class="col-sm-' + col2 + '">' +
'                <div class="input-group date" id="datetimepicker2-'+campo['cae_id']+'">'+
'                    <input type="text" class="form-control datetimepicker" name="campo_extra_'+campo['cae_id']+'" id="campo_extra_'+campo['cae_id']+'" value="'+valor+'" />'+
'                    <span class="input-group-addon">'+
'                        <span class="glyphicon glyphicon-calendar"></span>'+
'                    </span>'+
'                </div>'+
'              </div>'+
'            </div>'+
'';

                } else if (campo['tipo_dato'] == 'numero') {
                    contenido += ''+
                        '<div class="form-group">' +
                        '<label for="campo_extra_'+campo['cae_id']+'" class="col-sm-' + col1 + ' control-label">'+campo['cae_texto']+ ':</label>' +
                        '<div class="col-sm-' + col2 + '">' +
                        '<input type="number" '+campo['cae_validacion']+' class="form-control" id="campo_extra_'+campo['cae_id']+'" name="campo_extra_'+campo['cae_id']+'" placeholder="" value="' + valor + '" onblur="p_validar(this)">' +
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

function p_validar(target){
    var id = $(target).attr('id');
    console.log('validando', target, id, $(target)[0].checkValidity());
    var resultado = true;
    if (!$(target)[0].checkValidity()) {
        console.log('no valida...');
        $('<input type="submit">').hide().appendTo('#' + id).click().remove();
        resultado = false;
    }
    return resultado;
}

function p_guardar(){
    if (p_validar($('#formulario'))) {
        var dataset = $('#formulario').serialize();
        console.log('dataset: ', dataset   );
        $.post('/_guardarValoresExtra', dataset, function(data){

            console.log('OK guardado', data);
            $('#modal').modal('hide');
        })
    }
}
function p_nuevo(){
    $('#modal-nuevo').modal('show');
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

<?php if (isset($mostrar_nuevo) && $mostrar_nuevo): ?>
<a href="#" onclick="p_nuevo();return false;" style="position:fixed;bottom:50px;right:10px;"><img src="/img/plus.png" alt="Crear nuevo registro" title="Crear nuevo registro" ></img></a>
<?php endif; ?>
<!-- REQUIRED JS SCRIPTS -->

<!-- AdminLTE App -->
<script src="/js/adminlte.min.js"></script>

<!-- Optionally, you can add Slimscroll and FastClick plugins.
     Both of these plugins are recommended to enhance the
     user experience. -->


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
<div class="panel panel-default">
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
      <!--
      <input type="file" class="form-control" id="adjunto_<?=$destinatario?>" name="adjunto_<?=$destinatario?>">
      -->
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
        <button type="button" class="btn btn-success" onclick="p_ejecutar_transicion()" id="formulario_nuevo_crear">Ejecutar Transición</button>
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
    <label for="cliente" class="col-sm-4 control-label">Cliente</label>
    <div class="col-sm-8">
      <select required class="form-control combo-select2" style="width: 50%" id="cliente" name="cliente" tabindex="-1" aria-hidden="true">
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
    <label for="cuenta" class="col-sm-4 control-label">Dependencia de empresas:</label>
    <div class="col-sm-8">
      <select required class="form-control combo-select2" style="width: 50%" id="cuenta" name="cuenta" tabindex="-1" aria-hidden="true">

        <option value="">&nbsp;</option>
      <?php
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
        ?>
      </select> 
    </div>
  </div>


  <div class="form-group">
    <label for="servicio" class="col-sm-4 control-label">Servicio</label>
    <div class="col-sm-8">
      <select required class="form-control combo-select2" style="width: 50%" id="servicio" name="servicio" tabindex="-1" aria-hidden="true">

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
$result = q("
    SELECT *
    FROM sai_proveedor
    WHERE pro_borrado IS NULL
");
if ($result) {
    foreach($result as $r) {
        $value = $r['pro_id'];
        $label = $r['pro_razon_social']; 
        echo "<option value='$value'>$label</option>";
    }
}
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


