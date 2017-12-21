<?php

$tea_id = $args[0];

$return = q("SELECT *
    ,(SELECT vae_texto FROM sai_valor_extra, sai_paso_atencion WHERE vae_borrado IS NULL AND paa_borrado IS NULL AND vae_campo_extra = cae_id AND paa_id=vae_paso_atencion) AS valor
    FROM sai_campo_extra WHERE cae_transicion_estado_atencion=$tea_id");

echo json_encode($return);
