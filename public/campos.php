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
    ORDER BY cae_orden, cae_id, cae_texto
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

$plantilla_cabecera = <<<EOT
  <div id="campo_%CAE_ID%" class="panel panel-info">
    <div class="panel-heading">
      <div class="radio">
        <label>
          <input type="radio" name="optionsRadios" id="optionsRadios_%CAE_ID%"" value="%CAE_ID%">
          <h3 id="titulo_%CAE_ID%" class="panel-title">%CAE_TEXTO%</h3>
        </label>
      </div>
    </div>
    <div class="panel-body">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title">
            Metadata 
          </h3>
        </div>
        <div class="panel-body">
          <a class="btn btn-success pull-right" href="#" onclick="p_abrir(%CAE_ID%);return false;">Acciones</a>
          <strong>Texto:</strong> <span id="cae_texto_%CAE_ID%">%CAE_TEXTO%</span>
          <br>
          <strong>Código:</strong> \${<span id="cae_codigo_%CAE_ID%">%CAE_CODIGO%</span>}
          <br>
          <strong>Tipo de dato:</strong> <span id="tid_nombre_%CAE_ID%">%TID_NOMBRE%</span>
          <br>
          <strong>Validación:</strong> <span id="cae_validacion_%CAE_ID%">%CAE_VALIDACION%</span>
          <br>
          <strong>Orden:</strong> <span id="cae_orden_%CAE_ID%">%CAE_ORDEN%</span>
          <br>
          <strong>Cantidad:</strong> <span id="cae_cantidad_%CAE_ID%">%CAE_CANTIDAD%</span>
        </div>
      </div>
      <div class="panel panel-default">
        <div class="panel-heading">
          <h3 class="panel-title">
            Hijos <span class="badge" id="count_hijos_%CAE_ID%">%COUNT_HIJOS%</span> 
          </h3>
        </div>
        <div class="panel-body">
          <a href="#" onclick="p_nuevo(%CAE_ID%);return false;"><img src="/img/plus.png" alt="Crear nuevo registro" title="Crear nuevo registro" ></img></a>
          <div id="hijos_%CAE_ID%">
EOT;

$plantilla_pie = <<<EOT
          </div>
        </div>
      </div>
    </div>
  </div>
EOT;

function p_tree($arbol) {
    global $plantilla_cabecera, $plantilla_pie;
    foreach ($arbol as $cae_id => $c) {
        $cabecera = $plantilla_cabecera;
        $kv = array(
            '%CAE_ID%' => $c[cae_id]
            ,'%CAE_TEXTO%' => $c[cae_texto]
            ,'%CAE_CODIGO%' => $c[cae_codigo]
            ,'%TID_NOMBRE%' => $c[tid_nombre]
            ,'%CAE_VALIDACION%' => $c[cae_validacion]
            ,'%CAE_ORDEN%' => $c[cae_orden]
            ,'%CAE_CANTIDAD%' => $c[cae_cantidad]
            ,'%COUNT_HIJOS%' => count($c[hijos])
        );

        $cabecera = str_replace(array_keys($kv), array_values($kv), $cabecera);
        echo $cabecera;

        if (!empty($c[hijos])) {
            p_tree($c[hijos]);
        }
        $pie = $plantilla_pie;
        echo $pie;
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
<input type="hidden" id="accion" name="accion" value="">
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
    <label for="orden" class="col-sm-2 control-label">Orden:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="orden" name="orden" placeholder="">
    </div>
  </div>
  <div class="form-group">
    <label for="cantidad" class="col-sm-2 control-label">Cantidad de elementos:</label>
    <div class="col-sm-10">
      <input type="text" class="form-control" id="cantidad" name="cantidad" placeholder="">
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
        <button type="button" class="btn btn-warning" onclick="p_duplicar()" id="formulario_duplicar">Duplicar</button>
        <button type="button" class="btn btn-danger" onclick="p_borrar()" id="formulario_borrar">Eliminar registro</button>
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

$(document).on('keydown', function(e) {
    console.log('keydown', e.key, e.which);
});

$('input[type="radio"]').keydown(function(e)
{
    var arrowKeys = [37, 38, 39, 40];
    if (arrowKeys.indexOf(e.which) !== -1)
    {
        //$(this).blur();
        if (e.which == 38) {
            //arriba

            var y = $(window).scrollTop();
            $(window).scrollTop(y - 10);
        } else if (e.which == 40) {
            //abajo
            
            var y = $(window).scrollTop();
            $(window).scrollTop(y + 10);
        } else if (e.which == 37) {
            //izquierda, baja un nivel
        } else if (e.which == 39) {
            //derecha, sube un nivel
        }
        return false;
    }
});

$("input[name=optionsRadios]").on('click', function(e){
    console.log('radio on click', e);
    $('#campo_' + e.target.value).removeClass('panel-info');
    $('#campo_' + e.target.value).addClass('panel-primary');
});

$("input[name=optionsRadios]").on('blur', function(e){
    console.log('radio on blur', e);
    $('#campo_' + e.target.value).removeClass('panel-primary');
    $('#campo_' + e.target.value).addClass('panel-info');
});

var plantilla_cabecera = <?=json_encode($plantilla_cabecera)?>;
var plantilla_pie = <?=json_encode($plantilla_pie)?>;

function p_nuevo(padre) {
    console.log('p_nuevo', padre);
    if (typeof(padre) === 'undefined') {
        padre = 0;
    }
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
    $('#padre').val(padre);

    $('#modal').modal('show');
}


function p_abrir(id){
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
        $('#formulario_titulo').html('"'+campo['texto'] + '": ${' + campo['codigo'] + '} ' + badge);
        for (key in campo){
            $('#' + key).val(campo[key]);
            $('#' + key).prop('disabled', disabled);
            $('#' + key).trigger('change');
        }
        
        $('#id').val(id);
        $('#padre').val(campo['padre']);
        $('#modal').modal('show');
    }).fail(function(){
        console.error('ERROR AL ABRIR');
        alert('No se pudo cargar los datos. Contacte con el area de sistemas.');
    });
}

