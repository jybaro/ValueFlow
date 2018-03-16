<div class="page-header">
<h1>Reporte de Atenciones</h1>
</div>
<?php
$sql = ("
    SELECT * 
    ,(usu_tecnico.usu_nombres || ' ' || usu_tecnico.usu_apellidos) AS usu_tecnico_nombre
    ,(usu_comercial.usu_nombres || ' ' || usu_comercial.usu_apellidos) AS usu_comercial_nombre
    ,(
        SELECT to_char(paa_creado, 'YYYY-MM-DD ')
        FROM sai_paso_atencion
        WHERE paa_borrado IS NULL
        AND paa_atencion = ate_id
        ORDER BY paa_creado DESC
        LIMIT 1 
    ) AS fecha_vigencia
    , to_char(ate_creado, 'YYYY-MM-DD') AS fecha_creacion
    ,(
        SELECT esa_codigo
        FROM sai_estado_atencion
        WHERE esa_id = (
            SELECT esa_padre
            FROM sai_estado_atencion
            WHERE esa_id=ate_estado_atencion
        )
    ) AS esa_padre_codigo

    FROM sai_atencion

    LEFT OUTER JOIN sai_servicio
        ON ser_borrado IS NULL
        AND ate_servicio = ser_id

    LEFT OUTER JOIN sai_cuenta
        ON cue_borrado IS NULL
        AND cue_id = ate_cuenta

    LEFT OUTER JOIN sai_cliente
        ON cli_borrado IS NULL
        AND cli_id = ate_cliente

    LEFT OUTER JOIN sai_pertinencia_proveedor
        ON pep_borrado IS NULL
        AND ate_pertinencia_proveedor = pep_id

    LEFT OUTER JOIN sai_proveedor
        ON pro_borrado IS NULL
        AND pep_proveedor = pro_id

    LEFT OUTER JOIN sai_contacto
        ON con_borrado IS NULL
        AND ate_contacto = con_id

    LEFT OUTER JOIN sai_usuario AS usu_tecnico
        ON usu_tecnico.usu_borrado IS NULL
        AND usu_tecnico.usu_id = ate_usuario_tecnico

    LEFT OUTER JOIN sai_usuario AS usu_comercial
        ON usu_comercial.usu_borrado IS NULL
        AND usu_comercial.usu_id = ate_usuario_comercial

    LEFT OUTER JOIN sai_estado_atencion 
        ON esa_borrado IS NULL
        AND ate_estado_atencion = esa_id

    LEFT OUTER JOIN sai_nodo
        ON nod_borrado IS NULL
        AND nod_atencion = ate_id

    LEFT OUTER JOIN sai_ubicacion
        ON ubi_borrado IS NULL
        AND ubi_id = nod_ubicacion

    LEFT OUTER JOIN sai_provincia
        ON prv_borrado IS NULL
        AND prv_id = ubi_provincia

    LEFT OUTER JOIN sai_canton
        ON can_borrado IS NULL
        AND can_id = ubi_canton

    LEFT OUTER JOIN sai_parroquia
        ON par_borrado IS NULL
        AND par_id = ubi_parroquia

    LEFT OUTER JOIN sai_ciudad
        ON ciu_borrado IS NULL
        AND ciu_id = ubi_ciudad
    
    LEFT OUTER JOIN sai_tipo_ultima_milla
        ON tum_borrado IS NULL
        AND tum_id = nod_tipo_ultima_milla

    WHERE ate_borrado IS NULL
        $filtro
        $filtro_busqueda

    ORDER BY 
        ate_id DESC, esa_id DESC
        ,ate_creado DESC
");
$cols = array(
    array(
        'titulo' => 'Secuencial'
        ,'plantilla'=>'<a href="/%1$s#atencion_%2$d">Atención %2$d</a>'
        ,'campos' => array('esa_padre_codigo', 'ate_secuencial')
    )
    ,array(
        'titulo' => 'Tipo de servicio'
        ,'plantilla'=>'%s'
        ,'campos' => array('ser_nombre')
    )
    ,array(
        'titulo' => 'Proveedor'
        ,'plantilla'=>'%s'
        ,'campos' => array('pro_nombre_comercial')
    )
    ,array(
        'titulo' => 'Fecha de creacion'
        ,'plantilla'=>'%s'
        ,'campos' => array('fecha_creacion')
    )
    ,array(
        'titulo' => 'Fecha de último cambio'
        ,'plantilla'=>'%s'
        ,'campos' => array('fecha_vigencia')
    )
    ,array(
        'titulo' => 'Estado'
        ,'plantilla'=>'%s'
        ,'campos' => array('esa_nombre')
    )
    ,array(
        'titulo' => 'ID'
        ,'plantilla'=>'%s'
        ,'campos' => array('ate_codigo')
    )
    ,array(
        'titulo' => 'Empresa'
        ,'plantilla'=>'%s'
        ,'campos' => array('cli_razon_social')
    )
    ,array(
        'titulo' => 'RUC'
        ,'plantilla'=>'%s'
        ,'campos' => array('cli_ruc')
    )
    ,array(
        'titulo' => 'Contacto'
        ,'plantilla'=>'%1$s %2$s'
        ,'campos' => array('con_nombres', 'con_apellidos')
    )
    ,array(
        'titulo' => 'Dependencia'
        ,'plantilla'=>'%s'
        ,'campos' => array('cue_nombre')
    )
    ,array(
        'titulo' => 'Usuario técnico'
        ,'plantilla'=>'%s'
        ,'campos' => array('usu_tecnico_nombre')
    )
    ,array(
        'titulo' => 'Usuario comercial'
        ,'plantilla'=>'%s'
        ,'campos' => array('usu_comercial_nombre')
    )
    ,array(
        'titulo' => 'Punto'
        ,'plantilla'=>'%1$s: %2$s'
        ,'campos' => array('nod_codigo', 'nod_descripcion')
    )
    ,array(
        'titulo' => 'Dirección'
        ,'plantilla'=>'%s'
        ,'campos' => array('ubi_direccion')
    )
    ,array(
        'titulo' => 'Provincia'
        ,'plantilla'=>'%s'
        ,'campos' => array('prv_nombre')
    )
    ,array(
        'titulo' => 'Cantón'
        ,'plantilla'=>'%s'
        ,'campos' => array('can_nombre')
    )
    ,array(
        'titulo' => 'Parroquia'
        ,'plantilla'=>'%s'
        ,'campos' => array('par_nombre')
    )
    ,array(
        'titulo' => 'Ciudad'
        ,'plantilla'=>'%s'
        ,'campos' => array('ciu_nombre')
    )
    ,array(
        'titulo' => 'Tipo de UM'
        ,'plantilla'=>'%s'
        ,'campos' => array('tum_nombre')
    )
    ,array(
        'titulo' => 'Responsable de UM'
        ,'plantilla'=>'%s'
        ,'campos' => array('nod_responsable_ultima_milla')
    )
    ,array(
        'titulo' => 'Distancia'
        ,'plantilla'=>'%s'
        ,'campos' => array('nod_distancia')
    )
);
$result = q($sql);
//echo $sql;
if ($result) {
    echo <<<EOT
<table id="tabla" class="table table-striped table-condensed table-hover">
<thead>
  <tr>
EOT;
    foreach($cols as $c) {
        echo <<<EOT
        <td>{$c['titulo']}</td>
EOT;
    }
    echo <<<EOT
  </tr>
</thead>
<tbody>
EOT;
    foreach($result as $r) {
        echo "<tr>";
        foreach($cols as $c) {
            $valores = array();
            foreach ($c['campos'] as $campo) {
                array_push($valores, $r[$campo]);
            }
            $texto = vsprintf($c['plantilla'], $valores);
            echo <<<EOT
        <td>$texto</td>
EOT;
        }
        echo "</tr>";
    }
    echo "</tbody></table>";
}
?>

<script>
$(document).ready(function() {
    $('#tabla').DataTable({ 
        language: {
            "sProcessing":     "Procesando...",
            "sLengthMenu":     "Mostrar _MENU_ registros",
            "sZeroRecords":    "No se encontraron resultados",
            "sEmptyTable":     "Ningún dato disponible en esta tabla",
            "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
            "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix":    "",
            "sSearch":         "Buscar:",
            "sUrl":            "",
            "sInfoThousands":  ",",
            "sLoadingRecords": "Cargando...",
            "oPaginate": {
                "sFirst":    "Primero",
                "sLast":     "Último",
                "sNext":     "Siguiente",
                "sPrevious": "Anterior"
            },
            "oAria": {
                "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            },
        },
        "order": [[ 0, "desc" ]]
    });
});
</script>
