<?php $this->start('gestion'); ?>
	<style type="text/css">
		.dimension {
			max-height: 80vh; 
			overflow-y: scroll;
		}
	</style>

	<div class="row" style="margin-left: 8vw; margin-top: 2vh">			
		<div class="bg-light col-sm-5">
			<div class="dimension">
				<table class="table-stripped table table-hover table-sm small">
					<thead>
						<tr>
							<th class="text-center" colspan="3"><h5>Les comptes des utilisateurs</th></h5>
						</tr>
						<tr>
							<th>Pseudonyme</th>
							<!--<th>Nom complet</th>-->
							<th colspan="2" class="text-center">options</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($actives_users[0] as $active_user): ?>
							<tr>
								<?php 
								echo "<td class='".$actives_users[1][rand(0,5)]."'>".$active_user["User"]["username"]."</td>"; ?>
								<?php /*echo "<td class='".$actives_users[1][rand(0,5)]."'>".strtoupper($active_user["User"]["nom"])."</td>"*/; ?>
								<?php echo "<td>" ;?>
								<?php echo $this->Html->link('supprimer', array( 'action' => 'liste_compte',
									$active_user["User"]["username"]),array( 'class'=>'btn btn-light', 'escape' => false),__('Etes-vous sur de vouloir supprimer  %s?',$active_user["User"]["username"])); ?>
								<?php echo "</td>"; ?>
								<?php echo "<td>" ;?>
									<a class="<?=$actives_users[1][rand(0,5)];?> text-decoration-none btn btn-light" 
									href="<?=$this->request->base; ?>/Users/liste_compte?username=<?=$active_user['User']['username'];?>&&action=<?= ($active_user["User"]["active"])? 0 : 1;?>">
									<?= ($active_user["User"]["active"])? 'Désactiver': 'Activer';?>
									</a>
								<?php echo "</td>"; ?>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>							
		</div>
		<div class="col">
			<div class="dimension">
				<table class="table table-bordered table-sm small" id="tRestaurer">
					<thead class="text-center">
						<tr>
							<th colspan="3" class="bg-success">Corbeille</th>
						</tr>
						<tr>
							<th>N°MATRICULE</th>
							<th colspan="2">NOM COMPLET</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($recherche as $eleve): ;?>
							<tr>
								<td class="text-center <?=$actives_users[1][rand(0,5)];?>">
									<?=$eleve['Student']['num_matricule']; ?>
								</td>
								<td class="<?=$actives_users[1][rand(0,5)];?>">
									<?=$eleve['Student']['nom']; ?>
								</td>
								<td>
									<a href="JavaScript:Void(0)" class="Restaurer text-decoration-none <?=$actives_users[1][rand(0,5)];?>">
										Restaurer
									</a>
								</td>					
							</tr>
						<?php endforeach ;?>
					</tbody>
					<tfoot>				
						<form method="POST" action="liste_compte">
							<tr>
								<td colspan="2">
									<input type="text" name="search" class="form-control form-control-sm" placeholder="taper quelque chose" autocomplete="off">
								</td>
								<td>
									<input type="submit" value="Rechercher" class="form-control form-control-sm bg-info">
								</td>
							</tr>
						</form>
						<tr>
							<td colspan="3"><?php echo $this->element('paginateur'); ?></td>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>
	</div>
	<script type="text/javascript">
		$('#tRestaurer tbody').on('click', '.Restaurer', function(){
			var x = $(this).closest('tr');
			$.post('<?php echo $this->request->base; ?>/Students/supprimer',
				{
					num_matricule: $(this).closest('tr').find('td:eq(0)').html().trim(),
					flague: 0

				}, function(data){
					if(data == 'true') x.remove();
					else alert('L\'opération a echouée!');
			});
		});
	</script>
<?php $this->end(); ?>