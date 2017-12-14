<h1>Transiciones</h1>
<?php

$result= q("
    SELECT 
    *
    ,(
        SELECT 
        COUNT(*) 
        FROM sai_estado_atencion AS t 
        WHERE t.esa_padre=esa_id
    ) AS count_hijos
    ,(
        SELECT 
        COUNT(*)
        FROM sai_transicion_estado_atencion
        WHERE tea_estado_atencion_padre = esa_id
    ) AS count_transicion
    FROM sai_estado_atencion 
    ORDER BY esa_padre DESC, 
    esa_orden, 
    esa_id
    ");
$estados = array();

foreach($result as $r){
    $estados[$r['esa_id']] = $r;
    $estados[$r['esa_id']]['hijos'] = array();
    $estados[$r['esa_id']]['padre'] = null;
    $estados[$r['esa_id']]['label'] = '';
    $estados[$r['esa_id']]['num_hijos'] = 0;
    $estados[$r['esa_id']]['num_hojas'] = 0;
    $estados[$r['esa_id']]['primera_hoja'] = null;
    $estados[$r['esa_id']]['profundidad'] = 0;
}

$tree = array();

foreach($estados as $estado) {
    if (!empty($estado['esa_padre'])) {
        $estados[$estado['esa_padre']]['hijos'][$estado['esa_id']] = & $estados[$estado['esa_id']];
        //$estado['padre'] = &$estados[$estado['esa_padre']];
        $estados[$estado['esa_id']]['padre'] = &$estados[$estado['esa_padre']];
    }
}

$profundidad_maxima = 0;
foreach($estados as $estado) {
    $nodo = $estado;
    $profundidad = 0;
    while (!empty($nodo['padre']) ) {
        $profundidad++;
        $estados[$nodo['padre']['esa_id']]['num_hijos']++;
        $estados[$nodo['padre']['esa_id']]['profundidad'] = $profundidad;
        $nodo = $nodo['padre'];

    }
    $nodo = $estado;
    if (empty($nodo['hijos'])) {
        //es hoja, suma num hojas a su padre y  antepasados:
        $estados[$nodo['esa_id']]['primera_hoja'] = $nodo['esa_id'];

        $primera_hoja = null;
        if ($nodo['esa_id'] == array_keys($nodo['padre']['hijos'])[0]) {
            //es primera hoja
            $primera_hoja = $nodo['esa_id'];
        }
        while (!empty($nodo['padre'])) {
            $estados[$nodo['padre']['esa_id']]['num_hojas'] ++;
            if (!empty($primera_hoja)) {
                if (empty($estados[$nodo['padre']['esa _id']]['primera_hoja'])) {
                    $estados[$nodo['padre']['esa_id']]['primera_hoja'] = $primera_hoja;
                }
            }
            $nodo = $nodo['padre'];
        }
    }
    if ($profundidad_maxima < $profundidad) {
        $profundidad_maxima = $profundidad;
    }
}
foreach($estados as  $estado) {

    if (empty($estado['esa_padre'])) {
        $tree[] = & $estados[$estado['esa_id']];
    }
}

$filas_titulo = array();
$cols_titulo = array();
function p_cargador(& $nodo, $nivel = 0, $nombre_previo = '') {
    global $filas_titulo;
    global $cols_titulo;
    global $profundidad_maxima;

    foreach($nodo as $k=>  $hijo){
        //echo "<div class='alert alert-info'>";
        //echo "[{$hijo[esa_nombre]} $nivel]";

        $nodo[$k]['nivel'] = $nivel;
        $rowspan = 1;
        $colspan = $hijo['num_hojas'];
        $nombre_completo = $nombre_previo . ' >> '. $hijo['esa_nombre'];

        $t = 'td';
        if (!empty($nodo[$k]['hijos'])) {
            //no es hoja, cuenta los hijos:
            p_cargador($nodo[$k]['hijos'], $nivel + 1, $nombre_completo);
            $t = 'th';
        } else {
            //es hoja
            $rowspan = 1+$profundidad_maxima - $nivel;

        }
        //echo "</div>";
        //
        if(!isset($filas_titulo[$nivel])) {
            $filas_titulo[$nivel] = '';
        }
        $filas_titulo[$nivel] .= "<$t colspan='$colspan' rowspan='$rowspan'><span id='col_titulo_{$hijo[esa_id]}' title='$nombre_completo'>{$hijo[esa_nombre]} </span></$t>";

        $primera_hoja = $hijo['primera_hoja'];
        if (!isset($cols_titulo[$primera_hoja])) {
            $cols_titulo[$primera_hoja] = array();
        }
        $cols_titulo[$primera_hoja][$nivel] = "<$t colspan='$rowspan' rowspan='$colspan'><span id='fila_titulo_{$hijo[esa_id]}' title='$nombre_completo'>{$hijo[esa_nombre]} </span></$t>";
    }
}
p_cargador($tree);
//var_dump($filas_titulo);
//echo "<pre>";
//var_dump($cols_titulo);
//echo "</pre>";
echo '<table class="table table-striped table-condensed table-hover table-bordered">';

echo '<thead>';


function p_cols($hijos){
    global $cols_titulo;
    foreach($hijos as $hijo){
        if (!empty($hijo['hijos'])) {
            p_cols($hijo['hijos']);
        } else {
            //es hoja
            $celdas = $cols_titulo[$hijo['esa_id']];
            echo "<tr>";
            ksort($celdas);

            foreach ($celdas as $celda) {
                echo $celda;
            }

            foreach ($cols_titulo as $esa_id => $celda){
                $x = $hijo[esa_id];
                $y = $esa_id;

                if ($x == $y) {
                    echo "<td style='background-color:#000;'>&nbsp;</td>";
                } else {
                    echo "<td class='text-center' id='celda_{$x}_{$y}'><a href='#' title='X' onmouseover='p_mostrar_desde_hacia($x, $y, this)' onclick='p_abrir($x, $y);return false;'><span class='glyphicon glyphicon-cog' aria-hidden='true'></span></a></td>";
                }
            }
            echo "</tr>";
        }
    }
}
ksort($filas_titulo);
//ksort($cols_titulo);
$esquina = true;
foreach($filas_titulo as $nivel => $fila_titulo) {
    echo "<tr>";
    if ($esquina) {
        $dimension_esquina = 1 + $profundidad_maxima;
        echo "<th colspan='$dimension_esquina' rowspan='$dimension_esquina'>&nbsp;</th>";
        $esquina = false;
    }
    echo $fila_titulo;


    echo "</tr>";
}
echo '</thead>';
echo '<tbody>';


p_cols($tree);
/*
foreach($cols_titulo as $celdas) {

    $estado = $estados[$esa_id];
    echo '<tr>';
    ksort($celdas);
    foreach($celdas as $celda){
        echo $celda;
    }

    echo '</tr>';
}
 */

/*
foreach($estados as $estado){
    echo '<tr>';
    $label = $estado['esa_nombre'].' | '.$estado['num_hojas'].' | '.$estado['profundidad'].' | '.$estado['nivel'];
    echo "<th>$label</th>";

    foreach($estados as $estado2){
        if ($estado2['esa_id'] == $estado['esa_id']) {
            echo "<td style='background-color:#000;'>&nbsp;</td>";
        } else {
            echo "<td><a href='#' onclick=''><img src='/img/no.png' style='width:20px;height:20px;'></a></td>";
        }
    }
    echo '</tr>';
}
 */
echo '</tbody></table>';


$servicios = q("SELECT * FROM sai_servicio");

///////////////////////
//Pertinencias provedor:
//
$proveedor_generico = array(array('pep_servicio' => '0', 'ser_nombre' => 'Servicio General', 'pro_razon_social'=>'Para todos los proveedores', 'pro_id' => 0));

$pertinencias_proveedor = array(0=>$proveedor_generico);
foreach($servicios as $servicio){
    $pertinencias_proveedor[$servicio['ser_id']] = $proveedor_generico; 
}
$result = q("SELECT *  FROM sai_pertinencia_proveedor, sai_proveedor WHERE pro_id = pep_proveedor");

if ($result) {
    foreach ($result as $r) {
        $pertinencias_proveedor[$r['pep_servicio']][] = $r;
    }
}

///////////////////////
//Pertinencias usuario:
//
$pertinencias_usuario = array();
$result = q("SELECT * FROM sai_pertinencia_usuario, sai_usuario, sai_servicio WHERE usu_id = peu_usuario AND ser_id=peu_servicio");

if ($result) {
    foreach($result as $r) {
        $ser_id = $r['ser_id'];
        $usu_id = $r['usu_id'];

        if (!isset($pertinencias_usuario[$ser_id])) {
            $pertinencias_usuario[$ser_id] = array();
        }
        $pertinencias_usuario[$ser_id][$usu_id] = $r;
    }
}

$num_formulario = 0;

//echo '<pre>';
//var_dump($pertinencias_proveedor);
//echo '</pre>';
?>

<div id="modal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Transición <span id="formulario_titulo"></span></h4>
        <h4 class="modal-title">DESDE: <span id="formulario_titulo_desde"></span></h4>
        <h4 class="modal-title">HACIA: <span id="formulario_titulo_hacia"></span></h4>
      </div>
      <div class="modal-body">


<div>

  <!-- Nav tabs -->
  <ul class="nav nav-tabs" role="tablist">
    <?php //$servicios = array_merge(array(array('ser_id'=>'0', 'ser_nombre'=>'todos los servicios')), $servicios); ?>
    <?php foreach($servicios as $k => $servicio): ?>
    <li role="presentation" <?php if($k==0): ?>class="active"<?php endif; ?>><a href="#tab_servicio_<?=$servicio['ser_id']?>" aria-controls="tab_servicio_<?=$servicio['ser_id']?>" role="tab" data-toggle="tab"><?=$servicio['ser_nombre']?></a></li>
    <?php endforeach; ?>
  </ul>
  <!-- EBD Nav tabs -->

  <!-- Tab panes -->
  <div class="tab-content">
  <?php foreach($servicios as $k => $servicio): ?>
    <div role="tabpanel" class="tab-pane fade <?php if($k==0): ?>in active<?php endif; ?>" id="tab_servicio_<?=$servicio['ser_id']?>">
      <div>&nbsp;</div>


      <?php if(!empty($pertinencias_proveedor[$servicio['ser_id']])): ?>
<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
<?php foreach($pertinencias_proveedor[$servicio['ser_id']] as $kp => $proveedor): ?><?php $kp++;?>
<?php
//echo '<pre>';
//var_dump($servicio);
//var_dump($pertinencias_proveedor);
//var_dump($proveedor);
//echo '</pre>';
?>
<div class="panel panel-default">
  <div class="panel-heading" role="tab" id="panelHeading_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>">
    <h4 class="panel-title">
      <a <?=$kp==0?'':'class="collapsed"'?> role="button" data-toggle="collapse" data-parent="#accordion" href="#panelCollapse_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>" aria-expanded="<?=$kp==0?'true':'false'?></true>" aria-controls="#panelCollapse_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>"> 
        <?=$proveedor['pro_razon_social']?>
        <?=$proveedor['pep_servicio']==0?' de '.$servicio['ser_nombre']:''?>
      </a>
    </h4>
  </div>

<div id="panelCollapse_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>" class="panel-collapse collapse <?=$kp==0?'in':''?>" role="tabpanel" aria-labelledby="panelHeading_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>">
  <div class="panel-body">

  <?php foreach(array('proveedor', 'cliente', 'usuario') as $destinatario): ?>
  <h3>Acciones para <?=$destinatario?> (<?=$proveedor['pro_razon_social']?>)</h3>
<form id="formulario" class="form-horizontal" onsubmit="p_guardar(this);return false;" enctype="multipart/form-data">
<input type="hidden" id="desde" name="desde" value="">
<input type="hidden" id="hacia" name="hacia" value="">
<input type="hidden" id="ser_id" name="ser_id" value="<?=$servicio['ser_id']?>">
<input type="hidden" id="pro_id" name="pro_id" value="<?=$proveedor['pro_id']?>">
<input type="hidden" id="destinatario" name="destinatario" value="<?=$destinatario?>">
  <?php if($servicio['ser_id'] != 0): ?>
  <div class="form-group">
    <label for="asunto" class="col-sm-2 control-label">Responsable:</label>
    <div class="col-sm-10">
      <select class="form-control" id="usuario_responsable" name="usuario_responsable">
      <option></option>
<?php
if (isset($pertinencias_usuario[$servicio['ser_id']])) {
    foreach($pertinencias_usuario[$servicio['ser_id']] as $pertinencia_usuario) {
        $valor = $pertinencia_usuario['peu_id'];
        $etiqueta = $pertinencia_usuario['usu_nombres'] . ' ' . $pertinencia_usuario['usu_apellidos'];
        echo "<option value='$valor'>$etiqueta</option>";
    }
}

?>
      </select>
    </div>
  </div>
  <?php endif; ?>

  <div class="form-group">
    <label for="automatico" class="col-sm-2 control-label">Automático:</label>
    <div class="col-sm-10">
      <input type="checkbox" class="form-control" id="automatico" name="automatico" placeholder="">
    </div>
  </div>
  <div class="form-group">
    <label for="tiempo_alerta_horas" class="col-sm-2 control-label">Tiempo alerta horas:</label>
    <div class="col-sm-10">
      <input type="number" class="form-control" id="tiempo_alerta_horas" name="tiempo_alerta_horas" placeholder="">
    </div>
  </div>
  <div class="form-group">
    <label for="asunto" class="col-sm-2 control-label">Asunto:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="asunto" name="asunto" placeholder="Asunto">
    </div>
  </div>
  <div class="form-group">
    <label for="cuerpo" class="col-sm-2 control-label">Cuerpo:</label>
    <div class="col-sm-10">
      <textarea type="text" class="form-control" id="cuerpo" name="cuerpo" placeholder="Cuerpo"></textarea>
    </div>
  </div>
  <div class="form-group">
    <label for="plantilla_adjunto" class="col-sm-2 control-label">Plantilla adjunto:</label>
    <div class="col-sm-10">
      <textarea type="text" class="form-control" id="plantilla_adjunto" name="plantilla_adjunto" placeholder="Plantilla de adjunto"></textarea>
    </div>
  </div>
  <div class="form-group">
    <label for="archivo-adjunto" class="col-sm-2 control-label">Adjunto:</label>
    <div class="col-sm-10">
      <input type="file" class="form-control" id="archivo-adjunto" name="archivo-adjunto" placeholder="Archivo adjunto">
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-2">&nbsp;</div>
    <div class="col-sm-10">
    <button class="btn btn-info" onclick="//p_guardar('<?="formulario_"?>')">Guardar</button>
    </div>
  </div>
</form>
<hr />
<strong>CAMPOS:</strong>
<hr />

    <?php endforeach; ?>
  </div>
</div>
</div>
    <?php endforeach; ?>
</div>
<?php elseif($servicio['ser_id'] == 0): ?>
GENERAL
<?php else: ?>
<div class="panel panel-default">
  <div class="panel-heading">
  <h3 class="panel-title">Sin proveedores</h3>
  </div>
  <div class="panel-body">
No hay proveedores registrados para este servicio.
  </div>
</div>
    <?php endif; ?>

<hr>
<hr>
<hr>
<hr>

    </div>

    <?php endforeach; ?>
  </div>
  <!-- END Tab panes -->
</div>



      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
        <!--button type="button" class="btn btn-success" onclick="p_guardar()" id="formulario_guardar">Guardar cambios</button-->
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script>
var desde=0, hacia=0;
function p_abrir(x, y){
    //alert(x+' - '+ y);
    var col_titulo_x = $('#col_titulo_' + x).attr('title');
    var fila_titulo_y = $('#fila_titulo_' + y).attr('title');
    $('#formulario_titulo_desde').text(col_titulo_x);
    $('#formulario_titulo_hacia').text(fila_titulo_y);
    desde = x;
    hacia = y;
    $('#modal').modal('show');
}

function p_mostrar_desde_hacia(x, y, target) {
    var col_titulo_x = $('#col_titulo_' + x).attr('title');
    var fila_titulo_y = $('#fila_titulo_' + y).attr('title');
    target.title = 'DESDE: ' + col_titulo_x + '\n' + 'HACIA: ' + fila_titulo_y;
}

function p_guardar(target) {

    /*
            var respuestas_json = $('#formulario').serializeArray();

            console.log('respuestas json', respuestas_json);
            dataset_json = {};
            respuestas_json.forEach(function(respuesta_json){
                var name =  respuesta_json['name'];
                var value = respuesta_json['value'];
                dataset_json[name] = value;

            });

            console.log('dataset_json', dataset_json);
     */
    $(target).find('input[name=desde]').val(desde);
    $(target).find('input[name=hacia]').val(hacia);
            var fd = new FormData(target);
            $.ajax({
                url: '_guardarTransicion',
                    type: 'POST',
                    //dataType: 'json',
                    //data: JSON.stringify(dataset_json),
                    //contentType: 'application/json'
                     processData: false,
                     contentType: false,
                     cache: false,
                     data: fd
            }).done(function(data){
                console.log('Guardado OK, data:', data);
                //data = eval(data)[0];
                data = JSON.parse(data);
                data = data[0];

                console.log('eval data:', data);
                if (data['ERROR']) {
                    alert(data['ERROR']);
                } else {
                    console.log('nueva TRANSICION');
                    var desde = data['tea_estado_atencion_padre'];
                    var hacia = data['tea_estado_atencion_hijo'];
                    var celda_id = '#celda_' + desde + '_'+ hacia;
                    console.log('id de celda:', celda_id, $(celda_id));
                    $(celda_id).removeClass('alert alert-success alert-info alert-danger');
                    $(celda_id).addClass('alert alert-success');

                    $('#modal').modal('hide');
                }
            }).fail(function(xhr, err){
                console.error('ERROR AL GUARDAR', xhr, err);
                alert('Hubo un error al guardar, verifique que cuenta con Internet y vuelva a intentarlo en unos momentos.');
                //$('#modal').modal('hide');
            });
}
</script>
