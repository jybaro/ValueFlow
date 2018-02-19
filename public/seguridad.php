<div class="page-header">
<h1>Seguridad</h1>
</div>

<?php


$roles = q("SELECT * FROM sai_rol ORDER BY rol_id");
$modulos = q("SELECT * FROM sai_objeto ORDER BY obj_id");
$permisoes = q("SELECT * from sai_permiso ORDER BY per_id");
$permisos = array();
if ($permisoes){
    foreach($permisoes as $permiso) {
        if (!isset($permisos[$permiso['per_objeto']])) {
            $permisos[$permiso['per_objeto']] = array();
        }
        //$permisos[$permiso['per_objeto']][$permiso['per_rol']] = 1;
        $permisos[$permiso['per_objeto']][$permiso['per_rol']] = $permiso['per_solo_lectura'];
    }
}
?>

<table class="table table-striped table-condensed table-hover">
<thead>
<tr>
<th>&nbsp;</th>
<?php foreach($roles as $rol): ?>
<th><?=$rol['rol_nombre']?></th>
<?php endforeach; ?>
</tr>
</thead>
<?php if ($modulos): ?>
<?php foreach($modulos as $count => $modulo): ?>
<tr>
<th><?=($count+1) . '. ' . $modulo['obj_nombre']?></th>
<?php foreach($roles as $rol): ?>
<td><a href="#" onclick="p_cambiar_permiso(<?=$modulo['obj_id']?>, <?=$rol['rol_id']?>);return false;"><img id="permiso_<?=$modulo['obj_id']?>_<?=$rol['rol_id']?>" src="/img/<?=((isset($permisos[$modulo['obj_id']]) && isset($permisos[$modulo['obj_id']][$rol['rol_id']]))?($permisos[$modulo['obj_id']][$rol['rol_id']]==1?'me':'si'):'no')?>.png" style="width:20px;height:20px;" /></a></td>
<?php endforeach; ?>
</tr>
<?php endforeach; ?>
<?php endif; ?>
<tbody>
</tbody>
</table>
<hr>
<div class="container">
<div class="panel panel-default">
<div class="panel-heading"><h4>Leyenda</h4></div>
<div class="panel-body">
<div class="media">
  <div class="media-left media-middle">
    <a href="#">
      <img class="media-object" src="/img/si.png" alt="Sí">
    </a>
  </div>
  <div class="media-body media-middle">
    <h4 class="media-heading">Permiso de acceso de lectura y escritura</h4>
    <p>Se puede ingresar a la pantalla, visualizar los datos, modificar y pasar las transiciones de estados.</p>
  </div>
</div>

<div class="media">
  <div class="media-left media-middle">
    <a href="#">
      <img class="media-object" src="/img/me.png" alt="Sí">
    </a>
  </div>
  <div class="media-body media-middle">
    <h4 class="media-heading">Permiso de acceso de solo lectura</h4>
    <p>Se puede ingresar a la pantalla y visualizar los datos, pero no es posible modificar los datos ni pasar las transiciones de estados.</p>
  </div>
</div>

<div class="media">
  <div class="media-left">
    <a href="#">
      <img class="media-object" src="/img/no.png" alt="Sí">
    </a>
  </div>
  <div class="media-body media-middle">
    <h4 class="media-heading">Sin permiso de acceso</h4>
    <p>No se puede ingresar a la pantalla, apareciendo un mensaje de error.</p>
  </div>
</div>
</div>
</div>
</div>

<script>
function p_cambiar_permiso(modulo, rol){
    //if (confirm('Seguro desea cambiar este permiso?')) {
        var dataset_json = {modulo:modulo, rol:rol};
        console.log('dataset_json',dataset_json);
        $.ajax({
            url: '/_cambiarSeguridad',
            type: 'POST',
            //dataType: 'json',
            data: JSON.stringify(dataset_json),
            //contentType: 'application/json'
        }).done(function(data){
            console.log('Cambiado OK', data);
            //data = eval(data);
            data = JSON.parse(data);
            console.log('data:', data);
            if (data['error'].length != 0) {
                data['error'].forEach(function(error){
                    alert(error);
                });
            } else {
                data = data['respuesta'];
                var permiso = data['result_permiso'];

                //var si_no = (data['count_permiso'] == 0 ? 'no' : 'si');
                console.log('si hay data', data);
                var si_no = 'no';
                if (permiso && permiso.length > 0) {
                    if (permiso[0]['per_solo_lectura'] == 1) {
                        si_no = 'me';
                    } else {
                        si_no = 'si';
                    }
                    console.log('hay respuesta:', si_no);
                }
                $('#permiso_' + data['modulo'] + '_' + data['rol']).prop('src', '/img/' + si_no + '.png');
            }
        }).fail(function(xhr, err){
            console.error('ERROR AL CAMBIAR', xhr, err);
            alert('No se pudo cambiar el permiso.');
        });
    //}

}
</script>

