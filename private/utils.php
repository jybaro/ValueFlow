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
    //$fecha = utf8_encode($fecha);
    $fecha = empty($fecha) ? $timestamp : $fecha;
    return $fecha;
}
