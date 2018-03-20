<?php

$result = q("
UPDATE sai_archivo SET 
arc_nombre = replace(arc_nombre, 'ID_SERVICIO', 'ID_ORDEN_SERVICIO') 
,arc_ruta = replace(arc_ruta, 'ID_SERVICIO', 'ID_ORDEN_SERVICIO') 
WHERE arc_nombre ILIKE '%ID_SERVICIO%'
RETURNING *
");
echo "<pre>";
var_dump($result);
