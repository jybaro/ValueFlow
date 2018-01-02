<?php
$titulo_proceso = 'Factibilidades';
$titulo_proceso_singular = 'factibilidad';
$filtro = "(SELECT esa_id FROM sai_estado_atencion WHERE esa_padre=(SELECT esa_id FROM sai_estado_atencion WHERE esa_nombre ILIKE 'Factibilidad'))";
$mostrar_nuevo = true;
require_once('proceso.php');
