<?php
	class Matiere extends AppModel {
		public $validate = [
			'code_mat' => [
				'rule' => '/^MAT[0-9]{3}$/',
				'message' => 'Norme: MATXXX(X=[0-9])'
			],
			'libelle_mat' => [
				'rule'=>array('between', 1, 50),
				'message'=>'Longueur: [1 - 50]'
			]
		];
		 public function getListMatiere() {
		 	return ($this->find('list', 
			 		[
			 			'fields'=>['Matiere.code_mat', 'Matiere.libelle_mat'],
			 			'order' => 'Matiere.code_mat ASC'
			 		]
		 		)
		 	);
		 }
	}
?>