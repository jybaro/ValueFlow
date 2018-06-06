<?php
$titulo_proceso = 'Demo';

$filtro = "(SELECT esa_id FROM sai_estado_atencion WHERE esa_padre=(SELECT esa_id FROM sai_estado_atencion WHERE esa_codigo = 'demo'))";
require_once('proceso.php');
