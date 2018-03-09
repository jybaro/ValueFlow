<?php

$resultado = array();
$json = true;
if (isset($ate_id)) {
    $json = false;
} else if (isset($args[0]) && !empty($args[0])) {
    $ate_id = intval($args[0]);
} else {
    $ate_id = null;
}

if (!empty($ate_id)) {
    $codigos = array();

    //    SELECT concat(ate_secuencial, '. ', COALESCE(ate_codigo, '(sin ID)'))
    $result = q("
        SELECT *
        ,concat(vae_texto, vae_numero, to_char(vae_fecha, 'YYYY-MM-DD'), vae_nodo, vae_conexion, vae_ciudad) AS valor
    , (
        SELECT concat(trim(concat('', ate_secuencial, ' ', COALESCE(ate_codigo, ''))), ', punto ', nod_codigo)
        FROM 
        sai_nodo
        , sai_ubicacion
        , sai_atencion
        WHERE 
        nod_borrado IS NULL
        AND ubi_borrado IS NULL
        AND ate_borrado IS NULL
        AND nod_id = vae_nodo
        AND ubi_id = nod_ubicacion
        AND nod_atencion_referenciada = ate_id
    ) AS nodo
    , (
        SELECT ciu_nombre 
        FROM 
         sai_ciudad
        WHERE 
        ciu_borrado IS NULL
        AND ciu_id = vae_ciudad
    ) AS ciudad
        FROM sai_paso_atencion
        , sai_valor_extra
        , sai_campo_extra
        WHERE paa_borrado IS NULL
        AND vae_borrado IS NULL
        AND cae_borrado IS NULL
        AND vae_paso_atencion = paa_id
        AND vae_campo_extra = cae_id
        AND NOT paa_confirmado IS NULL
        AND paa_atencion = $ate_id
        ORDER BY vae_creado
    ");

    $etiquetas = array();
    $result_etiquetas = q("SELECT cat_texto FROM sai_catalogo WHERE cat_codigo='cae_codigo_etiquetas'");
    if ($result_etiquetas) {
        $etiquetas = $result_etiquetas[0]['cat_texto'];
        $etiquetas = (array) json_decode($etiquetas, true);
    }
    //var_dump($etiquetas);


        //SELECT concat(nod_codigo, ': ',  nod_descripcion, ' (', ubi_direccion, ')')
    if ($result){
        foreach($result as $r){
            if ($r[valor] === '0' || !empty($r[valor])) {
                $codigo = $r[cae_codigo];
                $codigo = str_replace('_', ' ', $codigo);
                $codigo = ucfirst(strtolower($codigo));
                $etiqueta = isset($etiquetas[$r[cae_codigo]]) ? $etiquetas[$r[cae_codigo]] : $codigo;
                $nodo = $r[nodo];
                $ciudad = $r[ciudad];
                $valor = $r[valor];
                $valor_detallado = (empty($nodo) && empty($ciudad)) ? $valor : $nodo . $ciudad;

                $codigos[$r['cae_codigo']] = array(
                    'codigo' => $r[cae_codigo]
                    , 'etiqueta' => $etiqueta
                    , 'valor' => $valor
                    , 'valor_detallado' => $valor_detallado
                    , 'nodo' => $nodo
                    , 'ciudad' => $ciudad

                );
            }
        }
    }

    //cambia el orden de acuerdo a lo del catalogo:
    $codigos_nuevo_orden = array();
    foreach ($etiquetas as $codigo => $etiqueta) {
        if (isset($codigos[$codigo])) {
            $codigos_nuevo_orden[$codigo] = $codigos[$codigo];
        }
    }
    foreach($codigos as $codigo => $valor) {
        if (!isset($codigos_nuevo_orden[$codigo])) {
            $codigos_nuevo_orden[$codigo] = $valor;
        }
    }
    //$resultado = array_values($codigos);
    $resultado = array_values($codigos_nuevo_orden);
}

if ($json) {
    echo json_encode($resultado);
}
