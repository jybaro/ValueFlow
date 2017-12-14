<h1>Atenciones</h1>
<?php

$result = q("SELECT * FROM sai_atencion");

if ($result) {
    foreach($result as $r){
        echo '<div><form><button class="btn btn-info" href="#" onclick="">'.$r['ate_creado'].'</button></form></div>';
        $estado = $r['ate_estado_atencion'];
        $result2 = q("SELECT * FROM sai_transicion_estado_atencion, sai_estado_atencion WHERE tea_estado_atencion_hijo = esa_id AND tea_estado_atencion_padre=$estado");
        if ($result2){
            echo "<ul>";
            foreach($result2 as $r2){
                echo "<li><button class='btn btn-success'>";
                echo "Pasar al estado: ". $r2['esa_nombre'];
                echo "</button></li>";

            }
            echo "</ul>";
        }

    }

}

