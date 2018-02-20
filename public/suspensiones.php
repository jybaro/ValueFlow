<?php
$titulo_proceso = 'Suspensiones';

$filtro = "(SELECT esa_id FROM sai_estado_atencion WHERE esa_padre=(SELECT esa_id FROM sai_estado_atencion WHERE esa_codigo = 'suspensiones'))";
require_once('proceso.php');
