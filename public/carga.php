<?php
if (isset($_FILES) && !empty($_FILES)) {
    var_dump($_FILES);
    $tmp_name = $_FILES['archivo']['tmp_name'];
    if (isset($_FILES['archivo']) && !empty($tmp_name)) {
        if ($error == UPLOAD_ERR_OK){
            $name = basename($_FILES["archivo"]["name"]);
            $fecha = date('Ymd-His'); 
            $ruta = "uploads/{$fecha}-{$name}";
            if ( move_uploaded_file($tmp_name, $ruta) ) {
                echo "Guardado $ruta";

            }
        }

    }
}
?>
<h1>Carga</h1>
<form method="POST" enctype="multipart/form-data">
<input name="archivo" type="file">
<input type="submit" value="Cargar">
</form>

