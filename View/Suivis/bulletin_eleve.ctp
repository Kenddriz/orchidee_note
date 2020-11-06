<?php $this->start('gestion'); ?>
	<div class="d-flex flex-column" style="margin-left: 7vw;">
	  <div class="p-2">
	  	<?= $this->Form->create("Suivi", ['action'=>'bulletin_eleve','type'=>'post']); ?>
		  	 <table class="table table-sm table-bordered small bg-info">
		  	 	<tbody>
		  	 		<tr>
		  	 			<td>
			                <?=$this->Form->input('num_exam', array(
			                  'type' => 'select',
			                      'options' => $calibrage[0],
			                       'label'=>false, 
			                      'class'=>'form-control form-control-sm recherche_select'
			                   ));?> 
		  	 			</td>
		  	 			<td>
			                <?=$this->Form->input('des_classe', array(
			                  'type' => 'select',
			                      'options' => $calibrage[1],
			                       'label'=>false, 
			                      'class'=>'form-control form-control-sm recherche_select'
			                   ));?> 
		  	 			</td>
		  	 			<!--<td>
			                <?=$this->Form->input('code_section', array(
			                  'type' => 'select',
			                      'options' => $calibrage[2],
			                       'label'=>false, 
			                      'class'=>'form-control form-control-sm recherche_select'
			                   ));?> 
		  	 			</td>-->
		  	 			<td>
			                <?=$this->Form->input('annee_scolaire', array(
			                  'type' => 'select',
			                      'options' => $calibrage[2],
			                       'label'=>false, 
			                      'class'=>'form-control form-control-sm recherche_select'
			                   ));?> 
		  	 			</td>
		  	 			<td>
							<?= $this->Form->submit('Consulter',['class' => 'form-control form-control-sm',]); ?>
		  	 			</td>
						<td>
			                <?=$this->Form->input('num_Appel', array(
			                	'type'=>'number',
			                	'min'=>1,
			                	'placeholder'=>'Taper un Num°Appel, laisser vide pour lister',
			                     'label'=>false, 
			                     'autocomplete' => 'off',
			                     'class'=>'form-control form-control-sm'
			                   ));?> 
						</td>
		  	 		</tr>
		  	 	</tbody>
		  	 </table>
	  	 <?= $this->Form->end(); ?>
	  </div>
	  <div class="p-2 overflow-auto" style="max-height: 65vh;">
	  	<?php 
	  		$Coeff_total = 0; $T = 0; $rang = ''; $moyenne = 0; 
	  		$tab = array("MAT998" => 0, "MAT999" => 0);
	  	 ?>
	  	<?php if(sizeof($bulletin[0]) > 0 && $bulletin[6] != 1): ?>
		  	<?php if(is_numeric($control_aff[0])) :?>
		  		<table class="table-bordered table table-sm">
		  			<?php foreach($bulletin[0] as  $matricule): ; ?>
			  			<thead>
			  				<tr>
			  					<th colspan="2">Nom et Prénoms : <?=$matricule['Student']['nom'];?></th>
			  					<th>Classe de <?=$bulletin[4]['classe'];?></th>
			  					<th>N° <?=$matricule['Promotion']['num_appel'];?></th>
			  				</tr>
			  			</thead>
			  			<tbody>
			  				<tr class="text-center">
			  					<td rowspan="2">MATIERES</td>
			  					<td rowspan="2">Coeff</td>
			  					<td colspan="2"><?=$bulletin[4]['exam'];?></td>
			  					<!--secondaire, lycée-->
			  					<?php if ($bulletin[6] == 3 || $bulletin[6] == 4): ?>
				  					<tr class="text-center">
				  						<td>Notes</td><td>Appréciations</td>
				  					</tr>
				  					<?php elseif ($bulletin[6] == 2): ?>
				  						<tr class="text-center">
				  						<td colspan="2">Notes</td>
				  						</tr>
			  					<?php endif; ?>
			  				</tr>
		  					<?php 
		  						$nbr = 0; $total=0; $key = $matricule['Student']['num_matricule'];
		  						$rang = array_key_exists($key, $bulletin[3]) ? $bulletin[3][$key][0] : $rang;
		  						$moyenne = array_key_exists($key, $bulletin[3]) ? number_format($bulletin[3][$key][1], 2) : $moyenne;
		  					 ?>
			  				<?php foreach($bulletin[1] as $matiere): ;?>
			  					<?php $nbr = 0; ?>
			  					<?php if(!array_key_exists($matiere['Matiere']['code_mat'], $tab)): ;?>
				  					<tr>
				  						<td><?=$matiere['Matiere']['libelle_mat'];?></td>
				  					    <td>
				  					    	<?php 
				  					    		echo $matiere['Coefficient']['valeur'];
				  					    		$Coeff_total += $matiere['Coefficient']['valeur'];
				  					    	?>
				  					    </td>
				  					    <?php foreach($bulletin[2] as $note): ;?>
				  					    	<?php if ($note['Note']['num_matricule']==$key && $note['Note']['code_mat'] == $matiere['Matiere']['code_mat']): ?>
				  					    	<?php if($nbr == 0 && ($bulletin[6] == 3 || $bulletin[6] == 4))
				  					    				echo '<td>'.$note['Note']['note_obtenue'].'</td><td></td>';
				  					    		  else if($nbr == 0 && $bulletin[6] == 2)
				  					    				echo '<td colspan="2">'.$note['Note']['note_obtenue'];
				  					   		 ?>
											<?php 
												$nbr++; 
												$T+=(is_numeric($note['Note']['note_obtenue'])) ? $note['Note']['note_obtenue'] : 0; 
											?>
				  					    	<?php endif; ?>
				  					    <?php endforeach;?>
				  					    <?php if($nbr == 0 && ($bulletin[6] == 3 || $bulletin[6] == 4))
				  					    		echo '<td></td><td></td>';
				  					    	else if($nbr == 0 && $bulletin[6] == 2)
				  					    		echo '<td colspan="2"></td>';
				  					    ?>
				  					</tr>
				  					<?php else: ;?>
				  					<?php foreach($bulletin[2] as $note): ;?>
				  						<?php 
				  							if ($note['Note']['num_matricule']==$key && $note['Note']['code_mat'] == $matiere['Matiere']['code_mat']) { $tab[$note['Note']['code_mat']] = $note['Note']['note_obtenue'];

				  							} ;?>
				  					<?php endforeach;?>
				  				<?php endif; ?>
			  				<?php endforeach;?>
			  				<tr>
			  					<td>TOTAL</td>
			  					<td class="text-danger"><?=$Coeff_total;?></td>
			  					<?=($bulletin[6] == 2)? '<td colspan="2">'.number_format($T, 2).'</td>' : '<td>'.number_format($T, 2).'</td><td></td>';?>
			  				</tr>
							<tr>
								<td rowspan="2">
									<table style="width: 100%;">
										<tbody>
											<tr><td rowspan="2">MOYENNE</td>
											<td>Elève</td></tr>
											<tr><td>Classe</td></tr>
										</tbody>
									</table>
								</td> 
							    <td>20</td>
							    <?=($bulletin[6] == 2)? '<td colspan="2">'.$moyenne.'</td>' : '<td>'.$moyenne.'</td><td></td>';?>
							</tr>
							<tr>
								<td>20</td>
								 <?=($bulletin[6] == 2)? '<td colspan="2">'.number_format($bulletin[5], 2).'</td>' : '<td>'.number_format($bulletin[5], 2).'</td><td></td>';?>
							</tr>
							<tr>
								<td>RANG</td>
								<td><?=$rang;?></td>
								   <?=($bulletin[6] == 2)? '<td colspan="2"></td>' : '<td></td><td></td>';?>
							</tr>
							<?php if ($bulletin[6] == 3 || $bulletin[6] == 4): ?>
								<tr>
									<td>Retards</td>
									<td><?=$tab['MAT998']?></td>
									<td></td>
									<td></td>
								</tr>
								<tr>
									<td>Absences</td>
									<td><?=$tab['MAT999']?></td>
									<td></td>
									<td></td>
								</tr>
								
								<tr>
									<td>Appréciation du professeur titulaire</td>
									<td colspan="3"></td>
								</tr>
								<tr>
									<td>Signatures</td>
									<td colspan="2"><ins>Directeur:</ins></td>
									<td><ins>Parents:</ins></td>
								</tr>
								<?php  elseif($bulletin[6] == 2): ?>
									<tr>
										<td>Absences</td>
										<td><?=$tab['MAT999']?></td>
										<td colspan="2"></td>
									</tr>
									<tr>
										<td>Le Directeur</td>
										<td colspan="3"></td>
									</tr>
									<tr>
										<td>Les parents</td>
										<td colspan="3"></td>
									</tr>
			  				<?php endif; ?>
		  				</tbody>
		  			<?php endforeach;?>
		  		</table>
			  	<?php else: ;?>
			  		<!--Niveau primaire: Moyenne générale-->
			  		<?php if($bulletin[6] == 2): ?>
			  		<table style="width: 100%">
			  			<tbody>
			  				<tr>
			  					<td rowspan="2">
			  						<table class="table table-sm table-bordered small">
			  							<?php foreach($bulletin[0] as $matricule): ;?>
			  								<thead>
								  				<tr>
								  					<th colspan="2">Nom et Prénoms : <?=$matricule['Student']['nom'];?></th>
								  					<th>Classe de <?=$bulletin[4]['classe'];?></th>
								  					<th>N° <?=$matricule['Promotion']['num_appel'];?></th>
								  				</tr>
				  							</thead>
				  							<tbody>
						  						<tr class="text-center">
								  					<td rowspan="2">MATIERES</td>
								  					<td rowspan="2">Coeff</td>
								  					<td colspan="2"><?=$bulletin[4]['exam'];?></td>
									  				<tr class="text-center">
									  					<td colspan="2">Notes</td>
									  				</tr>
				  								</tr>
							  					<?php 
							  						$nbr = 0; $total=0; $key = $matricule['Student']['num_matricule'];
							  						$rang = array_key_exists($key, $bulletin[3]) ? $bulletin[3][$key][0] : $rang;
							  						$moyenne = array_key_exists($key, $bulletin[3]) ? number_format($bulletin[3][$key][1], 2) : $moyenne;
							  					 ?>
					  							<?php foreach($bulletin[1] as $matiere): ;?>
								  					<?php $nbr = 0; ?>
								  					<?php if(!array_key_exists($matiere['Matiere']['code_mat'], $tab)): ;?>
									  					<tr>
									  						<td><?=$matiere['Matiere']['libelle_mat'];?></td>
									  					    <td>
									  					    	<?php 
									  					    		echo $matiere['Coefficient']['valeur'];
									  					    		$Coeff_total += $matiere['Coefficient']['valeur'];
									  					    	?>
									  					    </td>
									  					    <?php foreach($bulletin[2] as $note): ;?>
									  					    	<?php if ($note['notes']['num_matricule']==$key && $note['notes']['code_mat'] == $matiere['Matiere']['code_mat']): ?>
																		<td colspan="2"><?=$note[0]['note_obt']; ?></td>
																<?php 
																	$nbr++; 
																	$T+=(is_numeric($note[0]['note_obt'])) ? $note[0]['note_obt'] : 0; 
																?>
									  					    	<?php endif; ?>
									  					    <?php endforeach;?>
									  					    <?php 
									  					    	if($nbr == 0)
									  					    		echo '<td colspan="2"></td>';
									  					    ?>
									  					</tr>
								  					<?php else: ;?>
									  					<?php foreach($bulletin[2] as $note): ;?>
									  						<?php 
									  							if ($note['notes']['num_matricule']==$key && $note['notes']['code_mat'] == $matiere['Matiere']['code_mat']) { $tab[$note['notes']['code_mat']] = $note[0]['note_obt'];

									  							} ;?>
									  					<?php endforeach;?>	
									  				<?php endif; ?>
					  							<?php endforeach;?>
				  							</tbody>
			  							<?php endforeach; ?>
							  				<tr>
							  					<td>TOTAL</td>
							  					<td class="text-danger"><?=$Coeff_total;?></td>
							  					<?='<td colspan="2">'.number_format($T, 2).'</td>';?>
							  				</tr>
											<tr>
												<td rowspan="2">
													<table style="width: 100%;">
														<tbody>
															<tr><td rowspan="2">MOYENNE</td>
															<td>Elève</td></tr>
															<tr><td>Classe</td></tr>
														</tbody>
													</table>
												</td> 
											    <td>20</td>
											    <?='<td colspan="2">'.$moyenne.'</td>';?>
											</tr>
											<tr>
												<td>20</td>
												 <?='<td colspan="2">'.number_format($bulletin[5], 2).'</td>' ;?>
											</tr>
											<tr>
												<td>RANG</td>
												<td><?=$rang;?></td>
												<td colspan="2"></td>
											</tr>
											<tr>
												<td>Absences</td>
												<td><?=$tab['MAT999']; ?></td>
												<td colspan="2"></td>
											</tr>
											<tr>
												<td>Le Directeur</td>
												<td colspan="3"></td>
											</tr>
											<tr>
												<td>Les parents</td>
												<td colspan="3"></td>
											</tr>
			  						</table>
			  					</td>
			  					<td>
			  						<!--Appréciations-->
			  						<table class="table table-sm table-bordered small">
			  							<tbody class="text-center">
			  								<tr><td colspan="3"><b>APPRECIATIONS</b></td></tr>
			  								<?php foreach($list_exam as $exam):;?>
			  									<tr>
			  										<?php 
			  											$num_exam = explode('/', $exam['examens']['num_exam'])[0];
			  											$num_exam = ($num_exam > 1) ?$num_exam.'EME</br>' : $num_exam.'ERE</br>';
			  										 ?>
			  										<td><?=$num_exam.'EVALUATION'?></td>
				  									<td></td>
				  									<td></td>
			  									</tr>
			  								<?php endforeach;?>
			  							</tbody>
			  						</table>
			  					</td>
			  				</tr>
			  				<tr>
			  					<!--Conseil-->
			  					<td>
			  						<?=$this->element('decisions_conseil');?>
			  					</td>
			  				</tr>
			  			</tbody>
			  		</table>
			  		<!--Lycée et secondaire: Moyenne génerale-->
			  		<?php elseif($bulletin[6] == 3 || $bulletin[6] == 4): ;?>
			  		<table style="width: 100%;">
			  			<tbody>
			  				<tr>
			  					<td>
			  						<table class="table-bordered table table-sm">
							  			<?php foreach($bulletin[0] as  $matricule): ; ?>
								  			<thead>
								  				<tr>
								  					<th colspan="2">Nom et Prénoms : <?=$matricule['Student']['nom'];?></th>
								  					<th>Classe de <?=$bulletin[4]['classe'];?></th>
								  					<th>N° <?=$matricule['Promotion']['num_appel'];?></th>
								  				</tr>
								  			</thead>
								  			<tbody>
								  				<tr class="text-center">
								  					<td rowspan="2">MATIERES</td>
								  					<td rowspan="2">Coeff</td>
								  					<td colspan="2"><?=$bulletin[4]['exam'];?></td>
								  					<tr class="text-center">
								  						<td>Notes</td><td>Appréciations</td>
								  					</tr>
								  				</tr>
							  					<?php 
							  						$nbr = 0; $total=0; $key = $matricule['Student']['num_matricule'];
							  						$rang = array_key_exists($key, $bulletin[3]) ? $bulletin[3][$key][0] : $rang;
							  						$moyenne = array_key_exists($key, $bulletin[3]) ? number_format($bulletin[3][$key][1], 2) : $moyenne;
							  					 ?>
								  				<?php foreach($bulletin[1] as $matiere): ;?>
								  					<?php $nbr = 0; ?>
								  					<?php if(!array_key_exists($matiere['Matiere']['code_mat'], $tab)): ;?>
									  					<tr>
									  						<td><?=$matiere['Matiere']['libelle_mat'];?></td>
									  					    <td>
									  					    	<?php 
									  					    		echo $matiere['Coefficient']['valeur'];
									  					    		$Coeff_total += $matiere['Coefficient']['valeur'];
									  					    	?>
									  					    </td>
									  					    <?php foreach($bulletin[2] as $note): ;?>
									  					    	<?php if ($note['notes']['num_matricule']==$key && $note['notes']['code_mat'] == $matiere['Matiere']['code_mat']): ?>
																	<td><?=$note[0]['note_obt']; ?>
																	</td><td></td>
																<?php 
																	$nbr++; 
																	$T+=(is_numeric($note[0]['note_obt'])) ? $note[0]['note_obt'] : 0; 
																?>
									  					    	<?php endif; ?>
									  					    <?php endforeach;?>
									  					    <?php if($nbr == 0)
									  					    		echo '<td></td><td></td>';
									  					    ?>
									  					</tr>
								  					<?php else: ;?>
									  					<?php foreach($bulletin[2] as $note): ;?>
									  						<?php 
									  							if ($note['notes']['num_matricule']==$key && $note['notes']['code_mat'] == $matiere['Matiere']['code_mat']) { $tab[$note['notes']['code_mat']] = $note[0]['note_obt'];

									  							} ;?>
									  					<?php endforeach;?>	
									  				<?php endif; ?>
								  				<?php endforeach;?>
								  				<tr>
								  					<td>TOTAL</td>
								  					<td class="text-danger"><?=$Coeff_total;?></td>
								  					<?='<td>'.number_format($T, 2).'</td><td></td>';?>
								  				</tr>
												<tr>
													<td rowspan="2">
														<table style="width: 100%;">
															<tbody>
																<tr><td rowspan="2">MOYENNE</td>
																<td>Elève</td></tr>
																<tr><td>Classe</td></tr>
															</tbody>
														</table>
													</td> 
												    <td>20</td>
												    <?='<td>'.$moyenne.'</td><td></td>';?>
												</tr>
												<tr>
													<td>20</td>
													 <?='<td>'.number_format($bulletin[5], 2).'</td><td></td>';?>
												</tr>
												<tr>
													<td>RANG</td>
													<td><?=$rang;?></td><td></td><td></td>
												</tr>
												<tr>
													<td>Retards</td>
													<td><?=$tab['MAT998'] ;?></td><td></td><td></td>
												</tr>
												<tr>
													<td>Absences</td>
													<td><?=$tab['MAT999']; ?></td><td></td><td></td>
												</tr>
												
												<tr>
													<td>Appréciation du professeur titulaire</td>
													<td colspan="3"></td>
												</tr>
												<tr>
													<td>Signatures</td>
													<td colspan="2"><ins>Directeur:</ins></td>
													<td><ins>Parents:</ins></td>
												</tr>
							  				</tbody>
							  			<?php endforeach;?>
							  		</table>
			  					</td>
			  					<td>
			  						<?=$this->element('decisions_conseil');?>
			  					</td>
			  				</tr>
			  			</tbody>
			  		</table>
			  	<?php endif; ?>		
		    <?php endif; ?>
			<?php else: ;?>
			<h1 class="text-center text-danger">
				<?=($bulletin[6]==1) ? "Le niveau préscolaire n'est pas pris en compte" : ''; ?>
			</h1>
			<h5 class="text-center text-info"><?=(sizeof($bulletin[0])==0) ? 'Aucun élève n\'a été trouvé ': '' ;?></h5>
		<?php endif; ?>
	  </div>
	  <!--Option téléchargement-->
  		<table class="table table-sm table-bordered small jumbotron">
  			<tbody>
  				<tr>
  					<td class="bg-info text-center text-light">BULLETINS-PDF : <?=$control_aff[1];?></td>
  					<td>
						<a href="<?php echo $this->request->base; ?>/Suivis/bulletinElevePdf" data-toggle="tooltip" class="text-info"
							title="Télécharger">
							<h6 class="text-center">
								<?=(sizeof($bulletin[0])>0 && !empty($bulletin[4]['num_appel']))? 
								$bulletin[0][0]['Student']['nom'] :'Toute classe/'.$bulletin[4]['classe'];?>
								<img src="<?=$this->request->base; ?>/img/pdf.jpg" 
									height="20px" width="20px" 
								style="cursor: pointer;">
							</h6>
						</a>
  					</td>
  					<td><?= $this->element('paginateur'); ?></td>
  				</tr>
  			</tbody>
  		</table>
	</div>
<?php $this->end(); ?>