<?php

foreach($_POST as $k => $v) {
    $$k = $v;
}

q("
    UPDATE sai_campo_extra
    SET cae_texto = '$texto'
    ,cae_codigo = '$codigo'
    ,cae_tipo_dato = $tipo_dato
    ,cae_padre = $padre
    WHERE cae_id = $id
");
