<?php $this->start('gestion'); ?>
	<div style="margin-left: 8vw;">
	<div class="row">
		<div id="tabs" class="col-sm-3">
			<ul>
				<li><a href="#saisi" class="small">Entrer un élève</a></li>
				<li><a href="#import" class="small">Importer un fichier</a></li>
			</ul>
			<div id="saisi">
				<?= $this->Form->create("Promotion", ['action'=>'index','type'=>'post']); ?>
				<table cellpadding="4px" class="table table-sm small">
					<thead>
						<tr>
							<th colspan="3" class="bg-secondary">
							<h6 class="text-center text-light">Nouvel élève</h6>
							</th>
						</tr>
						<tr><th colspan="3"><?php if(isset($msg_ajout))echo $msg_ajout;?></th></tr>
					</thead>
					<tbody>
						<tr>
							<td colspan="2" class="text-center text-info">
								<?php foreach($selects[1] as $scolaire): ;?>
									<?= $this->Form->input('des_annee_scolaire', ['type'=>'hidden',
									'required'=>true, 'value'=>$scolaire]); ?>
									<?php echo 'Année scolaire actuelle : '.$scolaire ;break; ?>
								<?php endforeach; ?>
							</td>	
						</tr>
						<tr>
							<td>N°Matricule:</td>
							<td>
								<?= $this->Form->input('num_matricule', ['type'=>'number', 'min'=>'0', 'class'=>'form-control form-control-sm', 'label'=>false]); ?>
							</td>
						</tr>
						<tr>
							<td>Sexe:</td>
							<td>
					            <?=$this->Form->input('sexe', array(
					                  'type' => 'select',
					                      'options' => array("G"=>"Masculin [Garçon]", "F" => "Féminin [Fille]"),
					                       'label'=>false, 
					                      'class'=>'form-control form-control-sm'
					            ));?>
							</td>
						</tr>
						<tr>
							<td>Classe:</td>
							<td>
				                <?=$this->Form->input('classe', array(
				                  'type' => 'select',
				                      'options' => $selects[0],
				                       'label'=>false, 
				                      'class'=>'form-control form-control-sm recherche_select'
				                   ));?> 
							</td>
						</tr>
						<tr>
							<td colspan="3">
								<?= $this->Form->submit('Enregistrer',['class' => 'form-control form-control-sm',]); ?>
							</td>
						</tr>
					</tbody>
				</table>
				<?= $this->Form->end(); ?>
				<!-----####################################################--->
			</div>
			<div id="import">
				<div class="d-flex justify-content-center">
				<?= $this->Form->create("Promotion", 
					['type'=>'post', 'enctype'=>'multipart/form-data', 'id'=>'importer_excel']); ?>
					<table cellpadding="5px" class="table-sm table small">
						<tbody>
							<!--<tr>
								<td>
					                <?=$this->Form->input('des_classe', array(
					                  'type' => 'select',
					                  	'required'=>false,
					                    'options' => $selects[0],
					                    'label'=>false, 
					                    'class'=>'form-control form-control-sm recherche_select'
					                   ));?> 
								</td>
								<td>
					                <?= $this->Form->input('code_section', array(
					                    'type' => 'select',
					                  	'required'=>false, 
					                    ' options' => $selects[1], 
					                    'label'=>false, 
					                    'class'=>'form-control form-control-sm recherche_select'
					                   ));?> 
								</td>
							</tr>-->
							<tr>
								<td colspan="2" class="text-center text-info">
									<?php foreach($selects[1] as $scolaire): ;?>
										<?= $this->Form->input('des_annee_scolaire', ['type'=>'hidden',
										'required'=>true, 'value'=>$scolaire]); ?>
										<?php echo 'Année scolaire actuelle : '.$scolaire ;break; ?>
									<?php endforeach; ?>
								</td>	
							</tr>
							<tr>
								<th colspan="2" class="text-center">Choisir un fichier à importer:</th>
							</tr>
							<tr>
								<td colspan="2">
									<?= $this->Form->input('excel_file', 
									[
										'type'=>'file',
										'label'=>false,
										'required'=> true,
										'id'=>'inputGroupFile01',
										'accept'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel'
									]) ;?>
							    </td>
							</tr>
						</tbody>
						<tfoot>
							<tr>
								<td colspan="2">
									<?= $this->Form->submit('Accorder',
									 ['class'=>'form-control btn-outline-info text-dark']); ?>
								</td>
							</tr>
						</tfoot>
					</table>		
				<?= $this->Form->end(); ?>
				</div>
				<table class="table table-sm table-bordered small">
					<tbody id="total_export"></tbody>
				</table>
			</div>
			<!---######################################################--->
		</div>
		<!--Liste-->
		<span class="col">
			<div style="max-height: 65vh;" class="overflow-auto">
				<table class="table-bordered table table-sm small tfilter">
					<thead>
						<!--<tr class="text-center">
							<td class="bg-danger">Année scolaire</td>
							<td class="bg-warning">Classe</td>
							<td class="bg-primary">Section</td>
							<td class="bg-primary">Validation</td>
							<td class="bg-secondary">Filtrage</td>
						</tr>-->
						<tr class="bg-light">
						<?= $this->Form->create('Promotion', ['action'=>'lister','type'=>'post']) ;?>
							<th>
								<?= $this->Form->input('annee_scolaire2', array(
				                    'type' => 'select',
				                    'required' => true,
				                    'options' => $selects[1], 
				                    'label'=>false, 
				                     'class'=>'form-control form-control-sm recherche_select'
				                   ));?> 
						    </th>
						    <th>
				                <?= $this->Form->input('classe', array(
				                    'type' => 'select',
				                  	'required' => true, 
				                    'options' => $selects[0], 
				                    'label'=>false, 
				                     'class'=>'form-control form-control-sm recherche_select'
				                   ));?>
						    </th>
						    <!--<th>
				                <?= $this->Form->input('code_section', array(
				                    'type' => 'select',
				                  	'required' => false,
				                    'options' => $selects[1], 
				                    'label'=>false, 
				                     'class'=>'form-control-sm form-control recherche_select'
				                   ));?> 
							</th>-->
							<th>
								<?= $this->Form->submit('Lister', ['class'=>'form-control-sm form-control btn-outline-secondary']); ?>
							</th>
							<th colspan="2">
								<?=$this->Form->inut('search', [
									'label'=>false,
									'required'=>false,
									'class'=>'form-control-sm form-control ifilter',
									'placeholder'=>"filtrer le tableau",
									'autocomplete'=>"off"
								])
								;?>
							</th>
							<?= $this->Form->end(); ?>
						</tr>
						<tr>
							<th class="bg-success text-center">Cocher</th>
							<th class="bg-warning text-center">N°Matricule</th>
							<th class="bg-primary text-center" colspan="2">Nom Complet</th>
							<th class="bg-secondary text-center">
							<?php echo $this->Html->link(
								$this->Html->image('../img/sorter.ico',array('width'=> 15, 'height'=> 15, 'type' => 'icon', 'data-toggle'=>'tooltip','title'=>'Trier de A à Z')),
								array( 'action' => 'trier', '1'),array('class'=>'bg-light','escape' => false),__('Le triage va réarranger les numéros suivant l\'ordre alphabétique des noms des élèves. Accepté?')); ?>
							Numéro d'appel<?php echo $this->Html->image('../img/edit-class-.ico',
								array('width'=> 20, 'height'=> 20)); ?>
							</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($page_recherche[0] as $resultat): ;?>
							<tr>
								<td class="text-center">
									<input type="checkbox" name="selected[]" 
									value="<?=$resultat['Promotion']['num_matricule'].'|&|'.$resultat['Promotion']['des_classe'].'|&|'.$resultat['Promotion']['code_section'].'|&|'.$resultat['Promotion']['des_annee_scolaire']?>">
								</td>
								<td class="text-center">
									<?=preg_replace('/\D/', '', $resultat['Promotion']['num_matricule']).'/'.
									   preg_replace('/\d/', '', $resultat['Promotion']['num_matricule']);
									?>
								</td>
								<td colspan="2">
									<?=$resultat['Eleve']['nom']?>
									<span class="badge bg-primary text-light">
										<?=$page_recherche[3][$resultat['Promotion']['num_matricule']]?>
									</span>
								</td>
								<td contenteditable="true" class="text-center">
									<?=$resultat['Promotion']['num_appel']?>
								</td>
							</tr>
						<?php endforeach;?>
					</tbody>
					<tfoot>
						<tr>
							<td colspan="1">
								<a href="JavaScript:void(0);" id="checkDeleter">Transférer à</a>
							</td>
							<td colspan="3"><?= $this->element('paginateur'); ?></td>
							<td colspan="1"> 
								<?php echo $page_recherche[1]; ?>
							</td>
						</tr>					
					</tfoot>
				</table>
			</div>
			<!--footer tab-->
			<div class="d-flex justify-content-around">
				<label class="text-info">Les élèves de la classe <?php echo $page_recherche[2]; ?></label>
				<!--<a href="<?php echo $this->request->base; ?>/Promotions/expoterCollegue" data-toggle="tooltip"
					title="Télécharger">
					<h6 class="text-success text-center">
						EN EXCEL
						<img src="<?=$this->request->base; ?>/img/download.png" 
							height="20px" width="20px"
						style="cursor: pointer;">
					</h6>
				</a>-->
				<a  href="<?php echo $this->request->base; ?>/Promotions/listeAppel" data-toggle="tooltip"
					title="Télécharger la liste d'appel">
					<h6 class="text-success text-center">
						EN PDF
						<img src="<?=$this->request->base; ?>/img/liste_appel.png" 
							height="20px" width="20px"
						style="cursor: pointer;">
					</h6>
				</a>		
			</div>
			<div class="d-flex justify-content-around bg-secondary">
				<label class="text-info">N : Nouvel(le)</label>
				<label class="text-success">P : Passant(e)</label>
				<label class="text-warning">R : Redoublant(e)</label>
				<label class="text-danger">T : Triplant(e)</label>
			</div>
			<!--dialogue-->
			<div id="dialogue" title="Transfert">
				<?= $this->Form->create("Promotion", ['id'=>'transfert']); ?>
				<table cellpadding="4px" class="table table-sm small">
					<thead>
						<th colspan="2" class="text-center text-warning" id="nombre_coche"></th>
					</thead>
					<tbody>
						<tr>
							<td>Classe de destination:</td>
							<td>
				                <?=$this->Form->input('', array(
				                  'type' => 'select',
				                      'options' => $selects[0],
				                       'label'=>false, 
				                      'class'=>'form-control form-control-sm recherche_select',
				                      'id'=>'des_classe_nouvelle'
				                   ));?> 
							</td>
						</tr>
						<!--<tr>
							<td>Section:</td>
							<td>
				                <?=$this->Form->input('', array(
				                  'type' => 'select',
				                  'empty'=>'',
			                      'options' => $selects[1],
			                      'label'=>false, 
			                      'class'=>'form-control form-control-sm recherche_select',
			                      'id'=>'code_section_nouvelle'
				                   ));?> 
							</td>
						</tr>-->
						<tr class="bg-light text-dark text-center">
							<td>Année scolaire actuelle :</td>
							<?php foreach ($selects[1] as $scolaire): ?>
								<td id="annee_scolaire_n"><?php echo $scolaire; break;?></td>
							<?php endforeach ?>
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
		</span>
	</div>
