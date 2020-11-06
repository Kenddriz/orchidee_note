<?php $this->start('gestion'); ?>
	<div
		style="
			height: 85vh;
			background-image: url(../img/undraw_secure_login_pdn4.svg);
			background-size: contain;
			background-repeat: no-repeat;"
	>
		<div 
			style=" margin: 0;
			  position: absolute;
			  top: 50%;
			  left: 50%;
			  border: 1px solid black;
			  -ms-transform: translate(-50%, -50%);
			  transform: translate(-50%, -50%);"
		>

			<table>
				<thead class="text-center">
					<tr class="bg-secondary">
						<th class="text-warning" <td style="width: 50vw">
							 <?= AuthComponent::user('username'); ?>
						</th>						
					</tr>
					<tr class="text-light">
						<th>
							<?php if(isset($upate_message)) echo $upate_message;?>
						</th>
					</tr>
				</thead>
				<tbody>
					<!--<tr>
						<td>
							<?= $this->Form->create("User", ['id'=>'identite']); ?>
							 	<table class="table table-responsive-sm">
							 		<thead>
							 			<tr>
							 				<th class="bg-primary text-center text-light small">IDENTITE</th>
							 			</tr>
							 		</thead>
							 		<tbody>
							 			<tr>
							 				<td class="text-center text-dark">
								        		<?= $this->Form->input("nom", array("label" => "",
								        		"autocomplete" => "off",
								        		"class" => "text-center text-dark",
								        		"value" => $IDENTITE['User']['nom'])); ?>
							 				</td>
							 			</tr>
							 			<tr>
							 				<td>
								        		<?= $this->Form->input("prenom", array("label" => "", 
								        			"autocomplete" => "off", 
								        			"class" => "text-center text-dark",
								        			'value' => $IDENTITE['User']['prenom'])); ?>
							 				</td>
							 			</tr>
							 			<tr class="bg-success text-center">
							 				<td>
								        		<?= $this->Form->submit("Modifier", array("class" => "btn-secondary btn")); ?>
							 				</td>
							 			</tr>
							 		</tbody>
							 	</table>
							<?= $this->Form->end(); ?>							
						</td>
						<td>-->
							<?= $this->Form->create("User", ['id'=>'mot_de_passe']); ?>
					 			<tr>
					 				<th class="text-center text-primary">
					 					<h4>Mise à jour sécurité</h4>
					 				</th>
					 			</tr>
					 			<tr>
					 				<td style="padding: 10px">
						        		<?= $this->Form->input("password", array("label" => "","placeholder" => "nouveau mot de passe",
						        			'class' => 'form-control'
						        	)); ?>
					 				</td>
					 			</tr>
					 			<tr>
					 				<td style="padding: 10px">
						        		<?= $this->Form->input("password_confirm", array("label" => "", "placeholder" => "confirmer le mot de passe", "type" => "password", 'class' => 'form-control')); ?>
					 				</td>
					 			</tr>
					 			<tr >
					 				<td style="padding: 10px">
						        		<?= $this->Form->submit("Valider",array("class" => "form-control btn-outline-info")); ?>
					 				</td>
					 			</tr>
							<?=$this->Form->end(); ?>
						<!--</td>
					</tr>-->
				    <?php if(AuthComponent::user('username') == 'admin') 
				 			echo '<tr class="text-center">
				 					<td>
				 						<a style="text-decoration: none;" 
											href="liste_compte">Comptes et restauration</a>
									</td>
								</tr>'; ?>
				</tbody>
			</table>
		</div>		
	</div>
<?php $this->end(); ?>	