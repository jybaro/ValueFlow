<?php

echo "<pre>";
$result = q("
UPDATE sai_archivo SET 
arc_nombre = replace(arc_nombre, 'ID_SERVICIO', 'ID_ORDEN_SERVICIO') 
,arc_ruta = replace(arc_ruta, 'ID_SERVICIO', 'ID_ORDEN_SERVICIO') 
WHERE arc_borrado IS NULL
AND arc_nombre LIKE '%ID\_SERVICIO%'
RETURNING *
");
var_dump($result);



$result = q("
UPDATE sai_plantilla
SET
pla_asunto = replace(pla_asunto, 'ID_SERVICIO', 'ID_ORDEN_SERVICIO')
,pla_cuerpo = replace(pla_cuerpo, 'ID_SERVICIO', 'ID_ORDEN_SERVICIO')
WHERE pla_borrado IS NULL 
AND (
pla_asunto LIKE '%ID\_SERVICIO%'
OR pla_cuerpo LIKE '%ID\_SERVICIO%'
)
RETURNING *
");
var_dump($result);
