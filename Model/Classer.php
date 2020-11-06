<?php
	class Classer extends AppModel {
		public $validate = array(
			"des_classe" => array(
		        'rule1' => array(
		            'rule' => 'alphaNumeric',
		            'message' => 'Désignation:Alphanumérique',
		         ),
		        'rule2' => array(
		            'rule' => array('between', 1, 15),
		            'message' => 'Désignation:Longueur = [1 - 15]'
					)
		   		 ),
			"code_niv" => array(
		        'rule1' => array(
		            'rule' => '/^[1-9]{1}$/',
		            'required'=>true,
		            'message' => 'Niveau manquant ?',
		         )
		   		)
			);
		public function non_valider($col=2) {
			$erreurs_tab = array_values($this->validationErrors);
			$erreurs  = '';
			for($i  = 0; $i < sizeof($erreurs_tab); $i++)$erreurs.=$erreurs_tab[$i][0].' ';
			return ('<th colspan="'.$col.'" class="text-center text-danger bg-light small">'.$erreurs.'</th>');	
		}
		public function getListClasse() {
			return ($this->find('list', [
				'fields'=>['Classer.des_classe', 'Classer.des_classe'],
				'order'=> 'Classer.code_niv ASC'
			]));
		}	
	}
?>