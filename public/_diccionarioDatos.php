<style>
table {
    border-collapse: collapse;
}
table, th, td {
    border: 1px solid black;
}
</style>
<?php

$tabla = (isset($args[0]) ? $args[0] : null);
$accion = (isset($args[1]) ? $args[1] : '');

if (empty($tabla)) {
    //despliega todas las tablas
    $result = q("SELECT *
        FROM information_schema.columns
        WHERE table_schema = 'public'
        ORDER BY table_name, data_type, is_nullable, column_name
        ");
    $table_name = null;
    $count_tabla = 0;
    $count_campo = 0;
    echo "<h1>Diccionario de datos</h1>";
    echo "<table>";

    foreach($result as $r){
        if ($table_name != $r['table_name']) {
            $count_tabla++;
            $count_campo = 0;
            $table_name = $r['table_name'];
            $count_registros = q("SELECT COUNT(*) FROM $table_name")[0]['count'];
            $agregar_fin_tabla = true;
            echo "</table>";
            echo "<h3>$table_name</h3>";
            echo "<table class='table table-bordered'>";
            echo "<tr><th>&nbsp;</th><th>Campo</th><th>Tipo</th><th>Opcional</th><th>Valor por defecto</th></tr>";
        }
        $count_campo++;

        echo "<tr>";
        echo "<th>$count_campo.</th>";
        echo "<td>{$r[column_name]}</td>";
        echo "<td>{$r[data_type]}</td>";
        echo "<td>{$r[is_nullable]}</td>";
        echo "<td>{$r[column_default]}</td>";
        echo "</tr>";
        //var_dump($r);

    }
    echo "</table>";
}
?>



