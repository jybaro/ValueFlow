<?php


//header('Content-Type: application/json');



if (isset($_POST['dataset_json']) && !empty($_POST['dataset_json'])) {
    $dataset_json = $_POST['dataset_json'];
} else {
    $dataset_json = file_get_contents("php://input");
}

if (!empty($dataset_json)) {

    $dataset = json_decode($dataset_json);
    //if (isset($dataset->codigo) && !empty($dataset->codigo)) {
        $id = ( (isset($dataset->id) && !empty($dataset->id)) ? $dataset->id : null);
        $codigo = $dataset->codigo;

        if (isset($dataset->borrar) && !empty($dataset->borrar)) {
            $result = q("UPDATE sai_cuenta SET cue_borrado=now() WHERE cue_id=$id RETURNING *");
        } else if (isset($dataset->recuperar) && !empty($dataset->recuperar)) {
            $sql= ("SELECT COUNT(*) FROM sai_cuenta WHERE cue_borrado IS NULL AND cue_codigo='$codigo'");
            $result = q($sql);
            $count_cuentas_codigo = $result[0]['count']; 

            if ($count_cuentas_codigo == 0) {
                $result = q("UPDATE sai_cuenta SET cue_borrado=null WHERE cue_id=$id RETURNING *");
            } else {
                $result = array(array('ERROR'=>"No se puede recuperar, ya existe cuenta con codigo $codigo"));
            }
        } else {
            //guarda datos de cuenta

            $sql= ("SELECT COUNT(*) FROM sai_cuenta WHERE cue_borrado IS NULL AND cue_codigo='$codigo'");
            //echo "[$sql]";
            $result = q($sql);
            $count_cuentas_codigo = $result[0]['count']; 
            //echo "[count_cuentas_codigo: $count_cuentas_codigo]";

            //if ($count_cuentas_codigo == 0) {
            if (empty($dataset->id)) {
                    //crea cuenta
                    $padre = empty($dataset->padre) ? 'null' : $dataset->padre;
                    $sql = ("
                        INSERT INTO sai_cuenta(
                            cue_peso
                            ,cue_padre
                            ,cue_cliente
                            ,cue_responsable_cobranzas
                        ) VALUES(
                            {$dataset->peso}
                            ,$padre
                            ,{$dataset->cliente}
                            ,{$dataset->responsable_cobranzas}
                        ) RETURNING *
                    ");
                    //echo "[[$sql]]";
                    $result = q($sql);
                    if ($result) {
                        //obtiene el código y lo coloca en la cuenta:
                        $cue_id = $result[0]['cue_id'];
                        require('_obtenerCuenta.php');
                        $r = $result[0];
                        $cue_codigo = 'Cuenta ' . $r['tipo'] . ' de '. $r['cli_razon_social'];
                        q("
                            UPDATE sai_cuenta 
                            SET 
                            cue_codigo = '$cue_codigo'
                            WHERE cue_borrado IS NULL
                            AND cue_id = $cue_id
                        ");
                    }
            //} else if (!empty($id) && $count_cuentas_codigo == 1) {
            } else if (!empty($id)) {
                //actualiza cuenta
                if (empty($dataset->padre) || $dataset->padre != $id) {
                    //si no hay padre, o si el padre es distinto al propio hijo (para filtrar casos que una cuenta sea su propia padre):
                    //$campos = 'codigo,peso,padre,cliente,responsable_cobranzas,usuario_tecnico,contacto';
                    $cue_id = $id;
                    require('_obtenerCuenta.php');
                    $r = $result[0];
                    $cue_codigo = 'Cuenta ' . $r['tipo'] . ' de '. $r['cli_razon_social'];

                    $padre = empty($dataset->padre) ? 'null' : $dataset->padre;
                    $sql = ("
                        UPDATE sai_cuenta 
                        SET  
                        cue_peso = {$dataset->peso}
                        ,cue_padre = {$padre}
                        ,cue_cliente = {$dataset->cliente}
                        ,cue_responsable_cobranzas = {$dataset->responsable_cobranzas}
                        ,cue_codigo = '$cue_codigo'
                        WHERE cue_borrado IS NULL
                        AND cue_id = $id 
                        RETURNING *
                    ");
                    //echo $sql;
                    $result = q($sql);

                } else {
                    //no permite que una misma cuenta sea su propia cuenta padre
                    $result = array(array('ERROR' => "La cuenta con id $id no puede ser su propia cuenta padre."));
                }
            } else {
                //borra cuentas con codigo repetida
                $result = array(array('ERROR' => "Ya existe una cuenta con descripcion $codigo"));
            }
        }
    //} else {
    //    $result = array(array('ERROR' => 'No se ha enviado el codigo', 'dataset' => $dataset));
    // }
} else {
    $result = array(array('ERROR' => 'No se han enviado datos'));
}
$respuesta = array();
foreach($result[0] as $k => $v) {
    $respuesta[str_replace('cue_', '', $k)] = $v;
}
echo json_encode(array($respuesta));

