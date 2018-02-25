<?php


//var_dump($_POST);
//return;
$cli_id = $_POST['cliente'];
$cue_id = $_POST['cuenta'];
$pro_id_lista = $_POST['proveedor'];
$ser_id = $_POST['servicio'];
$con_id = $_POST['contacto'];
$contacto_en_sitio_con_id = $_POST['contacto_en_sitio'];
$usuario_tecnico = $_POST['usuario_tecnico'];
$usuario_comercial = $_POST['usuario_comercial'];
$esa_id = "(SELECT esa_id FROM sai_estado_atencion WHERE esa_nombre ILIKE '%factibilidad nueva%')";

$cantidad_extremos = (isset($_POST['cantidad_extremos']) && !empty($_POST['cantidad_extremos'])) ? intval($_POST['cantidad_extremos']) : 1;
$cantidad_extremos = ($cantidad_extremos > 0) ? $cantidad_extremos : 1;


$respuesta = array();
foreach ($pro_id_lista as $pro_id) {

    for ($i = 0; $i < $cantidad_extremos; $i++) {
        $pep_id = "(SELECT pep_id FROM sai_pertinencia_proveedor WHERE pep_borrado IS NULL AND pep_proveedor=$pro_id AND pep_servicio=$ser_id)";
        //$ser_id = "(SELECT pep_servicio FROM sai_pertinencia_proveedor WHERE pep_id=$pep_id)";
        $result = q("
            INSERT INTO sai_atencion(
                ate_cliente
                ,ate_cuenta
                ,ate_pertinencia_proveedor
                ,ate_usuario_tecnico
                ,ate_usuario_comercial
                ,ate_estado_atencion
                ,ate_servicio
                ,ate_contacto
                ,ate_contacto_en_sitio
                ,ate_creado_por
            ) VALUES (
                $cli_id
                ,$cue_id
                ,$pep_id
                ,$usuario_tecnico
                ,$usuario_comercial
                ,$esa_id
                ,$ser_id
                ,$con_id
                ,$contacto_en_sitio_con_id
                ,{$_SESSION['usu_id']}
            ) RETURNING *
        ");
        $respuesta[$pro_id] = $result;

        $ate_id = $result[0]['ate_id']; 

        //Trata de obtener una transicion para asociarla al paso que se va a crear:
        $result_tea_id = q("
            SELECT tea_id
            FROM sai_transicion_estado_atencion
            WHERE tea_borrado IS NULL
            AND tea_estado_atencion_actual = (
                SELECT ate_estado_atencion 
                FROM sai_atencion 
                WHERE ate_id = $ate_id
            )
            AND tea_pertinencia_proveedor = (
                SELECT ate_pertinencia_proveedor 
                FROM sai_atencion
                WHERE ate_id = $ate_id
            )
        ");
        $tea_id = 'null';
        if ($result_tea_id) {
            $tea = array();
            foreach ($result_tea_id as $r) {
                if (!isset($tea[$r[tea_estado_atencion_siguiente]])) {
                    $tea[$r[tea_estado_atencion_siguiente]] = array();
                }
                $tea[$r[tea_estado_atencion_siguiente]][$r[tea_destinatario]] = $r;
            }
            if (count($tea) == 1) {
                //si solo existe un estado siguiente posible para el estado de la atención: 
                foreach ($tea as $tea_estado_atencion_siguiente => $siguiente) {
                    foreach ($siguiente as $tea_destinatario => $destinatario) {
                        //coje el tea del último destinatario:
                        $tea_id = $destinatario[tea_id];
                    }
                }
            }
        }
        //crea el paso:
        $paa_id = q("
            INSERT INTO sai_paso_atencion (
                paa_transicion_estado_atencion
                , paa_atencion
                , paa_creado_por
            ) VALUES (
                $tea_id
                , $ate_id
                , {$_SESSION['usu_id']}
            ) RETURNING *
        ")[0][paa_id];

    }
}
echo json_encode($respuesta);
