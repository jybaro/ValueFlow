<?php

//var_dump($_POST);

$ate_id = $_POST['ate_id'];

$paa_id = "(SELECT paa_id FROM sai_paso_atencion WHERE paa_borrado IS NULL AND paa_atencion=$ate_id)";
$result = q($paa_id);
if (!$result) {
    $paa_id = q("INSERT INTO sai_paso_atencion (paa_atencion) VALUES ($ate_id) RETURNING *")[0]['paa_id'];
} else if (count($result) > 1) {
    q("UPDATE sai_paso_atencion SET paa_borrado = now() WHERE paa_atencion=$ate_id AND paa_id <> (SELECT MAX(paa_id) FROM sai_paso_atencion WHERE paa_atencion=$ate_id)");
    $paa_id = "(SELECT paa_id FROM sai_paso_atencion WHERE paa_borrado IS NULL AND paa_atencion=$ate_id)";
}
//echo $paa_id;
$respuesta = array();
foreach($_POST as $k => $v){
    if ($k != 'ate_id') {
        //$cae_id = "(SELECT cae_id FROM sai_campo_extra WHERE cae_codigo='$k')";
        $cae_id = str_replace('campo_extra_', '', $k);
        q("UPDATE sai_valor_extra SET vae_borrado=now() WHERE vae_borrado IS NULL AND vae_campo_extra=$cae_id AND vae_paso_atencion=$paa_id");
        $return = q("INSERT INTO sai_valor_extra (vae_campo_extra, vae_paso_atencion, vae_texto) VALUES ($cae_id, $paa_id, '$v') RETURNING *");
        $respuesta[] = $return;
    }
}

echo json_encode($respuesta);