function p_duplicar(){

    if (confirm('Seguro desea duplicar el campo "' + $('#texto').val() + '" (' + $('#codigo').val() + ')')) {
        $('#accion').val('duplicar');
        var dataset = $('#formulario').serialize();
        $.post('/_modificarCampo', dataset, function(data){
            console.log('_modificarCampo', data);
            data = JSON.parse(data);
            if (data['ERROR']) {
                alert (data['ERROR']);
            } else {
                data = data[0];
                console.log(data);
                //var nuevo = $('#campo_' + data['cae_id']).clone();
                //nuevo.prop('id', );
                //$('#hijos_' + data['cae_padre']).append($('#campo_' + data['cae_id']).clone());
                //var count = parseInt($('#count_hijos_' + data['cae_padre']).text());
                //$('#count_hijos_' + data['cae_padre']).text(count - 1);
                location.reload();
                
                $('#modal').modal('hide');
            }
        });
    }
}

function p_borrar(){

    if (confirm('Seguro desea eliminar el campo "' + $('#texto').val() + '" (' + $('#codigo').val() + ')')) {
        $('#accion').val('borrar');
        var dataset = $('#formulario').serialize();
        $.post('/_modificarCampo', dataset, function(data){
            console.log('_modificarCampo', data);
            data = JSON.parse(data);
            if (data['ERROR']) {
                alert (data['ERROR']);
            } else {
                data = data[0];
                console.log(data);
                $('#campo_' + data['cae_id']).remove();
                var count = parseInt($('#count_hijos_' + data['cae_padre']).text());
                $('#count_hijos_' + data['cae_padre']).text(count - 1);
                
                $('#modal').modal('hide');
            }
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
        $.post('/_modificarCampo', dataset, function(data){
            console.log('_modificarCampo', data);
            data = JSON.parse(data);
            if (data['ERROR']) {
                alert (data['ERROR']);
            } else {
                data = data[0];
                console.log(data);
                if ($('#id').val() == '') {
                    //nuevo, crea cuadro en interfaz
                    console.log('nuevo');
                    var cabecera = plantilla_cabecera;
                    var pie = plantilla_pie;
                    cabecera = cabecera.split('%CAE_ID%').join(data['cae_id']);
                    cabecera = cabecera.split('%CAE_TEXTO%').join(data['cae_texto']);
                    cabecera = cabecera.split('%CAE_CODIGO%').join(data['cae_codigo']);
                    cabecera = cabecera.split('%CAE_VALIDACION%').join(data['cae_validacion']);
                    cabecera = cabecera.split('%CAE_ORDEN%').join(data['cae_orden']);
                    cabecera = cabecera.split('%CAE_CANTIDAD%').join(data['cae_cantidad']);
                    cabecera = cabecera.split('%COUNT_HIJOS%').join('0');
                    console.log('caberera-pie:', cabecera, pie);

                    $('#hijos_' + data['cae_padre']).append(cabecera + pie);
                    var count = parseInt($('#count_hijos_' + data['cae_padre']).text());
                    $('#count_hijos_' + data['cae_padre']).text(count + 1);

                } else {
                    //existente, actualiza interfaz
                    console.log('existente');
                    $('#titulo_' + data['cae_id']).text(data['cae_texto']);
                    $('#cae_texto_' + data['cae_id']).text(data['cae_texto']);
                    $('#cae_codigo_' + data['cae_id']).text(data['cae_codigo']);
                    $('#cae_validacion_' + data['cae_id']).text(data['cae_validacion']);
                    $('#cae_orden_' + data['cae_id']).text(data['cae_orden']);
                    $('#cae_cantidad_' + data['cae_id']).text(data['cae_cantidad']);
                }
                $('#modal').modal('hide');
            }
        });
    }
}

</script>
