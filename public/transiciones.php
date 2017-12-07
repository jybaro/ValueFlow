<h1>Transiciones</h1>
<?php

$result= q("SELECT *,(SELECT COUNT(*) FROM sai_estado_atencion AS t WHERE t.esa_padre=esa_id) AS count_hijos FROM sai_estado_atencion");
$estados = array();

foreach($result as $r){
    $estados[$r['esa_id']] = $r;
    $estados[$r['esa_id']]['hijos'] = array();
    $estados[$r['esa_id']]['padre'] = null;
    $estados[$r['esa_id']]['label'] = '';
}

foreach($estados as & $estado) {
    if (!empty($estado['esa_padre'])) {
        $estados[$estado['esa_padre']]['hijos'][$estado['esa_id']] = $estado;
        $estado['padre'] = &$estados[$estado['esa_padre']];
    }
}


echo '<table class="table table-striped table-condensed table-hover">';

echo '<thead><tr>';
echo "<th>&nbsp;</th>";
foreach($estados as $estado){
    $label = $estado['esa_nombre'];
    echo "<th>$label</th>";
}
echo '</tr></thead><tbody>';
foreach($estados as $estado){
    echo '<tr>';
    $label = $estado['esa_nombre'];
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
echo '</tbody></table>';
