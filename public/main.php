<?php

$usu_id = $_SESSION['usu_id'];
$result = q("
    SELECT * 
    ,(
        SELECT ser_nombre 
        FROM sai_servicio
        WHERE ser_borrado IS NULL 
        AND ser_id=peu_servicio
    )
    ,(
        SELECT cli_razon_social
        FROM sai_cliente
        WHERE cli_borrado IS NULL
        AND cli_id = ate_cliente
    )
    ,(
        SELECT esa_codigo
        FROM sai_estado_atencion
        WHERE esa_id = (
            SELECT esa_padre
            FROM sai_estado_atencion
            WHERE esa_id=ate_estado_atencion
        )
    )
    FROM sai_atencion
    ,sai_pertinencia_usuario
    WHERE
    ate_borrado IS NULL
    AND peu_borrado IS NULL
    AND ate_pertinencia_usuario = peu_id
    AND (
        peu_usuario = $usu_id
        OR ate_usuario_comercial = $usu_id
    )
");

if ($result) {
    foreach($result as $r){
        $ser_nombre = $r['ser_nombre'];
        $cli_razon_social = $r['cli_razon_social'];
        //$estado = '#';
        $estado = $r['esa_codigo'];
        echo <<<EOT
<div class="panel panel-default" style="width:300px;float:left;margin:10px;">
  <div class="panel-heading">
    <h3 class="panel-title">
      {$r[ate_secuencial]}. Atención de servicio de $ser_nombre a $cli_razon_social
    </h3>
  </div>
  <div class="panel-body">
    <a class="btn btn-primary" href="/$estado#atencion_{$r[ate_secuencial]}">Ir a la resolución</a>
  </div>
</div>
EOT;
    }
} else {
    echo <<<EOT
    <div class="alert alert-info">No tiene atenciones asignadas</div>
EOT;
}
