<?php

$tea_id = $args[0];

$return = q("SELECT * FROM sai_campo_extra WHERE cae_transicion_estado_atencion=$tea_id");

echo json_encode($return);
