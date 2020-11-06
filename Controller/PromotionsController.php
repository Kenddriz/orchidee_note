<?php
App::import('Vendor','PHPExcel', ['file' => 'PHPExcel/Classes/PHPExcel.php']);
App::import("Vendor", "dompdf", array("file" => "dompdf/autoload.inc.php"));
	use Dompdf\Dompdf;

class PromotionsController extends AppController
{
	public $uses = array('Classer','Section', 'Promotion', 'Student', 'Anneescolaire');

	public function index() {
		$this->layout = 'gestion';
		//debug($this->Section->sectionExists('8ème', 'A'));die();
		if($this->request->is('post') && isset($this->request->data['Promotion']['des_annee_scolaire'])) {
			$classe = explode('|&|', $this->request->data['Promotion']['classe']);
			$des_classe = $classe[0];
			$code_section = ($classe[1] != 'vide') ? $classe[1] : '';
			//debug($this->request->data);die();
			$num_appel = $this->Promotion->getNombrePromotion($des_classe, $code_section, 
				$this->request->data['Promotion']['des_annee_scolaire']
			) + 1;
			$this->request->data['Promotion']['num_appel'] = $num_appel;
			$this->request->data['Promotion']['num_matricule'] .= $this->request->data['Promotion']['sexe'];
			$this->request->data['Promotion']['des_classe'] = $des_classe;
			$this->request->data['Promotion']['code_section'] = $code_section;
			$this->Promotion->create($this->request->data);

			if($this->Promotion->validates()) {
				if(!empty($this->Section->find('first',[
				'conditions'=>[
					'des_classe'=>$des_classe,
					'code_section'=>$code_section
				]
				]))
				) {
					if($this->Promotion->validates()) {
							//Verifier si l'élève existe
							if(!empty($this->Student->find('first',[
								'conditions'=>['num_matricule'=> $this->request->data['Promotion']['num_matricule']]
							]))) {
							//Verifier si l'enregistrement existe
							$info_eleve=$this->Student->getEleveInfos(
								$this->request->data['Promotion']['num_matricule'],
								$this->request->data['Promotion']['des_annee_scolaire']
							);

							if(empty($info_eleve)){
								if($this->Promotion->save())
									$this->set('msg_ajout', 
									'<div class="bg-success text-center text-light">Succès!</div>');
								else $this->set('msg_ajout', '<div class="bg-danger text-center text-light">Echec!</div>');
								}
							else $this->set('msg_ajout', 
										'<div class="bg-warning text-center text-light">C\'est un élève de '
										.$info_eleve['Promotion']['des_classe'].' '.
										$info_eleve['Promotion']['code_section'].' '.
										'</div>');
							}
							else  $this->set('msg_ajout', 
								'<div class="bg-warning text-center text-light">
								L\'élève numéro '.$this->request->data['Promotion']['num_matricule'].' n\'existe pas!</div>');
						}					

				} else $this->set('msg_ajout', '<div class="bg-warning text-center text-light">La classe:'.$des_classe." ".$code_section.' n\'existe pas</div>');
				//Refresh
				//header("Refresh:2");
			}
		}
		 $this->set('page_recherche', $this->defaultPaginator());
		 $this->set('selects', $this->getSelect());

	}
	public function mettre_jour() {
		$this->autoRender = false;
		$this->request->onlyAllow('ajax');
		$this->Promotion->create([
			'num_matricule'=> $this->request->data['num_matricule'],
			'des_classe'=>$this->request->data['des_classe'],
			'code_section'=> $this->request->data['code_section'],
			'des_annee_scolaire'=> $this->request->data['des_annee_scolaire'],
			'num_appel'=> $this->request->data['num_appel']
		 ]);
		if($this->Promotion->validates()) {
			return (
			$this->Promotion->update_numero(
				$this->request->data['num_matricule'],
				$this->request->data['des_classe'],
				$this->request->data['code_section'],
				$this->request->data['des_annee_scolaire'],
				$this->request->data['num_appel'])
			);
		}
		else return ('Champs non valide! Taille: 1 - 99');
	}
	public function transferer() {
		$this->autoRender = false;
		$this->request->onlyAllow('ajax');
		$listes = $this->request->data['liste'];
		$classe = explode('|&|', $this->request->data['des_classe_nouvelle']);
		$des_classe = $classe[0];
		$code_section = ($classe[1] != 'vide') ? $classe[1] : '';
		$count = 0;
		if(!empty($this->Section->find('first',[
				'conditions'=>[
					'des_classe'=>$des_classe,
					'code_section'=>$code_section
				]
				]))
				) {
					foreach ($listes as $liste_to_expload) {
						$liste = explode('|&|', $liste_to_expload);
						if($this->Promotion->updateAll(
							[
								'Promotion.des_classe'  => "'".$des_classe."'",
								'Promotion.code_section' => "'".$code_section."'",
								'Promotion.num_appel' => "'".($this->Promotion->getNombrePromotion($des_classe, $code_section, $liste[3]) + 1)."'"
							],
							[
								'Promotion.num_matricule' => $liste[0],
								'Promotion.des_classe'  => $liste[1],
								'Promotion.code_section' => $liste[2],
								'Promotion.des_annee_scolaire' => $liste[3]
							]
						))$count++;
							//si la classe de destination est # du départ, on supprimer ses notes.
						//if($liste[3] != $this->request->data['des_classe_nouvelle'])
					}
					return ($count.'/'.sizeof($listes).' élève(s) transféré(s)!');
				}
		else return 'La classe '.$des_classe.' '.$code_section." n'existe pas";
	}

