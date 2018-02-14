<?php

$cae_texto = 'Componente de Ubicación';
$cae_id = '0';
$cae_validacion = '';

$result_provincias = q("
    SELECT *
    FROM sai_provincia
    ORDER BY prv_nombre
");
$provincias = array();
if ($result_provincias) {
    foreach ($result_provincias as $r) {
        $provincias[] = $r;
    }
}
$opciones = '<option value="">&nbsp;</option>';

foreach($provincias as $provincia) {
    $codigo = $provincia['prv_id'];
    $nombre = $provincia['prv_nombre'];
    $opciones .= '<option value="'. $codigo . '">'.$nombre.' </option>';
}
?>
<div class="panel panel-default">
  <div class="panel-heading">
    <strong><?=$cae_texto?></strong>
  </div>
  <div class="panel-body">
    <input type="hidden" class="form-control" id=" $cae_validacionextra_<?=$cae_id?>" name=" $cae_validacionextra_<?=$cae_id?>" value="">

    <div class="form-group">
      <label for="provincia_<?=$cae_id?>" class="col-sm- col1  control-label">Provincia:</label>
      <div class="col-sm- col2 ">
        <select <?=$cae_validacion?> class="form-control combo-select2" id="provincia_<?=$cae_id?>" name="provincia_<?=$cae_id?>" placeholder="" value="" onblur="p_validar(this)" onchange="p_cargar_cantones_ciudades(this, <?=$cae_id?>)">
    <?=$opciones?> 
        </select>
      </div>
    </div>
   
    <div class="form-group">
      <label for="canton_<?=$cae_id?>" class="col-sm- col1  control-label">Cantón:</label>
      <div class="col-sm- col2 ">
        <select disabled <?=$cae_validacion?> class="form-control combo-select2" id="canton_<?=$cae_id?>" name="canton_<?=$cae_id?>" placeholder="" value="" onblur="p_validar(this)" onchange="p_cargar_parroquias(this, <?=$cae_id?>)">
          <option>Escoja primero la provincia</option>
        </select>
      </div>
    </div>

    <div class="form-group">
      <label for="parroquia_<?=$cae_id?>" class="col-sm- col1  control-label">Parroquia:</label>
      <div class="col-sm- col2 ">
        <select disabled <?=$cae_validacion?> class="form-control combo-select2" id="parroquia_<?=$cae_id?>" name="parroquia_<?=$cae_id?>" placeholder="" value="" onblur="p_validar(this)" >
          <option>Escoja primero la provincia y el cantón</option>
        </select>
      </div>
    </div>

    <div class="form-group">
      <label for="ciudad_<?=$cae_id?>" class="col-sm- col1  control-label">Ciudad:</label>
      <div class="col-sm- col2 ">
        <select disabled <?=$cae_validacion?> class="form-control combo-select2" id="ciudad_<?=$cae_id?>" name="ciudad_<?=$cae_id?>" placeholder="" value="" onblur="p_validar(this)" >
          <option>Escoja primero la provincia</option>
        </select>
      </div>
    </div>
       
    <div class="form-group">
      <label for="sector_ <?=$cae_id?> " class="col-sm- col1  control-label">Sector:</label>
      <div class="col-sm- col2 ">
        <input <?=$cae_validacion?> class="form-control" id="sector_<?=$cae_id?>" name="sector_<?=$cae_id?>" placeholder="" value="" onblur="p_validar(this)">
      </div>
    </div>

    <div class="form-group">
      <label for="direccion_ <?=$cae_id?> " class="col-sm- col1  control-label">Dirección:</label>
      <div class="col-sm- col2 ">
        <input <?=$cae_validacion?> class="form-control" id="direccion_<?=$cae_id?>" name="direccion_<?=$cae_id?>" placeholder="" value="" onblur="p_validar(this)">
      </div>
    </div>

    <div class="panel panel-default">
      <div class="panel-heading">
        <strong>Coordenadas</strong>
      </div>
      <div class="panel-body">
        <div class="form-group">
          <label for="longitud_ <?=$cae_id?> " class="col-sm- col1  control-label">Longitud:</label>
          <div class="col-sm- col2 ">
            <input <?=$cae_validacion?> class="form-control" id="longitud_<?=$cae_id?>" name="longitud_<?=$cae_id?>" placeholder="" value="" onblur="p_validar(this)">
          </div>
        </div>
        <div class="form-group">
          <label for="latitud_ <?=$cae_id?> " class="col-sm- col1  control-label">Latitud:</label>
          <div class="col-sm- col2 ">
            <input <?=$cae_validacion?> class="form-control" id="latitud_<?=$cae_id?>" name="latitud_<?=$cae_id?>" placeholder="" value="" onblur="p_validar(this)">
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
    $('.combo-select2').select2({
        language: "es"
        ,width: '100%'
    });
    function p_cargar_cantones_ciudades(target, cae_id){
        console.log('En p_cargar_cantones_ciudades', $(target).val(), cae_id);
        var prv_id = $(target).val();
        $('#canton_' + cae_id).prop('disabled', true);
        $('#canton_' + cae_id).html('<option value="">Escoja primero la provincia</option>');
        $('#ciudad_' + cae_id).prop('disabled', true);
        $('#ciudad_' + cae_id).html('<option value="">Escoja primero la provincia</option>');
        $('#parroquia_' + cae_id).prop('disabled', true);
        $('#parroquia_' + cae_id).html('<option value="">Escoja primero el cantón</option>');

        if (prv_id != '') {
            $.ajax({
                'url':'/_listar/canton/provincia/' + prv_id
            }).done(function(data){
                console.log('Respuesta /_listar/canton/provincia/' + prv_id, data);
                data = JSON.parse(data);
                console.log('data',data);
                var opciones = '';
                Array.from(data).forEach(function(canton){
                    opciones += '<option value="'+canton['id']+'">'+canton['nombre']+'</option>';
                });

                $('#canton_' + cae_id).prop('disabled', false);
                $('#canton_' + cae_id).html('<option value="">&nbsp;</option>' + opciones);

            });

            $.ajax({
                'url':'/_listar/ciudad/provincia/' + prv_id
            }).done(function(ciudades){
                console.log('Respuesta /_listar/ciudad/provincia/' + prv_id, ciudades);
                ciudades = JSON.parse(ciudades);
                console.log('ciudades',ciudades);
                var opciones = '';
                Array.from(ciudades).forEach(function(ciudad){
                    opciones += '<option value="'+ciudad['id']+'">'+ciudad['nombre']+'</option>';
                });

                $('#ciudad_' + cae_id).prop('disabled', false);
                $('#ciudad_' + cae_id).html('<option value="">&nbsp;</option>' + opciones);
            });
        }
    }

    function p_cargar_parroquias(target, cae_id){
        console.log('En p_cargar_parroquias', $(target).val(), cae_id);
        var can_id = $(target).val();
        $('#parroquia_' + cae_id).prop('disabled', true);
        $('#parroquia_' + cae_id).html('<option value="">Escoja primero el cantón</option>');
        if (can_id != '') {
            $.ajax({
                'url':'/_listar/parroquia/canton/' + can_id
            }).done(function(data){
                console.log('Respuesta /_listar/parroquia/canton/' + can_id, data);
                data = JSON.parse(data);
                console.log('data',data);
                var opciones = '';
                Array.from(data).forEach(function(canton){
                    opciones += '<option value="'+canton['id']+'">'+canton['nombre']+'</option>';
                });

                $('#parroquia_' + cae_id).prop('disabled', false);
                $('#parroquia_' + cae_id).html('<option value="">&nbsp;</option>' + opciones);
            });
        }
    }

    function p_validar() {
        console.log('En p_validar');
    }
</script>
