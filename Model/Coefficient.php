<?php

class Coefficient extends AppModel
{
	public $validate = array(
		'code_mat' => [
			'rule' => '/^MAT[0-9]{3}$/',
		],
		'des_classe' => [
				'rule' => array('between', 1, 15)
		],
	);
	public function getCoeffitientTotal($classe, $section) {
		$total = $this->find('first', [
			'conditions'=>[
				'Coefficient.des_classe'=>$classe,
			],
			'fields'=>['SUM(Coefficient.valeur) AS total']
		]);
		return $total[0]['total'];
	}
	public function getListMatiere_saisies($classe) {

		$list=$this->find('all', [
			    'joins' => array(
			        array(
			            'table' => 'matieres',
			            'alias' => 'Matiere',
			            'type' => 'INNER',
			            'conditions' => array(
			                'Matiere.code_mat=Coefficient.code_mat',
			                'Coefficient.des_classe'=>$classe,
			            )
			        )
			    ),
			    'fields' => array('Matiere.code_mat', 'Matiere.libelle_mat', 'Coefficient.valeur'),
			    'order' => 'Matiere.code_mat ASC',
			]);
		return $list;
	}
	public function checkCoeffExists($mat,$classe) {
		return(empty($this->find('first', [
				'conditions'=>array(
					'code_mat'=>$mat,
					'des_classe'=>$classe,
				)
			]))
		);
	}
}
?>