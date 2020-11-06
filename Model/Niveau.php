<?php
	class Niveau extends AppModel
		{
			public $validate = array(
				"code_niv" => array(
			        'rule1' => array(
			            'rule' => '/^[1-9]{1}$/',
			            'message' => 'Code:Un chiffre = [1 - 9]',
			         )
			   		 ),

				"libelle_niv" => array(
					 'rule1' => array(
			            'rule' => array('between', 5, 15),
			            'message' => 'Désignation:Longueur = [5 - 15]'
						),
					'rule2' => array(
 					'rule' => 'Alphanumeric',
		            'message' => 'Libellé: Caractères spéciaux interdits'
						)
			   		 )
			);
		public function non_valider($col=2) {
			$erreurs_tab = array_values($this->validationErrors);
			$erreurs  = '';
			for($i  = 0; $i < sizeof($erreurs_tab); $i++)$erreurs.=$erreurs_tab[$i][0].' ';
			return ('<th colspan="'.$col.'" class="text-center text-danger bg-light small">'.$erreurs.'</th>');	
		}
		public function getListNiveau() {
			return ($this->find('list', [
				'fields'=>['Niveau.code_niv', 'Niveau.libelle_niv'],
				'order'=> 'Niveau.code_niv'
			]));
		}
		public function getNiveau($des_classe) {
			$liste = $this->find('first', array(
				    'joins' => array(
				        array(
				            'table' => 'classers',
				            'alias' => 'Classe',
				            'type' => 'INNER',
				            'conditions' => array(
			                	'Niveau.code_niv = Classe.code_niv',
								'Classe.des_classe' => $des_classe
				            )
				        )
				    ),
				    'fields' => array('Niveau.code_niv')
			));
			return $liste['Niveau']['code_niv'];
				}

		}
?>
