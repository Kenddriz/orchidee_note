<?php
class Examen extends AppModel {
	//public $useTable = 'examens';
	public $validate = array(
			"num_exam" => array(
		        'rule1' => array(
		            'rule' => array('between', 1, 18),
		            'message' => '1<= Numéro <= 99]',
		         )
		   		 ),

			"libelle_exam" => array(
		        /*'rule1' => array(
		            'rule' => '/^[^<>]{1,15}$/i',
		            'message' => 'Libellé: interdits: < et >',
		         ),*/
		        'rule2' => array(
		            'rule' => array('between', 1, 15),
		            'message' => 'Libellé:Longueur = [1 à 15]'
					)
		   		 )
		);
	public function non_valider($col=2) {
		$erreurs_tab = array_values($this->validationErrors);
		$erreurs  = '';
		for($i  = 0; $i < sizeof($erreurs_tab); $i++)$erreurs.=$erreurs_tab[$i][0].' ';
		return ('<th colspan="'.$col.'" class="text-center text-danger bg-light small">'.$erreurs.'</th>');	
	}
	public function getListExam() {
		$list = $this->find('list', [
			'fields'=>['Examen.num_exam', 'libelle_exam'],
			'order'=> 'Examen.num_exam'
		]);
		foreach ($list as $num => $libelle)
			$list[$num] = $libelle.' [N°'.explode('/', $num)[0].']';
		return $list;
	}
	public function getListExamClasse($des_classe, $annee_scolaire) {
		
		return($this->query('SELECT DISTINCT examens.* FROM examens, notes, coefficients WHERE 
			examens.num_exam = notes.num_exam 
			AND coefficients.code_mat = notes.code_mat
			AND coefficients.des_classe = "'.$des_classe.'" 
			AND notes.des_annee_scolaire ="'.$annee_scolaire.'"'));

	}
}
?>