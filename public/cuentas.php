<h1>Cuentas</h1>
<?php

$result = q("
    SELECT *
    FROM sai_cliente,
    sai_cuenta
    WHERE cli_borrado IS NULL
    AND cue_borrado IS NULL
    AND cue_cliente = cli_id
");

$cuentas = array();
foreach ($result as $r) {
    $cuentas[$r[cue_id]] = $r;
    $cuentas[$r[cue_id]][padre] = null;
    $cuentas[$r[cue_id]][hijos] = array();
}

foreach ($cuentas as $cue_id => $cuenta) {
    $cuentas[$cue_id][padre] = & $cuentas[$cuenta[cue_padre]];
    $cuentas[$cuenta[cue_padre]][hijos][$cue_id] = & $cuentas[$cue_id];
}

function p_tree($cuenta) {
    $icono = '<span class="glyphicon glyphicon-search" aria-hidden="true"></span>';

    $titulo = !isset($cuenta[cue_codigo]) ? '': "$icono {$cuenta[cue_codigo]} ({$cuenta[cli_razon_social]})";
    echo <<<EOT
    <div class="panel panel-info">
      <div class="panel-heading">
        {$titulo}
      </div>
      <div class="panel-body">
EOT;
    foreach ($cuenta[hijos] as $hijo) {
        p_tree($hijo);
    }
    echo <<<EOT
      </div>
    </div>
EOT;
}
p_tree($cuentas[null]);
