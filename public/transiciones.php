<script src="/js/ckeditor/ckeditor.js"></script>
<h1>Transiciones</h1>
<?php
$result = q("SELECT * FROM sai_destinatario");
$destinatarios = array();
if ($result) {
    foreach($result as $r) {
        $destinatarios[$r['des_id']] = $r['des_nombre'];
    }
}

$result = q("
    SELECT
    COUNT(*),
    tea_estado_atencion_actual,
    tea_estado_atencion_siguiente
    FROM sai_transicion_estado_atencion
    WHERE tea_borrado IS NULL
    GROUP BY tea_estado_atencion_actual, tea_estado_atencion_siguiente
    ");
$transiciones = array();
foreach($result as $r){
    $padre = $r[tea_estado_atencion_actual];
    $hijo = $r[tea_estado_atencion_siguiente];

    if(!isset($transiciones[$padre])) {
        $transiciones[$padre] = array();
    }
    $transiciones[$padre][$hijo] = $r['count'];
}

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
        WHERE tea_estado_atencion_actual = esa_id
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

        $css_class = (empty($hijo[esa_borrado])) ? '' : 'class="alert alert-danger"';
        $filas_titulo[$nivel] .= "<$t colspan='$colspan' rowspan='$rowspan' $css_class><span id='col_titulo_{$hijo[esa_id]}' title='$nombre_completo'>{$hijo[esa_nombre]} </span></$t>";

        $primera_hoja = $hijo['primera_hoja'];
        if (!isset($cols_titulo[$primera_hoja])) {
            $cols_titulo[$primera_hoja] = array();
        }
        $cols_titulo[$primera_hoja][$nivel] = "<$t colspan='$rowspan' rowspan='$colspan' $css_class><span id='fila_titulo_{$hijo[esa_id]}' title='$nombre_completo'>{$hijo[esa_nombre]} </span></$t>";
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
    global $cols_titulo, $transiciones;
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
                    echo "<td style='background-color:#000;color:#999;'>";
                    echo htmlspecialchars(print_r($hijo[esa_nombre], true));
                    echo "</td>";
                } else {

                    $count_transicion = (isset($transiciones[$x]) && isset($transiciones[$x][$y])) ? $transiciones[$x][$y] : 0;

                    $style_class = ($count_transicion == 0) ? '' : 'alert alert-info';
                    $contenido = ($count_transicion == 0) ? '' : ' ' . n2t($count_transicion);
                    echo "<td class='text-center {$style_class}' id='celda_{$x}_{$y}'><a href='#' title='X' onmouseover='p_mostrar_desde_hacia($x, $y, this)' onclick='p_abrir($x, $y);return false;'><span class='glyphicon glyphicon-cog' aria-hidden='true'></span>$contenido</a></td>";
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
  <div class="modal-dialog modal-lg" role="document">
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
      <li role="presentation" <?php if($k==0): ?>class="active"<?php endif; ?>>
        <a href="#tab_servicio_<?=$servicio['ser_id']?>" aria-controls="tab_servicio_<?=$servicio['ser_id']?>" role="tab" data-toggle="tab">
          <?=$servicio['ser_nombre']?> <span class="badge badge-servicio" id="badge_servicio_<?=$servicio['ser_id']?>">0</span>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
  <!-- EBD Nav tabs -->

  <!-- Tab panes -->
  <div class="tab-content">
  <?php foreach($servicios as $k => $servicio): ?>
    <div role="tabpanel" class="tab-pane fade <?php if($k==0): ?>in active<?php endif; ?>" id="tab_servicio_<?=$servicio['ser_id']?>">
      <div>&nbsp;</div>


      <?php if(!empty($pertinencias_proveedor[$servicio['ser_id']])): ?>
<div class="panel-group" id="accordion_<?=$k?>" role="tablist" aria-multiselectable="true">
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
      <a <?=$kp==0?'':'class="collapsed"'?> role="button" data-toggle="collapse" data-parent="#accordion_<?=$k?>" onclick="p_cargar_detalle_transicion(<?=$servicio['ser_id']?>, <?=$proveedor['pro_id']?>)" href="#panelCollapse_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>" aria-expanded="<?=$kp==0?'true':'false'?>" aria-controls="#panelCollapse_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>"> 
        <?=$proveedor['pro_razon_social']?>
        <?=$proveedor['pep_servicio']==0?' de '.$servicio['ser_nombre']:''?>
        <span class="badge-proveedor" id="badge_proveedor_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>"></span>
      </a>
    </h4>
  </div>

<div id="panelCollapse_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>" class="panel-collapse collapse <?=$kp==0?'in':''?>" role="tabpanel" aria-labelledby="panelHeading_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>">
  <div class="panel-body">




<ul class="nav nav-tabs navbar-right" role="tablist">
  <?php $first=true;foreach($destinatarios as $des_id => $destinatario): ?>
  <li role="presentation" <?=$first?'class="active"':''?>>
    <a href="#tab_destinatario_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>_<?=$des_id?>" aria-controls="tab_destinatario_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>_<?=$des_id?>" role="tab" data-toggle="tab">
      <?=ucfirst($destinatario)?>
    </a>
  </li>
  <?php $first=false;endforeach; ?>
</ul>






  <div class="tab-content">
  <?php $first=true;foreach($destinatarios as $des_id => $destinatario): ?>
  <div role="tabpanel" class="tab-pane fade <?php if($first): ?>in active<?php endif; ?>" id="tab_destinatario_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>_<?=$des_id?>">
  <h3>Acciones para <?=$destinatario?> (<?=$proveedor['pro_razon_social']?>)</h3>
<form id="formulario" class="form-horizontal" onsubmit="p_guardar(this);return false;" enctype="multipart/form-data">
<input type="hidden" id="desde_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>_<?=$des_id?>" name="desde" value="">
<input type="hidden" id="hacia_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>_<?=$des_id?>" name="hacia" value="">
<input type="hidden" id="ser_id_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>_<?=$des_id?>" name="ser_id" value="<?=$servicio['ser_id']?>">
<input type="hidden" id="pro_id_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>_<?=$des_id?>" name="pro_id" value="<?=$proveedor['pro_id']?>">
<input type="hidden" id="destinatario_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>_<?=$des_id?>" name="destinatario" value="<?=$destinatario?>">
<input type="hidden" id="des_id_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>_<?=$des_id?>" name="des_id" value="<?=$des_id?>">
<input type="hidden" id="tea_id_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>_<?=$des_id?>" name="tea_id" value="<?=$des_id?>">
  <?php if($servicio['ser_id'] != 0): ?>
  <div class="form-group">
    <label for="asunto" class="col-sm-2 control-label">Responsable:</label>
    <div class="col-sm-10">
      <select class="form-control" id="usuario_responsable_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>_<?=$des_id?>" name="usuario_responsable">
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
      <input type="checkbox" class="form-control" id="automatico_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>_<?=$des_id?>" name="automatico" placeholder="">
    </div>
  </div>
  <div class="form-group">
    <label for="tiempo_alerta_horas" class="col-sm-2 control-label">Tiempo alerta horas:</label>
    <div class="col-sm-10">
      <input type="number" class="form-control" id="tiempo_alerta_horas_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>_<?=$des_id?>" name="tiempo_alerta_horas" placeholder="">
    </div>
  </div>
  <div class="form-group">
    <label for="asunto" class="col-sm-2 control-label">Asunto:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="asunto_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>_<?=$des_id?>" name="asunto" placeholder="Asunto">
    </div>
  </div>
  <div class="form-group">
    <label for="cuerpo" class="col-sm-2 control-label">Cuerpo:</label>
    <div class="col-sm-10">
      <textarea type="text" class="form-control" id="cuerpo_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>_<?=$des_id?>" name="cuerpo" placeholder="Cuerpo"></textarea>

    </div>
  </div>
  <div class="form-group">
    <label for="adjunto_nombre" class="col-sm-2 control-label">Nombre del archivo adjunto:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="adjunto_nombre_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>_<?=$des_id?>" name="adjunto_nombre" placeholder="Nombre del archivo adjunto">
    </div>
  </div>
  <div class="form-group">
    <label for="adjunto_texto" class="col-sm-2 control-label">Plantilla adjunto:</label>
    <div class="col-sm-10">
      <textarea type="text" class="form-control" id="adjunto_texto_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>_<?=$des_id?>" name="adjunto_texto" placeholder="Plantilla de adjunto"></textarea>
    </div>
  </div>
  <div class="form-group">
    <label for="archivo-adjunto" class="col-sm-2 control-label">Adjunto:</label>
    <div class="col-sm-10">
      <input type="file" class="form-control" id="archivo-adjunto_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>_<?=$des_id?>" name="archivo-adjunto" placeholder="Archivo adjunto">
      <div id="archivos_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>_<?=$des_id?>" class="col-sm-10">
      </div>
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-2">&nbsp;</div>
    <div class="col-sm-10">
    <button class="btn btn-info" onclick="//p_guardar('<?="formulario_"?>')">Guardar</button>
    <button class="btn btn-danger" onclick="p_eliminar(<?=$servicio['ser_id']?>, <?=$proveedor['pro_id']?>, <?=$des_id?>)" type="button">Eliminar</button>
    </div>
  </div>
</form>
<hr />
<strong>CAMPOS:</strong>
<form id="formulario_cae_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>_<?=$des_id?>" class="form-horizontal">
  <div class="form-group">
    <label for="campo" class="col-sm-2 control-label">Campo:</label>
    <div class="col-sm-8">
      <input type="hidden" id="campo_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>_<?=$des_id?>" name="campo" value="">
      <input class="form-control" required type="text" id="campo_typeahead_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>_<?=$des_id?>" data-provide="typeahead" autocomplete="off" placeholder="Ingrese al menos 2 caracteres" onblur="p_validar_campo()">
    </div>
    <div class="col-sm-1">
      <button type="button" class="btn btn-info" id="campo_agregar_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>_<?=$des_id?>" onclick="p_guardar_campo('<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>_<?=$des_id?>')"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span></button>
    </div>
  </div>
</form>

<table class="table table-striped">
<tbody id="antiguos_cae_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>_<?=$des_id?>"></tbody>
</table>

<hr />

</div><!-- tab-pane -->
    <?php $first=false;endforeach; ?>


  </div><!-- tab-content -->

  </div><!-- panel-body  -->





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

<script src="/js/bootstrap3-typeahead.min.js"></script>
<script>
$( document ).ready(function() {
    $('textarea').each(function(){
         CKEDITOR.replace(this);
    });
});
var desde=0, hacia=0;


function p_validar_campo(){
    console.log('on blur campo');
    if ($('#campo').val() == ''){
        $('#campo_typeahead').val('');
    }
}

function p_guardar_campo(id){
    if ($('#campo_'+id).val() !== '') {
        var respuestas_json = $('#formulario_cae_'+id).serializeArray();
        console.log('respuestas json', id, respuestas_json);
        dataset_json = {};
        dataset_json['campo'] = $('#campo_'+id).val();
        dataset_json['transicion'] = $('#tea_id_'+id).val();

        console.log('dataset_json', dataset_json);
        $.ajax({
        url: '_guardarCampo',
            type: 'POST',
            //dataType: 'json',
            data: JSON.stringify(dataset_json),
            //contentType: 'application/json'
        }).done(function(data){
            console.log('Guardado OK', data);
            data = JSON.parse(data);
            data = data[0];
            console.log('eval data:', data);
            if (data['ERROR']) {
                alert(data['ERROR']);
            } else {
                console.log('nuevo campo');
                var partes = id.split('_');
                var current_ser_id = partes[0];
                var current_pro_id = partes[1];
                console.log('current', current_ser_id, current_pro_id);
                p_cargar_detalle_transicion(current_ser_id, current_pro_id);
            }
            $('#campo_agregar_'+id).hide();
            $('#campo_'+id).val('');
            $('#campo_typeahead_'+id).val('');
        }).fail(function(xhr, err){
            console.error('ERROR AL GUARDAR', xhr, err);
            alert('Hubo un error al guardar, verifique que cuenta con Internet y vuelva a intentarlo en unos momentos.');
            //$('#modal').modal('hide');
        });
    } else {
        alert ('Ingrese el nombre del campo');
    }
}

function p_borrar_campo(id, campo) {
    console.log('FUNCTION p_borrar_campo: ', id, campo);
    if (confirm('Seguro desea quitar este campo a la transición?')) {
        dataset_json = {};
        dataset_json['campo'] = campo;
        dataset_json['transicion'] = $('#tea_id_'+id).val();
        dataset_json['borrar'] = 'borrar';

        console.log('dataset_json', dataset_json);
        $.ajax({
        url: '_guardarCampo',
            type: 'POST',
            //dataType: 'json',
            data: JSON.stringify(dataset_json),
            //contentType: 'application/json'
        }).done(function(data){
            console.log('Borrado OK, data:', data);
            //data = eval(data)[0];
            data = JSON.parse(data);
            data = data[0];
            console.log('eval data:', data);
            if (data['ERROR']) {
                alert(data['ERROR']);
            } else {
                $('#cae_' + data['id']).remove();
            }
        }).fail(function(xhr, err){
            console.error('ERROR AL BORRAR', xhr, err);
            alert('Hubo un error al borrar, verifique que cuenta con Internet y vuelva a intentarlo en unos momentos.');
            //$('#modal').modal('hide');
        });
    }
}

function p_cargar_detalle_transicion(ser_id, pro_id){
    console.log(desde + '->'+ hacia, ser_id, pro_id);
    $.get('/_obtenerDetalleTransicion/'+desde+'/'+hacia+'/'+ser_id+'/'+pro_id, function(data){
        data = JSON.parse(data);
        console.log('RESPUESTA: ', data);
        data.forEach(function(transicion){
            var des_id = transicion['tea_destinatario'];
            console.log(ser_id+'_'+pro_id+'_'+des_id);
            $('#tea_id_'+ser_id+'_'+pro_id+'_'+des_id).val(transicion['tea_id']);
            $('#usuario_responsable_'+ser_id+'_'+pro_id+'_'+des_id).val(transicion['tea_pertinencia_usuario']);
            $('#automatico_'+ser_id+'_'+pro_id+'_'+des_id).prop('checked', transicion['tea_automatico'] == '1');
            $('#tiempo_alerta_horas_'+ser_id+'_'+pro_id+'_'+des_id).val(transicion['tea_tiempo_alerta_horas']);
            $('#asunto_'+ser_id+'_'+pro_id+'_'+des_id).val(transicion['pla_asunto']);
            $('#adjunto_nombre_'+ser_id+'_'+pro_id+'_'+des_id).val(transicion['pla_adjunto_nombre']);
            CKEDITOR.instances['cuerpo_'+ser_id+'_'+pro_id+'_'+des_id].setData(transicion['pla_cuerpo']);
            CKEDITOR.instances['adjunto_texto_'+ser_id+'_'+pro_id+'_'+des_id].setData(transicion['pla_adjunto_texto']);
            console.log('#usuario_responsable_'+ser_id+'_'+pro_id+'_'+des_id);
            p_actualizar_archivos(transicion['archivos'], ser_id+'_'+pro_id+'_'+des_id);
            p_actualizar_campos(transicion['campos'], ser_id+'_'+pro_id+'_'+des_id);
            p_inicializar_autocompletar(ser_id+'_'+pro_id+'_'+des_id)
        });
    });
}

function p_inicializar_autocompletar(id){
    $('#campo_typeahead_'+id).typeahead({
        source:function(query, process){
            $.get('/_listarCampos/' + id + '/' + query, function(data){
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
            $('#campo_'+id).val(item.id);
            $('#campo_agregar_'+id).show();
            return item.name;
        }
    });
}

function p_actualizar_campos(campos, id) {
    console.log('p_actualizar_campos', campos, id);
    var tbody_id = '#antiguos_cae_' + id;
    $(tbody_id).html('');
    if (Array.isArray(campos)) {
        campos.forEach(function(campo){
            var numero = $(tbody_id).children().length + 1;
            //var nombre = $('#campo_typeahead_'+id).val();
            var nombre = campo['cae_texto'] + ' ('+campo['cae_codigo']+')';
            $(tbody_id).append('<tr class="alert alert-info" id="cae_'+campo['cae_id']+'"><th>'+numero+'.</th><td><span id="nombre_cae_'+campo['cae_id']+'">'+nombre+'</span></td><td><button class="btn btn-danger" onclick="p_borrar_campo(\''+id+'\','+campo['cae_id']+')"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></button></td></tr>');
        });
    }
}

function p_actualizar_archivos(archivos, id) {
    console.log('p_actualizar_archivos', archivos, id);
    var div_id = '#archivos_' + id;
    $(div_id).html('');
    if (Array.isArray(archivos)) {
        archivos.forEach(function(archivo){
            $(div_id).append('<div><a class="btn btn-default" href="/'+archivo['arc_ruta']+'" target="_blank">'+archivo['arc_nombre']+'</a></div>');
        });
    }
}

function p_abrir(x, y){
    console.log('p_abrir', x, y);
    //alert(x+' - '+ y);
    var col_titulo_x = $('#col_titulo_' + x).attr('title');
    var fila_titulo_y = $('#fila_titulo_' + y).attr('title');
    $('#formulario_titulo_desde').text(col_titulo_x);
    $('#formulario_titulo_hacia').text(fila_titulo_y);
            //$('#campo_agregar_'+id).hide();

    console.log(1);
    $('#modal').find(':input').each(function() {
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
            $(this).val('');
            break;
        case 'checkbox':
        case 'radio':
            this.checked = false;
            break;
        }
    });
    console.log(2);
    $('#modal').find('.panel-collapse.in').each(function() {
        $(this).collapse('hide');
    });
    console.log(3);
    for ( instance in CKEDITOR.instances ) {
        CKEDITOR.instances[instance].setData('');
    }
    desde = x;
    hacia = y;


    //$('#campo_agregar_'+id).hide();
    console.log(4);
    $('.badge-servicio').each(function(){
        $(this).text(0);
        $(this).hide();
    });
    $('.badge-proveedor').each(function(){
        $(this).html('');
    });
    console.log(5);
    $.get('_obtenerTransicionResumen/'+desde+'/'+hacia, function(data){
        console.log(data);
        data = JSON.parse(data);
        console.log('_obtenerTransicionResumen/'+desde+'/'+hacia, data);
        data.forEach(function(d, k){
            var ser_id = d['ser_id'];
            var pro_id = d['pro_id'];
            var tea_id = d['tea_id'];

            var count;

            $('#badge_servicio_'+ser_id).show();
            count = parseInt($('#badge_servicio_'+ser_id).text());
            $('#badge_servicio_'+ser_id).text(count+1);

            $('#badge_proveedor_'+ser_id+'_'+pro_id).append('<span class="badge" id="nuevabadge_'+tea_id+'">'+d['destinatario']+'</span>');
        });
    });
    $('#modal').modal('show');
}

function p_mostrar_desde_hacia(x, y, target) {
    var col_titulo_x = $('#col_titulo_' + x).attr('title');
    var fila_titulo_y = $('#fila_titulo_' + y).attr('title');
    target.title = 'DESDE: ' + col_titulo_x + '\n' + 'HACIA: ' + fila_titulo_y;
}

function p_eliminar(ser_id, pro_id, des_id){
    if (confirm('Seguro desea eliminar esta acción de transición?')) {
        var tea_id = $('#tea_id_'+ser_id+'_'+pro_id+'_'+des_id).val();
        console.log('tea_id', tea_id);
        if (tea_id != null && tea_id != '') {
            $.get('/_eliminarTransicion/'+tea_id, function(data){
                console.log('Respuesta de eliminada:', data);
                data = JSON.parse(data);
                console.log('eliminando transicion:', data);
                $('#nuevabadge_' + tea_id).remove();
            });
        } else {
            alert('No se encuentra registrada la acción de la transición, no se ha eliminado nada.');
        }
    }
}

function p_guardar(target) {

    for ( instance in CKEDITOR.instances ) {
        CKEDITOR.instances[instance].updateElement();
    }
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
    //console.log('p_guardar fd: ', fd);
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
            var desde = data['tea_estado_atencion_actual'];
            var hacia = data['tea_estado_atencion_siguiente'];
            var destinatario = data['tea_estado_atencion_siguiente'];
            var celda_id = '#celda_' + desde + '_'+ hacia;
            console.log('id de celda:', celda_id, $(celda_id));
            $(celda_id).removeClass('alert alert-success alert-info alert-danger');
            $(celda_id).addClass('alert alert-success');
            //var id = desde + '_'+ hacia + '_' + data['tea_destinatario'];
            //p_actualizar_archivos(data['archivos'], id);

            //$('#modal').modal('hide');
        }
    }).fail(function(xhr, err){
        console.error('ERROR AL GUARDAR', xhr, err);
        alert('Hubo un error al guardar, verifique que cuenta con Internet y vuelva a intentarlo en unos momentos.');
        //$('#modal').modal('hide');
    });
}
</script>
