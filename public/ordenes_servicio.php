<?php
$titulo_proceso = 'Órdenes de servicio';

$filtro = "(SELECT esa_id FROM sai_estado_atencion WHERE esa_padre=(SELECT esa_id FROM sai_estado_atencion WHERE esa_nombre ILIKE 'Orden de Servicio'))";
require_once('proceso.php');
