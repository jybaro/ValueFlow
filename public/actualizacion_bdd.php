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

$result = q("
UPDATE sai_cuenta
SET cue_codigo = concat('Cuenta ', cue_cliente)
WHERE cue_borrado IS NULL
AND cue_codigo LIKE '%Cuenta de la empresa%'
RETURNING *
");

var_dump($result);


$sql = ("
    SELECT
    cue_id AS id
    ,concat(
        'Cuenta '
        , CASE WHEN count_hijos > 0 
            THEN 'padre' 
            ELSE  (
                CASE WHEN cue_padre IS NULL 
                    THEN 'independiente' 
                    ELSE 'hijo' 
                END
            )
        END
        , ' de '
        , cli_razon_social
    ) AS text

    FROM (    
        SELECT *
        ,(
            SELECT count(*)
            FROM sai_cuenta AS hijos
            WHERE hijos.cue_borrado IS NULL
            AND hijos.cue_padre = padre.cue_id
        ) AS count_hijos
        FROM sai_cuenta AS padre
        ,sai_cliente
        WHERE cue_borrado IS NULL
        AND cli_borrado IS NULL
        AND cue_cliente = cli_id
    ) AS t
");

$result = q($sql);

var_dump($result);

if ($result) {
    foreach($result as $r)  {
        $cue_id = $r['id'];
        $cue_codigo = $r['text']; 
        $sql = ("
            UPDATE sai_cuenta
            SET cue_codigo = '$cue_codigo'
            WHERE cue_borrado IS NULL
            AND (cue_codigo IS NULL OR cue_codigo <> '$cue_codigo')
            AND cue_id = $cue_id
            RETURNING *
        ");
        if ($cue_id == 384) echo $sql;
        $result_update = q($sql);
        var_dump($result_update);
    }
}


