<?php

date_default_timezone_set('America/Guayaquil');

$bdd_config = array(
    'host' => '127.0.0.1',
    'port' => '5432',
    'dbname' => 'nedetel',
    'user' => 'nedetel_user',
    'password' => 'Nedetel.2017'
);

$string_conexion = '';
foreach($bdd_config as $k => $v) {
    $string_conexion .= " $k=$v";
}
$conn = pg_pconnect($string_conexion);