</div>
<script type="text/javascript">
	$(document).ready(function(){
		//Transform to tabs
		$('#tabs').tabs();
		$('#controlgroup').controlgroup({direction:'horizontal'});
		$('#dialogue').dialog({autoOpen: false});
				//Mis à jour
		$('.tfilter tbody td').on('keypress', function(event){

			if(event.which == 13){
				var x = ($(this).closest('tr').find('input:checkbox').val()).split('|&|');
				//alert($(this).closest('tr').find('td:eq(1)').html());
				$.post("<?php echo $this->request->base; ?>/Promotions/mettre_jour",
					{
						num_matricule: x[0],
						des_classe: x[1],
						code_section: x[2],
						des_annee_scolaire: x[3],
						num_appel: ($(this).text()).replace(/[^\d]/g, "")
					}, 
					function(data){
						if(data != 1)alert(data);
						location.reload();
					});
				return false;
			}
		});
		//Transfert
		/*---------------*/
		var selected = [];
		$('#checkDeleter').click(function(){
			selected = [];
			$('[name="selected[]"]:checked').each(function() {
			selected.push(this.value);
			});

			if(selected.length > 0){
				$('#nombre_coche').html("Déplacer " + selected.length + " élève(s) vers...");
				$('#dialogue').dialog('open');
			}

		});
		//Transfert
		$('#transfert').on('submit', function(e){
			e.preventDefault();
			$.post('<?php echo $this->request->base; ?>/Promotions/transferer',
				{
					liste: selected,
					des_classe_nouvelle: $('#des_classe_nouvelle').val(),
				 }, 
				function(data){
					alert(data);
					location.reload();
			});
		});
		
	});
	//Importation 
		$('#importer_excel').on('submit', function(e){
			e.preventDefault();
			$('#total_export').html('Chargement...');
			$.ajax({
				url:"<?php echo $this->request->base; ?>/Promotions/importer",
				method:"POST",
				data: new FormData(this),
				contentType: false,
				processData: false,
				success: function(data) {
					$('#total_export').html(data);
					//setTimeout(function(){ location.reload(); }, 2000);
					 }
			});
		});
</script>
<?php $this->end(); ?>