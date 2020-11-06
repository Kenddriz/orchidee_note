<?php
	class Anneescolaire extends AppModel {
		public $validate = [
			'des_annee_scolaire'=> [
				'rule'=>'/^[0-9]{4}[-]{1}[0-9]{4}$/',
				'message'=>'Format: XXXX-XXXX'
			]
		];
		public function non_valider($col=2) {
			$erreurs_tab = array_values($this->validationErrors);
			$erreurs  = '';
			for($i  = 0; $i < sizeof($erreurs_tab); $i++)$erreurs.=$erreurs_tab[$i][0].' ';
			return ('<th colspan="'.$col.'" class="text-center text-danger bg-light small">'.$erreurs.'</th>');	
		}
		public function getListAnneescolaire() {
			return($this->find('list', [
				'fields'=>['Anneescolaire.des_annee_scolaire', 'Anneescolaire.des_annee_scolaire'],
				'order'=>'Anneescolaire.des_annee_scolaire DESC'
			]));
		}
		public function editable($anneescolaire) {
			$max = $this->find('all', [
				'fields'=>['MAX(Anneescolaire.des_annee_scolaire) AS MAX'],
			]);
			return $anneescolaire == $max[0][0]['MAX'];
		}
	}
?>