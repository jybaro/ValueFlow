<?php
$titulo_proceso = 'Factibilidades';
$filtro = "(SELECT esa_id FROM sai_estado_atencion WHERE esa_padre=(SELECT esa_id FROM sai_estado_atencion WHERE esa_nombre ILIKE 'Factibilidad'))";
require_once('proceso.php');
