<?php
$titulo_proceso = 'Cambios de servicios';
$filtro = "(SELECT esa_id FROM sai_estado_atencion WHERE esa_padre IN (SELECT esa_id FROM sai_estado_atencion WHERE esa_padre=(SELECT esa_id FROM sai_estado_atencion WHERE esa_nombre ILIKE '%Cambio')))";

require_once('proceso.php');
