<h1>Atenciones</h1>
<?php
if (isset($_POST['estado']) && !empty($_POST['estado'])) {
    $estado = $_POST['estado'];
    $id = $_POST['id'];
    $ate_id = $id;
    $tea_id = $_POST['tea_id'];
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

    $result_contenido = q("
            SELECT *,
            pla_cuerpo,pla_adjunto_texto, pla_asunto, pla_adjunto_nombre
            FROM sai_atencion
            ,sai_plantilla
            ,sai_transicion_estado_atencion
            WHERE
            pla_transicion_estado_atencion = tea_id
            AND tea_estado_atencion_padre = ate_estado_atencion
            AND tea_borrado IS NULL
            AND ate_id=$ate_id
            ");
    $pla_asunto = $result_contenido[0]['pla_asunto'];
    $pla_adjunto_nombre = $result_contenido[0]['pla_adjunto_nombre'];


    $pla_cuerpo = $result_contenido[0]['pla_cuerpo'];
    $pla_adjunto_texto = $result_contenido[0]['pla_adjunto_texto'];

    //echo "<pre>";
    //var_dump($result_contenido);
    //die();

    require_once('_obtenerCampos.php');

    if (isset($campos) && is_array($campos)) {
        $search = array();
        $replace = array();
        foreach($campos as $campo) {
            $search[] = '%'.$campo['cae_codigo'].'%';
            $replace[] = $campo['valor'];
        }
        $pla_cuerpo = str_replace($search, $replace, $pla_cuerpo);
        $pla_asunto = str_replace($search, $replace, $pla_asunto);
        $pla_adjunto_nombre = str_replace($search, $replace, $pla_adjunto_nombre);
        $pla_adjunto_texto = str_replace($search, $replace, $pla_adjunto_texto);
    }

    $pla_adjunto_nombre = (empty($pla_adjunto_nombre)) ? 'adjunto' : $pla_adjunto_nombre;
    $pla_asunto = (empty($pla_asunto)) ? 'Notificacion' : $pla_asunto;


    require_once('../vendor/autoload.php');


    define('SMTP_SERVER', 'mail.nedetel.net');
    define('SMTP_PORT', 587);
    define('SMTP_USERNAME', 'sait@nedetel.net');
    define('SMTP_PASSWORD', 'n3D1$207*');

    define('MAIL_ORDERS_ADDRESS', 'sait@nedetel.net');
    define('MAIL_ORDERS_NAME', 'SAIT');


    try{
        //PDF
        if (file_exists('adjunto.html')) {
            unlink('adjunto.html');
        }
        if (file_exists($pla_adjunto_nombre.'.pdf')) {
            unlink($pla_adjunto_nombre.'.pdf');
        }

        $snappy = new Knp\Snappy\Pdf('../vendor/bin/wkhtmltopdf-amd64');
        $msg = ($pla_adjunto_texto);
        file_put_contents( 'adjunto.html', $msg);
        $msg = file_get_contents('adjunto.html');
        //$msg = utf8_decode($msg);
        $snappy->generateFromHtml($msg, $pla_adjunto_nombre.'.pdf', array('encoding' => 'utf-8'));

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
        $mail->AddAddress($email_cliente);
        $mail->AddAddress($email_proveedor);
        //$mail->AddAddress('sminga@nedetel.net');
        //$mail->AddAddress('dcedeno@nedetel.net');
        //$mail->AddAttachment('prueba.txt');
        $mail->AddAttachment($pla_adjunto_nombre.'.pdf');
        //$mail->AddAttachment('example.xlsx');
        $mail->AddBCC(MAIL_ORDERS_ADDRESS, MAIL_ORDERS_NAME);

        if(!$mail->Send()) throw new Exception($mail->ErrorInfo);
    }
    catch(Exception $e){
        //echo $e->getMessage();
    }

    q("UPDATE sai_paso_atencion SET paa_borrado=now() WHERE paa_atencion=$ate_id");
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

    $result = q("UPDATE sai_atencion SET ate_estado_atencion=$estado WHERE ate_id=$id RETURNING *");

}

$result = q("
    SELECT * 
    FROM sai_atencion
    ,sai_estado_atencion 
    ,sai_servicio
    ,sai_cuenta
    ,sai_cliente
    ,sai_pertinencia_proveedor
    ,sai_proveedor
    ,sai_transicion_estado_atencion
    WHERE ate_borrado IS NULL 
    AND esa_borrado IS NULL 
    AND cue_borrado IS NULL
    AND cli_borrado IS NULL
    AND ser_borrado IS NULL
    AND pep_borrado IS NULL
    AND pro_borrado IS NULL
    AND tea_borrado IS NULL
    AND cli_id = cue_cliente
    AND cue_id = ate_cuenta
    AND ate_servicio = ser_id
    AND ate_estado_atencion = esa_id
    AND ate_pertinencia_proveedor = pep_id
    AND pep_proveedor = pro_id
    AND tea_estado_atencion_padre = ate_estado_atencion
    ORDER BY cli_razon_social
    ,cue_creado DESC
    ,ate_creado DESC
");

if ($result) {
    foreach($result as $r){
        //echo '<div><form><button class="btn btn-info" onclick="">'."Servicio de {$r[ser_nombre]} a {$r[cli_razon_social]}, ".p_formatear_fecha($r['ate_creado']).' (estado '.$r['esa_nombre'].')</button></form></div>';
        $fecha_formateada = p_formatear_fecha($r['ate_creado']);
        echo <<< EOT
<div class="panel panel-info" style="width:500px;">
  <div class="panel-heading">
    <h3 class="panel-title">Servicio de {$r[ser_nombre]} a {$r[cli_razon_social]}</h3>
  </div>
  <div class="panel-body">
  $fecha_formateada 
  <br>
  <div>&nbsp;</div>
  <strong>Estado:</strong> {$r[esa_nombre]}
  <div>&nbsp;</div>
  <strong>Proveedor:</strong> {$r[pro_razon_social]}
  <div>&nbsp;</div>
  <div>
  <button class="btn btn-info" onclick="p_abrir({$r[tea_id]}, {$r[ate_id]})"><span class="glyphicon glyphicon-list-alt" aria-hidden="true"></span> Recopilar datos</button>
  </div>
  <div>&nbsp;</div>
EOT;
        $estado = $r['ate_estado_atencion'];
        $servicio = $r['ate_servicio'];
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
            AND tea_estado_atencion_hijo = esa_id 
            AND pep_servicio = ser_id
            AND pep_proveedor = pro_id
            AND tea_estado_atencion_padre = $estado
            AND pep_servicio = $servicio
            ORDER BY esa_nombre, tea_estado_atencion_hijo
        ");
        if ($result2){
            echo "<ul>";
            foreach($result2 as $r2){
                echo "<form method='POST'>";
                echo "<input type='hidden' name='estado' value='".$r2['esa_id']."'>";
                echo "<input type='hidden' name='tea_id' value='".$r2['tea_id']."'>";
                echo "<input type='hidden' name='id' value='".$r['ate_id']."'>";
                echo "<li><button class='btn btn-success'>" . '<span class="glyphicon glyphicon-arrow-right" aria-hidden="true"></span> ';
                echo "Pasar al estado: ". $r2['esa_nombre'] . ", Proveedor {$r2[pro_razon_social]}";
                echo "</button></li>";
                echo "</form>";

            }
            echo "</ul>";
        }
        echo <<<EOT
  </div>
</div>
EOT;

    }

}
?>

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
<script>
function p_abrir(tea_id, ate_id) {
    console.log('abrir', tea_id, ate_id);
    $.get('/_obtenerCampos/'+tea_id + '/'+ate_id, function(data){
        console.log(data);
        data = JSON.parse(data);
        console.log(data);

        $('#campos').html("");
        $('#ate_id').val(ate_id);

        data.forEach(function(campo){
            var valor = (campo['valor'] == 'null' || campo['valor'] == null) ? '' : campo['valor'];
        $('#campos').append('<div class="form-group"><label for="'+campo['cae_codigo']+'" class="col-sm-2 control-label">'+campo['cae_texto']+ ':</label>    <div class="col-sm-10"><input type="text" class="form-control" id="'+campo['cae_codigo']+'" name="'+campo['cae_codigo']+'" placeholder="" value="'+valor+'"></div></div>');
        });
        $('#modal').modal('show');
    })
}

function p_guardar(){

    var dataset = $('#formulario').serialize();
    console.log('dataset: ', dataset   );
    $.post('_guardarValoresExtra', dataset, function(data){

        console.log('OK guardado', data);
        $('#modal').modal('hide');
    })
}
</script>

