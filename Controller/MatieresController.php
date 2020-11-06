<?php 
	class MatieresController extends AppController {
		public function index() {
			$this->layout = 'gestion';
			//debug($this->Matiere->find('all'));
			if($this->request->is('post')) {
				if(strtolower($this->request->data['Matiere']['libelle_mat'])=='conduite')
					$this->request->data['Matiere']['code_mat'] = 'MAT997';
					//debug(strtolower($this->request->data['Matiere']['libelle_mat']));die();
				$this->Matiere->create($this->request->data);
				if($this->Matiere->validates())	{
					if(empty($this->Matiere->findByCode_mat([$this->request->data['Matiere']['code_mat']])))
					 {
					 	if($this->Matiere->save())
							$this->set("ajout_msg", "<label style=\"color:#28a745\">Succès!</label>");	
					 	else {
					 		$this->set("ajout_msg", "<label style=\"color: #dc3545\">Echec!</label>");
					 		header('Refresh: 2;');
					 	}
					 }
					 else $this->set("ajout_msg", "<label style=\"color: #17a2b8\">Ce code est déjà utilisé.</label>");
				}
			}

			$this->paginate = ['limit' => 20, 'order' =>'Matiere.code_mat ASC'];
			$this->set('liste_matiere', $this->paginate('Matiere'));
			$this->set('nombre_matiere', sizeof($this->Matiere->getListMatiere()));
		}
		/*public function supprimer() {
			$this->autoRender = false;
			$this->request->onlyAllow('ajax');
			return json_encode($this->Matiere->deleteAll(['code_mat'=>$this->request->data['code_mat']], false));
			}*/
		public function mettre_jour() {
				$this->autoRender = false;
				$this->request->onlyAllow('ajax');
				$this->Matiere->create([
					'code_mat'=> $this->request->data['code_mat'],
					'libelle_mat'=>$this->request->data['libelle_mat'],
				 ]);
				if($this->Matiere->validates()) {
					return ($this->Matiere->updateAll(
						['libelle_mat' => "'".htmlspecialchars($this->request->data['libelle_mat'])."'"],
						['code_mat' => $this->request->data['code_mat']]
					));
				}
				else return ('Saisir des caractères alphanumériques:[1-50]');
			}
	}
;?>