<?php $this->start('gestion'); ?>
<div class="row">
	<div class="col-sm-1"></div>
	<div class="col-sm-3 jumbotron">
		<h5 class="text-center">OPTIONS</h5>
		<?= $this->Form->create('Note', ['type'=>'post', 'action'=>'index']) ;?>
			<table class="table table-sm small">
				<thead>
					<tr>
						<td colspan="2"></td>
					</tr>
				</thead>
					<?php foreach($options as $cle => $option):;?>
						<?php if($cle == 'Anneescolaire'):;?>
							<tr>
								<td colspan="2" class="text-center text-">Année scolaire actuelle : <?=$option;?></td>
								<td>
									<td>
										<?= $this->Form->input($cle,array(
											'type'=> 'hidden',
											'value' => $option
										))?>
									</td>
								</td>
							</tr>
						<?php else:;?>
						<tr>
							<td  width="30%"><?=$cle;?></td>
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
							<?= $this->Form->submit('Consulter', ['class'=>'btn btn-sm btn-info form-control']); ?>
						</td>
					</tr>
			</table>
		<?= $this->Form->end(); ?>
	</div>
	<div class="col">
		<div class="bg-success font-italic text-center text-warning">
			<?php echo $liste_promos[0] ;?>
		</div>
		<div style="max-height: 60vh; overflow: auto;">
			<table class="table table-sm small table-bordered tfilter">
				<thead class="text-light text-center">
					<tr>
						<?php echo $liste_promos[1]; ?>
					</tr>
					<tr id="ajout_note"></tr>
				</thead>
				<tbody>
				<?php if($liste_promos[6] == 1):;?>
					<?php foreach ($liste_promos[2] as $num => $promos): ;?>
						<tr>
							<td class="text-center"><a href="#" class="sup_note">supprimer</a></td>
							<td class="text-center"><?= $num; ?></td>
							<td><?php echo $promos[0] ;?></td>
							<td class="text-center"><?php echo $promos[1] ;?></td>
								<!--On cherche la valeur par matière-->
							<?php $nbr = 0; ?>
							<?php foreach ($liste_promos[5] as $note): ;?>
								<?php if($note['Note']['num_matricule']==$num && $note['Note']['code_mat'] ==$liste_promos[4]): ;?>
									<?php $nbr = 1; ?>
									<td>
										<input type="text" class="form-control form-control-sm text-center"
										 value="<?= $note['Note']['note_obtenue'];?>"
										 <?=($liste_promos[3]==true) ? '': 'disabled';?>
										>
									</td>
									<?php break; ?>
								<?php endif; ?>
							<?php endforeach; ?>
							<?php if($nbr == 0)
								echo '<td><input type="text" '.(($liste_promos[3]==true) ? '': 'disabled').
										' class="form-control form-control-sm text-center">
									</td>';
							?>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
				</tbody>
			</table>
		</div>
		<table class="table table-sm">
			<tbody>
				<tr>
					<td>
						<input type="text" class="form-control form-control-sm text-center ifilter" 
							placeholder="filtrer le tableau" autocomplete="off">	
					</td>
				</tr>
				<tr>
					<td class="text-center text-info">
						<a href="<?php echo $this->request->base; ?>/Notes/saisir">
							Cliquer ici pour faire la saisie globale des notes...
						</a>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
<!----Script------>
<script type="text/javascript">
	$(document).ready(function(){
		$('.tfilter').arrowTable({
			enabledKeys: ['up', 'down'],
		  	listenTarget: 'input',
		  	focusTarget: 'input',
		  	namespace: 'arrowtable',
		});
	});
	//var nbr_col = $('.tfilter thead tr th').length;
	$('.tfilter tbody input').on('blur keyup', function(){
		//var conduite = {A: 'A', B: 'B', C: 'C'};
		var note = ($(this).val()).replace(',', '.').toUpperCase();
		var x =  $(this);
		note = $.isNumeric(note) ? parseFloat(note) : note;
			$.post('<?php echo $this->request->base; ?>/Notes/mettre_jour',
				{
					num_matricule: x.closest('tr').find('td:eq(1)').text().trim(),
					note_obtenue: note
				}, 
				function(data) {
					if(data != 1){x.css("background-color", "yellow");}
					else x.css("background-color", "white");
			});
	});

	//Suppression
	$('.tfilter tbody tr').on('click', '.sup_note', function(event){
		event.preventDefault();
		var x = $(this);
		if('<?= $liste_promos[6];?>' == true) {
			if('<?=AuthComponent::user('username');?>' == 'admin') {
				$.post('<?php echo $this->request->base; ?>/Notes/supprimer',
						{
							num_matricule: x.closest('tr').find('td:eq(1)').text().trim()
						}, 
						function(data) {
							if(data == 1)x.closest('tr').find('input[type="text"]').val("");
							else alert('Echec! Réessayer!');
					});
			}
			else alert('Cette opération est réservée à l\'administrateur.');
		}
		else alert('Seule une note de l\'année scolaire la plus recente est modifiable et supprimable!');
	});
</script>

<?php $this->end(); ?>