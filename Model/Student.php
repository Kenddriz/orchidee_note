<?php
	class Student extends AppModel {
		public $useTable = 'eleves';
		public $validate = [
			'num_matricule'=>['rule'=>'/^[0-9]{1,7}[GF]{1}$/', 'message'=>'Nombre entier naturel <= 9999999'],
			//'des_classe'=>['rule'=>array('between',1, 15), 'message'=>'veuiller sélectionner'],
			//'annee_scolaire'=>['rule'=>'/^[0-9]{4}[-]{1}[0-9]{4}$/', 'message'=>'Norme: xxxx-xxxx'],
			//'num_appel'=>['rule'=>'/^[0-9]{1,2}$/', 'message'=>'Taille: 1 - 99'],
			'nom'=>[
					'rule1' => array(
			            'rule' => '/^[^<>]{1,60}$/i',
			            'message' => 'Alphanumérique : 1 à 60 caractères',
			         )
			],
			'sexe'=>[
				'rule1' => array(
			            'rule'=>'/^[GF]{1}$/i',
			            'message' => 'caractères autorisés : G (Garçon) ou F (Fille)',
			         )
			],
			'date_nais' => ['rule' => 'date']
		];
		public function getTotal() {
			return $this->find('count',['conditions' => ['Student.flague' => 0]]);
		}
		public function getEleveInfos($matricule, $annee) {
			return($this->find('first', [
		    'joins' => array(
		        array(
		            'table' => 'promotions',
		            'alias' => 'Promotion',
		            'type' => 'INNER',
		            'conditions' => array(
		                'Student.num_matricule = Promotion.num_matricule',
		                'Student.flague' => 0,
		                'Student.num_matricule'=> $matricule,
		                'Promotion.des_annee_scolaire'=> $annee
		            )
		        )
		    ),
		    'fields' => array('Student.nom', 'Promotion.des_classe', 'Promotion.code_section'),
		]));
		}
		public function getEleveInfosTout($matricule, $annee) {
			return($this->find('first', [
		    'joins' => array(
		        array(
		            'table' => 'promotions',
		            'alias' => 'Promotion',
		            'type' => 'INNER',
		            'conditions' => array(
		                'Student.num_matricule = Promotion.num_matricule',
		                'Student.num_matricule'=> $matricule,
		                'Promotion.des_annee_scolaire'=> $annee
		            )
		        )
		    ),
		    'fields' => array('Student.nom', 'Promotion.des_classe', 'Promotion.code_section'),
		]));
		}
		//Numéro automatique
		public function produire_num($sexe) {

			$list_num = $this->find('all',[
				'conditions'=>array(
					'Student.num_matricule LIKE' => '%'.$sexe
				),
				'fields' => 'Student.num_matricule',
				'order' => 'Student.num_matricule DESC'
			]);
			$matricule = (empty($list_num)) ? 1: preg_replace('/\D/', '', $list_num[0]['Student']['num_matricule']) + 1;
		
			return $matricule.$sexe;
		}
		public function checkEleveExist($matricule) {
			return (empty($this->find('first', [
				'conditions'=>array('Student.num_matricule' => $matricule)
			])));
		}
	public function non_valider() {
		$erreurs_tab = array_values($this->validationErrors);
		$erreurs  = '';
		for($i  = 0; $i < sizeof($erreurs_tab); $i++)$erreurs.=$erreurs_tab[$i][0].' ';
		return $erreurs;	
	}

	}
?>