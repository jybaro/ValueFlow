<?php


//header('Content-Type: application/json');
$ess_id = $_SESSION['ess_id'];

if (isset($_POST['dataset_json']) && !empty($_POST['dataset_json'])) {
    $dataset_json = $_POST['dataset_json'];
} else {
    $dataset_json = file_get_contents("php://input");
}

$respuesta = array();
$error = array();
if (!empty($dataset_json)) {

    $dataset = json_decode($dataset_json);
    if (isset($dataset->rol) && !empty($dataset->rol) && isset($dataset->modulo) && !empty($dataset->modulo)) {
        $rol = $dataset->rol;
        $modulo = $dataset->modulo;

        //$count_permiso = q("SELECT COUNT(*) FROM sai_permiso WHERE per_rol=$rol AND per_objeto=$modulo")[0]['count'];
        $result_permiso = q("SELECT * FROM sai_permiso WHERE per_rol = $rol AND per_objeto = $modulo");


        //if ($count_permiso == 0) {
        if ($result_permiso) {
            $solo_lectura = $result_permiso[0]['per_solo_lectura'];
            if ($solo_lectura == 1) {
                q("DELETE FROM sai_permiso WHERE per_objeto = $modulo AND per_rol = $rol");
            } else {
                q("UPDATE sai_permiso SET per_solo_lectura = 1 WHERE per_objeto = $modulo AND per_rol = $rol");
            }
        } else {
            q("INSERT INTO sai_permiso (per_objeto, per_rol) VALUES ($modulo, $rol)");
        }
        $rol_version = q("UPDATE sai_rol SET rol_version=rol_version+1 WHERE rol_id = $rol RETURNING rol_version")[0]['rol_version'];

        //$count_permiso = q("SELECT COUNT(*) FROM sai_permiso WHERE per_rol = $rol AND per_objeto = $modulo")[0]['count'];
        $result_permiso = q("SELECT * FROM sai_permiso WHERE per_rol = $rol AND per_objeto = $modulo");

        $respuesta = array(
            'rol' => $rol,
            'modulo' => $modulo,
            //'count_permiso' => $count_permiso
            'result_permiso' => $result_permiso
        );

    } else {
        $error = array('sinRolModulo' => 'No se ha mandado el rol y/o el objeto.');
    }
}
echo json_encode(array('respuesta'=>$respuesta, 'error'=>$error));
