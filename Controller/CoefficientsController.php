<?php

class CoefficientsController extends AppController
{
	public $uses = array('Matiere', 'Classer', 'Section', 'Coefficient');

	public function index() {
		$this->layout='gestion';
		if(isset($this->request->data['Coefficient']['valeur'])) $this->ajouter();
		//pagination
		if(isset($this->request->data['Coefficient']['search'])) {
				$fic = fopen('../tmp/search/coefficient.txt', 'w');
				fputs($fic, '%'.$this->request->data['Coefficient']['search'].'%');
				fclose($fic);
		}
		$fic = fopen('../tmp/search/coefficient.txt', 'r');
		$search = fgets($fic);
		fclose($fic);
		$this->paginate = array(
			'limit' => 6,
			'joins' => array(
		        array(
		            'table' => 'matieres',
		            'alias' => 'Matiere',
		            'type' => 'INNER',
		            'conditions' => array(
						'Matiere.code_mat = Coefficient.code_mat',
						'or'=> [
							'Coefficient.code_mat LIKE' => $search,
							'Coefficient.des_classe LIKE' => $search
						]
					)
		        )
		    ),
		    'fields' => '*',
		    'order' => 'Coefficient.code_mat ASC');
		$matiere = $this->Matiere->getListMatiere();
		$classe = $this->Classer->getListClasse();
		$this->set('coef_params', [$matiere, $classe, $this->paginate('Coefficient')]);
	}

	private function ajouter() {
		if($this->request->data['Coefficient']['valeur'] == 0)
			$this->request->data['Coefficient']['valeur'] = null;
		$code_mat=$this->request->data['Coefficient']['code_mat'];
		$des_classe=$this->request->data['Coefficient']['des_classe'];
		$ajout_msg = '';
		$this->Coefficient->create($this->request->data);
		if($this->Coefficient->validates()) {
			if($this->Coefficient->checkCoeffExists($code_mat,$des_classe))
			 {
			 	$ajout_msg =(($this->Coefficient->save(['code_mat', 'des_classe', 'valeur'])) ? 'succès!': 'Echec!');
			}
			else $ajout_msg = 'Déjà enregistré';
		}
		else $ajout_msg = 'Vérifier s\'il y a de(s) liste(s) déroulante(s) vide(s)';
		$this->set(compact('ajout_msg'));
		
	}
	public function supprimer() {
		$this->autoRender = false;
		$this->request->onlyAllow('ajax');
		$total = $this->Coefficient->query("
			SELECT count(notes.num_matricule) AS nbr FROM coefficients, notes, promotions WHERE 
				promotions.num_matricule = notes.num_matricule AND
				notes.code_mat = coefficients.code_mat AND 
				promotions.des_classe = coefficients.des_classe AND
				coefficients.des_classe = '".$this->request->data['des_classe']."' AND 
				coefficients.code_mat = '".$this->request->data['code_mat']."'");
		$nbr = intval($total[0][0]['nbr']);
		if($nbr == 0) 
		{
			return(json_encode($this->Coefficient->deleteAll([
				'code_mat' => $this->request->data['code_mat'],
				'des_classe' => $this->request->data['des_classe'],	
			], false)));
		}
		else return('Des élèves de '.$this->request->data['des_classe'].' ayant des notes à cette matière ('.$nbr.' note(s)).');
	}
	public function mettre_jour() {
		$this->autoRender = false;
		$this->request->onlyAllow('ajax');
		//if($this->request->data['valeur'] == 0)$this->request->data['valeur'] = '';
		if($this->Coefficient->validates()) {
			return ($this->Coefficient->updateAll(
				['valeur' => ($this->request->data['valeur'] == 0) ? null : "'".$this->request->data['valeur']."'"],
				[
					'code_mat' => $this->request->data['code_mat'],
					'des_classe' => $this->request->data['des_classe']
				]
			));
		}
		else return ('Nombre:[0-4]');
	}

}
?>