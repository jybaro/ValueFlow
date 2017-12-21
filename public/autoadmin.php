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
    echo "<div id='diccionario'>";
    echo "<h1>Administración de la información del SAIT</h1>";

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
            $fkeys[$r['column_name']] = $r;
        }
    }


//echo "<pre>";
//var_dump($fk);
//echo "</pre>";

    $campos = q("SELECT *
        FROM information_schema.columns
        WHERE table_schema = 'public'
        AND table_name   = '$tabla'
        ORDER BY data_type, is_nullable, column_name
        ");

    $campos_js = array();
    $listado_campos_fk = array();

    foreach ($campos as $campo){
        $campos_js[] = substr($campo['column_name'], 4);
        if (isset($fkeys[$campo['column_name']])) {
            //solo si es clave foranea:
            //
            $listado_campos_fk[$campo['column_name']] = array();
            $fk = $fkeys[$campo['column_name']];

            $campos_fk = q("SELECT *
                FROM information_schema.columns
                WHERE table_schema = 'public'
                AND table_name   = '{$fk[foreign_table_name]}'
                ORDER BY data_type, is_nullable, column_name
                ");

            $campo_etiqueta = '';
            foreach($campos_fk as $campo_fk) {
                $etiqueta_fk = substr($campo_fk['column_name'], 4);
                $listado_campos_fk[$campo['column_name']][$etiqueta_fk] = $campo_fk;
            }
            $campos_posibles = array('nombre', 'razon_social', 'cedula', 'ruc', 'texto', 'codigo', 'etiqueta', 'descripcion', 'creado', 'id');
            foreach($campos_posibles as $campo_posible) {
                if (isset($listado_campos_fk[$campo['column_name']][$campo_posible])) {
                    $campo_etiqueta = $listado_campos_fk[$campo['column_name']][$campo_posible]['column_name'];
                    break;
                }
            }


            $fkeys[$campo['column_name']]['__opciones'] = array();
            $opciones = q("SELECT * FROM {$fk[foreign_table_name]} ORDER BY $campo_etiqueta");
            if ($opciones) {
                foreach($opciones as $opcion){
                    $valor = $opcion[$fk['foreign_column_name']];
                    $etiqueta = $opcion[$campo_etiqueta];
                    $fkeys[$campo['column_name']]['__opciones'][$valor] = $etiqueta;
                }
            }
        }
    }
    echo "<script>var tabla='".substr($tabla, 4)."';var campos = ".json_encode($campos_js).";</script>";
    echo "<a href='/autoadmin'><< Regresar al listado de tablas</a>";
    echo "<h1>$nombre_tabla</h1>";

    $prefijo = substr($campos[0]['column_name'], 0, 4);
    $result = q("SELECT * FROM $tabla ORDER BY {$prefijo}id");


    $campo_id = null;
    $campo_borrado = null;
    echo '<a href="#" download><span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span> Descargar XML </a>';
    echo '|';
    echo '<a href="#" onclick="p_xlsx();return false;"><span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span> Exportar datos</a>';

    echo "<table id='tabla' class='table table-striped table-condensed table-hover'>";
    echo "<thead>";
    echo "<tr>";
    echo "<th>&nbsp;</th>";
    foreach($campos as $campo){
        $valor = $campo['column_name'];
        $nombre_columna = n($campo['column_name']);
        echo "<th>$nombre_columna</th>";
        if (strlen($campo['column_name']) == 6 && strpos($campo['column_name'], '_id') == 3) {
            $campo_id = $campo['column_name'];
        }
        if (strpos($campo['column_name'], '_borrado') == 3) {
            $campo_borrado = $campo['column_name'];
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
                $valor = $r[$campo['column_name']];
                if(isset($fkeys[$campo['column_name']])) {
                    $etiqueta = $fkeys[$campo['column_name']]['__opciones'][$valor];
                } else {
                    $etiqueta = $valor;
                }

                if ($campo['column_name'] == $campo_id ){
                    echo "<td><a href='#' onclick='p_abrir($valor);return false;'>$etiqueta</a></td>";
                } else {
                    $campo_nombre = substr($campo['column_name'], 4);
                    echo "<td id='dato_{$id}_{$campo_nombre}'>$etiqueta</td>";
                }
            }
            echo "</tr>";
        }
    }
    echo "</tbody>";
    echo "</table>";
}
?>

    <?php if(!empty($tabla)): ?>
<a href="#" onclick="p_nuevo();return false;" style="position:fixed;bottom:50px;right:10px;"><img src="/img/plus.png" alt="Crear nuevo registro" title="Crear nuevo registro" ></img></a>
<div id="modal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Registro <span id="formulario_titulo"></span> de <?=n($tabla)?></h4>
      </div>
      <div class="modal-body">


