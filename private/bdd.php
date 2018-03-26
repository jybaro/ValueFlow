<?php

function q($sql, $callback = false) {
    global $conn;

    /*
    if (strpos($sql, 'SELECT') === false) {
    }
     */
    l('SQL: ' . $sql);
    $sql = str_replace("\n", ' ', $sql);
    $sql = str_replace("\r", ' ', $sql);

    $data = null;
    $result = pg_query($conn, $sql);
    if ($result) {
        if ($callback) {
            while($row = pg_fetch_array($result)){
                $callback($row);
            }
        } else {
            $data = pg_fetch_all($result);
            //var_dump($data);
            //$data = count($data) === 1 ? (count($data[0]) === 1 ? $data[0][0] : $data[0]) : $data;
        }
    } else {
        l(pg_last_error($conn) . " [$sql]");
    }
    return $data;
}

function l($texto){
    global $conn;
    $log = pg_escape_literal($texto);
    $usuario = ((isset($_SESSION['usu_id']) && !empty($_SESSION['usu_id'])) ? pg_escape_string($_SESSION['usu_id']) : 'null');
    $ip = ((isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR'])) ? pg_escape_literal($_SERVER['REMOTE_ADDR']) : 'null');
    pg_send_query($conn, "INSERT INTO sai_log(log_texto, log_creado_por, log_ip) VALUES ($log, $usuario, $ip)");
}

function c($codigo){
    $codigo = pg_escape_literal($codigo);
    $result = q("SELECT cat_texto FROM sai_catalogo WHERE cat_codigo=$codigo");
    $resultado = '';
    if ($result) {
        $resultado = $result[0]['cat_texto'];
    }
    return $resultado;
}
