<?php

/**
 * Función para cambiar el nombre técnico de una tabla o campo, a su versión en texto común
 */
function n($codigo){
    $nombre = ucfirst(str_replace('_', ' ', substr($codigo, 4)));
    $partes = explode(' ', $nombre);
    foreach($partes as & $parte){
        $parte = preg_replace('/on$/', 'ón', $parte);
        $parte = preg_replace('/fia$/', 'fía', $parte);
        $parte = preg_replace('/alogo$/', 'álogo', $parte);
        $parte = preg_replace('/odigo$/', 'ódigo', $parte);
        $parte = preg_replace('/atico$/', 'ático', $parte);
        $parte = preg_replace('/efono$/', 'éfono', $parte);
        $parte = preg_replace('/edula$/', 'édula', $parte);
        $parte = preg_replace('/onico$/', 'ónico', $parte);
    }
    return implode(' ', $partes);
}

function n2t($numero) {
    include_once 'lib/NumberToLetterConverter.class.php';
    $converter = new NumberToLetterConverter();
    return $converter->to_word($numero);
}


function array_to_xml( $data, &$xml_data = null) {
    $primero = false;
    //if (empty($xml_data)) {
    if (!isset($xml_data)) {
        $primero = true;
        $xml_data = new SimpleXMLElement('<p></p>');
        //echo '<pre>pre:';
        //var_dump($data);
        //echo '</pre>';
    }

    foreach( $data as $key => $value ) {
        if( is_numeric($key) ){
            //$key = 'item'.$key; //dealing with <0/>..<n/> issues
            $key = "ul";
        } else if ($key == 'font'){
            $key = "ul";
        }

        if( is_array($value) ) {
            $subnode = $xml_data->addChild($key);
            array_to_xml($value, $subnode);
        } else {
            $xml_data->addChild($key,trim(htmlspecialchars($value)));
        }
    }
    if ($primero) {
        //$asxml = trim(str_replace("\n", '<br>', trim(trim($xml_data->asXML()), "\n")), '<br>');
        $asxml = str_replace("\n", '<br />', trim(strip_tags($xml_data->asXML()))) ;
        /*
        echo '<div class="alert alert-success">';
        echo '<pre>';
        var_dump($xml_data);
        echo '</pre>';
        echo '<pre>';
        var_dump($xml_data->asXML());
        echo '</pre>';
        echo '<pre>';
        var_dump($asxml);
        echo '</pre>';
        echo '</div>';
         */
        return $asxml;
    }
}

function p_formatear_fecha($timestamp){
    date_default_timezone_set('America/Guayaquil');
    setlocale(LC_ALL,"es_ES@euro","es_ES","esp");
    $fecha = strftime("%A %d de %B de %Y a las %Hh%S", strtotime($timestamp));
    //$fecha = htmlentities($fecha);
    $fecha = utf8_encode($fecha);
    $fecha = empty($fecha) ? $timestamp : $fecha;
    return $fecha;
}

// https://gist.github.com/luiscelismx/9281064
function elimina_acentos($text) {
    $text = htmlentities($text, ENT_QUOTES, 'UTF-8');
    $text = strtolower($text);
    $patron = array (
        // Espacios, puntos y comas por guion
        //'/[\., ]+/' => ' ',

        // Vocales
        '/\+/' => '',
        '/&agrave;/' => 'a',
        '/&egrave;/' => 'e',
        '/&igrave;/' => 'i',
        '/&ograve;/' => 'o',
        '/&ugrave;/' => 'u',

        '/&aacute;/' => 'a',
        '/&eacute;/' => 'e',
        '/&iacute;/' => 'i',
        '/&oacute;/' => 'o',
        '/&uacute;/' => 'u',

        '/&acirc;/' => 'a',
        '/&ecirc;/' => 'e',
        '/&icirc;/' => 'i',
        '/&ocirc;/' => 'o',
        '/&ucirc;/' => 'u',

        '/&atilde;/' => 'a',
        '/&etilde;/' => 'e',
        '/&itilde;/' => 'i',
        '/&otilde;/' => 'o',
        '/&utilde;/' => 'u',

        '/&auml;/' => 'a',
        '/&euml;/' => 'e',
        '/&iuml;/' => 'i',
        '/&ouml;/' => 'o',
        '/&uuml;/' => 'u',

        '/&auml;/' => 'a',
        '/&euml;/' => 'e',
        '/&iuml;/' => 'i',
        '/&ouml;/' => 'o',
        '/&uuml;/' => 'u',

        // Otras letras y caracteres especiales
        '/&aring;/' => 'a',
        '/&ntilde;/' => 'n',

        // Agregar aqui mas caracteres si es necesario

    );

    $text = preg_replace(array_keys($patron),array_values($patron),$text);
    return $text;
}
