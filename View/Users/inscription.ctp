
	<!--création de compte-->
	<div>
	<?= $this->Form->create("User"); ?>
  		<table>
  			<thead>
  				<tr>
  					<th colspan="2">Créer un nouveau compte</th>
  				</tr>		  				
  			</thead>
  			<tbody>
  				<tr>
  					<td colspan="2" style="text-align: center;">
  						<?php if(isset($add_msg))echo $add_msg; ?>
  					</td>
  				</tr>
  				<tr>
  					<td><label for="username">Pseudonyme</label></td>
  					<td>
	        			<?= $this->Form->input("username", array("label" => "", "id"=> "username", "autocomplete" => "off")); ?>	
  					</td>
  				</tr>
  				<!--<tr>
  					<td><label for="nom">Nom</label></td>
  					<td>
						<?= $this->Form->input("nom", 
							array('type' => 'text', 'label'=>'','autocomplete' => 'off', 'id' => 'nom'));?>
  					</td>
  				</tr>-->
          <tr>
  					<td><label for="password">Mot de passe</label></td>
  					<td>
						<?= $this->Form->input("password", array("label" => "", "id" => "password", 'type'=>'password')); ?>
  					</td>
  				</tr>
          <tr>
            <td><label for="nom">Confirmation</label></td>
            <td>
            <?= $this->Form->input("password_confirm", 
              array('type' => 'password', 'label'=>'','autocomplete' => 'off'));?>
            </td>
          </tr>
  				<tr>
  					<td colspan="2">
  						<?= $this->Form->submit("Valider"); ?>
  					</td>
  				</tr>		  				
  			</tbody>
  		</table>
  	<?=$this->Form->end(); ?>	  			
</div>
