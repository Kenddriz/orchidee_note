<?php
	class ClassersController extends AppController {
		public function ajouter() {
			$this->autoRender = false;
			$this->request->onlyAllow('ajax');
			$this->Classer->create($this->request->data);
			if($this->Classer->validates()) {
				//$this->Classer->primaryKey = 'des_classe';
				if(empty($this->Classer->find('first', 
					['conditions'=>array('des_classe' => $this->request->data['des_classe'])])))
					{
						if($this->Classer->save())
							return('<th colspan="2" class="bg-success text-light text-center small">Succès</th>');

					}
				else {
						return('<th colspan=\'2\' class=\'bg-danger text-light text-center\'>Classe existante</th>');
					}
				}
			else return $this->Classer->non_valider();
		}

		public function supprimer() {
			$this->autoRender = false;
			$this->request->onlyAllow('ajax');
			$nbr_section = $this->Classer->find('count', [
			    'joins' => array(
			        array(
			            'table' => 'sections',
			            'alias' => 'Section',
			            'type' => 'INNER',
			            'conditions' => array(
			                'Classer.des_classe = Section.des_classe',
			                'Classer.des_classe' => $this->request->data['des_classe']
			            )
			        )
			    )
			]);
			return ($nbr_section > 0) ? 'Echec! Cette classe contient '.$nbr_section.' section(s).' : ($this->Classer->deleteAll(['Classer.des_classe'=>$this->request->data['des_classe']], false));
		}
	}
?>