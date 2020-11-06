<?php
class NiveausController extends AppController {
	public $uses = array('Niveau', 'Classer');
	public function ajouter() {
		$this->autoRender = false;
		$this->request->onlyAllow('ajax');
		$this->request->data['code_niv'] = $this->Niveau->find('count') + 1;
		$this->Niveau->create($this->request->data);
		if($this->Niveau->validates()){
			/*if(empty($this->Niveau->findByCode_niv($this->request->data['code_niv']))) 
			{*/								
				if($this->Niveau->save()) 
				{
					return ('<th colspan="2" class="bg-success text-light text-center small">Succès</th>');
				} 
			/*} else return ('<th colspan="2" class="bg-warning text-light text-center small">Code existant!</th>');*/
		}
		else return $this->Niveau->non_valider();
	}
	public function mettre_jour() {
		$this->autoRender = false;
		$this->request->onlyAllow('ajax');
		$this->Niveau->create($this->request->data);
		unset($this->Niveau->validate['code_niv']);
		if($this->Niveau->validates()) {
			if($this->Niveau->updateAll(
				["Niveau.libelle_niv" => "'".$this->request->data['libelle_niv']."'"],
				['Niveau.code_niv' => $this->request->data['code_niv']]
			)) return('<th colspan="3" class="text-center bg-light text-success">succès</th>');
			}
		else return $this->Niveau->non_valider(3);
	}
	public function supprimer() {
		$this->autoRender = false;
		$this->request->onlyAllow('ajax');
		$this->Niveau->primaryKey = 'code_niv';
		if(empty($this->Classer->find('first',[
			'conditions' => array('Classer.code_niv' => $this->request->data['code_niv'])
		]))) {
			return $this->Niveau->delete($this->request->data['code_niv']);
		}
		else return 'Ce niveau contient déjà au moins une classe.';
	}

}
?>