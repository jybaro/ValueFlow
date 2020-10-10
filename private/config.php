<?php

date_default_timezone_set('America/Guayaquil');

define('SMTP_SERVER', 'mail.jybaro.com');
define('SMTP_PORT', 666);
define('SMTP_USERNAME', 'vf@jybaro.com');
define('SMTP_PASSWORD', '__vf_smtp_change_me___');
define('MAIL_ORDERS_ADDRESS', 'vf@jybaro.com');

define('MAIL_ORDERS_NAME', 'VALUE FLOW');
define('MAIL_COPY_ALL_ADDRESS', 'soporte@jybaro.com');
define('MAIL_COPY_ALL_NAME', 'Soporte');

$bdd_config = array(
    'host' => '127.0.0.1',
    'port' => '5432',
    'dbname' => 'vf',
    'user' => 'vf_user',
    'password' => '___vf_user_change_me___'
);

$string_conexion = '';
foreach($bdd_config as $k => $v) {
    $string_conexion .= " $k=$v";
}
$conn = pg_pconnect($string_conexion);

