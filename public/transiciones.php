<script src="/js/ckeditor/ckeditor.js"></script>
<style>
.table-hoverCell > tbody > tr > td:hover {
  background-color: #f5f5f5;
}
table col[class*="col-"] {
  position: static;
  display: table-column;
  float: none;
}
</style>
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
    ,sai_pertinencia_proveedor
    WHERE tea_borrado IS NULL
    AND pep_borrado IS NULL
    AND tea_pertinencia_proveedor = pep_id
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
        WHERE t.esa_borrado IS NULL
        AND t.esa_padre=esa_id
    ) AS count_hijos
    ,(
        SELECT 
        COUNT(*)
        FROM sai_transicion_estado_atencion
        WHERE tea_borrado IS NULL
        AND tea_estado_atencion_actual = esa_id
    ) AS count_transicion
    FROM sai_estado_atencion 
    WHERE esa_borrado IS NULL
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
        if (!empty($nodo['padre']['hijos']) && $nodo['esa_id'] == array_keys($nodo['padre']['hijos'])[0]) {
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
echo '<table class="table table-condensed table-hover table-hoverCell table-bordered">';

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
                    //$contenido = ($count_transicion == 0) ? '' : ' ' . n2t($count_transicion);
                    $contenido = ($count_transicion == 0) ? '' : ' ' . ($count_transicion);
                    $contenido = "<span class='badge' id='badge_transicion_{$x}_{$y}'>$contenido</span>";
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


$servicios = q("
    SELECT * 
    FROM sai_servicio
    WHERE ser_borrado IS NULL
");

///////////////////////
//Pertinencias provedor:
//
/*
$proveedor_generico = array(array('pep_servicio' => '0', 'ser_nombre' => 'Servicio General', 'pro_nombre_comercial'=>'Para todos los proveedores', 'pro_id' => 0));

$pertinencias_proveedor = array(0=>$proveedor_generico);
 */

foreach($servicios as $servicio){
    $pertinencias_proveedor[$servicio['ser_id']] = $proveedor_generico; 
}
$result = q("
    SELECT * 
    FROM sai_pertinencia_proveedor
    , sai_proveedor 
    WHERE pep_borrado IS NULL
    AND pro_borrado IS NULL
    AND pro_id = pep_proveedor
");

if ($result) {
    foreach ($result as $r) {
        $pertinencias_proveedor[$r['pep_servicio']][] = $r;
    }
}

///////////////////////
//Pertinencias usuario:
//
$usuarios = array();
$result = q("
    SELECT *
    FROM sai_usuario
    ,sai_rol
    WHERE usu_borrado IS NULL
    AND rol_borrado IS NULL
    AND usu_rol = rol_id
");

if ($result) {
    foreach($result as $r) {
        $usuarios[$r[usu_id]] = $r;
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
        <?=$proveedor['pro_nombre_comercial']?>
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
  <h3>Acciones para <?=$destinatario?> (<?=$proveedor['pro_nombre_comercial']?>)</h3>
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
      <select class="form-control select2" id="usuario_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>_<?=$des_id?>" name="usuario">
      <option></option>
<?php
    foreach($usuarios as $usu_id => $usuario) {
        $valor = $usu_id;
        $etiqueta = $usuario['usu_nombres'] . ' ' . $usuario['usu_apellidos'] . " ({$usuario[rol_nombre]})";
        echo "<option value='$valor'>$etiqueta</option>";
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
    <label for="adjunto_nombre" class="col-sm-2 control-label">Nombre del PDF adjunto:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="adjunto_nombre_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>_<?=$des_id?>" name="adjunto_nombre" placeholder="Nombre del archivo adjunto">
    </div>
  </div>
  <div class="form-group">
    <label for="adjunto_texto" class="col-sm-2 control-label">Plantilla del PDF adjunto:</label>
    <div class="col-sm-10">
      <textarea type="text" class="form-control" id="adjunto_texto_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>_<?=$des_id?>" name="adjunto_texto" placeholder="Plantilla de adjunto"></textarea>
    </div>
  </div>
  <div class="form-group">
    <label for="archivo_adjunto" class="col-sm-2 control-label">Plantilla de archivo adjunto:</label>
    <div class="col-sm-10">
      <input type="file" class="form-control" id="archivo_adjunto_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>_<?=$des_id?>" name="archivo_adjunto" placeholder="Archivo adjunto">
      <div id="archivos_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>_<?=$des_id?>" class="archivos col-sm-10">
      </div>
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-2">&nbsp;</div>
    <div class="col-sm-10">
    <button class="btn btn-info" onclick="//p_guardar('<?="formulario_"?>')">Guardar</button>
    <button class="btn btn-danger boton-eliminar" id="boton_eliminar_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>_<?=$des_id?>" onclick="p_eliminar(<?=$servicio['ser_id']?>, <?=$proveedor['pro_id']?>, <?=$des_id?>)" type="button">Eliminar</button>
    </div>
  </div>
</form>
<hr />
<strong>CAMPOS:</strong>
<div id="formulario_cae_mensaje_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>_<?=$des_id?>" class="alert alert-warning formulario-cae-mensaje">Guarde el formulario primero antes de poder agregar campos.</div>
<form id="formulario_cae_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>_<?=$des_id?>" class="form-horizontal formulario-cae" onsubmit="return false;">
  <div class="form-group">
    <label for="campo" class="col-sm-2 control-label">Campo:</label>
    <div class="col-sm-8">
      <input type="hidden" id="campo_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>_<?=$des_id?>" name="campo" value="">
      <input class="form-control" required type="text" id="campo_typeahead_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>_<?=$des_id?>" data-provide="typeahead" autocomplete="off" placeholder="Ingrese al menos 2 caracteres" onblur="p_validar_campo('<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>_<?=$des_id?>')">
    </div>
    <div class="col-sm-1">
      <button type="button" class="btn btn-info boton-agregar" id="campo_agregar_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>_<?=$des_id?>" onclick="p_guardar_campo('<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>_<?=$des_id?>')"><span class="glyphicon glyphicon-plus" aria-hidden="true"></span></button>
    </div>
  </div>
</form>

<table id="tabla_cae_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>_<?=$des_id?>" class="table table-striped">
<tbody class="antiguos-cae" id="antiguos_cae_<?=$servicio['ser_id']?>_<?=$proveedor['pro_id']?>_<?=$des_id?>"></tbody>
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

<div class="alert alert-warning">No hay proveedores registrados para este servicio.</div>
<a class="btn btn-primary" href="/autoadmin/sai_pertinencia_proveedor">Ir a administración de pertinencias de proveedor</a>
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

var destinatarios = <?=json_encode($destinatarios)?>;

function p_validar_campo(id){
    console.log('on blur campo', id);
    if ($('#campo_'+id).val() == ''){
        $('#campo_typeahead_'+id).val('');
    }
}

function p_guardar_campo(id){
    if ($('#campo_'+id).val() !== '') {
        var respuestas_json = $('#formulario_cae_'+id).serializeArray();
        console.log('respuestas json', id, respuestas_json);
        dataset_json = {}; 
        dataset_json['campo'] = $('#campo_'+id).val();
        var tea_id = $('#tea_id_'+id).val();
        dataset_json['transicion'] = tea_id;

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
                //p_cargar_detalle_transicion(current_ser_id, current_pro_id);
                p_actualizar_campos(tea_id, id);
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


function p_borrar_archivo(adp_id, id) {

    console.log('borrar archivo', adp_id);
    if (confirm('Seguro desea borrar el archivo?')){
        $.get('/_borrarArchivo/'+adp_id, function(data){
            console.log('Respuesta de borrarArchivo', data);
            data = JSON.parse(data);
            console.log('data', data);
            var tea_id = $('#tea_id_' + id).val();
            p_actualizar_archivos(tea_id, 0, id);

        });
    }

}

function p_borrar_campo(cae_id, id) {
    console.log('FUNCTION p_borrar_campo: ', cae_id, id);
    if (confirm('Seguro desea quitar este campo a la transición?')) {
        dataset_json = {};
        dataset_json['campo'] = cae_id;
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
        console.log('/_obtenerDetalleTransicion/: ', data);
        data = JSON.parse(data);
        console.log('RESPUESTA: ', data);

        var transiciones = [];

        data.forEach(function(transicion){
            var tea_id = transicion['tea_id'];
            if (typeof(transiciones[tea_id]) === 'undefined') {
                transiciones[tea_id] = transicion;
            } else if (transiciones[tea_id]['pla_id'] < transicion['pla_id']) {
                transiciones[tea_id] = transicion;
            }
        });

        transiciones.forEach(function(transicion){
            var des_id = transicion['tea_destinatario'];
            console.log(ser_id+'_'+pro_id+'_'+des_id);
            $('#tea_id_'+ser_id+'_'+pro_id+'_'+des_id).val(transicion['tea_id']);
            $('#usuario_'+ser_id+'_'+pro_id+'_'+des_id).val(transicion['tea_usuario']);
            $('#automatico_'+ser_id+'_'+pro_id+'_'+des_id).prop('checked', transicion['tea_automatico'] == '1');
            $('#tiempo_alerta_horas_'+ser_id+'_'+pro_id+'_'+des_id).val(transicion['tea_tiempo_alerta_horas']);
            $('#asunto_'+ser_id+'_'+pro_id+'_'+des_id).val(transicion['pla_asunto'] == 'null' ? '' : transicion['pla_asunto']);
            $('#adjunto_nombre_'+ser_id+'_'+pro_id+'_'+des_id).val(transicion['pla_adjunto_nombre'] == 'null' ? '' : transicion['pla_adjunto_nombre']);
            CKEDITOR.instances['cuerpo_'+ser_id+'_'+pro_id+'_'+des_id].setData(transicion['pla_cuerpo'] == 'null' ? '' : transicion['pla_cuerpo']);
            CKEDITOR.instances['adjunto_texto_'+ser_id+'_'+pro_id+'_'+des_id].setData(transicion['pla_adjunto_texto'] == 'null' ? '' : transicion['pla_adjunto_texto']);
            console.log('#usuario_'+ser_id+'_'+pro_id+'_'+des_id);

            //p_actualizar_archivos(transicion['archivos'], ser_id+'_'+pro_id+'_'+des_id);
            p_actualizar_archivos(transicion['tea_id'], transicion['pla_id'], ser_id+'_'+pro_id+'_'+des_id);

            //p_actualizar_campos(transicion['campos'], ser_id+'_'+pro_id+'_'+des_id);
            p_actualizar_campos(transicion['tea_id'], ser_id+'_'+pro_id+'_'+des_id);
            p_inicializar_autocompletar(ser_id+'_'+pro_id+'_'+des_id);

            //$('#boton_eliminar_'+ser_id+'_'+pro_id+'_'+des_id).show();
            //$('#formulario_cae_'+ser_id+'_'+pro_id+'_'+des_id).show();
            //$('#formulario_cae_mensaje_'+ser_id+'_'+pro_id+'_'+des_id).hide();
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

//function p_actualizar_campos(campos, id) {
function p_actualizar_campos(tea_id, id) {
    console.log('p_actualizar_campos', tea_id, id);
    var tbody = $('#antiguos_cae_' + id);
    tbody.html('');
    $.get('/_obtenerCampos/'+tea_id, function(data){
        console.log('respuesta desde obtenerCampos', data);
        data = JSON.parse(data);
        console.log('data', data);
        if (Array.isArray(data)) {
            data.forEach(function(campo){
                var numero = tbody.children().length + 1;
                //var nombre = $('#campo_typeahead_'+id).val();
                var nombre = '<strong>${' + campo['cae_codigo'] + '}</strong> '+campo['cae_texto']+'';
                tbody.append('<tr class="alert alert-info" id="cae_'+campo['cae_id']+'"><th>'+numero+'.</th><td><span id="nombre_cae_'+campo['cae_id']+'">'+nombre+'</span></td><td><button class="btn btn-danger" onclick="p_borrar_campo('+campo['cae_id']+', \''+id+'\')"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></button></td></tr>');
            });
        }
    });
}

//function p_actualizar_archivos(archivos, id) {
function p_actualizar_archivos(tea_id, pla_id, id) {
    console.log('p_actualizar_archivos', tea_id, pla_id, id);
    var div = $('#archivos_' + id);
    div.html('');

    $.get('/_obtenerArchivos/' + tea_id + '/' + pla_id, function(data){
        console.log('respuesta obetenerArchivos', data);
        data = JSON.parse(data);
        console.log('data', data);

        if (Array.isArray(data)) {
            data.forEach(function(archivo){
                var hidden = '<input type="hidden" name="adp_id[]" value="'+archivo['adp_id']+'">';
                var icono = '<span class="glyphicon glyphicon-download" aria-hidden="true"></span> ';
                var trash = ' <button class="btn btn-danger" onclick="p_borrar_archivo('+archivo['adp_id']+', \''+id+'\')"><span class="glyphicon glyphicon-trash" aria-hidden="true"></span></button>';
                div.append('<div><a class="btn btn-default" href="/'+archivo['arc_ruta']+'" target="_blank" title="Descargar archivo">'+icono+archivo['arc_nombre']+'</a>'+hidden+trash+'</div>');
            });
        }
    });
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
    $('.archivos').each(function(){
        $(this).html('');
    });
    $('.antiguos-cae').each(function(){
        $(this).html('');
    });
    $('.boton-eliminar').each(function(){$(this).hide();});
    $('.formulario-cae').each(function(){$(this).hide();});
    $('.formulario-cae-mensaje').each(function(){$(this).show();});
    $('.boton-agregar').each(function(){$(this).hide();});

    $.get('/_obtenerTransicionResumen/'+desde+'/'+hacia, function(data){
        console.log(data);
        data = JSON.parse(data);
        console.log('/_obtenerTransicionResumen/'+desde+'/'+hacia, data);
        data.forEach(function(d, k){
            var ser_id = d['ser_id'];
            var pro_id = d['pro_id'];
            var tea_id = d['tea_id'];
            var des_id = d['tea_destinatario'];

            var count;

            $('#badge_servicio_'+ser_id).show();
            count = parseInt($('#badge_servicio_'+ser_id).text());
            $('#badge_servicio_'+ser_id).text(count+1);

            $('#badge_proveedor_'+ser_id+'_'+pro_id).append('<span class="badge" id="nuevabadge_'+tea_id+'">'+d['destinatario']+'</span>');

            $('#boton_eliminar_'+ser_id+'_'+pro_id+'_'+des_id).show();
            $('#formulario_cae_'+ser_id+'_'+pro_id+'_'+des_id).show();
            $('#formulario_cae_mensaje_'+ser_id+'_'+pro_id+'_'+des_id).hide();
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
                $('#tea_id_'+ser_id+'_'+pro_id+'_'+des_id).val('');

                $('#boton_eliminar_'+ser_id+'_'+pro_id+'_'+des_id).hide();
                $('#formulario_cae_'+ser_id+'_'+pro_id+'_'+des_id).hide();
                $('#formulario_cae_mensaje_'+ser_id+'_'+pro_id+'_'+des_id).show();

                var count;

                count  = parseInt($('#badge_servicio_' + ser_id).text());
                console.log('servicio count', ser_id, count - 1);
                $('#badge_servicio_' + ser_id).text(count - 1);
                if (count - 1 == 0) {
                    $('#badge_servicio_' + ser_id).hide();
                }

                count  = parseInt($('#badge_transicion_' + desde + '_'+ hacia).text());
                console.log('transicion count', desde + '_'+ hacia, count - 1);
                $('#badge_transicion_' + desde + '_'+ hacia).text(count - 1);
                if (count - 1 == 0) {
                    $('#badge_transicion_' + desde + '_'+ hacia).hide();
                }

                p_actualizar_archivos(tea_id, 0, ser_id+'_'+pro_id+'_'+des_id);
                p_actualizar_campos(tea_id, ser_id+'_'+pro_id+'_'+des_id);
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
    $(target).find('input[name=desde]').val(desde);
    $(target).find('input[name=hacia]').val(hacia);
    var ser_id =  $(target).find('input[name=ser_id]').val();
    var pro_id =  $(target).find('input[name=pro_id]').val();
    var des_id =  $(target).find('input[name=des_id]').val();
    var destinatario =  $(target).find('input[name=destinatario]').val();
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
            var tea_id = data['tea_id'];
            $('#tea_id_'+ser_id+'_'+pro_id+'_'+des_id).val(tea_id);
            var celda_id = '#celda_' + desde + '_'+ hacia;
            console.log('id de celda:', celda_id, $(celda_id));
            $(celda_id).removeClass('alert alert-success alert-info alert-danger');
            $(celda_id).addClass('alert alert-success');
            //var id = desde + '_'+ hacia + '_' + data['tea_destinatario'];
            //p_actualizar_archivos(data['archivos'], id);
            if ($('#badge_proveedor_'+ser_id+'_'+pro_id+":contains('"+destinatario+"')").length > 0) {
                $('#badge_proveedor_'+ser_id+'_'+pro_id).find('span'+":contains('"+destinatario+"')").remove();
            } else {
                var count;

                $('#badge_servicio_' + ser_id).show();
                count  = parseInt($('#badge_servicio_' + ser_id).text());
                console.log('servicio count', ser_id, count + 1);
                $('#badge_servicio_' + ser_id).text(count + 1);

                $('#badge_transicion_' + desde + '_'+ hacia).show();
                count  = $('#badge_transicion_' + desde + '_'+ hacia).text();
                count  = (count == '') ? 0 : parseInt($('#badge_transicion_' + desde + '_'+ hacia).text());
                console.log('transicion count', desde + '_'+ hacia, count + 1);
                $('#badge_transicion_' + desde + '_'+ hacia).text(count + 1);
            }
            $('#badge_proveedor_'+ser_id+'_'+pro_id).append('<span class="badge" id="nuevabadge_' + tea_id + '">' + destinatario + '</span>');
            $('#boton_eliminar_'+ser_id+'_'+pro_id+'_'+des_id).show();
            $('#formulario_cae_'+ser_id+'_'+pro_id+'_'+des_id).show();
            $('#formulario_cae_mensaje_'+ser_id+'_'+pro_id+'_'+des_id).hide();

            $('#archivo_adjunto_'+ser_id+'_'+pro_id+'_'+des_id).val('');
            p_actualizar_archivos(tea_id, 0, ser_id+'_'+pro_id+'_'+des_id);


            p_inicializar_autocompletar(ser_id+'_'+pro_id+'_'+des_id);

            //$('#modal').modal('hide');
        }
    }).fail(function(xhr, err){
        console.error('ERROR AL GUARDAR', xhr, err);
        alert('Hubo un error al guardar, verifique que cuenta con Internet y vuelva a intentarlo en unos momentos.');
        //$('#modal').modal('hide');
    });
}
</script>