<form id="formulario" class="form-horizontal">
  <?php foreach ($campos as $campo): ?>
  <?php $c = substr($campo['column_name'], 4); ?>
  <?php $label = n($campo['column_name']) . ':'; ?>
  <?php if ($c != 'creado' && $c != 'modificado' && $c != 'borrado'): ?>
  <div class="form-group">
    <label for="<?=$c?>" class="col-sm-2 control-label"><?=$label?></label>
    <div class="col-sm-10">
      <?php if(isset($fkeys[$campo['column_name']])): ?>

      <select class="form-control js-example-basic-single" style="width: 50%" id="<?=$c?>" name="<?=$c?>" >
        <option></option>
<?php
    $opciones = $fkeys[$campo['column_name']]['__opciones'];

    foreach($opciones as $valor => $etiqueta){
        echo "<option value='$valor'>$etiqueta</option>";
    }
?>
      </select>
      <?php else: ?>
      <input type="text" class="form-control" id="<?=$c?>" name="<?=$c?>" placeholder="">
      <?php endif; ?>

    </div>
  </div>
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

<script>

$(document).ready(function() {
    $('.js-example-basic-single').select2({
        language: "es"
    });
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
function p_abrir(id){
    $.ajax({
        'url':'/_listar/'+tabla+'/'+id
    }).done(function(data){
        console.log('ABRIENDO ' + tabla, data);
        //data = eval(data);
        data = JSON.parse(data);
        data = data[0];

        if (!data['borrado']) {
            $('#formulario_eliminar').show();
            $('#formulario_guardar').show();
            $('#formulario_recuperar').hide();
        } else {
            //ya esta con borrado suave
            $('#formulario_eliminar').hide();
            $('#formulario_guardar').hide();
            $('#formulario_recuperar').show();
        }
        $('#formulario_titulo').html(data['id'] );
        for (key in data){
            $('#' + key).val(data[key]).trigger('change');;
        }

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

function p_guardar(){
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
        } else {
            if ($("#fila_" + data['id']).length) { // 0 == false; >0 == true
                //ya existe:
                for (key in data){
                    var id = '#dato_' + data['id'] + '_' + key;
                    var texto = '';

                    //console.log('campo del form: ','#'+ key + ' option:selected')
                    if (($('#'+key + ' option:selected').length > 0)){
                        texto = $('#'+key + ' option:selected').text(); 
                    } else {
                        texto = data[key];
                    }
                    $(id).text(texto);
                }
            } else {
                //nuevo:
                console.log('nuevo registro');
                var numero = $('#lista_registros').children().length + 1;
                var celdas = '';
                var valor = '';
                var key = '';
                campos.forEach(function(campo){
                    valor = (data[campo] == null) ? '' : (($('#'+campo + ' option:selected').length > 0) ? $('#'+campo+' option:selected').text() : data[campo]);
                    valor = (campo == 'id' ? '<a href="#" onclick="p_abrir('+data['id']+');return false;">'+data['id']+'</a>' : valor);
                    celdas += '<td id="dato_'+data['id']+'_'+campo+'">'+valor+'</td>';
                });
                /*
                for (key in data){
                    valor = (key == 'id' ? '<a href="#" onclick="p_abrir('+data['id']+')">'+data['id']+'</a>' : data[key]);
                    celdas += '<td id="dato_'+data['id']+'_'+key+'">'+valor+'</td>';
                }
                 */

                console.log('celdas:', celdas, '<tr id="fila_'+data['id']+'" class="alert alert-success"><th>'+numero+'.</th>' + celdas + '</tr>');
                $('#lista_registros').append('<tr id="fila_'+data['id']+'" class="alert alert-success"><th>'+numero+'.</th>' + celdas + '</tr>');
            }
            $('#fila_' + data['id']).removeClass('alert alert-danger alert-success alert-info');
            $('#fila_' + data['id']).addClass('alert alert-success');
            $('#modal').modal('hide');
        }
    }).fail(function(aaa, bbb){
        console.log('ERROR AL GUARDAR', aaa, bbb);
        alert('No se pudieron guardar los datos.');
    });
}

function p_nuevo(){
    $('#formulario_titulo').text('nuevo');
    $('#formulario').trigger('reset');
    $('#id').val('');
    $("#id").prop('disabled', true);
    $('#formulario_eliminar').hide();
    $('#formulario_recuperar').hide();
    $('#formulario_guardar').show();
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

    $('#modal').modal('show');
}
</script>
