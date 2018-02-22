<?php

function p_reemplazar_campos_valores($celda) {
    global $campos_valores;

    $nuevo_valor = $celda;
    if (preg_match_all('/\$\{([a-zA-Z0-9_]+)\}/', $celda, $matches)){
        foreach ($matches[0] as $k => $match) {
            $campo_codigo = $matches[1][$k];
            $valor = $campos_valores[$campo_codigo];
            $nuevo_valor = str_replace($match, $valor, $nuevo_valor);
        }
    }
    return $nuevo_valor;
}

$txt = '67 %';

echo p_reemplazar_campos_valores($txt);
