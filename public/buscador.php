<?php
if (isset($args[0]) && !empty($args[0])) {
    $titulo_proceso = 'Resultados de búsqueda';
    $titulo_proceso_singular = 'resultado de búsqueda';
    $busqueda = pg_escape_string($args[0]);
    $filtro = "(SELECT esa_id FROM sai_estado_atencion)";
    $mostrar_nuevo = true;
    require_once('proceso.php');
} else {
    header('Location:/factibilidades');
}
