<link rel="stylesheet" type="text/css" href="/css/daterangepicker.css" />

<div class="page-header">

<h1>Reporte de Histórico de Atenciones</h1>
</div>



<div class="container">
<form method="POST">
    <div class="row form-group ">
    <label for="estados" class="col-sm-3  control-label">Estado dentro del histórico de las atenciones:</label>
      <div class="col-sm-7">
<select multiple style="display:none;" id="estados" name="estados[]" class="form-control combo-select2" >
<?php
    $result_estados = q("
        SELECT * FROM
        (SELECT * 
        ,(
            SELECT count(*)
            FROM sai_estado_atencion AS hijo
            WHERE hijo.esa_padre = padre.esa_id
        ) AS count_hijos
        FROM sai_estado_atencion AS padre
        WHERE esa_borrado IS NULL
        AND NOT esa_nombre ILIKE '%fin%'
        ) AS t
        WHERE count_hijos = 0
    ");


    foreach($result_estados as $r) {
        echo <<<EOT
        <option value="{$r[esa_id]}">{$r[esa_nombre]}</option>
EOT;
    }
    ?>
    </select>
          </div>
          <div class="col-sm-2">
            <button class="btn btn-primary" onclick="p_cambiar_estado(this)">Mostrar</button>
          </div>
        </div>
    <div class="row form-group ">
      <label for="rango_fechas" class="col-sm-3  control-label">Rango de fechas:</label>
      <div class="col-sm-7">
            <input class="form-control" id="rango_fechas" name="rango_fechas">
      </div>
    </div>
    <div class="row form-group ">
      <label for="empresa" class="col-sm-3  control-label">Empresa:</label>
      <div class="col-sm-7">
            <select multiple style="display:none;" class="form-control combo-select2" id="empresa" name="empresa[]">
<?php
    $result_cliente = q("
        SELECT *
        FROM sai_cliente
        WHERE cli_borrado IS NULL
    ");
    foreach($result_cliente as $r){
        echo <<<EOT
    <option value="{$r[cli_id]}">{$r[cli_razon_social]}</option>
EOT;
    }
?>
            </select>
      </div>
    </div>
        </form>
    </div>
    <hr>

    <?php
$filtro_fechas_vigentes = '';
$filtro_fechas_historicas = '';
$filtro_cliente = '';

if (isset($_POST['rango_fechas']) && !empty($_POST['rango_fechas'])) {
    $filtro_fechas = explode(' >> ', $_POST['rango_fechas']);
    $filtro_fechas_vigentes = "
        AND paa_creado >= to_timestamp('{$filtro_fechas[0]}', 'YYYY-MM-DD')
        AND paa_creado <= to_timestamp('{$filtro_fechas[1]}', 'YYYY-MM-DD')
    ";
    $filtro_fechas_historicas = "
        AND paa_creado >= to_timestamp('{$filtro_fechas[0]}', 'YYYY-MM-DD')
        AND paa_creado <= to_timestamp('{$filtro_fechas[1]}', 'YYYY-MM-DD')
    ";
}
if (isset($_POST['empresa']) && !empty($_POST['empresa'])) {
    $filtro_cliente = implode(',', array_filter($_POST['empresa']));
    $filtro_cliente = " AND ate_cliente IN ($filtro_cliente) ";
}


if (isset($_POST['estados']) && !empty($_POST['estados'])) {
    $estados = implode(',', array_filter($_POST['estados']));
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
        , to_char(paa_creado, 'YYYY-MM-DD') AS fecha_historico
        ,(
            SELECT esa_codigo
            FROM sai_estado_atencion
            WHERE esa_id = (
                SELECT esa_padre
                FROM sai_estado_atencion
                WHERE esa_id=ate_estado_atencion
            )
        ) AS esa_padre_codigo
        ,(
            SELECT esa_nombre 
            FROM sai_estado_atencion 
            WHERE esa_borrado IS NULL
            AND ate_estado_atencion = esa_id
        ) AS estado_actual
        ,(
            SELECT esa_nombre 
            FROM sai_estado_atencion 
            WHERE esa_borrado IS NULL
            AND tea_estado_atencion_actual = esa_id
        ) AS estado_historico
        ,(
            SELECT concat(
                con_nombres
                , ' '
                ,con_apellidos 
                , ' '
                ,con_cargo
                , ' '
                ,con_correo_electronico
                , ' '
                ,con_telefono
                , ' '
                ,con_celular

            )
            FROM sai_contacto
            WHERE con_borrado IS NULL
            AND ate_contacto = con_id
        ) AS contacto

        ,(
            SELECT concat(
                con_nombres
                , ' '
                ,con_apellidos 
                , ' '
                ,con_cargo
                , ' '
                ,con_correo_electronico
                , ' '
                ,con_telefono
                , ' '
                ,con_celular

            )
            FROM sai_contacto
            WHERE con_borrado IS NULL
            AND ate_contacto_en_sitio = con_id
        ) AS contacto_en_sitio


        FROM sai_paso_atencion

        LEFT OUTER JOIN sai_atencion
            ON ate_borrado IS NULL
            AND paa_atencion = ate_id

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

        LEFT OUTER JOIN sai_usuario AS usu_tecnico
            ON usu_tecnico.usu_borrado IS NULL
            AND usu_tecnico.usu_id = ate_usuario_tecnico

        LEFT OUTER JOIN sai_usuario AS usu_comercial
            ON usu_comercial.usu_borrado IS NULL
            AND usu_comercial.usu_id = ate_usuario_comercial

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




        RIGHT OUTER JOIN sai_transicion_estado_atencion
            ON tea_borrado IS NULL
            AND tea_id = paa_transicion_estado_atencion
            AND (
                tea_estado_atencion_actual IN ($estados)
                OR ate_estado_atencion IN ($estados)
            )

        WHERE 
            paa_borrado IS NULL
            AND NOT paa_confirmado IS NULL
            $filtro_cliente
            $filtro_fechas_historicas

        ORDER BY 
            ate_id DESC
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
            ,'campos' => array('pro_razon_social')
        )
        ,array(
            'titulo' => 'Fecha de creacion'
            ,'plantilla'=>'%s'
            ,'campos' => array('fecha_creacion')
        )
        ,array(
            'titulo' => 'Estado histórico buscado'
            ,'plantilla'=>'<strong>%s</strong>'
            ,'campos' => array('estado_historico')
        )
        ,array(
            'titulo' => 'Fecha que estuvo en el estado buscado'
            ,'plantilla'=>'%s'
            ,'campos' => array('fecha_historico')
        )
        ,array(
            'titulo' => 'Estado vigente actual'
            ,'plantilla'=>'%s'
            ,'campos' => array('estado_actual')
        )
        ,array(
            'titulo' => 'Fecha de último cambio'
            ,'plantilla'=>'%s'
            ,'campos' => array('fecha_vigencia')
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
            ,'plantilla'=>'%s'
            ,'campos' => array('contacto')
        )
        ,array(
            'titulo' => 'Contacto en sitio'
            ,'plantilla'=>'%s'
            ,'campos' => array('contacto_en_sitio')
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
    <tfoot>
      <tr>
EOT;
        foreach($cols as $c) {
            echo <<<EOT
            <td>{$c['titulo']}</td>
EOT;
        }
        echo <<<EOT
      </tr>
    </tfoot>
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
    } else {
        echo '<div class="alert alert-warning">No hay coincidencias a la búsqueda.</div>';
    }
} else {

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
        ,(
            SELECT paa_creado
            FROM sai_paso_atencion
            WHERE paa_borrado IS NULL
            AND paa_atencion = ate_id
            ORDER BY paa_creado DESC
            LIMIT 1 
        ) 

        ,(
            SELECT concat(
                con_nombres
                , ' '
                ,con_apellidos 
                , ' '
                ,con_cargo
                , ' '
                ,con_correo_electronico
                , ' '
                ,con_telefono
                , ' '
                ,con_celular

            )
            FROM sai_contacto
            WHERE con_borrado IS NULL
            AND ate_contacto = con_id
        ) AS contacto

        ,(
            SELECT concat(
                con_nombres
                , ' '
                ,con_apellidos 
                , ' '
                ,con_cargo
                , ' '
                ,con_correo_electronico
                , ' '
                ,con_telefono
                , ' '
                ,con_celular

            )
            FROM sai_contacto
            WHERE con_borrado IS NULL
            AND ate_contacto_en_sitio = con_id
        ) AS contacto_en_sitio

        FROM sai_atencion

        LEFT OUTER JOIN sai_paso_atencion
            ON paa_borrado IS NULL
            AND paa_atencion = ate_id
            AND paa_id = (
                SELECT max(paa_id)
                FROM sai_paso_atencion
                WHERE paa_borrado IS NULL
                AND paa_atencion = ate_id
            )

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
            $filtro_cliente
            $filtro_fechas_vigentes

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
            ,'campos' => array('pro_razon_social')
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
            ,'plantilla'=>'%s'
            ,'campos' => array('contacto')
        )
        ,array(
            'titulo' => 'Contacto en sitio'
            ,'plantilla'=>'%s'
            ,'campos' => array('contacto_en_sitio')
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
}
//echo $sql;
?>
<script src="/js/daterangepicker.js"></script>
<script>
$(document).ready(function() {
    $('#rango_fechas').daterangepicker({
        locale: {
            format: 'YYYY-MM-DD',
        "separator": " >> ",
        "applyLabel": "Aplicar",
        "cancelLabel": "Cancelar",
        "fromLabel": "Desde",
        "toLabel": "hasta",
        "customRangeLabel": "Personalizado",
        "weekLabel": "S",
        "daysOfWeek": [
            "Do",
            "Lu",
            "Ma",
            "Mi",
            "Ju",
            "Vi",
            "Sa"
        ],
        "monthNames": [
            "Enero",
            "Febrero",
            "Marzo",
            "Abril",
            "Mayo",
            "Junio",
            "Julio",
            "Agosto",
            "Septiembre",
            "Octubre",
            "Noviembre",
            "Diciembre"
        ],
        "firstDay": 1
        },
    });
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
        }
        ,"order": [[ 0, "desc" ]]
        ,fixedHeader: {
            header: true,
            footer: true
        }
    });
    $('.combo-select2').select2({
        language: "es"
        ,width: '100%'
    });

    $('#estados').show();
});
function p_cambiar_estado(target){
    console.log($(target).val());
    //document.reload();
}
</script>
