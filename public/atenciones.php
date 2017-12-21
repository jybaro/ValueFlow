<h1>Atenciones</h1>
<?php
if (isset($_POST['estado']) && !empty($_POST['estado'])) {
    $estado = $_POST['estado'];
    $id = $_POST['id'];
    q("UPDATE sai_atencion SET ate_estado_atencion=$estado WHERE ate_id=$id");
}

$result = q("
    SELECT * 
    FROM sai_atencion
    ,sai_estado_atencion 
    WHERE ate_borrado IS NULL 
    AND esa_borrado IS NULL 
    AND ate_estado_atencion = esa_id
");

if ($result) {
    foreach($result as $r){
        echo '<div><form><button class="btn btn-info" onclick="">'.$r['ate_creado'].' (estado '.$r['esa_nombre'].')</button></form></div>';
        $estado = $r['ate_estado_atencion'];
        $result2 = q("SELECT * FROM sai_transicion_estado_atencion, sai_estado_atencion WHERE tea_estado_atencion_hijo = esa_id AND tea_estado_atencion_padre=$estado");
        if ($result2){
            echo "<ul>";
            foreach($result2 as $r2){
                echo "<form method='POST'>";
                echo "<input type='hidden' name='estado' value='".$r2['esa_id']."'>";
                echo "<input type='hidden' name='id' value='".$r['ate_id']."'>";
                echo "<li><button class='btn btn-success'>";
                echo "Pasar al estado: ". $r2['esa_nombre'];
                echo "</button></li>";
                echo "</form>";

            }
            echo "</ul>";
        }

    }

}

