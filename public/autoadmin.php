<link type="text/css" rel="stylesheet" href="/css/multiple-emails.css" />
<script src="/js/multiple-emails.js"></script>

<?php

function array2ul($array) {
  $output = '<ul>';
  foreach ($array as $key => $value) {
    $function = (is_array($value) || is_object($value)) ? __FUNCTION__ : 'htmlspecialchars';
    if (is_int($key)) {
        $output .= '<li><b>' . ($key+1) . ':</b> ' . $function($value) . '</li>';
    } else {
        //$output .= '<li>' . $function($value) . '</li>';
        $output .= '<li><b>' . $key . ':</b> ' . $function($value) . '</li>';
    }
  }
  return $output . '</ul>';
}

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
    echo "<div id='diccionario'>";
    echo "<div class='page-header'><h1>Administración de la información del SAIT</h1></div>";

    $letra = '';
    foreach($result as $r){
        if ($table_name != $r['table_name']) {
            $count_tabla++;
            $count_campo = 0;
            $table_name = $r['table_name'];
            $nombre_tabla = n($table_name);
            $count_registros = q("SELECT COUNT(*) FROM $table_name")[0]['count'];
            $agregar_fin_tabla = true;
            if ($letra != $nombre_tabla[0]) {
                echo '<hr>';
                echo '<h2>'.$nombre_tabla[0].'</h2>';
                $letra = $nombre_tabla[0];
            }
            echo "<a class='btn btn-info' style='margin:5px;' href='/autoadmin/$table_name'>$nombre_tabla <span class='badge'>$count_registros registros</span></a>";
        }
        $count_campo++;

    }
    echo "</div>";
} else {
    $nombre_tabla = n($tabla);
    //despliega solo una tabla

    $result = q("SELECT
    tc.constraint_name, tc.table_name, kcu.column_name, 
    ccu.table_name AS foreign_table_name,
    ccu.column_name AS foreign_column_name 
FROM 
    information_schema.table_constraints AS tc 
    JOIN information_schema.key_column_usage AS kcu
      ON tc.constraint_name = kcu.constraint_name
    JOIN information_schema.constraint_column_usage AS ccu
      ON ccu.constraint_name = tc.constraint_name
    WHERE constraint_type = 'FOREIGN KEY' AND tc.table_name='$tabla';");

    $fkeys = array();
    if ($result) {
        foreach($result as $r){
            $fkeys[substr($r['column_name'], 4)] = $r;
        }
    }


//echo "<pre>";
//var_dump($fk);
//echo "</pre>";

    $campos_js = array();
    $campos = q("SELECT cat_texto FROM sai_catalogo WHERE cat_codigo='autoadmin_$tabla'");
    $prefijo = null;
    $campo_id = null;
    $campo_etiqueta = null;
    $campo_borrado = null;
    if ($campos) {
        //echo "<pre>";
        $campos = $campos[0]['cat_texto'];
        $campos = str_replace("\n", "", $campos);
        $campos = str_replace("\r", "", $campos);
        $campos = json_decode($campos, true);
        //$campos[] = array('nombre'=> 'id', 'etiqueta' =>'Id', 'validacion'=> '');
        //var_dump($campos);
        if ($campos) {
            $tiene_id = false;
            foreach($campos as $key => $campo){
                if (isset($campo['titulo'])) {
                    $nombre_tabla = $campo['titulo'];
                }
                if (isset($campo['prefijo'])) {
                    $prefijo = $campo['prefijo'] . '_';
                }
                if (isset($campo['nombre']) && $campo['nombre'] == 'id') {
                    $tiene_id = true;
                }
            }
            if (!$tiene_id) {
                $campos[] = array('nombre'=> 'id', 'validacion'=> 'hidden');
            }
            foreach($campos as $key => $campo){
                $campos[$key]['column_name'] = $prefijo . $campo['nombre'];
                if (!isset($campo['etiqueta'])) {
                    $campos[$key]['etiqueta'] = n($campos[$key]['column_name']);
                }
                if (!isset($campo['validacion'])) {
                    $campos[$key]['validacion'] = '';
                }
                if (!isset($campo['tipo'])) {
                    $campos[$key]['tipo'] = 'text';
                }
                if (!isset($campo['unico'])) {
                    $campos[$key]['unico'] = false;
                }
            }
            $campo_etiqueta = $campos[0]['nombre'];
           // $campo_borrado = $prefijo . 'borrado';
        }
//die();
    } 

    if (empty($prefijo)) {

        $campos = q("SELECT *
            FROM information_schema.columns
            WHERE table_schema = 'public'
            AND table_name   = '$tabla'
            ORDER BY data_type, is_nullable, column_name
        ");
        //var_dump($campos);
        $campo_etiqueta = 'id';

        foreach ($campos as $key => $campo){

            $c = substr($campo['column_name'], 4); 
            $campos[$key]['nombre'] = $c;

            $label = n($campo['column_name']) . ':'; 
            $campos[$key]['etiqueta'] = $label;

            $prefijo = substr($campo['column_name'], 0, 4);
            $campos[$key]['prefijo'] = $prefijo;

            $campos[$key]['validacion'] = '';
            $campos[$key]['unico'] = false;
            $campos[$key]['tipo'] = $campo['data_type'];

        }
    }
    $campo_id = $prefijo . 'id';
    $campo_borrado = $prefijo . 'borrado';

    //var_dump($campos);
    $listado_campos_fk = array();
    $campos_unicos = array();

    foreach ($campos as $campo){
        $c = $campo['nombre']; 

        if ($campo['validacion'] != 'hidden') {
            $campos_js[] = $c;
        }

        if ($campo['unico'] == 'unico') {
            $campos_unicos[] = $c;
        }

        if (isset($fkeys[$c])) {
            //solo si es clave foranea:
            //
            $fk = $fkeys[$c];

            $campo_etiqueta_fk = '';
            $campo_etiqueta2_fk = '';
            $listado_campos_fk[$campo['column_name']] = array();
            $campos_fk = q("SELECT *
                FROM information_schema.columns
                WHERE table_schema = 'public'
                AND table_name   = '{$fk[foreign_table_name]}'
                ORDER BY data_type, is_nullable, column_name
                ");

            foreach($campos_fk as $campo_fk) {
                $etiqueta_fk = substr($campo_fk['column_name'], 4);
                $listado_campos_fk[$campo['column_name']][$etiqueta_fk] = $campo_fk;
            }
            $campos_posibles = array(
                  'nombre'
                , 'nombre_comercial'
                , 'nombres'
                , 'apellidos'
                , 'username'
                , 'cedula'
                , 'ruc'
                , 'correo_electronico'
                , 'texto'
                , 'secuencial'
                , 'etiqueta'
                , 'descripcion'
                , 'direccion'
                , 'sector'
            );
            $result_plantilla_fk = q("SELECT cat_texto FROM sai_catalogo WHERE cat_codigo='autoadmin_{$fk[foreign_table_name]}'");
            $campo_plantilla = '';
            if ($result_plantilla_fk) {
                $plantilla_fk = $result_plantilla_fk[0]['cat_texto'];
                $plantilla_fk = str_replace("\n", "", $plantilla_fk);
                $plantilla_fk = str_replace("\r", "", $plantilla_fk);
                $plantilla_fk = json_decode($plantilla_fk, true);
                if ($plantilla_fk) {
                    foreach ($plantilla_fk as $campo_plantilla_fk) {
                        if (isset($campo_plantilla_fk['prefijo'])) {
                            $campo_plantilla = $campo_plantilla_fk['prefijo'] . '_'. $campo_plantilla_fk['nombre'];
                        }
                    }
                }
            }

            $campos_desesperados = array(
                 'codigo'
                , 'creado'
                , 'id'
            );

            if (empty($campo_plantilla)) {
                foreach($campos_posibles as $campo_posible) {
                    if (isset($listado_campos_fk[$campo['column_name']][$campo_posible])) {
                        if (!empty($campo_etiqueta_fk) && empty($campo_etiqueta2_fk)) {
                            $campo_etiqueta2_fk = $listado_campos_fk[$campo['column_name']][$campo_posible]['column_name'];
                        }
                        if (empty($campo_etiqueta_fk)) {
                            $campo_etiqueta_fk = $listado_campos_fk[$campo['column_name']][$campo_posible]['column_name'];
                        }
                    }
                }
                if (empty($campo_etiqueta_fk)) {
                    foreach($campos_desesperados as $campo_desesperado) {
                        if (isset($listado_campos_fk[$campo['column_name']][$campo_desesperado])) {
                            if (!empty($campo_etiqueta_fk) && empty($campo_etiqueta2_fk)) {
                                $campo_etiqueta2_fk = $listado_campos_fk[$campo['column_name']][$campo_desesperado]['column_name'];
                            }
                            if (empty($campo_etiqueta_fk)) {
                                $campo_etiqueta_fk = $listado_campos_fk[$campo['column_name']][$campo_desesperado]['column_name'];
                            }
                        }
                    }
                }
            } else {
                $campo_etiqueta_fk = $campo_plantilla;
            }

            $fkeys[$c]['__opciones'] = array();
            $fkeys[$c]['__campo_etiqueta_fk'] = $campo_etiqueta_fk;
            $fkeys[$c]['__campo_etiqueta2_fk'] = $campo_etiqueta2_fk;
            $prefijo_fk = substr($campo_etiqueta_fk, 0, 4);
            $opciones = q("SELECT * FROM {$fk[foreign_table_name]} WHERE {$prefijo_fk}borrado IS NULL ORDER BY $campo_etiqueta_fk");
            if ($opciones) {
                foreach($opciones as $opcion){
                    $valor = $opcion[$fk['foreign_column_name']];
                    $etiqueta = empty($opcion[$campo_etiqueta_fk]) ? '' : $opcion[$campo_etiqueta_fk];
                    $etiqueta2 = empty($opcion[$campo_etiqueta2_fk]) ? '' : $opcion[$campo_etiqueta2_fk];
                    $etiqueta = trim(str_replace('null', '', "$etiqueta $etiqueta2"));
                    $etiqueta = empty($etiqueta) ? '<i>(id:'.$opcion[$prefijo_fk . 'id'] . ')</i>' : $etiqueta;
                    $fkeys[$c]['__opciones'][$valor] = $etiqueta;
                }
            }
        }
    }
    //var_dump($campos);
    echo "<script>var tabla='".substr($tabla, 4)."';var campos = ".json_encode($campos_js).";var campos_unicos=".json_encode($campos_unicos).";var catalogo_campos=".json_encode($campos).";</script>";
    //echo "<a href='/autoadmin'><< Regresar al listado de tablas</a>";
    echo "<h1>$nombre_tabla</h1>";


    $result = q("SELECT * FROM $tabla ORDER BY {$prefijo}borrado DESC, {$prefijo}id DESC");


    //echo '<a href="#" download><span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span> Descargar XML </a>';
    //echo '|';
    //echo '<a href="#" onclick="p_xlsx();return false;"><span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span> Exportar datos</a>';

    echo "<table id='tabla' class='table table-striped table-condensed table-hover'>";
    echo "<thead>";
    echo "<tr>";
    echo "<th>&nbsp;</th>";
    foreach($campos as $campo){
        if ($campo['validacion'] != 'hidden') {
            $nombre_columna = $campo['etiqueta'];
            echo "<th>$nombre_columna</th>";
        }
    }

    echo "</tr>";
    echo "</thead>";
    echo "<tbody id='lista_registros'>";

    $count = 0;
    if ($result) {
        foreach($result as $r){
            $count++;

            $id = $r[$campo_id];
            $clase_css = ''; 
            if (!empty($campo_borrado) && !empty($r[$campo_borrado])){
                $clase_css = " class='alert alert-danger' "; 
            }
            echo "<tr $clase_css id='fila_{$id}'>";
            echo "<th>$count.</th>";

            foreach($campos as $campo){
                if ($campo['validacion'] != 'hidden') {
                    $valor = $r[$campo['column_name']];
                    $etiqueta = '';
                    if(isset($fkeys[$campo['nombre']])) {
                        $etiqueta = $fkeys[$campo['nombre']]['__opciones'][$valor];
                    } else if ($campo['tipo'] == 'fecha' && !empty($valor)) {
                        $etiqueta = date('Y-m-d', strtotime($valor));
                    } else if ($campo['tipo'] == 'si-no') {
                        $etiqueta = $valor == 1 ? 'Sí': 'No';
                    } else {
                        $etiqueta = $valor;
                    }

                    //verifica si es JSON:
                    if (!empty($etiqueta[0]) && ($etiqueta[0] == '{' || $etiqueta[0] == '[')) {
                        //muy probable sea JSON si empieza con { o [

                        $listado_etiquetas = json_decode($etiqueta);
                        if (!empty($listado_etiquetas)) {
                            $etiqueta = array2ul($listado_etiquetas); 
                        }
                    }

                    
                    $campo_nombre = $campo['nombre'];
                    if ($campo['nombre'] == $campo_etiqueta && !$_solo_lectura ){
                        $etiqueta = empty($etiqueta) ? "(registro $id)" : $etiqueta;
                        echo "<td><a href='#' onclick='p_abrir($id, this);return false;' id='dato_{$id}_{$campo_nombre}'>$etiqueta</a></td>";
                    } else {
                        echo "<td id='dato_{$id}_{$campo_nombre}'>$etiqueta</td>";
                    }
                }
            }
            echo "</tr>";
        }
    }
    echo "</tbody>";
    echo "<tfoot>";
    echo "<tr>";
    echo "<th>&nbsp;</th>";
    foreach($campos as $campo){
        if ($campo['validacion'] != 'hidden') {
            $nombre_columna = $campo['etiqueta'];
            echo "<th>$nombre_columna</th>";
        }
    }

    echo "</tr>";
    echo "</tfoot>";
    echo "</table>";
}
?>

    <?php if(!empty($tabla)): ?>
    <?php if(!$_solo_lectura): ?>
<a href="#" onclick="p_nuevo();return false;" style="position:fixed;bottom:50px;right:10px;"><img src="/img/plus.png" alt="Crear nuevo registro" title="Crear nuevo registro" ></img></a>
    <?php endif; ?>
<div id="modal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <!--h4 class="modal-title">Registro <span id="formulario_titulo"></span> de <?=n($tabla)?></h4-->
        <h4 class="modal-title">Registro <span id="formulario_titulo"></span> de <?=$nombre_tabla?></h4>
      </div>
      <div class="modal-body">


<form id="formulario" class="form-horizontal">
  <?php foreach ($campos as $campo): ?>
  <?php $c = $campo['nombre']; ?>
  <?php $label = $campo['etiqueta']; ?>
  <?php $tipo = $campo['tipo']; ?>
  <?php if($campo['validacion']=='hidden'): ?>
  <input type="hidden" id="<?=$c?>" name="<?=$c?>">
  <?php else: ?>
  <?php if ($c != 'creado' && $c != 'modificado' && $c != 'borrado'): ?>
  <div class="form-group">
    <label for="<?=$c?>" class="col-sm-4 control-label"><?=$label?></label>
    <div class="col-sm-8">
      <?php if(isset($fkeys[$campo['nombre']])): ?>

      <!--pre>FOREIGN KEY
      <?php //var_dump($fkeys[$campo['nombre']]); ?>
      <?php //echo "TABLE NAME: $tabla"; ?>
      </pre-->
        <?php if($fkeys[$campo['nombre']]['foreign_table_name'] == $tabla): ?>
        <span id="recursivo_<?=$c?>"></span>
        <?php endif; ?>
<?php
$__fk_tabla = substr($fkeys[$campo['nombre']]['foreign_table_name'],4);
$__campo_etiqueta_fk = substr($fkeys[$campo['nombre']]['__campo_etiqueta_fk'],4); 
$__campo_etiqueta2_fk = substr($fkeys[$campo['nombre']]['__campo_etiqueta2_fk'],4); 
?>

      <select <?=$campo['validacion']?> class="form-control combo-select2" style="width: 50%" id="<?=$c?>" name="<?=$c?>" data-campo-etiqueta-fk="<?=$__campo_etiqueta_fk?>" data-campo-etiqueta2-fk="<?=$__campo_etiqueta2_fk?>">
        <option value="">&nbsp;</option>
<?php
/*
    $opciones = $fkeys[$campo['nombre']]['__opciones'];


    foreach($opciones as $valor => $etiqueta){
        echo "<option value='$valor'>$etiqueta</option>";
    }
 */
?>
      </select>
<script>
$('#<?=$c?>').select2({
    language: "es"
    ,width: '100%'
    ,ajax: {
        url: function (params) {
            console.log('SELECT2 URL params:', params);
            var busqueda = (params.term) ? params.term : '';
            return '/_listar/<?=$__fk_tabla?>/borrado/null/<?=$__campo_etiqueta_fk?>/ilike-' + busqueda + '/';
        }
        ,data:function(){return '';}
        ,processResults: function (data) {
            console.log('Respuesta /_listar/<?=$__fk_tabla?>/borrado/null/<?=$__campo_etiqueta_fk?>/ilike-', data);
            data = JSON.parse(data);
            console.log('data',data);
            var opciones = [];
            data.forEach(function(opcion){
                var texto = opcion['<?=$__campo_etiqueta_fk?>'] + (opcion['<?=$__campo_etiqueta2_fk?>'] ? ' ' + opcion['<?=$__campo_etiqueta2_fk?>'] : '');
                opciones.push( {
                    "id": opcion['id']
                    ,"text":texto
                });
            });
            return {
                results: opciones
            };
        }
    }
});
</script>
      <?php elseif($tipo == 'multitexto'): ?>

      <textarea class="form-control" id="<?=$c?>" name="<?=$c?>" placeholder="" <?=$campo['validacion']?> onblur="p_validar(this)" ></textarea>

      <?php elseif($tipo == 'multiemail'): ?>

      <input class="form-control multiemail" id="<?=$c?>" name="<?=$c?>" placeholder="" <?=$campo['validacion']?> onblur="p_validar(this)" >

      <?php elseif($tipo == 'fecha'): ?>

      <input type="text" class="form-control datetimepicker" id="<?=$c?>" name="<?=$c?>" placeholder="" <?=$campo['validacion']?> onblur="p_validar(this)" >

      <?php elseif($tipo == 'si-no'): ?>

      <input type="checkbox" value="1" class="form-control checkbox-toggle" id="<?=$c?>_checkbox" <?=$campo['validacion']?> onchange="p_cambiar_checkbox(this)" >
      <input type="hidden" id="<?=$c?>" name="<?=$c?>"  >

      <?php else: ?>

      <input class="form-control" id="<?=$c?>" name="<?=$c?>" placeholder="" <?=$campo['validacion']?> onblur="p_validar(this)" >

      <?php endif; ?>

    </div>
  </div>
  <?php endif; ?>
  <?php endif; ?>
  <?php endforeach; ?>
</form>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-danger" onclick="p_borrarSuave()" id="formulario_eliminar">Eliminar registro</button>
        <button type="button" class="btn btn-success" onclick="p_guardar()" id="formulario_guardar">Guardar cambios</button>
        <button type="button" class="btn btn-success" onclick="p_recuperar()" id="formulario_recuperar">Recuperar registro</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<script src="/js/Blob.min.js"></script>
<script src="/js/xlsx.full.min.js"></script>
<script src="/js/FileSaver.min.js"></script>
<script src="/js/tableexport.min.js"></script>


<script src="/js/jspdf.min.js"></script>
<script src="/js/html2canvas.min.js"></script>
<script src="/js/html2pdf.js"></script>

  <link rel="stylesheet" href="/css/bootstrap-toggle.min.css">
  <script src="/js/bootstrap-toggle.min.js"></script>

<script>

$(document).ready(function() {
    /*
    $('.combo-select2').select2({
        language: "es"
        ,width: '100%'
    });
     */
    $('.checkbox-toggle').bootstrapToggle({
        on: 'Sí'
        ,off: 'No'
    });
    $('.datetimepicker').datetimepicker({
        locale: 'es',
        format: 'YYYY-MM-DD'
    });
    $('.multiemail').multiple_emails({position: "bottom"});
    //refresh_emails

    $('#tabla').DataTable({ language: {
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
    }
    }});
});

function p_cambiar_checkbox(target) {
    var id = $(target).attr('id').replace('_checkbox', '');
    $('#' + id).val($(target).is(":checked") ? '1' : '0');
}

function p_validar(target) {
    console.log('validando', target, target.type, $(target).prop("tagName"));
    var resultado = true;
    var id = $(target).prop('id');
    var value = $(target).val();
    $(target).parent().parent().removeClass('has-success');
    $(target).parent().parent().removeClass('has-error');
    if (!$(target)[0].checkValidity()) {
        console.log('no valida...', $(target)[0].validationMessage);
        //$('<input type="submit">').hide().appendTo('#formulario').click().remove();
        //$('#formulario').submit();
        //$(target)[0].reportValidity();
        //$('#formulario')[0].reportValidity();
        $(target).popover('hide');
        $(target).popover('destroy');
        $(target).popover({
            placement:'auto right',
            trigger:'manual',
            html:true,
            content:target.validationMessage
        });
        $(target).popover('show');
        setTimeout(function () {
            $(target).popover('hide');
            $(target).popover('destroy');
        }, 4000);

        resultado = false;
    } else if (jQuery.inArray(id, campos_unicos) > -1){
        console.log('Validando que campo '+id+' sea unico...');
        var candidato_a_valor_unico = $(target).val();
        $(target).val('');
        $.get('/_listar/'+tabla+'/borrado/null/'+id+'/'+value, function(data){
            console.log('/_listar/'+tabla+'/'+id+'/'+value, data);
            data = JSON.parse(data);
            console.log('data', data.length, data);
            if (data.length > 0 && $('#id').val() != data[0]['id']) {
                var repetido = data[0][campo_etiqueta];
                console.log('No valida...');
                $(target).popover('hide');
                $(target).popover('destroy');
                $(target).popover({
                    placement:'auto top',
                    trigger:'manual',
                    html:true,
                    content:'Ya existe un registro con el valor "'+value+'", este campo no admite valores duplicados.'
                });
                $(target).popover('show');
                setTimeout(function () {
                    $(target).popover('hide');
                    $(target).popover('destroy');
                }, 4000);
                $(target).parent().parent().removeClass('has-success');
                $(target).parent().parent().addClass('has-error');

            } else {
                $(target).val(candidato_a_valor_unico);
                $(target).parent().parent().removeClass('has-error');
                $(target).parent().parent().addClass('has-success');
            }
        });
    }
    console.log('RESULTADO DE VALIDACION:', resultado, ($(target).prop("tagName") == 'INPUT'));
    if ($(target).prop("tagName") == 'FORM') {
        $(target)[0].reportValidity();
    }
    return resultado;
}

function p_imprimir(frm_id){
    var element = document.getElementById('diccionario');
    html2pdf(element, {
        margin:       1,
        filename:     'diccionario_datos.pdf',
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { dpi: 192, letterRendering: true },
        jsPDF:        { unit: 'cm', format: 'A4', orientation: 'portrait' }
    });
}

function p_xlsx(){
    $('#tabla').tableExport();
}
</script>
<?php endif; ?>


<script>

var campo_etiqueta = '<?=$campo_etiqueta?>';

function p_abrir(id, target){
    var etiqueta = $(target).text();
    $.ajax({
        'url':'/_listar/'+tabla+'/id/'+id
    }).done(function(data){
        console.log('ABRIENDO /_listar/'+tabla+'/id/'+id, data);
        //data = eval(data);
        data = JSON.parse(data);
        console.log('data', data);
        data = data[0];

        if (!data['borrado']) {
            $('#formulario_eliminar').show();
            $('#formulario_guardar').show();
            $('#formulario_guardar').prop('disabled', false);
            $('#formulario_recuperar').hide();
        } else {
            //ya esta con borrado suave
            $('#formulario_eliminar').hide();
            $('#formulario_guardar').hide();
            $('#formulario_guardar').prop('disabled', true);
            $('#formulario_recuperar').show();
        }
        //$('#formulario_titulo').html(data['id'] );
        $('#formulario_titulo').html(etiqueta );
        for (key in data){
            $('#' + key).val(data[key]).trigger('change');;
        }
        //setea los combos ajax select2
        $('.combo-select2').each(function(){
            var fk_campo = $(this).attr('id');
            var fk_id = data[fk_campo];
            var texto = $('#dato_' + id + '_' + fk_campo).text();
            //var campo_etiqueta_fk = $(this).attr('data-campo-etiqueta-fk');
            //var campo_etiqueta2_fk = $(this).attr('data-campo-etiqueta2-fk');
            //console.log('SETEANDO SELECT2: ' , id, data[id], fk_id, data[fk_id], campo_etiqueta_fk, data[campo_etiqueta_fk], campo_etiqueta2_fk, data[campo_etiqueta2_fk]);

            if (fk_id) {
                console.log('SETEANDO SELECT2: ' , id, fk_campo, fk_id, texto );
                $(this).select2("trigger", "select", {
                    data: { id: fk_id, text: texto }
                });
            } else {
                console.log('VACIANDO SELECT2');
                $(this).val('').change();
            }
        });
        //reinicializa los checkbox
        $('.checkbox-toggle').each(function(){
            var id = $(this).attr('id').replace('_checkbox', '');
            console.log('aqui checkbox', id, $('#' + id).val());
            $(this).prop('checked', $('#' + id).val() == 1).change();
        });
        //reinicializa los multi-email:
        $('.multiple_emails-container').remove();
        $('.multiemail').multiple_emails({position: "bottom"});

        $("#id").prop('disabled', true);
        
        $('#modal').modal('show');
    }).fail(function(){
        console.error('ERROR AL ABRIR');
        alert('No se pudo cargar los datos. Contacte con el area de sistemas.');
    });
}


function p_recuperar(){
    if (confirm('Seguro desea recuperar el registro de la tabla "' + tabla + '" con ID ' + $('#id').val() + '?')) {
        var dataset_json = [{id:$('#id').val()}];
        console.log('dataset_json',dataset_json);
        $.ajax({
            url: '/_recuperar/' + tabla,
            type: 'POST',
            //dataType: 'json',
            data: JSON.stringify(dataset_json),
            //contentType: 'application/json'
        }).done(function(data){
            console.log('Recuperado OK', data);
            //data = eval(data);
            data = JSON.parse(data);
            data = data[0];

            if (data['ERROR']) {
                alert(data['ERROR']);
            } else {
                for (key in data){
                    $('#dato_' + data['id'] + '_' + key).text(data[key]);
                }
                $('#fila_' + data['id']).removeClass('alert alert-danger alert-success alert-info');
                $('#fila_' + data['id']).addClass('alert alert-info');
                $('#modal').modal('hide');
            }
        }).fail(function(xhr, err){
            console.error('ERROR AL RECUPERAR', xhr, err);
            alert('No se pudo recuperar el registro.');
        });
    }
}


function p_borrarSuave(){
    if (confirm('Seguro desea eliminar el registro de la tabla "' + tabla + '" con ID ' + $('#id').val() + '?')) {
        var dataset_json = [{id:$('#id').val()}];
        console.log('dataset_json',dataset_json);
        $.ajax({
            url: '/_borrarSuave/' + tabla,
            type: 'POST',
            //dataType: 'json',
            data: JSON.stringify(dataset_json),
            //contentType: 'application/json'
        }).done(function(data){
            console.log('Borrado OK', data);
            //data = eval(data);
            data = JSON.parse(data);
            data = data[0];

            if (data['ERROR']) {
                alert(data['ERROR']);
            } else {

                for (key in data){
                    $('#dato_' + data['id'] + '_' + key).text(data[key]);
                    //si es recursivo:
                    campo = key;
                    if ($('#recursivo_' + campo).length > 0) {
                        option_value = data['id'];
                        option_label = data[campo_etiqueta];
                        console.log('RECURSIVO YA EXISTE:', '#recursivo_' + campo, 'value '+option_value, 'label '+option_label);
                        $('#'+campo).find('option[value='+option_value+']').remove();
                        $('#'+campo).select2({
                            language: "es"
                            ,width: '100%'
                        });
                    }
                }
                $('#fila_' + data['id']).removeClass('alert alert-danger alert-success alert-info');
                $('#fila_' + data['id']).addClass('alert alert-danger');
                $('#modal').modal('hide');
            }
        }).fail(function(xhr, err){
            console.error('ERROR AL BORRAR', xhr, err);
            alert('No se pudo eliminar el registro.');
        });
    }
}

function txt(t){
    return t;
}
function array2ul($array) {
  console.log('EN array2ul:', $array);
  if (!Array.isArray($array)) {
      return $array;
  }
  var $output = '<ul>';
  Array.from($array).forEach (function( $value, $key ) {
    $function = (Array.isArray($value)) ? array2ul : txt;
    if (Number.isInteger($key)) {
        $output += '<li><b>' + ($key+1) + ':</b> ' + $function($value) + '</li>';
    } else {
        //$output .= '<li>' . $function($value) . '</li>';
        $output += '<li><b>' + $key + ':</b> ' + $function($value) + '</li>';
    }
  });
  return $output + '</ul>';
}
function p_guardar() {

    if (p_validar($('#formulario'))) {
        $('#formulario_guardar').prop('disabled', true);
        var respuestas_json = $('#formulario').serializeArray();
        console.log(respuestas_json);
        dataset_json = [];
        dataset_json[0] = {};
        respuestas_json.forEach(function(respuesta_json){
            var name =  respuesta_json['name'];
            var value = respuesta_json['value'];
            dataset_json[0][name]=value;
        });

        if ($('#id').val() != '') {
            dataset_json[0]['id'] = $('#id').val();
        }

        console.log('dataset_json', dataset_json);

        $.ajax({
        'url': '/_guardar/' + tabla + '/',
            type: 'POST',
            data: JSON.stringify(dataset_json),
        }).done(function(data){
            console.log('Guardado OK', data);
            data = JSON.parse(data);
            data = data[0];

            if (data['ERROR']) {
                alert(data['ERROR']);
                $('#formulario_guardar').prop('disabled', false);
            } else {
                if ($("#fila_" + data['id']).length) { // 0 == false; >0 == true

                    //location.reload();

                    ///////////////
                    //YA EXISTE:
                    for (key in data){
                        var id = '#dato_' + data['id'] + '_' + key;
                        var texto = '';

                        //console.log('campo del form: ','#'+ key + ' option:selected')
                        if (($('#'+key + ' option:selected').length > 0)){
                            texto = $('#'+key + ' option:selected').text(); 
                        } else {
                            texto = data[key];
                            catalogo_campos.forEach(function(catalogo_campo){
                                if (catalogo_campo['nombre'] == key) {
                                    if (catalogo_campo['tipo'] == 'si-no') {
                                        texto = (texto == 1) ? 'Sí' : 'No';
                                    } else if (catalogo_campo['tipo'] == 'fecha') {
                                        texto = moment(texto).format('YYYY-MM-DD');
                                    }
                                }
                            });
                        }
                        var valor = texto;
                        var isValidJSON = true;
                        try { JSON.parse(valor); } catch(e) { isValidJSON = false; }
                        console.log('isValidJSON', isValidJSON);
                        if (isValidJSON) {
                            valor = array2ul(JSON.parse(valor));
                            console.log('array2ul', valor);
                        }
                        //$(id).text(texto);
                        $(id).html(valor);

                        //si es recursivo:
                        campo = key;
                        if ($('#recursivo_' + campo).length > 0) {
                            option_value = data['id'];
                            option_label = data[campo_etiqueta];
                            console.log('RECURSIVO YA EXISTE:', '#recursivo_' + campo, 'value '+option_value, 'label '+option_label);
                            $('#'+campo).find('option[value='+option_value+']').text(option_label);
                            $('#'+campo).select2({
                                language: "es"
                                ,width: '100%'
                            });
                        }
                    }
                } else {
                    ///////////
                    //NUEVO:
                    console.log('nuevo registro');
                    //var numero = $('#lista_registros').children().length + 1;
                    var numero = $('#tabla').DataTable().data().length + 1;
                    var celdas = '';
                    var valor = '';
                    var option_value = '';
                    var option_label = '';
                    var key = '';
                    campos.forEach(function(campo){
                        valor = '';
                        if (data[campo] != null && $('#'+campo + ' option:selected').length > 0) {
                            valor = $('#'+campo+' option:selected').text();
                        } else {
                            valor = data[campo];
                            catalogo_campos.forEach(function(catalogo_campo){
                                if (catalogo_campo['nombre'] == campo) {
                                    if (catalogo_campo['tipo'] == 'si-no') {
                                        valor = (valor == 1) ? 'Sí' : 'No';
                                    } else if (catalogo_campo['tipo'] == 'fecha') {
                                        valor = moment(valor).format('YYYY-MM-DD');
                                    }
                                }
                            });
                        }
                        //valor = (data[campo] == null) ? '' : (($('#'+campo + ' option:selected').length > 0) ? $('#'+campo+' option:selected').text() : data[campo]);
                        var isValidJSON = true;
                        try { JSON.parse(valor); } catch(e) { isValidJSON = false; }
                        console.log('isValidJSON', isValidJSON);
                        if (isValidJSON) {
                            valor = array2ul(valor);
                            console.log('array2ul', valor);
                        }
                        valor = (campo == campo_etiqueta ? '<td><a href="#" onclick="p_abrir('+data['id']+', this);return false;" id="dato_'+data['id']+'_'+campo+'">'+valor+'</a></td>' : '<td id="dato_'+data['id']+'_'+campo+'">'+valor+'</td>');
                        celdas += valor;
                        //si es recursivo:
                        if ($('#recursivo_' + campo).length > 0) {
                            option_value = data['id'];
                            option_label = data[campo_etiqueta];
                            console.log('RECURSIVO NUEVO:', '#recursivo_' + campo, 'value '+option_value, 'label '+option_label);
                            $('#'+campo).append('<option value="'+option_value+'">'+option_label+'</option>');
                            $('#'+campo).trigger('change');
                        }
                    });
                /*
                    for (key in data){
                    valor = (key == 'id' ? '<a href="#" onclick="p_abrir('+data['id']+')">'+data['id']+'</a>' : data[key]);
                    celdas += '<td id="dato_'+data['id']+'_'+key+'">'+valor+'</td>';
                }
                 */

                    console.log('celdas:', celdas, '<tr id="fila_'+data['id']+'" class="alert alert-success"><th>'+numero+'.</th>' + celdas + '</tr>');
                    //$('#lista_registros').append('<tr id="fila_'+data['id']+'" class="alert alert-success"><th>'+numero+'.</th>' + celdas + '</tr>');
                    var jRow = $('<tr id="fila_'+data['id']+'" class="alert alert-success">').append('<th>'+numero+'.</th>' + celdas);
                    $('#tabla').DataTable().row.add(jRow).draw();
                    //$('#lista_registros').prepend('<tr id="fila_'+data['id']+'" class="alert alert-success"><th>'+numero+'.</th>' + celdas + '</tr>');
                    
                }
                $('#fila_' + data['id']).removeClass('alert alert-danger alert-success alert-info');
                $('#fila_' + data['id']).addClass('alert alert-success');
                $('#modal').modal('hide');
            }
        }).fail(function(aaa, bbb){
            console.log('ERROR AL GUARDAR', aaa, bbb);
            alert('No se pudieron guardar los datos.');
            $('#formulario_guardar').prop('disabled', false);
        });
    }
}

function p_nuevo(){
    $('#formulario_titulo').text('nuevo');
    $('#formulario').trigger('reset');
    $('#id').val('');
    $("#id").prop('disabled', true);
    $('#formulario_eliminar').hide();
    $('#formulario_recuperar').hide();
    $('#formulario_guardar').show();
    $('#formulario_guardar').prop('disabled', false);
    $('#formulario').find(':input').each(function() {
        switch(this.type) {
        case 'password':
        case 'text':
        case 'textarea':
        case 'file':
        case 'select-one':
        case 'select-multiple':
        case 'date':
        case 'number':
        case 'tel':
        case 'email':
            $(this).val('').trigger('change');;
            break;
        case 'checkbox':
        case 'radio':
            this.checked = false;
            break;
        }
    });
    //inicializa los checkbox
    $('.checkbox-toggle').each(function(){
        var id = $(this).attr('id').replace('_checkbox', '');
        $('#' + id).val('0');
        $(this).prop('checked', false).change();
    });
    //inicializa los multi-email:
    $('.multiple_emails-container').remove();
    $('.multiemail').multiple_emails({position: "bottom"});
    //$('.multiemail').multiple_emails({position: "bottom"}).refresh_emails();
    //$('.multiemail').multiple_emails.refresh_emails();
    //$('.multiemail').refresh_emails();
    //refresh_emails();

    $('#modal').modal('show');
}
</script>
