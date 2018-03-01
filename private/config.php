<?php

date_default_timezone_set('America/Guayaquil');

define('SMTP_SERVER_ZENIX', 'mail.zenix.com.ec');
define('SMTP_PORT_ZENIX', 587);
define('SMTP_USERNAME_ZENIX', 'sait@zenix.com.ec');
define('SMTP_PASSWORD_ZENIX', 'n3D1$207*');
define('MAIL_ORDERS_ADDRESS_ZENIX', 'sait@zenix.com.ec');

define('SMTP_SERVER', 'mail.nedetel.net');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'sait@nedetel.net');
define('SMTP_PASSWORD', 'n3D1$207*');
define('MAIL_ORDERS_ADDRESS', 'sait@nedetel.net');

define('MAIL_ORDERS_NAME', 'SAIT');
define('MAIL_COPY_ALL_ADDRESS', 'soporte@nedetel.net');
define('MAIL_COPY_ALL_NAME', 'Soporte');

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

