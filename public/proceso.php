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
        <li class="header">ESTADOS</li>
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
if (isset($_POST['estado']) && !empty($_POST['estado'])) {
    $estado = $_POST['estado'];
    $id = $_POST['id'];
    $ate_id = $id;
    $tea_id = $_POST['tea_id'];
    require_once('_obtenerCampos.php');

    $email_cliente = q("
            SELECT 
            con_correo_electronico
            FROM sai_contacto
            ,sai_atencion
            WHERE ate_contacto = con_id
            AND ate_id=$ate_id
    ")[0]['con_correo_electronico'];

    $email_proveedor = q("
            SELECT
            vpr_correo_electronico
            FROM sai_vendedor_proveedor
            ,sai_pertinencia_proveedor
            ,sai_atencion
            WHERE ate_pertinencia_proveedor = pep_id
            AND pep_vendedor_proveedor = vpr_id
            AND ate_id=$ate_id
    ")[0]['vpr_correo_electronico'];

    $email_usuario = q("
            SELECT
            usu_correo_electronico
            FROM sai_usuario
            ,sai_pertinencia_usuario
            ,sai_atencion
            WHERE ate_pertinencia_usuario = peu_id
            AND peu_usuario = usu_id
            AND ate_id=$ate_id
    ")[0]['usu_correo_electronico'];


    $email_usuario_comercial = q("
            SELECT
            usu_correo_electronico
            FROM sai_usuario
            ,sai_atencion
            WHERE usu_borrado IS NULL
            AND ate_usuario_comercial = usu_id
            AND ate_id=$ate_id
    ")[0]['usu_correo_electronico'];



    //echo "[$email_cliente - $email_proveedor - $email_usuario]";
    $result_contenido = q("
            SELECT *
            ,(SELECT des_nombre FROM sai_destinatario WHERE des_id = tea_destinatario) AS destinatario
            FROM sai_atencion
            ,sai_transicion_estado_atencion
            ,sai_plantilla
            WHERE ate_borrado IS NULL
            AND tea_borrado IS NULL
            AND pla_borrado IS NULL
            AND pla_transicion_estado_atencion = tea_id
            AND tea_estado_atencion_actual = ate_estado_atencion
            AND ate_pertinencia_proveedor = tea_pertinencia_proveedor
            AND ate_id=$ate_id
            ");
    if ($result_contenido) {
        q("UPDATE sai_paso_atencion SET paa_borrado=now() WHERE paa_atencion=$ate_id");

        foreach ($result_contenido as $rc) {
            $pla_asunto = $rc['pla_asunto'];
            $pla_adjunto_nombre = $rc['pla_adjunto_nombre'];

            $pla_cuerpo = $rc['pla_cuerpo'];
            $pla_adjunto_texto = $rc['pla_adjunto_texto'];
            $pla_id = $rc['pla_id'];

            $destinatario = $rc['destinatario'];

            $sql = ("
                SELECT * 
                FROM sai_adjunto_plantilla
                ,sai_archivo
                WHERE adp_borrado IS NULL
                AND arc_borrado IS NULL
                AND arc_id = adp_archivo 
                AND adp_plantilla=$pla_id
            ");
            $adjunto_plantilla = q($sql);





//echo '<pre>';
            //  var_dump($adjunto_plantilla);
    echo $sql;

    //echo "<pre>";
  //  echo "<hr><h1>RESULT CONTENIDO</h1>";
    //var_dump($result_contenido);
//echo '</pre>';
    //die();

            $campos_valores = array();

            if (isset($campos) && is_array($campos)) {
                $search = array();
                $replace = array();
                foreach($campos as $campo) {
                    $search[] = '%'.$campo['cae_codigo'].'%';
                    $replace[] = $campo['valor'];
                    $campos_valores['%'.$campo['cae_codigo'].'%'] = $campo['valor'];
                }
                //echo "<pre>";
                //echo $pla_cuerpo;
                //var_dump($search);
                //var_dump($replace);
                //var_dump($campos_valores);
                //echo str_replace($search, $replace, $pla_cuerpo);
                //echo "</pre>";
                $pla_cuerpo = str_replace($search, $replace, $pla_cuerpo);
                $pla_asunto = str_replace($search, $replace, $pla_asunto);
                $pla_adjunto_nombre = str_replace($search, $replace, $pla_adjunto_nombre);
                $pla_adjunto_texto = str_replace($search, $replace, $pla_adjunto_texto);
            }

            $pla_adjunto_nombre = (empty($pla_adjunto_nombre)) ? 'adjunto' : $pla_adjunto_nombre;
            $pla_asunto = (empty($pla_asunto)) ? 'Notificacion' : $pla_asunto;


            //require_once('../vendor/autoload.php');


echo '<pre>';
var_dump($adjunto_plantilla);
echo '</pre>';

            try{
                if ($adjunto_plantilla) {
                    //////////////
                    //Excel

                    //echo "sacando Excel";

                    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load('uploads/'.$adjunto_plantilla['arc_nombre']);

                    $worksheet = $spreadsheet->getActiveSheet();

                    $filas = $worksheet->toArray();

                    //var_dump($filas);
                    foreach($filas as $x => $fila){
                        foreach($fila as $y => $celda){
                            if (!empty($celda)) {
                                //echo "[$x, $y: $celda]";
                                if (preg_match('/\%.+\%/', $celda)){
                                    $nuevo_valor = (isset($campos_valores[$celda])) ? $campos_valores[$celda] : 'Dato no definido';
                                    $worksheet->setCellValueByColumnAndRow($y+1, $x+1, $nuevo_valor);
                                }
                            }
                        }
                    }

                    //$worksheet->getCell('A1')->setValue('John');
                    //$worksheet->getCell('A2')->setValue('Smith');

                    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
                    $writer->save('adjunto.xls');
                }


                //////////////
                //PDF
                if (file_exists('adjunto.html')) {
                    unlink('adjunto.html');
                }
                if (file_exists($pla_adjunto_nombre.'.pdf')) {
                    unlink($pla_adjunto_nombre.'.pdf');
                }

                if (!empty($pla_adjunto_texto)) {
                    $snappy = new Knp\Snappy\Pdf('../vendor/bin/wkhtmltopdf-amd64');
                    $msg = ($pla_adjunto_texto);
                    file_put_contents( 'adjunto.html', $msg);
                    $msg = file_get_contents('adjunto.html');
                    //$msg = utf8_decode($msg);
                    $snappy->generateFromHtml($msg, $pla_adjunto_nombre.'.pdf', array('encoding' => 'utf-8'));
                }

                //MAIL
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                $mail->IsSMTP();
                $mail->SMTPSecure = 'tls';
                $mail->SMTPAuth = true;
                $mail->Host = SMTP_SERVER;
                $mail->Port = SMTP_PORT;
                $mail->Username = SMTP_USERNAME;
                $mail->Password = SMTP_PASSWORD;
                //$mail->SMTPDebug = 2;
                $mail->SetFrom(MAIL_ORDERS_ADDRESS, MAIL_ORDERS_NAME);
                $mail->Subject = $pla_asunto;
                $mail->MsgHTML($pla_cuerpo);
                if ($destinatario == 'cliente') {
                    $mail->AddAddress($email_cliente);
                }
                if ($destinatario == 'proveedor') {
                    $mail->AddAddress($email_proveedor);
                }
                $mail->AddAddress($email_usuario);
                $mail->AddAddress($email_usuario_comercial);
                //$mail->AddAddress('sminga@nedetel.net');
                //$mail->AddAddress('dcedeno@nedetel.net');
                //$mail->AddAddress('edgar.valarezo@gmail.com');
                //$mail->AddAttachment('prueba.txt');
                if (!empty($pla_adjunto_texto)) {
                    $mail->AddAttachment($pla_adjunto_nombre.'.pdf');
                }
                if ($adjunto_plantilla) {
                    $mail->AddAttachment('adjunto.xls');
                }
                //$mail->AddAttachment('example.xlsx');
                $mail->AddBCC(MAIL_ORDERS_ADDRESS, MAIL_ORDERS_NAME);

                if(!$mail->Send()) throw new Exception($mail->ErrorInfo);
            }
            catch(Exception $e){
                //echo $e->getMessage();
                l($e->getMessage());
            }

            $result = q("
                INSERT INTO sai_paso_atencion (
                    paa_atencion
                    ,paa_transicion_estado_atencion
                    ,paa_codigo
                    ,paa_asunto
                    ,paa_cuerpo
                    ,paa_destinatarios 
                ) VALUES (
                    $ate_id
                    ,$tea_id
                    ,''
                    ,'$pla_asunto'
                    ,'$pla_cuerpo'
                    ,'$email_cliente,$email_proveedor'
                ) RETURNING *
            ");

        }
    }
    $result = q("UPDATE sai_atencion SET ate_estado_atencion=$estado WHERE ate_id=$id RETURNING *");

}

$filtro = isset($filtro) ? "AND tea_estado_atencion_actual IN $filtro" : '';
if (isset($args[0]) && !empty($args[0])) {
    $filtro = "AND tea_estado_atencion_actual = {$args[0]}";
}
$sql = ("
    SELECT * 
    ,e1.esa_nombre AS estado_actual
    ,e2.esa_nombre AS estado_siguiente
    ,e2.esa_id AS estado_siguiente_id

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

    LEFT OUTER JOIN sai_pertinencia_usuario
        ON peu_borrado IS NULL
        AND ate_pertinencia_usuario = peu_id

    LEFT OUTER JOIN sai_usuario
        ON usu_borrado IS NULL
        AND peu_usuario = usu_id

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
        ate_id, estado_actual,
        ate_creado DESC
");
$result = q($sql);
//echo $sql;
if ($result) {
    $estado_actual = null;
    $estado_siguiente = null;
    foreach ($result as $r) {
        //echo '<div><form><button class="btn btn-info" onclick="">'."Servicio de {$r[ser_nombre]} a {$r[cli_razon_social]}, ".p_formatear_fecha($r['ate_creado']).' (estado '.$r['esa_nombre'].')</button></form></div>';
        if ($estado_actual != $r[ate_id] . $r[estado_actual]) {
            if (!empty($estado_actual)) {

        echo <<<EOT
  </div>
</div>
EOT;
            } 
        $fecha_formateada = p_formatear_fecha($r['ate_creado']);
        //$vardump = print_r($result, true);
        //<pre>$vardump</pre>
        echo <<< EOT
<div class="panel panel-info" style="width:500px;">
  <div class="panel-heading">
    <h3 class="panel-title"><a name="atencion_{$r[ate_secuencial]}">{$r[ate_secuencial]}. Servicio de {$r[ser_nombre]} a {$r[cli_razon_social]}</a></h3>
  </div>
  <div class="panel-body">
  $fecha_formateada 
  <br>
  <div>&nbsp;</div>
  <strong>Estado:</strong> {$r[estado_actual]}
  <div>&nbsp;</div>
  <strong>Proveedor:</strong> {$r[pro_razon_social]}
  <div>&nbsp;</div>
  <strong>Usuario:</strong> {$r[usu_nombres]} {$r[usu_apellidos]}
  <div>&nbsp;</div>


  <!--
  <strong>Destinatario:</strong> {$r[tea_destinatario]}
  <div>&nbsp;</div>
  <strong>Estado padre:</strong> {$r[tea_estado_atencion_actual]}
  <div>&nbsp;</div>
  <strong>Estado hijo:</strong> {$r[tea_estado_atencion_siguiente]}
  <div>&nbsp;</div>
  <strong>Pertinencia proveedor:</strong> {$r[tea_pertinencia_proveedor]}
  <div>&nbsp;</div>
  <strong>Pertinencia usuario:</strong> {$r[tea_pertinencia_usuario]}
  <div>&nbsp;</div>
  -->


  <div>
  <button class="btn btn-info" onclick="p_abrir({$r[tea_id]}, {$r[ate_id]})"><span class="glyphicon glyphicon-list-alt" aria-hidden="true"></span> Recopilar datos</button>
  </div>
  <div>&nbsp;</div>
EOT;
        }
        $estado_actual = $r[ate_id] . $r[estado_actual];
        $estado = $r['ate_estado_atencion'];
        $servicio = $r['ate_servicio'];
        $pertinencia_proveedor = $r['tea_pertinencia_proveedor'];
        /*
        $result2 = q("
            SELECT * 
            FROM sai_transicion_estado_atencion
            , sai_estado_atencion 
            , sai_pertinencia_proveedor
            , sai_proveedor
            , sai_servicio
            WHERE esa_borrado IS NULL
            AND tea_borrado IS NULL
            AND pep_borrado IS NULL
            AND ser_borrado IS NULL
            AND pro_borrado IS NULL
            AND tea_pertinencia_proveedor = pep_id
            AND tea_estado_atencion_siguiente = esa_id 
            AND pep_servicio = ser_id
            AND pep_proveedor = pro_id
            AND tea_estado_atencion_actual = $estado
            AND pep_servicio = $servicio
            AND tea_pertinencia_proveedor = $pertinencia_proveedor
            ORDER BY esa_nombre, tea_estado_atencion_siguiente
        ");
         */
        
        if ($estado_siguiente != $r['ate_id'] . '-' . $r['estado_siguiente']) {
            //se repite por múltiples destinatarios (cliente, proveedor, usuario), por eso se consolida:
            //
                echo "<form method='POST' onsubmit='return p_validar_transicion(this, ".$r['tea_id'].", ".$r['ate_id'].")'>";
                echo "<input type='hidden' name='estado' value='".$r['estado_siguiente_id']."'>";
                echo "<input type='hidden' name='tea_id' value='".$r['tea_id']."'>";
                echo "<input type='hidden' name='id' value='".$r['ate_id']."'>";
                echo "<li><button class='btn btn-success'>" . '<span class="glyphicon glyphicon-arrow-right" aria-hidden="true"></span> ';
                //echo "Pasar al estado: ". $r2['esa_nombre'] . ", Proveedor {$r2[pro_razon_social]} - {$r2[tea_]}";
                //echo "Pasar al estado: ". $r['estado_siguiente'] . ' - '. $r['tea_destinatario']; 
                echo "Pasar al estado: ". $r['estado_siguiente']; 
                echo "</button></li>";
                echo "</form>";
        }

        $estado_siguiente = $r['ate_id'] . '-' . $r['estado_siguiente'];
    }
}
?>

<script>
$(document).ready(function() {
    $('.combo-select2').select2({
        language: "es"
    });
});
function p_validar_transicion(target, tea_id, ate_id){
    $.get('/_obtenerCampos/'+tea_id + '/'+ate_id, function(data){
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
                }
            });
        }
        if (completo){
            target.submit();
            //console.log('submit');
        } else {
            alert('Faltan de completar campos.');
        }
    });
    return false;
}
function p_abrir(tea_id, ate_id) {
    console.log('abrir', tea_id, ate_id);
    $.get('/_obtenerCampos/'+tea_id + '/'+ate_id, function(data){
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

    campos.forEach(function(campo){
        var valor = (campo['valor'] == 'null' || campo['valor'] == null) ? '' : campo['valor'];

        var contenido = '';
        console.log('CAMPO:', campo);
        if (padre_id == campo['cae_padre']) {
            if (campo['hijos'].length == 0 ) {
                //if (campo['padre'] != null) {
                contenido += '<div class="form-group">' + '<label for="campo_extra_'+campo['cae_id']+'" class="col-sm-' + col1 + ' control-label">'+campo['cae_texto']+ ':</label>    <div class="col-sm-' + col2 + '"><input '+campo['cae_validacion']+' class="form-control" id="campo_extra_'+campo['cae_id']+'" name="campo_extra_'+campo['cae_id']+'" placeholder="" value="' + valor + '" onblur="p_validar(this)"></div>' + '</div>';
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
    console.log('validando', target);
    var resultado = true;
    if (!$(target)[0].checkValidity()) {
        console.log('no valida...');
        $('<input type="submit">').hide().appendTo('#formulario').click().remove();
        resultado = false;
    }
    return resultado;
}

function p_guardar(){
    if (p_validar($('#formulario'))) {
        var dataset = $('#formulario').serialize();
        console.log('dataset: ', dataset   );
        $.post('_guardarValoresExtra', dataset, function(data){

            console.log('OK guardado', data);
            $('#modal').modal('hide');
        })
    }
}
function p_nuevo(){
    $('#modal-nuevo').modal('show');
}

function p_crear(){
    //if (p_validar($('#formulario_nuevo'))) {
        var dataset = $('#formulario_nuevo').serialize();
        console.log('dataset: ', dataset   );
        $.post('_crearAtencion', dataset, function(data){

            console.log('OK creacion de atencion', data);
            $('#modal').modal('hide');
            location.reload();
        })
    //}
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
      <select class="form-control combo-select2" style="width: 50%" id="cliente" name="cliente" tabindex="-1" aria-hidden="true">
        <option>&nbsp;</option>
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
    <label for="cuenta" class="col-sm-4 control-label">Cuenta</label>
    <div class="col-sm-8">
      <select class="form-control combo-select2" style="width: 50%" id="cuenta" name="cuenta" tabindex="-1" aria-hidden="true">

        <option>&nbsp;</option>
      <?php
$result = q("
    SELECT *
    FROM sai_cuenta
    WHERE cue_borrado IS NULL
");
if ($result) {
    foreach($result as $r) {
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
      <select class="form-control combo-select2" style="width: 50%" id="servicio" name="servicio" tabindex="-1" aria-hidden="true">

        <option>&nbsp;</option>
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
      <select class="form-control combo-select2" style="width: 50%" id="proveedor" name="proveedor" tabindex="-1" aria-hidden="true">
        <option>&nbsp;</option>
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
    <label for="pertinencia_usuario" class="col-sm-4 control-label">Usuario técnico</label>
    <div class="col-sm-8">

      <!--pre>FOREIGN KEY
                  </pre-->
      <select class="form-control combo-select2" style="width: 50%" id="pertinencia_usuario" name="pertinencia_usuario" tabindex="-1" aria-hidden="true">
        <option>&nbsp;</option>
      <?php
$result = q("
    SELECT *
    FROM sai_pertinencia_usuario
    ,sai_usuario
    ,sai_servicio
    WHERE peu_borrado IS NULL
    AND usu_borrado IS NULL
    AND ser_borrado IS NULL
    AND peu_usuario = usu_id
    AND peu_servicio = ser_id
");
if ($result) {
    foreach($result as $r) {
        $value = $r['peu_id'];
        $label = $r['usu_nombres'] . ' ' .$r['usu_apellidos'] . ' (' . $r['ser_nombre'] . ')';
        echo "<option value='$value'>$label</option>";
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
      <select class="form-control combo-select2" style="width: 50%" id="usuario_comercial" name="usuario_comercial" tabindex="-1" aria-hidden="true">
        <option>&nbsp;</option>
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
        <h4 class="modal-title">Campos <span id="formulario_titulo"></span></h4>
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


