<?php
	class SectionsController extends AppController {
		public $uses = array('Section', 'Promotion');

		public function ajouter() {
			$this->autoRender = false;
			$this->request->onlyAllow('ajax');
			$this->request->data['code_section'] = strtoupper(trim($this->request->data['code_section']));
			$this->Section->create($this->request->data);

			if($this->Section->validates()) {
				//$this->Section->primaryKey = 'des_classe';
				if(empty($this->Section->find('first', 
					['conditions'=>array(
						'Section.code_section' => $this->request->data['code_section'],
						'Section.des_classe' => $this->request->data['des_classe'],

					)
					]
				)))
					{
						if($this->Section->save())
							return('<th colspan="2" class="bg-success text-light text-center small">Succès</th>');

					}
				else {
						return('<th colspan=\'2\' class=\'bg-danger text-light text-center\'>Classe existante!</th>');
					}
				}
			else return $this->Section->non_valider();

		}
		public function supprimer() {
			$this->autoRender = false;
			$this->request->onlyAllow('ajax');
			if(empty($this->Promotion->find('first', 
					['conditions'=>array(
						'Promotion.des_classe' => $this->request->data['des_classe'],
						'Promotion.code_section' => $this->request->data['code_section'],
						)
					]
			))) {
					return ($this->Section->deleteAll([
					'Section.code_section' => $this->request->data['code_section'],
					'Section.des_classe'=>$this->request->data['des_classe']
				], false));

			}
			else return ('Cette classe contient des élèves.');	
		}

	}
?>