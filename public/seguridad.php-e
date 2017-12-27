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
        $permisos[$permiso['per_objeto']][$permiso['per_rol']] = 1;
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
<td><a href="#" onclick="p_cambiar_permiso(<?=$modulo['obj_id']?>, <?=$rol['rol_id']?>);return false;"><img id="permiso_<?=$modulo['obj_id']?>_<?=$rol['rol_id']?>" src="/img/<?=((isset($permisos[$modulo['obj_id']]) && isset($permisos[$modulo['obj_id']][$rol['rol_id']]))?'si':'no')?>.png" style="width:20px;height:20px;" /></a></td>
<?php endforeach; ?>
</tr>
<?php endforeach; ?>
<?php endif; ?>
<tbody>
</tbody>
</table>
<script>
function p_cambiar_permiso(modulo, rol){
    if (confirm('Seguro desea cambiar este permiso?')) {
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
            if (data['error'].length != 0) {
                data['error'].forEach(function(error){
                    alert(error);
                });
            } else {
                data = data['respuesta'];

                var si_no = (data['count_permiso'] == 0 ? 'no' : 'si');
                $('#permiso_' + data['modulo'] + '_' + data['rol']).attr('src', '/img/' + si_no + '.png');
            }
        }).fail(function(xhr, err){
            console.error('ERROR AL CAMBIAR', xhr, err);
            alert('No se pudo cambiar el permiso.');
        });
    }

}
</script>

