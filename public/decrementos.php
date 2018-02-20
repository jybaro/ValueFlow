<?php
$titulo_proceso = 'Decrementos';

$filtro = "(SELECT esa_id FROM sai_estado_atencion WHERE esa_padre=(SELECT esa_id FROM sai_estado_atencion WHERE esa_codigo = 'decrementos'))";
require_once('proceso.php');
