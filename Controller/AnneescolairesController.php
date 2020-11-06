<?php
class AnneescolairesController extends AppController {

	public function ajouter() {
		$this->autoRender = false;
		$this->request->onlyAllow('ajax');
		$this->Anneescolaire->create($this->request->data);
		if($this->Anneescolaire->validates()){

				$annee = explode('-', $this->data['des_annee_scolaire']);
				if(($annee[1] - $annee[0]) == 1) 
			{
					if(empty($this->Anneescolaire->findByDes_annee_scolaire($this->request->data['des_annee_scolaire']))) 
					{								
						if($this->Anneescolaire->save()) 
							return ('<th colspan="2" class="bg-success text-light text-center small">Succès</th>');
					} else 
						return ('<th colspan="2" class="bg-warning text-light text-center small">Année scolaire existante!</th>');
			}
				else 
					return ('<th colspan="2" class="bg-warning text-light text-center small">Année1 = Année2 + 1</th>');
			}
			else return ($this->Anneescolaire->non_valider());

	}
	public function supprimer(){
		$this->autoRender = false;
		$this->request->onlyAllow('ajax');

		$promos_exist = $this->Anneescolaire->find('first', [
		    'joins' => array(
		        array(
		            'table' => 'promotions',
		            'alias' => 'Promotion',
		            'type' => 'INNER',
		            'conditions' => array(
		                'Promotion.des_annee_scolaire = Anneescolaire.des_annee_scolaire',
		                'Promotion.des_annee_scolaire' => $this->request->data['des_annee_scolaire']
		            )
		        )
		    )
		]);
		return (!empty($promos_exist)) ? 'Echec ! Cette année scolaire contient des élèves.' : ($this->Anneescolaire->deleteAll([
					'Anneescolaire.des_annee_scolaire' => $this->request->data['des_annee_scolaire']
					], false));
		}
	}
?>