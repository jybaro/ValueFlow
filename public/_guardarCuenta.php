<?php


//header('Content-Type: application/json');



if (isset($_POST['dataset_json']) && !empty($_POST['dataset_json'])) {
    $dataset_json = $_POST['dataset_json'];
} else {
    $dataset_json = file_get_contents("php://input");
}

if (!empty($dataset_json)) {

    $dataset = json_decode($dataset_json);
    if (isset($dataset->codigo) && !empty($dataset->codigo)) {
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

            if ($count_cuentas_codigo == 0) {
                    //crea cuenta
                    $campos = 'codigo,peso,padre,cliente,responsable_cobranzas,usuario_tecnico,contacto';
                    $campos_array = explode(',', $campos);
                    $sql_insert_campos = '';
                    $sql_insert_valores = '';
                    $glue = '';
                    foreach ($campos_array as $campo){

                        if (isset($dataset->$campo) && !empty($dataset->$campo)) {
                            $_ = '';

                            switch ($campo){
                            case 'codigo':
                                $_ = "'";
                                break;
                            case 'peso': default:
                                break;
                            }

                            $sql_insert_campos .= $glue . 'cue_' . $campo;
                            $sql_insert_valores .= $glue . $_ . $dataset->$campo . $_;
                            $glue = ',';
                        }

                    }
                    $result = q("INSERT INTO sai_cuenta($sql_insert_campos) VALUES($sql_insert_valores) RETURNING *");
            } else if (!empty($id) && $count_cuentas_codigo == 1) {
                //actualiza cuenta
                $campos = 'codigo,peso,padre,cliente,responsable_cobranzas,usuario_tecnico,contacto';
                $campos_array = explode(',', $campos);
                $sql_update = '';
                $glue = '';
                foreach ($campos_array as $campo){

                    if (isset($dataset->$campo) && !empty($dataset->$campo)) {
                        $_ = '';

                        switch ($campo){
                        case 'codigo':
                            $_ = "'";
                            break;
                        case 'peso': default:
                            break;
                        }

                        $sql_update .= "$glue cue_$campo = ". $_ . $dataset->$campo . $_;
                        $glue = ',';
                    }

                }
                $sql = ("UPDATE sai_cuenta SET $sql_update WHERE cue_id=$id RETURNING *");
                echo $sql;
                $result = q($sql);
            } else {
                //borra cuentas con codigo repetida
                $result = array(array('ERROR' => "Ya existe una cuenta con descripcion $codigo"));
            }
        }
    } else {
        $result = array(array('ERROR' => 'No se ha enviado el codigo', 'dataset' => $dataset));
    }
} else {
    $result = array(array('ERROR' => 'No se han enviado datos'));
}
$respuesta = array();
foreach($result[0] as $k => $v) {
    $respuesta[str_replace('cue_', '', $k)] = $v;
}
echo json_encode(array($respuesta));

