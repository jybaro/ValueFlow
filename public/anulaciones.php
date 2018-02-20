<?php
$titulo_proceso = 'Anulaciones';

$filtro = "(SELECT esa_id FROM sai_estado_atencion WHERE esa_padre=(SELECT esa_id FROM sai_estado_atencion WHERE esa_codigo = 'anulaciones'))";
require_once('proceso.php');
