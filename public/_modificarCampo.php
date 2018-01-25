<?php

$txt = array('texto', 'codigo');

foreach($_POST as $k => $v) {
    $v = empty($v) ? '= NULL' : ' = ' . (in_array($k, $txt) ? "'$v'" : $v);
    $$k = $v;
}


$result = q("
    UPDATE sai_campo_extra
    SET cae_texto $texto
    ,cae_codigo $codigo
    ,cae_tipo_dato $tipo_dato
    ,cae_padre $padre
    WHERE cae_id $id
    RETURNING *
    ");
echo json_encode($result);
