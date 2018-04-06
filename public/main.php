<div class="page-header">
  <h1>Pendientes asignados</h1>
</div>
<?php

$usu_id = $_SESSION['usu_id'];
$sql = ("
    SELECT * 
    ,(
        SELECT ser_nombre 
        FROM sai_servicio
        WHERE ser_borrado IS NULL 
        AND ser_id=pep_servicio
    )
    ,(
        SELECT cli_razon_social
        FROM sai_cliente
        WHERE cli_borrado IS NULL
        AND cli_id = ate_cliente
    )
    ,(
        SELECT pro_nombre_comercial
        FROM sai_proveedor
        WHERE pro_borrado IS NULL
        AND pro_id = pep_proveedor
    )
    ,(
        SELECT esa_codigo
        FROM sai_estado_atencion
        WHERE esa_borrado IS NULL
        AND esa_id = (
            SELECT esa_padre
            FROM sai_estado_atencion
            WHERE esa_borrado IS NULL
            AND esa_id = ate_estado_atencion
        )
        AND 0 < (
            SELECT count(*)
            FROM sai_permiso
            ,sai_objeto
            WHERE per_borrado IS NULL
            AND obj_borrado IS NULL
            AND per_objeto = obj_id
            AND per_solo_lectura = 0
            AND obj_nombre = esa_codigo
            AND per_rol = (
                SELECT usu_rol
                FROM sai_usuario
                WHERE usu_borrado IS NULL
                AND usu_id = $usu_id
            )
        )
    ) AS esa_padre_codigo
    FROM sai_atencion
    ,sai_estado_atencion
    ,sai_usuario
    ,sai_paso_atencion
    ,sai_transicion_estado_atencion
    ,sai_pertinencia_proveedor
    WHERE
    ate_borrado IS NULL
    AND esa_borrado IS NULL
    AND usu_borrado IS NULL
    AND paa_borrado IS NULL
    AND tea_borrado IS NULL
    AND pep_borrado IS NULL
    AND ate_usuario_tecnico = usu_id
    AND ate_estado_atencion = esa_id
    AND paa_atencion = ate_id
    AND paa_transicion_estado_atencion = tea_id
    AND pep_id = ate_pertinencia_proveedor
    AND paa_paso_anterior IS NULL
    AND tea_tiempo_alerta_horas > 0
    AND (
        ate_usuario_tecnico = $usu_id
        OR 
        ate_usuario_comercial = $usu_id
    )
    ORDER BY ate_creado DESC
");
    //AND tea_tiempo_alerta_horas > 0

//echo "<pre>";
//echo $sql;
//echo "</pre>";
$result = q($sql);
$count_pendientes = 0;
if ($result) {
    $ate = array();
    foreach($result as $r){
        if (!isset($ate[$r[ate_id]])) {
            $ate[$r[ate_id]] = $r;
            $ser_nombre = $r['ser_nombre'];
            $cli_razon_social = $r['cli_razon_social'];
            //$estado = '#';
            $estado = $r['esa_padre_codigo'];
            if (!empty($estado)) {
                $count_pendientes++;
                $fecha_formateada = p_formatear_fecha($r['ate_creado']);
                echo <<<EOT
    <div class="list-group" style="margin:0 5% 1% 5%;">

      <a class="list-group-item" href="/$estado#atencion_{$r[ate_secuencial]}">
        {$r[ate_secuencial]}. <strong>{$r[esa_nombre]}</strong> de servicio de $ser_nombre ({$r[pro_nombre_comercial]}) a $cli_razon_social
        <small class="text-muted">- $fecha_formateada</small> 
      </a>
    </div>
EOT;
            }
        }
    }
} 

if ($count_pendientes == 0) {
    echo <<<EOT
    <div class="alert alert-info">No tiene atenciones asignadas</div>
EOT;
}
