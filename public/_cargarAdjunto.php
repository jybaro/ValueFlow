<?php

//var_dump($_POST);
//var_dump($_FILES);
//print_r($_FILES);
$adjuntoName = $_FILES['adjunto']['name'];
$adjuntoType = $_FILES['adjunto']['type'];
$adjuntoError = $_FILES['adjunto']['error'];
$adjuntoContent = file_get_contents($_FILES['adjunto']['tmp_name']);
$message = '';
$nombre = '';

if($adjuntoError == UPLOAD_ERR_OK){
    $nombre = basename($_FILES["adjunto"]["name"]);
    $nombre = limpiar_nombre_archivo($nombre);
    $nombre = rand(100000,999999) . '-'. $nombre;
    //$ruta = "uploads/".$nombre;
    $ruta = "".$nombre;
    $tmp_name = $_FILES["adjunto"]["tmp_name"];
    if ( move_uploaded_file($tmp_name, $ruta) ) {
        $message = 'OK';
    } else {
        $message = 'No se pudo subir el archivo.';
    }
}else{
   switch($adjuntoError){
     case UPLOAD_ERR_INI_SIZE:
          $message = 'Error al intentar subir un archivo que excede el tamaño permitido.';
          break;
     case UPLOAD_ERR_FORM_SIZE:
          $message = 'Error al intentar subir un archivo que excede el tamaño permitido.';
          break;
     case UPLOAD_ERR_PARTIAL:
          $message = 'Error: no terminó la acción de subir el archivo.';
          break;
     case UPLOAD_ERR_NO_FILE:
          $message = 'Error: ningún archivo fue subido.';
          break;
     case UPLOAD_ERR_NO_TMP_DIR:
          $message = 'Error: servidor no configurado para carga de archivos.';
          break;
     case UPLOAD_ERR_CANT_WRITE:
          $message= 'Error: posible falla al grabar el archivo.';
          break;
     case  UPLOAD_ERR_EXTENSION:
          $message = 'Error: carga de archivo no completada.';
          break;
     default: $message = 'Error: carga de archivo no completada.';
              break;
    }
}

echo json_encode(array(
    'error' => true,
    'message' => $message,
    'nombre' => $nombre
));
