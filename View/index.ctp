<?php $this->start('gestion'); ?>
<div style="margin-left: 8vw;">
	<div class="row">
		<?= $this->Form->create("Student", ['class' => 'col-sm-3','action'=>'index','type'=>'post']); ?>
			<table cellpadding="4px" class="table table-sm small">
				<thead>
					<tr><th colspan="3" class="bg-secondary">
						<h6 class="text-center text-light">Nouvel élève</h6>
						</th>
					</tr>
					<tr><th colspan="3"><?php if(isset($msg_ajout))echo $msg_ajout;?></th></tr>
				</thead>
				<tbody>
					<tr>
						<td>N°Matricule:</td>
						<td>
							<?= $this->Form->input('num_matricule', ['type'=>'number', 'autocomplete'=>'off', 'class'=>'form-control form-control-sm', 'label'=>false, 'min'=>1]); ?>
						</td>
						<td width="100%">
							<?= $this->Form->input('sexe', ['type'=>'select', 'autocomplete'=>'off', 
								'options'=>array('G'=>'Garçon', 'F'=>'Fille'),
							'class'=>'form-control form-control-sm', 'label'=>false]); ?>
						</td>
					</tr>
					<tr>
						<td>Nom :</td>
						<td colspan="2">
							<?= $this->Form->input('nom', ['type'=>'text', 'autocomplete'=>'off', 'class'=>'form-control form-control-sm', 'label'=>false]); ?>
						</td>
					</tr>
					<tr>
						<td>Né le:</td>
						<td colspan="2">
							<?php echo $this->Form->input('date_nais', [
								'class' => 'form-control form-control-sm',
								'type' => 'text', 'label' => false, 'id' => 'date_nais'
							]); ?>
						</td>
					</tr>
					<tr>
						<td colspan="3">
							<?= $this->Form->submit('Enregistrer',['class' => 'form-control form-control-sm']); ?>
						</td>
					</tr>
				</tbody>
			</table>
		<?= $this->Form->end(); ?>
		<span class="col">
			<table class="table-bordered table table-sm small tfilter">
				<thead class="text-center">
					<tr>
						<th class="bg-info" id="add_collegue">
							<a href="JavaScript:Void(0)" class="text-dark text-decoration-none" 
							data-toggle="tooltip" title="ajouter à la classe...">
								<?php echo $this->Html->image('../img/Add.svg',
									array('width'=> 20, 'height'=> 20)); ?> à ?
							</a>
						</th>
						<th class="bg-warning">N°Matricule</th>
						<th class="bg-primary">
						Nom Complet <?php echo $this->Html->image('../img/edit-class-.ico',
							array('width'=> 20, 'height'=> 20)); ?>
						</th>
						<th class="bg-danger">
						sexe <?php echo $this->Html->image('../img/edit-class-.ico',
							array('width'=> 20, 'height'=> 20)); ?>
						</th>
						<th class="bg-danger">
						Date de naissance <?php echo $this->Html->image('../img/edit-class-.ico',
							array('width'=> 20, 'height'=> 20)); ?>
						</th>
						<th class="bg-success">Supprimer</th>
					</tr>
				</thead>
				<tbody>
				<?php
					if(isset($page_recherche)) {
							foreach ($page_recherche as $val) {
						echo '<tr>
							  <td class="text-center">
							  <input type="checkbox" 
							  		name="selected[]"
							  		value='.$val['Student']['num_matricule'].'>
							  </td>
							  <td class="text-center">'.preg_replace('/\D/', '', $val['Student']['num_matricule']).'</td>
							  <td contentEditable="true">'.$val['Student']['nom'].'</td>
							  <td contentEditable="true" class="text-center">'.$val['Student']['sexe'].'</td>
							  <td contentEditable="true" class="text-center">'.
							   ((!is_null($val['Student']['date_nais']) ? 
							   	date('d/m/Y', strtotime($val['Student']['date_nais'])): null))
							  .'</td>
							  <td class="text-center">
                                <img src="'.$this->request->base.'/img/save_delete.ico"
                                data-toggle="tooltip" title="Supprimer"
                                height="20px" width="20px" style=\'cursor: pointer;\' class=\'delete\'>
                   			  </td>
							</tr>';
							}
						}
					?>
				</tbody>
				<tfoot>
					<?= $this->Form->create('Student', 
						['action'=>'rechercher','type'=>'post']) ;?>
					<tr>
						<td colspan="4">
							<?= $this->Form->input('search', [
								'type'=>'text',
								'class'=>'form-control text-center form-control-sm',
								'placeholder'=>'taper quelque chose', 'label'=>false,
								'autocomplete'=>'off'
							]) ;?>
						</td>
						<td colspan="2">
							<?= $this->Form->submit('Rechercher',
							['class'=>'form-control form-control-sm btn-outline-secondary']); ?>
						</td>
					</tr>
					<?= $this->Form->end() ;?>
					<tr>
						<td colspan="4"><?php if(isset($page_recherche)) echo $this->element('paginateur'); ?></td>
						<td colspan="2">Total: <label><?= $total; ?></label></td>
					</tr>
					<tr>
						<td colspan="6">
							<a href="JavaScript:Void(0);" data-toggle="tooltip"
								title="Télécharger" id="telecharger">
								<h6 class="text-success text-center">
									Télécharger la liste des effectifs des élèves
									<img src="<?=$this->request->base; ?>/img/download.png" 
										height="20px" width="20px" 
									style="cursor: pointer;">
								</h6>
							</a>
						</td>
					</tr>				
				</tfoot>
			</table>
			<div id="dialogue" title="Liste des élèves">
				<?= $this->Form->create("Student", ['action'=>'telecharger', 'type'=>'post']); ?>
				<table cellpadding="4px" class="table table-sm small">
					<thead>
						<th colspan="2">
							Choisir l'année scolaire
						</th>
					</thead>
					<tbody>
						<tr>
							<td>Année scolaire:</td>
							<td>
				                <?=$this->Form->input('annee_scolaire', array(
				                  'type' => 'select',
				                      'options' => $selects[2],
				                       'label'=>false, 
				                      'class'=>'form-control form-control-sm recherche_select',
				                   ));?> 
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<?= $this->Form->submit('Télécharger',['class' => 'form-control form-control-sm btn-outline-info',]); ?>
							</td>
						</tr>
					</tbody>
				</table>
				<?= $this->Form->end(); ?>
			</div>
		</span>
	</div>

	<div>
		<form method="post" enctype="multipart/form-data" id="exporter_excel">
			<table cellpadding="5px">
				<tbody>
					<tr><td colspan="2" id="total_export"></td></tr>
					<tr>
						<td>
							<div class="input-group">
							  	<div class="input-group-prepend">
							    	<span class="input-group-text" id="inputGroupFileAddon01">Importer un fichier</span>
							 	 </div>
							  	<div class="custom-file">
							    	<input type="file" class="custom-file-input" id="inputGroupFile01"
							     		 aria-describedby="inputGroupFileAddon01" name="excel_file[]" 
							     		 accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" multiple required>
							    	<label class="custom-file-label" for="inputGroupFile01">Choisir</label>
							 	</div>
							</div>						
						</td>					
						<td>
							<input type="submit" class="form-control btn-outline-warning text-info" 
								value="Importer"/>
						</td>
					</tr>
				</tbody>
			</table>		
		</form>
	</div></br>
	<div class="text-danger text-center">
		<p>
			<i>Les numéros supprimés sont restaurables par l'administrateur.
			Ces numéros ne pourront plus être reutilisés.</i>
		</p>	
	</div>
