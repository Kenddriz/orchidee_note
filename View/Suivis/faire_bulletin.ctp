<?php $success = 0; $fail = 0; $coeff_total = 0; $tab = array('MAT998'=>'','MAT999'=>'');?>
<table class="table table-sm small table-bordered tfilter">
	<thead>
		<tr>
			<th style="visibility: hidden;"></th>
			<?php foreach ($bulletin[0] as $matiere): ;?>
				<th class="small bg-light" data-toggle="tooltip" title="<?=$matiere['Matiere']['libelle_mat'];?>">
					<?php echo strtoupper(substr($matiere['Matiere']['libelle_mat'], 0, 3)) ?>
				</th>
			<?php endforeach; ?>
		</tr>
		<tr class="bg-secondary text-light">
			<th class="small">Coefficient</th>
			<?php foreach ($bulletin[0] as $matiere): ;?>
				<th class="small">
					<?php 
					echo $matiere['Coefficient']['valeur'];
					$coeff_total+=$matiere['Coefficient']['valeur'];
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
			<?php $rang_eleve = 0; $execo=-200; $T = 0; $success = 0; $fail = 0; $tot_execo = 0; ?>
			<?php foreach ($bulletin[1] as $num =>$moyenne): ;?>
				<?php $nbr = 0; $total=0; ?>
				<tr>
					<td><?php echo $bulletin[2][$num][0]; ?></td>
					<!---Parcourir les notes par matière-->
					<?php foreach ($bulletin[0] as $matiere): ;?>
					<?php $nbr = 0; 
						$coef = (!is_null($matiere['Coefficient']['valeur'])) ? $matiere['Coefficient']['valeur'] : 0;
					?>
						<?php foreach ($bulletin[3] as $note): ;?>
							<?php if ($note['Note']['num_matricule']==$num && $note['Note']['code_mat'] == $matiere['Matiere']['code_mat']): ?>
								<td class="small">
								<?php 
									if($coef == 0)echo $note['Note']['note_obtenue'];
									else {
										if($note['Note']['note_obtenue']/$coef < 10)
											echo "<b class='text-danger'>".$note['Note']['note_obtenue']."</b>";//Mettre en gras
										else echo $note['Note']['note_obtenue'];
									}
								?>		
								</td>
								<?php 
								$nbr++; 
								//si numérique et qu'il s'agit vraies notes;
								$autorise = is_numeric($note['Note']['note_obtenue']) && !array_key_exists($matiere['Matiere']['code_mat'], $tab) ;
								$total+=($autorise) ? $note['Note']['note_obtenue'] : 0; 
								$T+=($autorise) ? $note['Note']['note_obtenue'] : 0; 
								?>
							<?php endif; ?>
						<?php endforeach; ?>
					<?php if($nbr == 0)echo '<td class="text-warning">vide</td>';?>
					<?php endforeach; ?>

					<td class="small bg-light"><?php echo number_format($total, 2) ;?></td>
					<td class="small">
						<?php 
							$moyenne >= 10 ? $success++ : $fail++;
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
							echo $rang_eleve;  unset($bulletin[2][$num]);
						 ?>
					</td>
				</tr>
			<?php endforeach; ?>
			<!--Les élèves restants-->
			<?php foreach($bulletin[2] as $eleve): ?>
				<tr>
					<td class="text-danger"><?=$eleve[0];?></td>
					<?php for($x = 0; $x < sizeof($bulletin[0]); $x++) echo "<td class='text-info'>?</td>";?>
					<td></td>
					<td></td>
					<td></td>
				</tr>
			<?php endforeach;?>
			<tr>
				<?php $nbr = 0; ?>
				<td class="small bg-secondary text-light">Note de classe</td>
				<?php $tab = array('MAT998', 'MAT999');?>
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
				<td class="small bg-light"><?=number_format($T, 2) ?></td>
				<td class="small bg-info text-light text-center">
				<?php
				//Suppression Retards/Absences
					unset($bulletin[4]['MAT998']); unset($bulletin[4]['MAT999']);
				if(sizeof($bulletin[1]) > 0)echo number_format(
					array_sum($bulletin[1])/sizeof($bulletin[1]), 2);
				?>
				</td>
			</tr>
		<?php endif; ?>
	</tbody>
</table>
<div class="d-flex justify-content-around small">
	<button class="btn-info badge text-danger">
	  	<?='<img src="'.$this->request->base.'/img/ok.svg" height="20px" width="20px">'.$success;?>
	  	<?='<img src="'.$this->request->base.'/img/ko.svg" height="20px" width="20px">'.$fail;?>
	</button>
	<?php if($bulletin[6] != '')
		echo '<a href="'.$this->request->base.'/Notes/exporter_note" data-toggle="tooltip" title="Télécharger">
			<h6 class="text-info text-center">'
				 .$bulletin[6].
				'<img src="'.$this->request->base.'/img/download.png" height="20px" width="20px" 
				style="cursor: pointer;">
			</h6>
		</a>'
	; ?>
</div>
