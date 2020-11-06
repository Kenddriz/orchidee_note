<?php $this->start('gestion'); ?>
	<div class="navbar"></div>
	<div class="row">
		<div class="col-sm-1"></div>
		<div class="col-sm-3">
			<div>
				<?= $this->Form->create("Coefficient", ['type'=>'post','action'=>'index']); ?>
				<table class="table table-sm">
					<thead>
						<tr class="bg-secondary">
							<td class="text-center text-light" colspan="2">Nouveau coefficient</td>
						</tr>
						<tr>
							<td colspan='2' class='text-center text-danger bg-light'>
								<?php if(isset($ajout_msg)) echo $ajout_msg;?>
							</td>
						</tr>
					</thead>
					<tbody class="jumbotron">
						<tr>
							<td>Matière:</td>
							<td colspan="2">
								<?= $this->Form->input('code_mat',['type'=>'select','class'=>'form-control form-control-sm recherche_select',
				      		 		'label'=> false, 'options'=> $coef_params[0]]);?>	
							</td>
						</tr>
						<tr>
							<td>Classe:</td>
							<td colspan="2">
								<?= $this->Form->input('des_classe', ['type'=>'select', 'class'=>'form-control form-control-sm recherche_select',
				      		 		'label'=> false, 'options'=> $coef_params[1]])
				      		 	;?>	
							</td>
						</tr>
						<tr>
							<td>Valeur:</td>
							<td>
								<?= $this->Form->input('valeur', ['type'=>'number', 'class'=>'form-control form-control-sm','step'=>'0.5','max'=>5,
				      		 		'label'=> false, 'min' => 0])
				      		 	;?>	
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<?= $this->Form->submit('Enregistrer',
								 ['class'=>'form-control form-control-sm btn-outline-success']); ?>	
							</td>
						</tr>
					</tbody>
					<tfoot>
						<tr>
							<td colspan="2" class="text-center bg-secondary text-warning">
								Laisser vide la valeur si la matière sélectionnée ne présente pas de coefficient.
							</td>
						</tr>
					</tfoot>
				</table>
				<?= $this->Form->end(); ?>		
			</div>
		</div>
		<div class="col-sm-1"></div>
		<div class="col">
			<h6 class="text-center text-secondary">Tableau de coefficients</h6>
			<table class="table-sm table small tfilter"><!-- tfilter-->
				<thead>
					<?= $this->Form->create("Coefficient", ['type'=>'post','action'=>'index']); ?>
					<tr>
						<td colspan="4">
							<?= $this->Form->input('search', ['type'=>'text', 'class'=>'form-control form-control-sm','label'=> false, 'placeholder' => 'Recherche par classe ou par code d\'une matière',
								'autocomplete' => 'off'
							])
			      		 	;?>	
						</td>
						<td colspan="2">
							<?= $this->Form->submit('Rechercher',
							 ['class'=>'form-control form-control-sm btn-outline-success']); ?>
						</td>
					</tr>
					<?= $this->Form->end(); ?>	
					<tr class="jumbotron">
						<th>Code Matière</th>
						<th>Désignation</th>
						<th>Classe</th>
						<th>Valeur<?php echo $this->Html->image('../img/edit-class-.ico',
								array('width'=> 20, 'height'=> 20)); ?>
						</th>
						<th>Action</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($coef_params[2] as $params): ;?>
						<tr>
							<td><?= $params['Matiere']['code_mat']?></td>
							<td><?= $params['Matiere']['libelle_mat']?></td>
							<td><?= $params['Coefficient']['des_classe']; ?></td>
							<td contenteditable="true"><?= $params['Coefficient']['valeur']?></td>
							<td>
								<button class="btn btn-outline-secondary btn-sm delete">supprimer</button>
							</td>
						</tr>
					<?php endforeach ;?>
				</tbody>
				<tfoot>
					<tr>
						<td colspan="6">
							<?= $this->element('paginateur'); ?>
						</td>
					</tr>
					<tr>
						<td colspan="6" class="jumbotron text-center text-danger">
							Cliquer sur votre touche entrée pour enregistrer une modification.
						</td>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
	<script type="text/javascript">
		$(document).ready(function(){
			//suppression
		$('.tfilter tbody').on('click', '.delete', function(){
			var donnees  = {
				code_mat: $(this).closest('tr').find("td").eq(0).text(),
				des_classe: $(this).closest('tr').find("td").eq(2).text(),
				code_section: $(this).closest('tr').find("td").eq(3).text()
			}
			$.post('<?php echo $this->request->base; ?>/Coefficients/supprimer',
				{
					data: donnees
				}, function(response){
					if(response != 'true')alert(response);
					window.location.replace('<?php echo $this->request->base; ?>/Coefficients/index');
				});
		});
		//Mis à jour
		$('.tfilter tbody td').on('keypress', function(event){
			if(event.which == 13){
				var nombre = $(this).text().trim();
					nombre = (!$.isNumeric(nombre)) ? 0 : nombre;
				var val = parseFloat(nombre) ;
				//alert(val >= 0);
				if(val >= 0 && val <= 5) {
					//alert(val >= 0);
					$.post("<?php echo $this->request->base; ?>/Coefficients/mettre_jour",
						{
							code_mat: $(this).closest('tr').find('td:eq(0)').text(),
							des_classe: $(this).closest('tr').find('td:eq(2)').text(),
							code_section: $(this).closest('tr').find('td:eq(3)').text(),
							valeur: nombre
						}, 
						function(data){
							if(data != 1)alert(data);
							location.reload();
					});
				}
				else alert('Saisir un nombre entre 0 à 5 ou laisser la cellule vide');
				return false;
			}
		});
	});
	</script>
<?php $this->end(); ?>	