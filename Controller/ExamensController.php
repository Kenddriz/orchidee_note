<?php

class ExamensController extends AppController
{
	public $uses = array('Examen', 'Note');
	public function ajouter() {
		$this->autoRender = false;
		$this->request->onlyAllow('ajax');
		$this->request->data['num_exam'] .= '/'.preg_replace('/(é|è)/', 'e', $this->request->data['libelle_exam']);
		$this->Examen->create($this->request->data);
		if($this->Examen->validates()){
			if(empty($this->Examen->findByNum_exam($this->request->data['num_exam']))) 
			{							
				if($this->Examen->save()) 
				{
					return ('<th colspan="2" class="bg-success text-light text-center small">Succès</th>');
				} 
			} else return ('<th colspan="2" class="bg-warning text-light text-center small">Type d\'examen existant!</th>');
		}
		else return $this->Examen->non_valider();
		//return(json_encode($this->Examen->validates()));
	}
	public function mettre_jour() {
		$this->autoRender = false;
		$this->request->onlyAllow('ajax');
		$this->Examen->create($this->request->data);
		if($this->Examen->validates()){
			return($this->Examen->updateAll(
				['Examen.libelle_exam' => "'".$this->request->data['libelle_exam']."'"],
				['Examen.num_exam' => $this->request->data['num_exam']]
			)) ;
			}
		else return $this->Examen->non_valider(3);
	}
	public function supprimer() {
		$this->autoRender = false;
		$this->request->onlyAllow('ajax');
		$this->Examen->primaryKey = 'num_exam';
		if(empty($this->Note->find('first',[
			'conditions' => array('Note.num_exam' => $this->request->data['num_exam'])
		]))) {
			return ($this->Examen->delete($this->request->data['num_exam']));
		}
		else return 'Il y a des notes des élèves correspondantes à cette évaluation.';
		
	}
}
?>