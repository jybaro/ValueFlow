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
    echo "<h1>Diccionario de datos</h1>";

    foreach($result as $r){
        if ($table_name != $r['table_name']) {
            $count_tabla++;
            $count_campo = 0;
            $table_name = $r['table_name'];
            $count_registros = q("SELECT COUNT(*) FROM $table_name")[0]['count'];
            $agregar_fin_tabla = true;
            echo "</table>";
            echo "$table_name";
            echo "<table xxxclass='table table-striped table-condensed table-hover'>";
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
    echo "</div>xxx";
}
?>




    <?php if(!empty($tabla)): ?>
<a href="#" onclick="p_nuevo();return false;" style="position:fixed;bottom:50px;right:10px;"><img src="/img/plus.png" alt="Crear nuevo registro" title="Crear nuevo registro" ></img></a>
<div id="modal" xxxclass="modal fade" tabindex="-1" role="dialog">
  <div xxxclass="modal-dialog" role="document">
    <div xxxclass="modal-content">
      <div xxxclass="modal-header">
        <button type="button" xxxclass="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 xxxclass="modal-title">Registro <span id="formulario_titulo"></span> de tabla <?=$tabla?></h4>
      </div>
      <div xxxclass="modal-body">


<form id="formulario" xxxclass="form-horizontal">
  <?php foreach ($campos as $campo): ?>
  <?php $c = substr($campo['column_name'], 4); ?>
  <?php $label = ucfirst(str_replace('_', ' ', $c)) . ':'; ?>
  <?php if ($c != 'creado' && $c != 'modificado'): ?>
  <div xxxclass="form-group">
    <label for="<?=$c?>" xxxclass="col-sm-2 control-label"><?=$label?></label>
    <div xxxclass="col-sm-10">
      <input type="text" xxxclass="form-control" id="<?=$c?>" name="<?=$c?>" placeholder="">
    </div>
  </div>
  <?php endif; ?>
  <?php endforeach; ?>
</form>

      </div>
      <div xxxclass="modal-footer">
        <button type="button" xxxclass="btn btn-default" data-dismiss="modal">Cerrar</button>
        <button type="button" xxxclass="btn btn-danger" onclick="p_borrar()" id="formulario_eliminar">Eliminar registro</button>
        <button type="button" xxxclass="btn btn-success" onclick="p_guardar()" id="formulario_guardar">Guardar cambios</button>
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

        $('#formulario_eliminar').show();
        $('#formulario_titulo').html(data['id'] );
        for (key in data){
            $('#' + key).val(data[key]);
        }

        $("#id").prop('disabled', true);
        
        $('#modal').modal('show');
    }).fail(function(){
        console.error('ERROR AL ABRIR');
        alert('No se pudo cargar los datos. Contacte con el area de sistemas.');
    });
}

function p_borrar(){
    if (confirm('Seguro desea eliminar el registro de la tabla "' + tabla + '" con ID ' + $('#id').val() + '?')) {
        var dataset_json = [{id:$('#id').val()}];
        console.log('dataset_json',dataset_json);
        $.ajax({
            url: '/_borrar/' + tabla,
            type: 'POST',
            //dataType: 'json',
            data: JSON.stringify(dataset_json),
            //contentType: 'application/json'
        }).done(function(data){
            console.log('Borrado OK', data);
            //data = eval(data);
            data = JSON.parse(data);
            data = data[0];

            $('#fila_' + data['id']).remove();
            $('#modal').modal('hide');
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
                    $('#dato_' + data['id'] + '_' + key).text(data[key]);
                }
            } else {
                //nuevo:
                console.log('nuevo registro');
                var numero = $('#lista_registros').children().length + 1;
                var celdas = '';
                var valor = '';
                var key = '';
                campos.forEach(function(campo){
                    valor = (data[campo] == null) ? '' : data[campo];
                    valor = (campo == 'id' ? '<a href="#" onclick="p_abrir('+data['id']+');return false;">'+data['id']+'</a>' : valor);
                    celdas += '<td id="dato_'+data['id']+'_'+campo+'">'+valor+'</td>';
                });
                /*
                for (key in data){
                    valor = (key == 'id' ? '<a href="#" onclick="p_abrir('+data['id']+')">'+data['id']+'</a>' : data[key]);
                    celdas += '<td id="dato_'+data['id']+'_'+key+'">'+valor+'</td>';
                }
                 */

                console.log('celdas:', celdas, '<tr id="fila_'+data['id']+'" xxxclass="alert alert-success"><th>'+numero+'.</th>' + celdas + '</tr>');
                $('#lista_registros').append('<tr id="fila_'+data['id']+'" xxxclass="alert alert-success"><th>'+numero+'.</th>' + celdas + '</tr>');
            }
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
            $(this).val('');
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