	public function importer() {
		$this->autoRender = false;
		$this->request->onlyAllow('ajax');

       $count = 0;$indice = 0; $eleve_non_existe = array();
       $classe_non_existe = array();
       $annee_scolaire = $this->request->data['Promotion']['des_annee_scolaire'];

		$object = PHPExcel_IOFactory::load($this->request->data['Promotion']['excel_file']['tmp_name']);
		$highestRow = $object->getSheet(0)->getHighestRow();
		if($object->getSheet(0)->getHighestColumn() != 'F') return 'Structure de fichier non valide';
		for($ligne  = 1; $ligne <= $highestRow; $ligne++) {
			//Classe
			$des_classe = trim($object->getActiveSheet()->getCell("A".$ligne)->getValue());
			$code_section = trim(strtoupper($object->getActiveSheet()->getCell("B".$ligne)->getValue()));
			$classe_trouve = $this->Section->sectionExists($des_classe, $code_section);
			if(!empty($classe_trouve)) {

				$matricule=trim($object->getActiveSheet()->getCell("D".$ligne)->getValue());
				$matricule .= trim(strtoupper($object->getActiveSheet()->getCell("E".$ligne)->getValue()));
				$num_appel = $this->Promotion->getNombrePromotion($des_classe, $code_section, $annee_scolaire);
				$num_appel += 1;
			
				$this->Promotion->create([
					'num_matricule'=>$matricule,
					'des_classe'=>$des_classe,
					'code_section'=>$code_section,
					'des_annee_scolaire'=> $annee_scolaire,
					'num_appel'=>$num_appel					
				]);
				if($this->Promotion->validates()) {
					//Verifier si l'élève existe
					if(!empty($this->Student->find('first',[
						'conditions'=>['num_matricule'=> $matricule]
					]))) {
					//Verifier si l'enregistrement existe
					$info_eleve=$this->Student->getEleveInfosTout($matricule,
							$this->request->data['Promotion']['des_annee_scolaire']
						);
					if(empty($info_eleve))if($this->Promotion->save())$count++;
					}
					else $eleve_non_existe[] = $matricule;

				}
		}
		else $classe_non_existe[] = $des_classe.' '.$code_section;
			}
    $msg = '<tr><td>élève importé</td><td>'.$count.'/'.$highestRow.' lignes</td></tr>';
   // if(sizeof($error) > 0)$msg.= '&echec sur: '.implode('-', $error);
    $msg .= '<tr><td>élève inexistant</td><td class="text-warning">';
    array_unique($eleve_non_existe);
    $i = 0;
    foreach ($eleve_non_existe as $num){
    	$msg .= ($i >= 2 ) ? $num.'<br>' : $num. '-';
    	$i = ($i++ >= 2) ? 0 : $i;
    }
    $msg .= '</td></tr>';

    $msg .= '<tr><td>Classe inexistante</td><td class="text-danger">';
    array_unique($classe_non_existe);
     $i = 0;
    foreach ($classe_non_existe as $num) {
    	$msg .= ($i >= 2 ) ? $num.'<br>' : $num.'-';
    	$i = ($i++ >= 2) ? 0 : $i;
    }
	$msg .= '</td></tr>';

    return $msg;

	}
	public function lister() {
		$this->layout = "gestion";
		$this->view = 'index';
		$this->set('page_recherche', $this->defaultPaginator());
		$this->set('selects', $this->getSelect());
	}
	private function getSelect() {
		$select = array();
		//$classe = $this->Classer->getListClasse();
		$select[0] = $this->Section->getListSection();
		$select[1] = $this->Anneescolaire->getListAnneescolaire();
		return $select;
	}
	private function defaultPaginator() {
		if($this->request->is('post') && isset($this->request->data['Promotion']['annee_scolaire2'])) {
			$fic = fopen('../tmp/search/promotion.txt', 'w');
			//fputs($fic, $this->request->data['Promotion']['des_classe']."\n");
			//fputs($fic, $this->request->data['Promotion']['code_section']."\n");
			fputs($fic, $this->request->data['Promotion']['classe']."\n");
			fputs($fic, $this->request->data['Promotion']['annee_scolaire2']);
			fclose($fic);
				}
			$fic = fopen('../tmp/search/promotion.txt', 'r');
			$classe = explode('|&|', trim(fgets($fic)));
			$annee_scolaire = trim(fgets($fic));
			$des_classe = $classe[0];
			$code_section = ($classe[1] != 'vide') ? $classe[1] : '';
			$this->paginate = [
				'limit' => 50,
			    'joins' => array(
			        array(
			            'table' => 'eleves',
			            'alias' => 'Eleve',
			            'type' => 'INNER',
			            'conditions' => array(
		                	'Promotion.num_matricule = Eleve.num_matricule',
		                	'Eleve.flague' => 0,
							'Promotion.des_classe' => $des_classe,
							'Promotion.code_section' => $code_section,
							'Promotion.des_annee_scolaire' => $annee_scolaire,
			            )
			        )
			    ),
			    'fields' => array('Promotion.*', 'Eleve.nom'),
			    'order' => 'Promotion.num_appel ASC'
			];
			fclose($fic);
			$total_promo = $this->Promotion->nombre_par_sexe($des_classe, $code_section, $annee_scolaire);
			$titre = $des_classe.' '.$code_section.'/'.$annee_scolaire;
		 return array(
		 		$this->paginate('Promotion'), 
		 		$total_promo, $titre, 
		 		$this->getStatus($des_classe, $code_section, $annee_scolaire)
		 	);	
	}

