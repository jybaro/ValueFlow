<div class="page-header"><h1>Dependencia de Empresas</h1></div>
<?php

$result = q("
    SELECT *
    FROM sai_cuenta

    INNER JOIN sai_cliente
        ON cli_borrado IS NULL
        AND cue_cliente = cli_id

    LEFT OUTER JOIN sai_contacto
        ON con_borrado IS NULL
        AND con_id = cue_contacto

    WHERE cue_borrado IS NULL
    ORDER BY cue_id DESC
");

$cuentas = array();
foreach ($result as $r) {
    $cuentas[$r[cue_id]] = $r;
    $cuentas[$r[cue_id]][padre] = null;
    $cuentas[$r[cue_id]][hijos] = array();
}

foreach ($cuentas as $cue_id => $cuenta) {
    $cuentas[$cue_id][padre] = & $cuentas[$cuenta[cue_padre]];
    $cuentas[$cuenta[cue_padre]][hijos][$cue_id] = & $cuentas[$cue_id];
}

function p_tree($cuentas) {
    $plus = '<span class="glyphicon glyphicon-plus" aria-hidden="true"></span>';
    $minus = '<span class="glyphicon glyphicon-minus" aria-hidden="true"></span>';
    $leaf = '<span class="glyphicon glyphicon-leaf" aria-hidden="true"></span>';

    if (!empty($cuentas) && is_array($cuentas)) {

        foreach ($cuentas as $cuenta) {
            $icono = count($cuenta[hijos]) == 0 ? '&nbsp;&nbsp;&nbsp;' : $plus;

            $titulo = !isset($cuenta[cue_codigo]) ? '': "{$cuenta[cue_codigo]} ({$cuenta[cli_razon_social]})";
            $count_hijos = count($cuenta[hijos]);
            echo <<<EOT
<div class="media" id="cuenta_{$cuenta[cue_id]}">
  <div class="media-left">
    <a href="#" onclick="p_toggle({$cuenta[cue_id]}); return false;" id="icono_{$cuenta[cue_id]}">
      $icono
    </a>
  </div>
  <div class="media-body">
    <h4 class="media-heading"><a href="#" onclick="p_abrir({$cuenta[cue_id]}); return false;">{$titulo}</a> <span id="badge_{$cuenta[cue_id]}" class="badge">$count_hijos</span></h4>
    <strong>RUC:</strong> {$cuenta[cli_ruc]} &nbsp;&nbsp;|&nbsp;&nbsp; <strong>Mail:</strong> {$cuenta[cli_representante_legal_email]} &nbsp;&nbsp;|&nbsp;&nbsp; <strong>Dirección:</strong> {$cuenta[cli_direccion_correspondencia]}
    <div style="margin-top:10px;" id="hijos_{$cuenta[cue_id]}">
EOT;
            p_tree($cuenta[hijos]);
            echo <<<EOT
    </div>
  </div>
</div>
EOT;
        }
    }
}
p_tree($cuentas[null][hijos]);

?>

<a href="#" onclick="p_nuevo();return false;" style="position:fixed;bottom:50px;right:10px;"><img src="/img/plus.png" alt="Crear nuevo registro" title="Crear nuevo registro" ></img></a>
<div id="modal" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Dependencia <span id="formulario_titulo"></span></h4>
      </div>
      <div class="modal-body">


<form id="formulario" class="form-horizontal">
<input type="hidden" id="id" name="id" value="">
  <div class="form-group">
    <label for="codigo" class="col-sm-4 control-label">Descripción:</label>
    <div class="col-sm-8">
      <input type="text" required class="form-control" id="codigo" name="codigo" placeholder="Codigo">
    </div>
  </div>
  <div class="form-group">
    <label for="peso" class="col-sm-4 control-label">Peso:</label>
    <div class="col-sm-8">
      <input type="number" required class="form-control" id="peso" name="peso" placeholder="Peso">
    </div>
  </div>
  <div class="form-group">
    <label for="padre" class="col-sm-4 control-label">Dependiencia padre:</label>
    <div class="col-sm-8">
      <select id="padre" name="padre" class="form-control combo-select2" style="width:50%">
        <option value="">&nbsp;</option>
        <?php $roles=q("SELECT * FROM sai_cuenta ORDER BY cue_codigo"); ?>
        <?php foreach($roles as $rol): ?>
            <option value="<?=$rol['cue_id']?>"><?=$rol['cue_codigo']?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
  <div class="form-group">
    <label for="cliente" class="col-sm-4 control-label">Empresa:</label>
    <div class="col-sm-8">
      <select required id="cliente" name="cliente" class="form-control combo-select2" style="width:50%">
        <option value="">&nbsp;</option>
        <?php $roles=q("SELECT * FROM sai_cliente ORDER BY cli_razon_social"); ?>
        <?php foreach($roles as $rol): ?>
            <option value="<?=$rol['cli_id']?>"><?=$rol['cli_razon_social']?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
  <div class="form-group">
    <label for="responsable_cobranzas" class="col-sm-4 control-label">Responsable cobranzas:</label>
    <div class="col-sm-8">
      <select required class="form-control combo-select2" style="width: 50%" id="responsable_cobranzas" name="responsable_cobranzas" tabindex="-1" aria-hidden="true">
        <option value="">&nbsp;</option>
      <?php
$result = q("
    SELECT *
    FROM sai_usuario
    ,sai_rol
    WHERE 
    usu_borrado IS NULL
    AND rol_id = usu_rol
    AND rol_codigo = 'comercial'
");
if ($result) {
    foreach($result as $r) {
        $value = $r['usu_id'];
        $label = $r['usu_nombres'] . ' ' .$r['usu_apellidos'];
        echo "<option value='$value'>$label</option>";
    }
}
        ?>

      </select> 
    </div>
  </div>
<!--
  <div class="form-group">
    <label for="usuario_tecnico" class="col-sm-4 control-label">Usuario Técnico:</label>
    <div class="col-sm-8">
      <select required class="form-control combo-select2" style="width: 50%" id="usuario_tecnico" name="usuario_tecnico" tabindex="-1" aria-hidden="true">
        <option value="">&nbsp;</option>
      <?php
$result = q("
    SELECT *
    FROM sai_usuario
    ,sai_rol
    WHERE 
    usu_borrado IS NULL
    AND rol_id = usu_rol
    AND rol_codigo = 'tecnico'
");
if ($result) {
    foreach($result as $r) {
        $value = $r['usu_id'];
        $label = $r['usu_nombres'] . ' ' .$r['usu_apellidos'];
        echo "<option value='$value'>$label</option>";
    }
}
        ?>

      </select> 
    </div>
  </div>
  <div class="form-group">
    <label for="contacto" class="col-sm-4 control-label">Contacto:</label>
    <div class="col-sm-8">
      <select required id="contacto" name="contacto" class="form-control combo-select2" style="width:50%">
        <option value="">&nbsp;</option>
        <?php $contactos = q("SELECT * FROM sai_contacto ORDER BY con_apellidos"); ?>
        <?php foreach($contactos as $contacto): ?>
            <option value="<?=$contacto['con_id']?>"><?=$contacto['con_nombres'].' '.$contacto['con_apellidos']?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
-->

</form>


      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-danger" onclick="p_borrar()" id="formulario_eliminar">Eliminar usuario</button>
        <button type="button" class="btn btn-success" onclick="p_recuperar()" id="formulario_recuperar">Recuperar usuario</button>
        <button type="button" class="btn btn-success" onclick="p_guardar()" id="formulario_guardar">Guardar cambios</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<script src="/js/bootstrap3-typeahead.min.js"></script>
<script>
$(document).ready(function() {
    $('.combo-select2').select2({
        language: "es"
    });
});

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

    if (p_validar($('#formulario'))) {
        if ($('#codigo').val() !== '' && $('#peso').val() !== '' && $('#cuenta_padre').val() !== '' && $('#cliente').val() !== '' && $('#responsable_cobranzas').val() !== '' && $('#usuario_tecnico').val() !== '' && $('#contacto').val() !== '') {
                var respuestas_json = $('#formulario').serializeArray();
                console.log('respuestas json', respuestas_json);
                dataset_json = {};
                respuestas_json.forEach(function(respuesta_json){
                    var name =  respuesta_json['name'];
                    var value = respuesta_json['value'];
                    dataset_json[name] = value;

                });
                dataset_json['codigo'] = $('#codigo').val();

                console.log('dataset_json', dataset_json);
                $.ajax({
                url: '/_guardarCuenta',
                    type: 'POST',
                    //dataType: 'json',
                    data: JSON.stringify(dataset_json),
                    //contentType: 'application/json'
                }).done(function(data){
                    console.log('Guardado OK, data:', data);
                    //data = eval(data)[0];
                    data = JSON.parse(data);
                    data = data[0];

                    console.log('eval data:', data);
                    if (data['ERROR']) {
                        alert(data['ERROR']);
                    } else {

                        if ($("#nombre_" + data['id']).length) { // 0 == false; >0 == true
                            //ya existe:
                            console.log('CUENTA ya existe');
                        } else {
                            //nuevo:
                            console.log('nueva CUENTA');
                        }
                        location.reload();
                        $('#modal').modal('hide');
                    }
                }).fail(function(xhr, err){
                    console.error('ERROR AL GUARDAR', xhr, err);
                    alert('Hubo un error al guardar, verifique que cuenta con Internet y vuelva a intentarlo en unos momentos.');
                    //$('#modal').modal('hide');
                });
        } else {
            alert ('Ingrese los datos del formulario'); 
        }
    }
}

function p_abrir(id){
    $.ajax({
        'url':'/_listar/cuenta/'+id
    }).done(function(data){
        //data = eval(data);
        console.log('/_listar/cuenta/'+id, data);
        data = JSON.parse(data);
        data = data[0];
        console.log('ABRIENDO CUENTA', data);

        var badge = '';
        var disabled = false;
        if (data['borrado'] == null) {
            $('#formulario_eliminar').show();
            $('#formulario_guardar').show();
            $('#formulario_recuperar').hide();
            disabled = false;
            //p_abrir_permiso_ingreso(data['id']);
        } else {
            badge = '<span class="badge">ELIMINADO</span>';
            $('#formulario_eliminar').hide();
            $('#formulario_guardar').hide();
            $('#formulario_recuperar').show();
            disabled = true;
        }
        $('#formulario_titulo').html(data['codigo'] + ' ' + badge);
        for (key in data){
            $('#' + key).val(data[key]);
            $('#' + key).trigger('change');
            $('#' + key).prop('disabled', disabled);
        }

        $("#codigo").prop('disabled', true);
        
        $('#modal').modal('show');
    }).fail(function(){
        console.error('ERROR AL ABRIR');
        alert('No se pudo cargar los datos. Contacte con el area de sistemas.');
    });
}

function p_toggle(id) {
    $('#hijos_' + id).toggle('fast');
    $('span', "#icono_" + id).toggleClass("glyphicon-plus glyphicon-minus");
}

function p_nuevo(){

    $('#formulario_titulo').text('nueva');
    $('#formulario').trigger('reset');
    $('#id').val('');
    $('#formulario_eliminar').hide();
    $('#formulario_recuperar').hide();
    $('#formulario_guardar').show();
 
    $('#codigo').prop('disabled', false);

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

function p_recuperar(){

    dataset_json = {};
    dataset_json['codigo'] = $('#codigo').val();
    dataset_json['id'] = $('#id').val();
    dataset_json['recuperar'] = 'recuperar';

    console.log('dataset_json', dataset_json);
    $.ajax({
    url: '/_guardarCuenta',
        type: 'POST',
        //dataType: 'json',
        data: JSON.stringify(dataset_json),
        //contentType: 'application/json'
    }).done(function(data){
        console.log('RECUPERADO OK, data:', data);
        //data = eval(data)[0];
        data = JSON.parse(data);
        data = data[0];
        console.log('eval data:', data);
        if (data['ERROR']) {
            alert(data['ERROR']);
        } else {
            $('#nombre_' + data['id']).parent().parent().removeClass('alert alert-danger alert-info');
            $('#nombre_' + data['id']).parent().parent().addClass('alert alert-success');
            $('#modal').modal('hide');
        }

    }).fail(function(xhr, err){
        console.error('ERROR AL RECUPERAR', xhr, err);
        alert('Hubo un error al recuperar, verifique que cuenta con Internet y vuelva a intentarlo en unos momentos.');
        //$('#modal').modal('hide');
    });
}

function p_borrar(){

    if (confirm('Seguro desea eliminar la Dependiencia ' + $('#codigo').val() + '')) {
        dataset_json = {};
        dataset_json['id'] = $('#id').val();
        dataset_json['codigo'] = $('#codigo').val();
        dataset_json['borrar'] = 'borrar';

        console.log('dataset_json', dataset_json);
        $.ajax({
        url: '_guardarCuenta',
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
                location.reload();
            }

        }).fail(function(xhr, err){
            console.error('ERROR AL BORRAR', xhr, err);
            alert('Hubo un error al borrar, verifique que cuenta con Internet y vuelva a intentarlo en unos momentos.');
            //$('#modal').modal('hide');
        });
    }
}

</script>
