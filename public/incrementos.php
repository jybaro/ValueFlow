<?php
$titulo_proceso = 'Incrementos';

$filtro = "(SELECT esa_id FROM sai_estado_atencion WHERE esa_padre=(SELECT esa_id FROM sai_estado_atencion WHERE esa_codigo = 'incrementos'))";
require_once('proceso.php');
