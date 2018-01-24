<h1>Reporte de Pendientes</h1>
<?php
$result = q("
    SELECT *
    FROM sai_paso_atencion
    ,sai_transicion_estado_atencion
    ,sai_atencion
    ,sai_servicio
    ,sai_estado_atencion
    ,sai_cliente
    ,sai_proveedor
    ,sai_pertinencia_proveedor
    WHERE paa_borrado IS NULL
    AND tea_borrado IS NULL
    AND ate_borrado IS NULL
    AND ser_borrado IS NULL
    AND esa_borrado IS NULL
    AND cli_borrado IS NULL
    AND pro_borrado IS NULL
    AND pep_borrado IS NULL
    AND paa_transicion_estado_atencion = tea_id
    AND paa_atencion = ate_id
    AND ate_servicio = ser_id
    AND ate_estado_atencion = esa_id
    AND ate_cliente = cli_id
    AND ate_pertinencia_proveedor = pep_id
    AND pep_proveedor = pro_id
    AND paa_paso_anterior IS NULL
    AND tea_tiempo_alerta_horas > 0
");

if ($result) {
    foreach ($result as $r) {
        echo <<<EOT
<div class="alert alert-info">
  {$r[esa_nombre]} de servicio de {$r[ser_nombre]} ({$r[pro_razon_social]}) a {$r[cli_razon_social]}
</div>
EOT;
    }
} else {
        echo <<<EOT
<div class="alert alert-warning">
  No hay pendientes
</div>
EOT;
}
