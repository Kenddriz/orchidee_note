<?php
 class Section extends AppModel {

 		public $validate = array(
 			'code_section' => [
			        'rule2' => array(
			            'rule' => array('between', 0, 1),
			            'message' => 'Désignation:Longueur: [1]'
						)
 			],
 			'des_classe' => [
 				'rule' => array('between', 1, 15),
 				'required'=>true,
 				'message' => 'Choisir une classe'
 				]
 		);
		public function non_valider($col=2) {
			$erreurs_tab = array_values($this->validationErrors);
			$erreurs  = '';
			for($i  = 0; $i < sizeof($erreurs_tab); $i++)$erreurs.=$erreurs_tab[$i][0].' ';
			return ('<th colspan="'.$col.'" class="text-center text-danger bg-light small">'.$erreurs.'</th>');	
		}
 		public function getListSection() {
			$lists = $this->find('all',[
				'fields' =>array('Section.des_classe', 'Section.code_section')
				]);
			$classes = array();
			foreach ($lists as $val) {
				//if(empty($section))$section = 'vide';
				$classes[$val['Section']['des_classe'].'|&|'.$val['Section']['code_section']] = $val['Section']['des_classe'].' '.$val['Section']['code_section'];
			}
			return $classes;
		}
	public function sectionExists($classe, $section) {

		$lists = $this->find('first',[
				'conditions'=>[
					'des_classe'=>$classe,
					'code_section'=>$section
				],
				'fields' =>array('Section.des_classe', 'Section.code_section')
				]);
		$classes = '';
		foreach ($lists as $val)
			$classes = $val['des_classe'].' '.$val['code_section'];
		return $classes; 
	}

	}
;?>