	private function lister_collegue($des_classe, $code_section, $annee_scolaire) {

		$liste = $this->Promotion->find('all', array(
			    'joins' => array(
			        array(
			            'table' => 'eleves',
			            'alias' => 'Eleve',
			            'type' => 'INNER',
			            'conditions' => array(
		                	'Promotion.num_matricule = Eleve.num_matricule',
		                	'Eleve.flague' => 0,
							'Promotion.des_classe' => $des_classe,
							'Promotion.code_section' => $code_section,
							'Promotion.des_annee_scolaire' => $annee_scolaire,
			            )
			        )
			    ),
			    'fields' => array('Promotion.num_appel', 'Eleve.*'),
			    'order' => 'Promotion.num_appel ASC'
		));
		return $liste;
	 }

	public function listeAppel() {
		$this->layout = null;
		$fic = fopen('../tmp/search/promotion.txt', 'r');
		$classe = explode('|&|', trim(fgets($fic)));
		$annee_scolaire = trim(fgets($fic));
		$des_classe = $classe[0];
		$code_section = ($classe[1] != 'vide') ? $classe[1] : '';
		fclose($fic);
		$listes = $this->lister_collegue($des_classe, $code_section, $annee_scolaire);
		$document = '<table>
						<style>
							table {
							  font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
							  border-collapse: collapse;
							  width: 100%;
							}

							table td, table th {
							  border: 1px solid black;
							}
							.text-center {text-align: center;}
							table th{background-color: #f2f2f2;}
						</style>';
					$document .= '<tbody>
							<tr><th colspan="4" class="text-center">CLASSE : '.$des_classe.' '.$code_section.'</th></tr>
							<tr>
								<th class="text-center">N°APPEL</th>
								<th class="text-center">NOM ET PRENOMS</th>
								<th class="text-center">N°MATRICULE</th>
								<th class="text-center">SEXE</th>
							</tr>';
		foreach ($listes as $eleve) {
			$document .= '<tr>
							<td class="text-center">'.$eleve['Promotion']['num_appel'].'</td>
							<td>'.$eleve['Eleve']['nom'].'</td>
							<td class="text-center">'.preg_replace('/\D/', '', $eleve['Eleve']['num_matricule']).'</td>
							<td class="text-center">'.$eleve['Eleve']['sexe'].'</td>
						 </tr>';
		}
		$document .= '</tbody></table>';
		$dompdf = new Dompdf();
		$dompdf->load_html($document);
		$dompdf->setPaper('A4', 'portrait');
		$dompdf->render();
		$filename = $des_classe.' '.$code_section.'_'.$annee_scolaire.'.pdf';
		header('Content-type: application/pdf');
		$dompdf->stream($filename,array("Attachment"=> 1));
	}
	public function repartir() {
		$this->autoRender = false;
		$fic = fopen('../tmp/search/bulletin.txt', 'r');
		$classe1 = explode('|&|', trim(fgets($fic)))[0];
		fclose($fic);
		$classe = explode('|&|', $this->request->data['classe10']);
		$classe[1] = ($classe[1] != 'vide') ? $classe[1] : '';
		if($this->request->data['moyenne_index'] >= 10 && $classe1==$classe[0])
			return 'Choisir une classe supérieure à transmettre les élèves admis.';
		
		$listes = $this->request->data['liste'];
		$count = 0;
			foreach ($listes as $num_matricule) {
				$this->Promotion->create(
					[	'num_matricule'=> $num_matricule,
						'des_classe'=> $classe[0],
						'code_section' => $classe[1],
						'des_annee_scolaire' => $this->request->data['annee_scolaire'],
						'num_appel' => ($this->Promotion->getNombrePromotion($classe[0], $classe[1], $this->request->data['annee_scolaire']) + 1)	
					]
				);
				$info_eleve=$this->Student->getEleveInfos($num_matricule,$this->request->data['annee_scolaire']);

				if(empty($info_eleve)){
					if($this->Promotion->save())$count++;
					}

			}
			return 'Parmi '.sizeof($listes).' élève(s) choisi(s),'.$count.' reinscrit(s) et '.(sizeof($listes) - $count).' déjà reinscrit(s) en classe de '.$classe[0].' '.$classe[1]. ' ou dans une autre classe de '.$this->request->data['annee_scolaire'].'.';
	}
	//Triage numéro
	public function trier() {
		$fic = fopen('../tmp/search/promotion.txt', 'r');
		$classe = explode('|&|', trim(fgets($fic)));
		$annee_scolaire = trim(fgets($fic));
		$des_classe = $classe[0];
		$code_section = ($classe[1] != 'vide') ? $classe[1] : '';
		fclose($fic);
		if(isset($this->request->pass[0])) {
			$list = $this->Promotion->find('all', [
			    'joins' => array(
			        array(
			            'table' => 'eleves',
			            'alias' => 'Student',
			            'type' => 'INNER',
			            'conditions' => array(
			                'Promotion.num_matricule=Student.num_matricule',
			                'Student.flague' => 0,
			                'Promotion.des_classe'=>$des_classe,
			                'Promotion.code_section'=>$code_section,
			                'Promotion.des_annee_scolaire'=>$annee_scolaire
			            )
			        )
			    ),
			    'fields' => array('Student.num_matricule'),
			    'order' => 'Student.nom ASC',
			]);
		$numero = 1;
		foreach ($list as $matricule) {
			$this->Promotion->update_numero(
				$matricule['Student']['num_matricule'], $des_classe,$code_section,$annee_scolaire,$numero++);
			}
		}
		$this->redirect('index');
	}
	//Get status
	private function getStatus($des_classe, $code_section, $annee_scolaire) {
		$annees = explode('-', $annee_scolaire)[0];
		//Pour savoir si l'élève est redoublant
		$redouble = $this->Promotion->find('list', [
			'conditions' => array(
				'Promotion.des_annee_scolaire' => ($annees - 1).'-'.$annees,
				//'Promotion.des_classe' => $des_classe
			),
			'fields' => array('Promotion.num_matricule', 'Promotion.des_classe')
		]);
		//Pour savoir si l'élève est triplant
		$triple = $this->Promotion->find('list', [
			'conditions' => array(
				'Promotion.des_annee_scolaire' => ($annees - 2).'-'.($annees - 1),
				'Promotion.des_classe' => $des_classe
			),
			'fields' => array('Promotion.num_matricule', 'Promotion.des_classe')
		]);

		$list_status = array();
		$list_promos = $this->Promotion->list_promotion($des_classe, $code_section, $annee_scolaire);
		foreach ($list_promos as $num => $infos) {
			$list_status[$num] = (!array_key_exists($num, $redouble)) ? 'N' : ($redouble[$num] == $des_classe ? 'R' : 'P');
			$list_status[$num] = (!array_key_exists($num, $triple)) ? $list_status[$num] : ($redouble[$num] == $des_classe ? 'T' : $list_status[$num]);
		}
		return $list_status;
	}
}
?>
