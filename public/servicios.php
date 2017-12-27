<?php
$titulo_proceso = 'Servicios';

$filtro = "(SELECT esa_id FROM sai_estado_atencion WHERE esa_padre=(SELECT esa_id FROM sai_estado_atencion WHERE esa_nombre = 'Servicio'))";
require_once('proceso.php');