</div>
<div id="dialogue2" title="Transfert">
	<?= $this->Form->create("Student", ['id'=>'transfert']); ?>
	<table cellpadding="4px" class="table table-sm small">
		<thead>
			<th colspan="2" class="text-center text-warning" id="nombre_coche"></th>
		</thead>
		<tbody>
			<tr>
				<td>Classe:</td>
				<td>
	                <?=$this->Form->input('', array(
	                  'type' => 'select',
	                      'options' => $selects[0],
	                       'label'=>false, 
	                      'class'=>'form-control form-control-sm recherche_select',
	                      'id'=>'des_classe_n'
	                   ));?> 
				</td>
			</tr>
			<tr>
				<td>Section:</td>
				<td>
	                <?=$this->Form->input('', array(
	                  'type' => 'select',
	                  'empty'=>'',
                      'options' => $selects[1],
                      'label'=>false, 
                      'class'=>'form-control form-control-sm recherche_select',
                      'id'=>'code_section_n'
	                   ));?> 
				</td>
			</tr>
			<tr>
				<td>Année scolaire:</td>
				<td>
	                <?=$this->Form->input('', array(
	                  'type' => 'text',
	                  'empty' => false,
                      'value' => $selects[2]['annee_scolaire'],
                      'label'=>false,
                      'disabled' => true,
                      'class'=>'form-control form-control-sm text-center',
                      'id'=>'annee_scolaire_n'
	                   ));?> 
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<?= $this->Form->submit('Valider',['class' => 'form-control form-control-sm btn-outline-info',]); ?>
				</td>
			</tr>
		</tbody>
	</table>
	<?= $this->Form->end(); ?>
