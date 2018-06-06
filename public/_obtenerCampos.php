<?php

$imprimir_json = (!isset($tea_id));

$tea_id = (isset($tea_id)) ? $tea_id : $args[0];
$ate_id = (isset($ate_id)) ? $ate_id : (isset($args[1])?$args[1]:0);

$traer_campos_asociados = (isset($traer_campos_asociados)) ? $traer_campos_asociados : (isset($args[2])?$args[2]:0);
$extender_campos_anteriores = (isset($extender_campos_anteriores)) ? $extender_campos_anteriores : (isset($args[3])?$args[3]:0);

$cae_transicion_estado_atencion = $tea_id;
//$filtro_valor_actual = "AND paa_paso_anterior IS NULL";
$filtro_valor_actual = "AND paa_confirmado IS NULL";

if ($extender_campos_anteriores == 1) {
    //traer todos los campos y valores de la atencion, incluidos pasos anteriores, sin considerar la transicion:
    $cae_transicion_estado_atencion = "
        SELECT tea_id
        FROM sai_transicion_estado_atencion
        ,sai_paso_atencion
        WHERE tea_borrado IS NULL
        AND paa_borrado IS NULL
        AND paa_transicion_estado_atencion = tea_id
        AND NOT paa_confirmado IS NULL
        AND paa_atencion = $ate_id
    ";
    $filtro_valor_actual = "";
} else if ($traer_campos_asociados == 1) {
    //trae los campos de las transiciones de los otros destinatarios
    $cae_transicion_estado_atencion = "
        SELECT tea_id
        FROM sai_transicion_estado_atencion
        WHERE tea_borrado IS NULL
        AND tea_estado_atencion_actual = (
            SELECT tea_estado_atencion_actual 
            FROM sai_transicion_estado_atencion 
            WHERE tea_id = $tea_id
        )
        AND tea_estado_atencion_siguiente = (
            SELECT tea_estado_atencion_siguiente 
            FROM sai_transicion_estado_atencion 
            WHERE tea_id = $tea_id
        )
        AND tea_pertinencia_proveedor = (
            SELECT tea_pertinencia_proveedor 
            FROM sai_transicion_estado_atencion 
            WHERE tea_id = $tea_id
        )
    ";
}

