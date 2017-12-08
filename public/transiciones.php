<h1>Transiciones</h1>
<?php

$result= q("SELECT *,(SELECT COUNT(*) FROM sai_estado_atencion AS t WHERE t.esa_padre=esa_id) AS count_hijos FROM sai_estado_atencion ORDER BY esa_padre DESC, esa_id");
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
function p_cargador(& $nodo, $nivel = 0) {
    global $filas_titulo;
    global $cols_titulo;
    global $profundidad_maxima;

    foreach($nodo as $k=>  $hijo){
        //echo "<div class='alert alert-info'>";
        //echo "[{$hijo[esa_nombre]} $nivel]";

        $nodo[$k]['nivel'] = $nivel;
        $rowspan = 1;
        $colspan = $hijo['num_hojas'];

        $t = 'td';
        if (!empty($nodo[$k]['hijos'])) {
            //no es hoja, cuenta los hijos:
            p_cargador($nodo[$k]['hijos'], $nivel+1);
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
        $filas_titulo[$nivel] .= "<$t colspan='$colspan' rowspan='$rowspan'>{$hijo[esa_nombre]} </$t>";

        $primera_hoja = $hijo['primera_hoja'];
        if (!isset($cols_titulo[$primera_hoja])) {
            $cols_titulo[$primera_hoja] = array();
        }
        $cols_titulo[$primera_hoja][$nivel] = "<$t colspan='$rowspan' rowspan='$colspan'>{$hijo[esa_nombre]}</$t>";
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
                $x = $esa_id;
                $y = $hijo[esa_id];

                if ($x == $y) {
                    echo "<td style='background-color:#000;'>&nbsp;</td>";
                } else {
                    echo "<td class='text-center'><a href='#' onclick='p_abrir($x, $y);return false;'><span class='glyphicon glyphicon-cog' aria-hidden='true'></span></a></td>";
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

$pertinencias_proveedor = array();
foreach($servicios as $servicio){
    $pertinencias_proveedor[$servicio['ser_id']] = array();
}
$result = q("SELECT *,(SELECT pro_razon_social FROM sai_proveedor WHERE pro_id=pep_proveedor) FROM sai_pertinencia_proveedor");

if ($result) {
    foreach ($result as $r) {
        $pertinencias_proveedor[$r['pep_servicio']][] = $r;
    }
}

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
      </div>
      <div class="modal-body">


<div>

  <!-- Nav tabs -->
  <ul class="nav nav-tabs" role="tablist">
    <?php foreach($servicios as $k => $servicio): ?>
    <li role="presentation" <?php if($k==0): ?>class="active"<?php endif; ?>><a href="#tab_servicio_<?=$servicio['ser_id']?>" aria-controls="tab_servicio_<?=$servicio['ser_id']?>" role="tab" data-toggle="tab"><?=$servicio['ser_nombre']?></a></li>
    <?php endforeach; ?>
  </ul>

  <!-- Tab panes -->
  <div class="tab-content">
  <?php foreach($servicios as $k => $servicio): ?>
    <div role="tabpanel" class="tab-pane fade <?php if($k==0): ?>in active<?php endif; ?>" id="tab_servicio_<?=$servicio['ser_id']?>">
      <div>&nbsp;</div>



  <?php foreach($pertinencias_proveedor[$servicio['ser_id']] as $proveedor): ?>
<?php
//echo '<pre>';
//var_dump($servicio);
//echo '</pre>';
?>
<div class="panel panel-default">
  <div class="panel-heading">
  <h3 class="panel-title"><?=$proveedor['pro_razon_social']?></h3>
  </div>
  <div class="panel-body">

<form id="formulario" class="form-horizontal">
<input type="hidden" id="id" name="id" value="">
  <div class="form-group">
    <label for="asunto" class="col-sm-2 control-label">Asunto:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="asunto" name="asunto" placeholder="Asunto">
    </div>
  </div>
  <div class="form-group">
    <label for="cuerpo" class="col-sm-2 control-label">Cuerpo:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="cuerpo" name="cuerpo" placeholder="Cuerpo">
    </div>
  </div>
  <div class="form-group">
    <label for="automatico" class="col-sm-2 control-label">Automatico:</label>
    <div class="col-sm-10">
      <input type="checkbox" class="form-control" id="automatico" name="automatico" placeholder="">
    </div>
  </div>
  <div class="form-group">
    <label for="adjunto" class="col-sm-2 control-label">Adjunto:</label>
    <div class="col-sm-10">
      <input type="file" class="form-control" id="adjunto" name="adjunto" placeholder="Adjunto">
    </div>
  </div>
</form>

  </div>
    <?php endforeach; ?>
</div>

<hr>
<hr>
<hr>
<hr>

    </div>
  </div>

    <?php endforeach; ?>

</div>



      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-success" onclick="p_guardar()" id="formulario_guardar">Guardar cambios</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script>
function p_abrir(x, y){
    //alert(x+' - '+ y);
    $('#modal').modal('show');
}
</script>
