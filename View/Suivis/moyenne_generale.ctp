<?php $admis = 0; $redoublant = 0; $coeff_total = 0; $tab = array('MAT998'=>'','MAT999'=>''); $tot_execo = 0;?>
<table class="table table-sm small table-bordered tfilter" id="table_mg">
	<thead>
		<tr>
			<th style="visibility: hidden;" colspan="2"></th>
			<?php foreach ($bulletin[0] as $matiere): ;?>
				<th class="small bg-light" data-toggle="tooltip" title="<?=$matiere['Matiere']['libelle_mat'];?>">
					<?php echo strtoupper(substr($matiere['Matiere']['libelle_mat'], 0, 3)); ?>
				</th>
			<?php endforeach; ?>
		</tr>
		<tr class="bg-secondary text-light">
			<th class="small bg-info">Cocher
				<?php echo $this->Html->image('../img/Tatice-Cristal-Intense-Fleche-bas-rouge.ico',
					array('width'=> 10, 'height'=> 10)); ?>
			</th>
			<th class="small">Coefficient</th>
			<?php foreach ($bulletin[0] as $matiere): ;?>
				<th class="small">
					<?php 
					echo $matiere['Coefficient']['valeur'];
					$coeff_total += $matiere['Coefficient']['valeur'];
					 ?>
				</th>
			<?php endforeach; ?>
			<th class="small">Total</th>
			<th class="small">Moyenne</th>
			<th class="small">Rang</th>
		</tr>
	</thead>
	<tbody>
		<?php if (!empty($bulletin[2])): ?>
			<?php $rang_eleve = 0; $execo=-200; $T = 0; $ligne = 1; ?>
			<?php foreach ($bulletin[1] as $num => $moyenne): ;?>
				<?php $nbr = 0; $total = 0; ?>
			<tr>
				<td class="small">
					<input type="checkbox" name="selected[]" 
					value="<?=$num.'/'.$ligne++;?>" class="check_control">
				</td>
				<td><?php echo $bulletin[2][$num][0]; ?></td>
				<!---Parcourir les notes par matière-->
				<?php foreach ($bulletin[0] as $matiere): ;?>
					<?php $nbr = 0;
						$coef = (!is_null($matiere['Coefficient']['valeur'])) ? $matiere['Coefficient']['valeur'] : 0;
					 ?>
					<?php foreach ($bulletin[3] as $note): ;?>
						<?php if ($note['notes']['num_matricule']==$num && $note['notes']['code_mat'] == $matiere['Matiere']['code_mat']): ?>
							<td class="small">
								<?php 
									if($coef == 0)echo $note[0]['note_obt'];
									else {
										if($note[0]['note_obt']/$coef < 10)
											echo "<b class='text-danger'>".$note[0]['note_obt']."</b>";//Mettre en gras
										else echo $note[0]['note_obt'];
									}
								?>
							</td>
							<?php 
								$nbr++; 
								//si numérique et qu'il s'agit vraies notes;
								$autorise = is_numeric($note[0]['note_obt']) && !array_key_exists($matiere['Matiere']['code_mat'], $tab) ;
								$total+=($autorise) ? $note[0]['note_obt'] : 0; 
								$T+=($autorise) ? $note[0]['note_obt']: 0;
							?>
						<?php endif; ?>
					<?php endforeach; ?>
				<?php if($nbr == 0)echo '<td class="text-warning">vide</td>';?>
				<?php endforeach; ?>

				<td class="small bg-light"><?php echo number_format($total, 2) ;?></td>
				<td class="small">
					<?php 
					$moyenne >= 10 ? $admis++ : $redoublant++;
					$moyenne = number_format($moyenne, 2);
					if($moyenne >= 10)echo '<b class="text-success">'.$moyenne.'</b>';
					else echo '<b class="text-danger">'.$moyenne.'</b>';
					?>
				</td>
				<td class="small">
						<?php 
							// s'il y a des execos
							if($execo  == $moyenne) $tot_execo++;
							else {$rang_eleve += $tot_execo+1; $execo=$moyenne ; $tot_execo = 0;}
							echo $rang_eleve; unset($bulletin[2][$num]);
						 ?>
				</td>
			</tr>
			<?php endforeach; ?>
						<!--Les élèves restants-->
			<?php foreach($bulletin[2] as $num => $eleve): ?>
				<tr class="small text-danger">
					<td>
						<input type="checkbox" name="selected[]" 
						value="<?=$num.'/'.$ligne++;?>" class="check_control">
					</td>
					<td><?=$eleve[0];?></td>
					<?php for($x = 0; $x < sizeof($bulletin[0]); $x++) echo "<td>0</td>";?>
					<td>0</td>
					<td>0</td>
					<td></td>
				</tr>
			<?php endforeach;?>
			<tr>
				<td class="small bg-secondary text-light" colspan="2">Note de classe</td>
				<?php foreach ($bulletin[0] as $matiere): ;?>
					<?php $nbr = 0; ?>
					<?php foreach ($bulletin[4] as $key => $m_classe): ;?>
						<?php if ($matiere['Matiere']['code_mat']==$key): ?>
							<td class="small bg-secondary text-light">
								<?php echo is_numeric($m_classe)? number_format($m_classe, 2) : $m_classe; $nbr++;?>
							</td>
						<?php endif; ?>
					<?php endforeach; ?>
					<?php if($nbr == 0)echo '<td class="text-warning">vide</td>';?>
				<?php endforeach; ?>
				<td class="small bg-light"><?=number_format($T, 2); ?></td>
				<td class="small bg-info text-light text-center">
				<?php
				unset($bulletin[4]['MAT998']); unset($bulletin[4]['MAT999']);
				if(sizeof($bulletin[1]) > 0)echo number_format(
					array_sum($bulletin[1])/sizeof($bulletin[1]), 2);
				?>
				</td>
			</tr>
		<?php endif; ?>
	</tbody>