$sql = "
    SELECT *
    ,(
        SELECT concat(vae_texto, vae_numero, to_char(vae_fecha, 'YYYY-MM-DD'), vae_nodo, vae_ciudad) 
        FROM sai_valor_extra
        , sai_paso_atencion 
        WHERE vae_borrado IS NULL 
        AND paa_borrado IS NULL 
        AND vae_campo_extra = cae.cae_id 
        AND paa_id=vae_paso_atencion
        $filtro_valor_actual



        AND paa_atencion = $ate_id
        ORDER BY vae_creado DESC
        LIMIT 1
    ) AS valor
    ,(
        SELECT concat(vae_texto, vae_numero, to_char(vae_fecha, 'YYYY-MM-DD'), vae_nodo, vae_ciudad) 
        FROM sai_valor_extra
        , sai_paso_atencion 
        WHERE vae_borrado IS NULL 
        AND paa_borrado IS NULL 
        AND vae_campo_extra IN (
            SELECT cae_historico.cae_id
            FROM sai_campo_extra AS cae_historico
            WHERE cae_historico.cae_borrado IS NULL
            AND cae_historico.cae_codigo = cae.cae_codigo
        ) 
        AND paa_id = vae_paso_atencion
        AND NOT paa_confirmado IS NULL

        AND paa_atencion = $ate_id
        ORDER BY vae_creado DESC
        LIMIT 1
    ) AS valor_historico
    ,(
        SELECT concat(vae_texto, vae_numero, to_char(vae_fecha, 'YYYY-MM-DD'), vae_nodo, vae_ciudad) 
        FROM sai_valor_extra
        , sai_paso_atencion 
        WHERE vae_borrado IS NULL 
        AND paa_borrado IS NULL 
        AND vae_campo_extra IN (
            SELECT cae_historico.cae_id
            FROM sai_campo_extra AS cae_historico
            WHERE cae_historico.cae_borrado IS NULL
            AND cae_historico.cae_codigo = cae.cae_valor_por_defecto
        ) 
        AND paa_id = vae_paso_atencion
        AND NOT paa_confirmado IS NULL

        AND paa_atencion = $ate_id
        ORDER BY vae_creado DESC
        LIMIT 1
    ) AS valor_por_defecto 
    ,(
        SELECT concat(vae_texto, vae_numero, to_char(vae_fecha, 'YYYY-MM-DD'), vae_nodo, vae_ciudad) 
        FROM sai_valor_extra
        , sai_paso_atencion 
        WHERE vae_borrado IS NULL 
        AND paa_borrado IS NULL 
        AND vae_campo_extra IN (
            SELECT cae_historico.cae_id
            FROM sai_campo_extra AS cae_historico
            WHERE cae_historico.cae_borrado IS NULL
            AND cae_historico.cae_codigo = cae.cae_menor_que
        ) 
        AND paa_id = vae_paso_atencion
        AND NOT paa_confirmado IS NULL

        AND paa_atencion = $ate_id
        ORDER BY vae_creado DESC
        LIMIT 1
    ) AS menor_que 
    ,(
        SELECT concat(vae_texto, vae_numero, to_char(vae_fecha, 'YYYY-MM-DD'), vae_nodo, vae_ciudad) 
        FROM sai_valor_extra
        , sai_paso_atencion 
        WHERE vae_borrado IS NULL 
        AND paa_borrado IS NULL 
        AND vae_campo_extra IN (
            SELECT cae_historico.cae_id
            FROM sai_campo_extra AS cae_historico
            WHERE cae_historico.cae_borrado IS NULL
            AND cae_historico.cae_codigo = cae.cae_mayor_que
        ) 
        AND paa_id = vae_paso_atencion
        AND NOT paa_confirmado IS NULL


        AND paa_atencion = $ate_id
        ORDER BY vae_creado DESC
        LIMIT 1
    ) AS mayor_que 
    , (
        SELECT 
        CASE 
            WHEN nod_atencion <> nod_atencion_referenciada
            THEN concat(trim(concat('Servicio activo ', ate_secuencial, ' ', COALESCE(ate_codigo, ''))), ', punto ', nod_codigo)

            ELSE concat('Punto ', nod_codigo)
        END
        FROM sai_valor_extra
        , sai_paso_atencion 
        , sai_nodo
        , sai_ubicacion
        , sai_atencion
        WHERE vae_borrado IS NULL 
        AND paa_borrado IS NULL 
        AND nod_borrado IS NULL
        AND ubi_borrado IS NULL
        AND ate_borrado IS NULL
        AND vae_campo_extra = cae.cae_id 
        AND paa_id=vae_paso_atencion
        AND nod_id = vae_nodo
        AND ubi_id = nod_ubicacion
        AND nod_atencion_referenciada = ate_id
        $filtro_valor_actual
        AND paa_atencion = $ate_id
        ORDER BY vae_creado DESC
        LIMIT 1
    ) AS nodo
    , (
        SELECT to_json(sai_nodo)
        FROM sai_valor_extra
        , sai_paso_atencion 
        , sai_nodo
        WHERE vae_borrado IS NULL 
        AND paa_borrado IS NULL 
        AND nod_borrado IS NULL
        AND vae_campo_extra = cae.cae_id 
        AND paa_id=vae_paso_atencion
        AND nod_id = vae_nodo
        $filtro_valor_actual
        AND paa_atencion = $ate_id
        ORDER BY vae_creado DESC
        LIMIT 1
    ) AS nodo_completo
    , (
        SELECT ciu_nombre 
        FROM sai_valor_extra
        , sai_paso_atencion 
        , sai_ciudad
        WHERE vae_borrado IS NULL 
        AND paa_borrado IS NULL 
        AND ciu_borrado IS NULL
        AND vae_campo_extra = cae.cae_id 
        AND paa_id=vae_paso_atencion
        AND ciu_id = vae_ciudad
        $filtro_valor_actual
        AND paa_atencion = $ate_id
        ORDER BY vae_creado DESC
        LIMIT 1
    ) AS ciudad

    , (
        SELECT des_nombre
        FROM sai_destinatario
        ,sai_transicion_estado_atencion
        WHERE tea_borrado IS NULL
        AND tea_destinatario = des_id
        AND tea_id = cae.cae_transicion_estado_atencion
    ) AS destinatario
    ,(
        SELECT tid_codigo
        FROM sai_tipo_dato
        WHERE tid_id = cae.cae_tipo_dato
    ) AS tipo_dato
    FROM sai_campo_extra AS cae
    WHERE cae.cae_borrado IS NULL
    AND cae.cae_transicion_estado_atencion IN (
        $cae_transicion_estado_atencion
    )
    ORDER BY cae_orden
";
        //AND NOT paa_paso_anterior IS NULL//quitado ya que está validando con paa_confirmado
            //AND cae_historico.cae_id <> cae.cae_id // quitado del historico, menor que, mayor que
//echo "[$sql]";
$campos = q($sql);

//////////////////////
//plantillas de campos:


//var_dump($campos);
if ($imprimir_json) {
    echo json_encode($campos);
}
