<div class="page-header">
<h1>Campos</h1>
</div>
<div class="container">
<?php

$result = q("
    SELECT *
    ,(
        SELECT tid_nombre
        FROM sai_tipo_dato
        WHERE cae_tipo_dato = tid_id
    )
    FROM sai_campo_extra
    ,sai_tipo_dato
    WHERE cae_borrado IS NULL
    ");


$campos = array();
foreach ($result as $r) {
    $campos[$r[cae_id]] = $r;
    $campos[$r[cae_id]][hijos] = array();
    $campos[$r[cae_id]][padre] = null;
}

foreach ($campos as $c) {
    $campos[$c[cae_padre]][hijos][$c[cae_id]] = & $campos[$c[cae_id]];
    $campos[$c[cae_id]][padre] = & $campos[$c[cae_padre]];
}

function p_tree($arbol) {
    foreach ($arbol as $cae_id => $c) {

        echo <<<EOT
          <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">{$c[cae_texto]}</h3>
            </div>
            <div class="panel-body">
EOT;
        //metadata
        echo <<<EOT
              <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">
                      Metadata 
                    </h3>
                </div>
                <div class="panel-body">
                <a class="btn btn-success pull-right" href="#" onclick="p_abrir({$c[cae_id]});return false;">Editar</a>
                <strong>Código:</strong> %{$c[cae_codigo]}%
                <br>
                <strong>Validación:</strong> {$c[cae_validacion]}
                <br>
                <strong>Tipo de dato:</strong> {$c[tid_nombre]}
EOT;

        echo '</div>';
        echo '</div>';
        //hijos
        echo <<<EOT
              <div class="panel panel-info">
                <div class="panel-heading">
                    <h3 class="panel-title">
                    Hijos 
                    </h3>
                </div>
                <div class="panel-body">
EOT;
        echo '<a xxxclass="pull-right" href="#" onclick="p_nuevo();return false;"><img src="/img/plus.png" alt="Crear nuevo registro" title="Crear nuevo registro" ></img></a>';
        if (!empty($c[hijos])) {
            p_tree($c[hijos]);
        }
        echo '</div>';
        echo '</div>';

        echo '</div>';
        echo '</div>';
    }
}

p_tree($campos[null][hijos]);
?>
</div>
<a href="#" onclick="p_nuevo();return false;" style="position:fixed;bottom:50px;right:10px;"><img src="/img/plus.png" alt="Crear nuevo registro" title="Crear nuevo registro" ></img></a>
<div id="modal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Campo <span id="formulario_titulo"></span></h4>
      </div>
      <div class="modal-body">


<form id="formulario" class="form-horizontal">
<input type="hidden" id="id" name="id" value="">
<input type="hidden" id="padre" name="padre" value="">
  <div class="form-group">
    <label for="texto" class="col-sm-2 control-label">Etiqueta:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="texto" name="texto" placeholder="">
    </div>
  </div>
  <div class="form-group">
    <label for="codigo" class="col-sm-2 control-label">Código:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="codigo" name="codigo" placeholder="">
    </div>
  </div>
  <div class="form-group">
    <label for="validacion" class="col-sm-2 control-label">Validación:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="validacion" name="validacion" placeholder="">
    </div>
  </div>
  <div class="form-group">
    <label for="tipo_dato" class="col-sm-2 control-label">Tipo de dato:</label>
    <div class="col-sm-10">
      <select id="tipo_dato" name="tipo_dato" class="form-control combo-select2" style="width:50%">
        <?php $result=q("SELECT * FROM sai_tipo_dato ORDER BY tid_nombre"); ?>
        <?php foreach($result as $r): ?>
            <option value="<?=$r['tid_id']?>"><?=$r['tid_nombre']?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
</form>


      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-success" onclick="p_guardar()" id="formulario_guardar">Guardar cambios</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<script>
$(document).ready(function() {
    $('.combo-select2').select2({
        language: "es"
    });
});
function p_nuevo() {
    $('#formulario_titulo').text('nuevo');
    $('#formulario').trigger('reset');
    $('#id').val('');

    console.log('nuevo');
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
        case 'hidden':
        case 'email':
            $(this).val('');
            $(this).prop('disabled', false);
            break;
        case 'checkbox':
        case 'radio':
            this.checked = false;
            $(this).prop('disabled', false);
            break;
        }
    });

    $('#modal').modal('show');
}


function p_abrir(id){
    $.ajax({
        'url':'/_listar/campo_extra/'+id
    }).done(function(data){
        //data = eval(data);
        console.log('_listar/campo_extra/' + id, data);
        data = JSON.parse(data);
        campo = data[0];
        console.log('ABRIENDO CAMPO', campo);

        var badge = '';
        var disabled = false;
        $('#formulario_titulo').html('"'+campo['texto'] + '" (%' + campo['codigo'] + '%) ' + badge);
        for (key in campo){
            $('#' + key).val(campo[key]);
            $('#' + key).prop('disabled', disabled);
            $('#' + key).trigger('change');
        }
        
        $('#modal').modal('show');
    }).fail(function(){
        console.error('ERROR AL ABRIR');
        alert('No se pudo cargar los datos. Contacte con el area de sistemas.');
    });
}

function p_borrar(){

    if (confirm('Seguro desea eliminar el campo "' + $('#texto').val() + '" (' + $('#codigo').val() + ')')) {
        dataset_json = {};
        dataset_json['id'] = $('#id').val();
        dataset_json['borrar'] = 'borrar';

        console.log('dataset_json', dataset_json);
        $.ajax({
        url: '_guardarUsuario',
            type: 'POST',
            //dataType: 'json',
            data: JSON.stringify(dataset_json),
            //contentType: 'application/json'
        }).done(function(data){
            console.log('Borrado OK, data:', data);
            //data = eval(data)[0];
            data = JSON.parse(data);
            data = data[0];
            console.log('eval data:', data);
            //$('#nombre_' + data['id']).parent().parent().remove();
            if (data['ERROR']) {
                alert(data['ERROR']);
            } else {
                $('#nombre_' + data['id']).parent().parent().removeClass('alert alert-success alert-info');
                $('#nombre_' + data['id']).parent().parent().addClass('alert alert-danger');
                $('#modal').modal('hide');
            }

        }).fail(function(xhr, err){
            console.error('ERROR AL BORRAR', xhr, err);
            alert('Hubo un error al borrar, verifique que cuenta con Internet y vuelva a intentarlo en unos momentos.');
            //$('#modal').modal('hide');
        });
    }
}

function p_validar(target) {
    console.log('validando', target);
    var resultado = true;
    if (!$(target)[0].checkValidity()) {
        console.log('no valida...');
        $('<input type="submit">').hide().appendTo('#formulario').click().remove();
        resultado = false;
    }
    return resultado;
}
function p_guardar(){

    console.log('p_guardar');
    if (p_validar($('#formulario'))) {
        var dataset = $('#formulario').serialize();
        $.post('_guardarCampo', dataset, function(data){
        });
    }
}

</script>>
