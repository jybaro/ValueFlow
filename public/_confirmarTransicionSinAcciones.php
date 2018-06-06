<?php

//var_dump($_POST);
//return;

function p_confirmar_transicion_sin_acciones($ate_id, $tea_id, $estado_siguiente_id){
    

    //echo "[[EN p_confirmar_transicion_sin_acciones: $ate_id, $tea_id, $estado_siguiente_id]]";
    $respuesta = array();

    //$ate_id = $_POST['ate_id'];
    //$estado_siguiente_id = $_POST['estado_siguiente_id'];

    /*
    $result_destinatarios = q("
        SELECT des_nombre FROM sai_destinatario
    ");
    //$destinatarios = array('cliente', 'proveedor', 'usuario');
    $destinatarios = array();
    if ($result_destinatarios) {
        foreach($result_destinatarios  as $r) {
            $destinatarios[] = $r['des_nombre'];
        }
    }
     */

    $paa_id_lista = array();
    $paa_lista = array();

    //foreach ($destinatarios as $destinatario) {


    //obtiene el tea_id ultimo, por si haya sido actualizado durante el guardado:
    $result_tea = q("
        SELECT *
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
        AND tea_destinatario = (
            SELECT tea_destinatario
            FROM sai_transicion_estado_atencion 
            WHERE tea_id = $tea_id
        )
    ");

    if ($result_tea) {
        $tea_id = $result_tea[0][tea_id];
        //$tea_automatico = $result_tea[0][tea_automatico];
        $tea_estado_atencion_siguiente = $result_tea[0][tea_estado_atencion_siguiente];
    }

    $result = q("
        INSERT INTO sai_paso_atencion (
            paa_atencion
            ,paa_transicion_estado_atencion
            ,paa_creado_por
            ,paa_confirmado
        ) VALUES (
            $ate_id
            ,$tea_id
            ,{$_SESSION['usu_id']}
            ,now()
        ) RETURNING *
    ");
    if ($result) {
        //echo "[[INSERTADO NUEVO PASO: $ate_id, $tea_id]]";
        $paa_id = $result[0]['paa_id'];
        $paa_id_lista[] = $paa_id;
        $paa_lista[] = $result;
    }
    //}

    if (!empty($paa_id_lista)) {
        require('_obtenerValoresVigentes.php');
        $valores_vigentes = $resultado;
        $campos_valores = array();
        foreach($valores_vigentes as $valor_vigente){
            $campos_valores[$valor_vigente['codigo']] = $valor_vigente['valor'];
        }

        $capacidad_contratada = isset($campos_valores['CAPACIDAD_CONTRATADA']) ? $campos_valores['CAPACIDAD_CONTRATADA'] : 0;
        $capacidad_facturada = isset($campos_valores['CAPACIDAD_FACTURADA']) ? $campos_valores['CAPACIDAD_FACTURADA'] : 0;
        $capacidad_solicitada = isset($campos_valores['CAPACIDAD_SOLICITADA']) ? $campos_valores['CAPACIDAD_SOLICITADA'] : 0;
        $precio_mb = isset($campos_valores['PRECIO_MB']) ? $campos_valores['PRECIO_MB'] : 0;
        $costo_mb = isset($campos_valores['COSTO_MB']) ? $campos_valores['COSTO_MB'] : 0;

        $servicio_activado = "NULL";
        $sql = ("
            SELECT count(*)
            FROM sai_estado_atencion
            WHERE esa_borrado IS NULL
            AND esa_play = 1
            AND esa_id = $estado_siguiente_id
        ");
        /*
        $sql = ("
            SELECT count(*)
            FROM sai_estado_atencion
            WHERE esa_borrado IS NULL
            AND (
                esa_nombre ILIKE '%servicio activo%'
                OR esa_nombre ILIKE '%incremento%'
                OR esa_nombre ILIKE '%decremento%'
            )
            AND esa_id = $estado_siguiente_id
        ");
         */
        //echo "[[Sin Acciones]]";
        //echo $sql;
        $result_estado = q($sql);

        if ($result_estado) {
            if ($result_estado[0]['count'] == 1) {
                $servicio_activado = "now()";
            }
        }

        $respuesta['pasos_nuevos'] = $paa_lista;
        $paa_id_lista = implode(',', $paa_id_lista);
        $result = q("
            UPDATE sai_paso_atencion 
            SET 
            paa_capacidad_contratada = $capacidad_contratada
            ,paa_capacidad_facturada = $capacidad_facturada
            ,paa_precio_mb = $precio_mb
            ,paa_costo_mb = $costo_mb
            ,paa_servicio_activado = $servicio_activado
            WHERE paa_borrado IS NULL
            AND paa_id IN ($paa_id_lista)
            RETURNING *
        ");
        $result = q("
            UPDATE sai_paso_atencion 
            SET paa_paso_anterior = now()
            WHERE paa_borrado IS NULL
            AND paa_paso_anterior IS NULL
            AND paa_atencion = $ate_id
            AND NOT paa_id IN ($paa_id_lista)
            RETURNING *
        ");
        $sql = ("
            UPDATE sai_atencion 
            SET ate_estado_atencion = $estado_siguiente_id 
            ,ate_servicio_activado = $servicio_activado
            WHERE ate_borrado IS NULL
            AND ate_id = $ate_id 
            RETURNING *
        ");
        $result = q($sql);
        //echo "[[ACTUALIZADOS PASOS ANTERIORES: ate_id:$ate_id, NOT paa_id IN ($paa_id_lista)]]";
        $respuesta['pasos_anteriores'] = $result;
        $result = array();

        $respuesta['atencion'] = $result;
    } else {
        $respuesta = array('ERROR' => 'No se pudo realizar el cambio de estado.');
    }

    //if ($tea_automatico == 1) {
        $result_next = q("
            SELECT *
            FROM sai_transicion_estado_atencion
            WHERE tea_borrado IS NULL
            AND tea_estado_atencion_actual = (
                SELECT tea_estado_atencion_siguiente 
                FROM sai_transicion_estado_atencion 
                WHERE tea_id = $tea_id
            )
            AND tea_pertinencia_proveedor = (
                SELECT tea_pertinencia_proveedor 
                FROM sai_transicion_estado_atencion 
                WHERE tea_id = $tea_id
            )
        ");
        if ($result_next) {
            $tea_next = $result_next[0];
            
            $tea_id_next = $tea_next['tea_id'];
            $tea_automatico_next = $tea_next['tea_automatico'];
            $tea_estado_atencion_siguiente_next = $tea_next['tea_estado_atencion_siguiente'];
            if ($tea_automatico_next == 1) {
                $respuesta['automatico'] = p_confirmar_transicion_sin_acciones($ate_id, $tea_id_next, $tea_estado_atencion_siguiente_next);
            }
        }
    //}

    //echo json_encode($respuesta);
    return $respuesta;
}

//if (!empty($_POST) && isset($_POST['ate_id']) && !empty($_POST['ate_id']) && isset($_POST['estado_siguiente_id']) && !empty($_POST['estado_siguiente_id'])) {
//    p_confirmar_transicion_sin_acciones($_POST['ate_id'], $_POST['tea_id'], $_POST['estado_siguiente_id']);
//}

