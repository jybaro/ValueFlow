<?php

$cue_id = $args[0];

$result = q("
SELECT *
, CASE WHEN count_hijos > 0
    THEN 'padre'
    ELSE  (
        CASE WHEN cue_padre IS NULL
            THEN 'independiente'
            ELSE 'hijo'
        END
    )
END AS tipo
FROM (
    SELECT *
    ,(
        SELECT cli_razon_social
        FROM sai_cliente AS cliente_padre
        ,sai_cuenta AS padre
        WHERE cliente_padre.cli_borrado IS NULL
        AND padre.cue_borrado IS NULL
        AND padre.cue_cliente = cliente_padre.cli_id
        AND padre.cue_id = hijo.cue_padre
    ) AS padre
    ,(
        SELECT count(*)
        FROM sai_cuenta AS hijos
        WHERE hijos.cue_borrado IS NULL
        AND hijos.cue_padre = hijo.cue_id
    ) AS count_hijos
    FROM sai_cuenta AS hijo
    
    LEFT OUTER JOIN sai_cliente
    ON cli_borrado IS NULL
    AND cue_cliente = cli_id

    LEFT OUTER JOIN  sai_usuario
    ON usu_borrado IS NULL
    AND cue_responsable_cobranzas = usu_id

    WHERE cue_borrado IS NULL
    AND cue_id = $cue_id
) AS t
");

echo json_encode($result);
