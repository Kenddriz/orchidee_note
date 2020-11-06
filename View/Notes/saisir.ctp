<?php $this->start('gestion'); ?>
<div class="row">
	<div class="col-sm-1"></div>
	<div class="col-sm-2 jumbotron">
		<h6 class="text-center">Options de saisi</h6>
		<?= $this->Form->create('Note', ['type'=>'post', 'action'=>'saisir']) ;?>
			<table class="table table-sm small">
				<thead>
					<tr>
						<td colspan="2"></td>
					</tr>
				</thead>
					<?php foreach($options as $cle => $option):;?>
						<?php if($cle == 'Anneescolaire'):;?>
							<tr>
								<td colspan="2" class="text-center text-">
									Année actuelle : <?=$option;?>
								</td>
								<td>
									<?= $this->Form->input($cle,array(
										'type'=> 'hidden',
										'value' => $option
									))?>
								</td>
							</tr>
						<?php else:;?>
						<tr>
							<td><?= strtolower(explode(' ', $cle)[0]);?></td>
							<td>
								<?= $this->Form->input($cle, 
									[
										'type'=>'select',
										'options'=>$option,
										'label'=>false,
										'required'=>false,//On autorise pas la validation si les données sont nulles
										'class' => 'form-control form-control-sm recherche_select'
									]
								); ?>
							</td>
						</tr>
					<?php endif;?>
					<?php endforeach; ?>
					<tr>
						<td colspan="2">
							<?= $this->Form->submit('Valider', ['class'=>'btn btn-sm btn-info form-control']); ?>
						</td>
					</tr>
			</table>
		<?= $this->Form->end(); ?>
		<!--notification-->
		<!--<table class="table table-bordered table-hover table-sm small bg-light">
			<tbody>
				<tr><td colspan="3" class="text-info text-center"> Conduite : </td></tr>
				<tr>
					<td>Type 1</td>
					<td colspan="2">comme toutes matières</td>
				</tr>
				<tr>
					<td>Type 2</td>
					<td colspan="2">Bonus ou Malus</td>
				</tr>
				<tr>
					<td>Type 3</td>
					<td colspan="2">Lettres: A, B, C</td>
				</tr>
				<tr>
					<td class="text-success">Bonne</td>
					<td class="text-warning">Passable</td>
					<td class="text-danger">Mauvaise</td>
				</tr>
			</tbody>
		</table>-->
	</div>
	<div class="col">
		<div style="max-height: 65vh; overflow: auto">
			<table class="table table-sm small table-bordered tfilter" id="table_saisi">
				<thead>
					<tr><!-- Rétards et absences à nepas saisir-->
						<th style="visibility: hidden;"></th>
						<?php foreach ($saisi[0] as $matiere): ;?>
							<th class="small bg-light" data-toggle="tooltip" 
								title="<?=$matiere['Matiere']['libelle_mat'];?>">
								<span style="writing-mode: vertical-rl; text-orientation: mixed;">
									<?php echo strtoupper(substr($matiere['Matiere']['libelle_mat'], 0, 3)); ?>
								</span>
							</th>
						<?php endforeach; ?>
					</tr>
					<!--code matière-->
					<tr style="display: none;">
						<th></th>
						<?php foreach ($saisi[0] as $matiere): ;?>
							<th>
								<?=$matiere['Matiere']['code_mat']; ?>
							</th>
						<?php endforeach; ?>
					</tr>
					<tr class="bg-secondary text-light">
						<th class="small">Coefficient</th>
						<?php foreach ($saisi[0] as $matiere): ;?>
							<th class="small">
								<?=$matiere['Coefficient']['valeur'];?>
							</th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($saisi[1] as $num => $promos): ;?>
						<tr>
							<td><?= $promos[0]; ?></td>
							<td style="display: none;"><?= $num; ?></td>
								<!--On cherche la valeur par matière-->
							<?php foreach ($saisi[0] as $matiere): ;?>
									<?php $nbr = 0; ?>
									<?php foreach ($saisi[2] as $note): ;?>
										<?php if($note['Note']['num_matricule']==$num && $note['Note']['code_mat'] == $matiere['Matiere']['code_mat']): ;?>
											<td>
												<input type="text" class="form-control form-control-sm"
												 value="<?= $note['Note']['note_obtenue'];?>"
												 <?=($editable==true) ? '': 'disabled';?>
												>
											</td>
											<?php $nbr++; ?>
										<?php endif; ?>
									<?php endforeach; ?>
									<?php if($nbr == 0)
										echo '<td><input type="text" '.(($editable==true) ? '': 'disabled').
												' class="form-control form-control-sm">
											</td>';?>
							<?php endforeach; ?>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<input type="tex" class="text-center form-control form-control-sm ifilter" placeholder="chercher">
	</div>
	<table id="ajout_note" class="table text-center"></table>
</div>
<h6 class="text-center text-primary"><?= $saisi[3];?></h6>
<div class="text-center text-info">
	<?php 
	if($editable == 'true')
		echo $this->Html->image('../img/Bell_icon-icons.com_75182.ico',
		array('width'=> 25, 'height'=> 25)).'Editer et quitter une cellule du tableau correspondante à une matière pour ajouter ou modifier une note.'; ?>
</div>
<!----Script------>
<script type="text/javascript">
	$(document).ready(function(){
		$('#table_saisi').arrowTable({
			enabledKeys: ['left', 'right', 'up', 'down'],
		  	listenTarget: 'input',
		  	focusTarget: 'input',
		  	namespace: 'arrowtable',
		});
	});
	$('#table_saisi tbody input').on('blur keyup', function(){

		var conduite = {A: 5, B: 10, C: 15};
  		var note = ($(this).val()).replace(',', '.').toUpperCase();
  		var col = $(this).closest('#table_saisi tr td').index();
  		var row = $(this).closest('#table_saisi tr').index();
  		var code_mat = $(this).closest( "#table_saisi" ).find( "thead tr:nth-child(2) th" ).eq(col-1).text();
  			code_mat = code_mat.trim();
  		var num_matricule = $(this).closest('#table_saisi tr').find('td:eq(1)').text();
  			num_matricule = num_matricule.trim();
  		var coeff = $(this).closest( "#table_saisi" ).find( "thead tr:nth-child(3) th" ).eq(col - 1).text();
  			coeff = coeff.trim();
  			note = (code_mat == 'MAT997') ? ((note in conduite) ? conduite[note] : note) : note;
		if($.isNumeric(note)) {
			note = ($.isNumeric(coeff)) ? parseFloat(note)/parseFloat(coeff) : note;
			if(note <= 20) {
			    $(this).css("background-color", "white");
				$.post('<?php echo $this->request->base; ?>/Notes/ajouter',
					{
						num_matricule: num_matricule,
						code_mat: code_mat,
						note_obtenue: note
					}, 
					function(data) {
						if(data!= 1)$('#ajout_note').html(data);
				});
			}
			else $(this).css("background-color", "yellow");

		}
		else $(this).css("background-color", "yellow");
	});
</script>

<?php $this->end(); ?>