</table>
<!--Répartition -->
<div class="d-flex justify-content-around small">
	<button class="btn-outline-info" id="repartir">
		Répartition des élèves choisis
	</button>
	<button class="btn-info badge">
	  <span class="badge bg-success text-light"><?=$admis;?> admis</span>
	  <span class="badge bg-danger text-light"><?=$redoublant;?> redoublant(s)</span>
	</button>
	<a href="<?php echo $this->request->base; ?>/Notes/exporter_generale" data-toggle="tooltip" 
		title="Télécharger" class="text-success text-center">
			<?php echo $bulletin[5]; ?>
			<img src="<?=$this->request->base; ?>/img/download.png" height="20px" width="20px" 
			style="cursor: pointer;">
	</a>
	<div id="dialogue" title="Répartition">
		<?= $this->Form->create("Promotion", ['id'=>'transfert']); ?>
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
		                  'empty'=> false,
	                      'options' => $selects[0],
	                      'label'=>false, 
	                      'class'=>'form-control form-control-sm recherche_select',
	                      'id'=>'des_classe10'
		                   ));?> 
					</td>
				</tr>
				<tr class="bg-light text-dark text-center">
					<td>Nouvelle année scolaire :</td>
					<?php foreach ($selects[1] as $scolaire): ?>
						<td id="annee_scolaire10"><?php echo $scolaire; break;?></td>
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
</div>
<script type="text/javascript">
	$(document).ready(function(){
		$('#dialogue').dialog({autoOpen: false});
			//Multiselect
		$('.recherche_select').on("mouseover mouseout", function(event){
	        switch(event.type){
	          case 'mouseover': $(this).attr('size', 3); break;
	          default: $(this).attr('size', 1); break;
     	 }
 	   });
	});

		//Cochage et decochage
	var moyenne_index = 0;
	$('#table_mg tbody').on('click', '.check_control', function () {
		var cell_length = $(this).closest('tr').find('td').length;
		moyenne_index = $(this).closest('tr').find('td').eq(cell_length - 2).text().trim();
		moyenne_index = Number(moyenne_index.replace(/[^\d]/g, ""));
		moyenne_index /= 100;

		$('[name="selected[]"]:checked').each(function() {
			var val = this.value.split('/');
			var moyenne = $('#table_mg tbody').find( "tr:nth-child("+val[1]+") td" ).eq(cell_length - 2).text().trim();
				moyenne = Number(moyenne.replace(/[^\d]/g, ''));
				moyenne /= 100;
				//Les rédoublants ne s'en vont pas avec les admis
				if((moyenne_index >= 10 && moyenne < 10) || (moyenne_index < 10 && moyenne >= 10))
					$(this).removeAttr('checked');
		});

	});
	//Répartition
	var selected = [];
	$('#repartir').click(function(){
		selected = [];
		$('[name="selected[]"]:checked').each(function() {
			var val = this.value.split('/');
			selected.push(val[0]);
		});

		if(selected.length > 0){
			$('#nombre_coche').html(selected.length + " élève(s) "+ 
				(moyenne_index >= 10 ? 'admis en ...' : 'remis en ...'));
			$('#dialogue').dialog('open');
		}
		else alert('Veuillez choisir au moins un élève...');
	});
	/*---------------*/
	$('#transfert').on('submit', function(e){
		e.preventDefault();
		var dat = {
				liste: selected,
				classe10: $('#des_classe10').val(),
				annee_scolaire: $('#annee_scolaire10').text().trim(),
				moyenne_index: moyenne_index
			 };
		$.post('<?php echo $this->request->base; ?>/Promotions/repartir', dat,
 
			function(data){
				$('#dialogue').dialog('close');
				alert(data);
				$('input:checkbox').removeAttr('checked');
		});
	});
</script>