</div>
<script type="text/javascript">
	$(document).ready(function(){
		//Transform input to date
		$('#date_nais').attr('type', 'date');
		$('#date_nais').attr('value', '<?php echo date('Y-m-d');?>');
		$('#dialogue').dialog({autoOpen: false});
		$('#dialogue2').dialog({autoOpen: false});
				//Mis à jour
		$('.tfilter tbody td').on('keypress', function(event){
			var col = 'nom';
			switch($(this).index()) {
				case 3: col = 'sexe';break;
				case 4: col = 'date_nais'; break;
				default: break;
			}
			if(event.which == 13){

				$.post("<?php echo $this->request->base; ?>/Students/mettre_jour",
					{
						num_matricule: $(this).closest('tr').find('input:checkbox').val().trim(),
						champs: col,
						val: $(this).html().trim().replace('<br>','').replace(/&nbsp;/g, '')
					}, 
					function(data){
						if(data != 1)alert(data);
						location.reload();
					});
				return false;
			}
		});
		//Suppression
		$('.tfilter tbody').on('click', '.text-center', function(){
			if('<?=AuthComponent::user('username');?>' == 'admin') {
				$.post('<?php echo $this->request->base; ?>/Students/supprimer',
					{
						num_matricule: $(this).closest('tr').find('input:checkbox').val().trim(),
						flague: 1

					}, function(data){
						if(data != 'true')alert('L\'opération a echouée!');
						window.location.replace('<?php echo $this->request->base; ?>/Students/index');
					});
			}
			else alert('Cette opération est réservée à l\'administrateur.');
		});

	});
	//Exportation 
	$('#exporter_excel').on('submit', function(e){
		e.preventDefault();
		$('#total_export').html('<div class="bg-danger text-center text-light">Veuillez patienter...</div>');
		$.ajax({
			url:"<?php echo $this->request->base; ?>/Students/importer",
			method:"POST",
			data: new FormData(this),
			contentType: false,
			processData: false,
			success: function(data) {
				$('#total_export').html('');
				alert(data);
				 },
		});
	});
	//ocuments scolaires
	$('#telecharger').click(function(){
		$('#dialogue').dialog('open');
	})
	//.................
		/*---------------*/
	$('#add_collegue').click(function(){
		var selected = [];
		$('[name="selected[]"]:checked').each(function() {
		selected.push(this.value);
		});

		if(selected.length > 0){
			$('#nombre_coche').html("Ajouter " + selected.length + " élève(s) à...");
			$('#dialogue2').dialog('open');
		}
		else alert('Cocher au moins une case...');
	/*---------------*/
		$('#transfert').on('submit', function(e){
			e.preventDefault();
			$.post('<?php echo $this->request->base; ?>/Students/ajout_collegue',
				{
					selected: selected,
					des_classe: $('#des_classe_n').val(),
					code_section: $('#code_section_n').val(),
					annee_scolaire: $('#annee_scolaire_n').val()

				 }, 
				function(data){
					alert(data);
					location.reload();
			});
		});
	/*---------------*/

	});
</script>
<?php $this->end(); ?>