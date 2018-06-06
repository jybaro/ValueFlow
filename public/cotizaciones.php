<?php
$titulo_proceso = 'Cotizaciones';
$filtro = "(SELECT esa_id FROM sai_estado_atencion WHERE esa_padre=(SELECT esa_id FROM sai_estado_atencion WHERE esa_nombre ILIKE 'Cotización'))";


require_once('proceso.php');
