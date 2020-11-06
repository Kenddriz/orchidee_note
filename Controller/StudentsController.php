<?php
	App::import('Vendor','PHPExcel', ['file' => 'PHPExcel/Classes/PHPExcel.php']);
	App::import("Vendor", "dompdf", array("file" => "dompdf/autoload.inc.php"));
	use Dompdf\Dompdf;

	class StudentsController extends AppController {
		public $uses = array('Niveau', 'Classer','Section', 'Promotion', 'Student', 'Anneescolaire');
			//Page d'acceuil
			public function index() {
				$this->layout = "gestion";
				//debug($this->Student->produire_num());
				if($this->request->is('post')) {
					//$num_matricule = $this->Student->produire_num($this->request->data['Student']['sexe']);
					$num_matricule = $this->request->data['Student']['num_matricule'].$this->request->data['Student']['sexe'];
					$this->Student->create([
						'num_matricule'=>$num_matricule,
						'nom'=>$this->request->data['Student']['nom'],
						'sexe'=>$this->request->data['Student']['sexe'],
						'date_nais'=> $this->request->data['Student']['date_nais'],				
					]);
					if($this->Student->checkEleveExist($num_matricule)) {
						if($this->Student->validates()) {
							if($this->Student->save()) {
								$this->set('msg_ajout', '<div class="small text-light bg-success text-center">Ajouté(e) avec succès sous matricule: '.$num_matricule.'</div>');
								header('Refresh: 2;');
							}
							
							else $this->set('msg_ajout', '<div class="small text-light bg-danger text-center">Echec</div>');
						}
						//else $this->set('msg_ajout', '<div class="small text-light bg-danger text-center"></div>');
					}

					else $this->set('msg_ajout', '<div class="small text-light bg-danger text-center">Le N°matricule : '.$num_matricule.' est déjà utilisé.</div>');
				}
					$this->paginate = [
							'limit' => 5,
							'fields'=>'*',
							'conditions'=>['Student.flague' => 0],
							'order' => 'CAST(REPLACE(REPLACE(Student.num_matricule, "G", ""), "F", "") AS UNSIGNED) DESC'
						];
					$this->set('page_recherche', $this->paginate('Student'));
					$this->set('total', $this->Student->getTotal());
					//$this->set('annee_scolaire', $this->Anneescolaire->getListAnneescolaire());
					$this->set('selects', $this->getSelect());
			}
		private function getSelect() {
		$select = array();
		//$select[0] = $this->Classer->getListClasse();
		$select[0] = $this->Section->getListSection();
		$select[1] = $this->Anneescolaire->getListAnneescolaire();
		
		return $select;
	}
	public function rechercher() {
			$this->layout = "gestion";
			$this->view = 'index';
			if($this->request->is('post')) {
				$search = str_replace('/', '', $this->request->data['Student']['search']);
				$fic = fopen('../tmp/search/section.txt', 'w');
				fputs($fic, '%'.$search.'%');
				fclose($fic);
					}
				$fic = fopen('../tmp/search/section.txt', 'r');
				$search = fgets($fic);
				$this->paginate = [
					'limit' => 5,
					'fields' => '*',
					'conditions' => [
						'Student.flague' => 0,
						'or'=>[
							'Student.num_matricule LIKE' => $search,
							'Student.nom LIKE' => $search,
						]
					],
			        'order' => 'CAST(REPLACE(REPLACE(Student.num_matricule, "G", ""), "F", "") AS UNSIGNED) DESC'
				];
				fclose($fic);
				//debug($this->paginate('Student')); die();
				$this->set('page_recherche', $this->paginate('Student'));
				$this->set('total', $this->Student->getTotal());
				$this->set('selects', $this->getSelect());
		}
	public function mettre_jour() {
			$this->autoRender = false;
			$this->request->onlyAllow('ajax');
			$num_matricule = $this->request->data['num_matricule'];
			$champs = $this->request->data['champs'];
			$val = $this->request->data['val'];
			$val = ($champs=='date_nais') ? date('Y-m-d', strtotime(str_replace('/','-', $val))) : $val;
			$new_matricule = $num_matricule;
			if($champs == 'sexe'){

				$val = strtoupper($val);
				$new_matricule = preg_replace('/\D/', '', $num_matricule).$val;
				if(!$this->Student->checkEleveExist($new_matricule))
					return 'Un autre élève de même sexe ayant le numéro : '.
						preg_replace('/\D/', '', $new_matricule).'.'."\nVous devez interchanger leurs noms.";

			} 

			$this->Student->create([
				'num_matricule'=> $num_matricule,
				'nom'=>($champs == 'nom') ? $val: 'default',//to validate
				'sexe' => ($champs =='sexe') ? $val : 'G',//to validate
				'date_nais'=> ($champs=='date_nais') ? $val: '2019-12-06'//to validate
			 ]);
			if($this->Student->validates()) {
				return ($this->Student->updateAll(
					[
						$champs => "'".htmlspecialchars($val)."'",
						'num_matricule' => "'".$new_matricule."'"
					],
					['num_matricule' => $num_matricule]
				));
			}
			else return $this->Student->non_valider();
		}
	public function supprimer() {
			$this->autoRender = false;
			$this->request->onlyAllow('ajax');
			return json_encode($this->Student->updateAll(
				['flague' => intval($this->request->data['flague'])],
				['num_matricule'=>$this->request->data['num_matricule']]
			));
		}
	public function importer() {
			$this->autoRender = false;
			$this->request->onlyAllow('ajax');

            $fileCount = count($_FILES['excel_file']["name"]);
            $count = 0;$indice = 0; $total = 0;
            $error = array();
            $exist = array();
            for($i=0; $i < $fileCount; $i++) {
				$object = PHPExcel_IOFactory::load($_FILES['excel_file']["tmp_name"][$i]);
				$highestRow = $object->getSheet(0)->getHighestRow();
				$total += $highestRow;
				for($ligne  = 1; $ligne <= $highestRow; $ligne++) {
					$num_matricule = trim($object->getActiveSheet()->getCell("D".$ligne)->getValue());
					$sexe = trim(strtoupper($object->getActiveSheet()->getCell("E".$ligne)->getValue()));
					$num_matricule .= $sexe;
					if($this->Student->checkEleveExist($num_matricule)) {

						$this->Student->create([
							'num_matricule'=> $num_matricule,
							'nom'=>trim($object->getActiveSheet()->getCell("C".$ligne)->getValue()),
							'sexe'=>$sexe,
							'date_nais'=> PHPExcel_Style_NumberFormat::toFormattedString($object->getActiveSheet()->getCell("F".$ligne)->getValue(), 'YYYY-MM-DD')				
						]);
						if($this->Student->validates()) {
								if($this->Student->save())$count++;
						}
						else $error[] = $num_matricule;
				}
				 else $exist[] = $num_matricule;
					}
            }
            $msg = $count.' élève(s) importé(s)/'.$total.' ligne(s)'."\n";
            if(sizeof($error) > 0)$msg.= 'Erreur(s) sur: '.implode('-', $error)."\n";
            if(sizeof($exist) > 0) $msg.= 'Numéro(s) déjà utilisé(s): '.implode(';', $exist)."\n";
			return $msg;
		}

	public function telecharger() {
			//$this->autoRender = false;
			$this->layout = null;
			$niveau = $this->Niveau->getListNiveau();
			//$classe = $this->Classer->getListClasse();
			$liste_sexe_par_niv = array();
			foreach ($niveau as $key => $value) {
				$listes = $this->Promotion->liste_eleve_par_classe($this->request->data['Student']['annee_scolaire'], $key);
				$liste_sexe_par_classe = array();
				$tab_sexe = array();
				foreach ($listes as $liste){
					$tab_sexe[$liste['eleves']['sexe']] = $liste[0]['nombre'];
					$liste_sexe_par_classe[$liste['sections']['des_classe'].' '.$liste['sections']['code_section']] = $tab_sexe;
				}
				$liste_sexe_par_niv[$key] = $liste_sexe_par_classe;
			}
			//Parcour par niveau
			$document='<table>
						<style>
							table {
							  font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
							  border-collapse: collapse;
							  width: 100%;
							}

							table td, table th {
							  border: 1px solid #ddd;
							  text-align: center;
							}

							table th{background-color: #f2f2f2;}
						</style>';

			$document .= '<tbody>
							<tr><th colspan="4">LISTE DES ELEVES EN '.$this->request->data['Student']['annee_scolaire'].'</th></tr>
							<tr>
								<th>CLASSE</th>
								<th>GARCON</th>
								<th>FILLE</th>
								<th>TOTAL</th>
							</tr>';
			$TOTAL = 0;
			foreach ($liste_sexe_par_niv as $code_niv => $classes) {
				$document .= '<tr><th colspan="4">'.$niveau[$code_niv].'</th></tr>';
				$garcon = 0; $fille = 0;
				foreach ($classes as $classe => $sexes) {
					$document.='<tr><td>'.$classe.'</td>';
					$document.='<td>'.(array_key_exists('G', $sexes) ? $sexes['G'] : 0).'</td>
								<td>'.(array_key_exists('F', $sexes) ? $sexes['F'] : 0).'</td>';
					$document.='<td>'.array_sum($sexes).'</td></tr>';
					$garcon += (array_key_exists('G', $sexes) ? $sexes['G'] : 0);
					$fille += (array_key_exists('F', $sexes) ? $sexes['F'] : 0);
				}
				$document.='<tr><td>TOTAL</td><td>'.$garcon.'</td><td>'.$fille.'</td>';
				$total = $garcon + $fille;
				$document.='<td style="color: red;">'.$total.'</td></tr>';
				$TOTAL += $total;
			}
			$document.='<tr><td colspan="4">TOTAL DES ELEVES : '.$TOTAL.'</td></tr>';
			$document.='</tbody></table>';

			$dompdf = new Dompdf();
			$dompdf->load_html($document);
			$dompdf->setPaper('A4', 'portrait');
			$dompdf->render();
			$filename = "Répartition des élèves ".$this->request->data['Student']['annee_scolaire'].'.pdf';
			header('Content-type: application/pdf');
			$dompdf->stream($filename,array("Attachment"=> 1));
		}
	public function ajout_collegue () {
		$this->autoRender = false;
		$count = 0;

		if($this->request->is('post')) {
			$classe = explode('|&|', $this->request->data['classe']);
			$des_classe = $classe[0];
			$code_section = ($classe[1] != 'vide') ? $classe[1] : '';
			$num_appel = $this->Promotion->getNombrePromotion(
				$des_classe, $code_section, $this->request->data['annee_scolaire']
			) + 1;

		if(!empty($this->Section->find('first',[
			'conditions'=>[
				'des_classe'=>$des_classe,
				'code_section'=>$code_section
			]
			]))
			) {
				foreach ($this->request->data['selected'] as $num_matricule) {
					$this->Promotion->create([
						'num_matricule' => $num_matricule,
						'des_classe'=> $des_classe,
						'code_section'=> $code_section,
						'des_annee_scolaire' => $this->request->data['annee_scolaire'],
						'num_appel'=> $num_appel++
					]);
					$info_eleve=$this->Student->getEleveInfos($num_matricule,
								$this->request->data['annee_scolaire']
							);
					if(empty($info_eleve))if($this->Promotion->save())$count++;
				}
				return 'Parmi '.sizeof($this->request->data['selected']).' élève(s) choisi(s),'.$count.' ajouté(s) et '.(sizeof($this->request->data['selected']) - $count).' déjà existant dans la classe '.
						$des_classe.' '.$code_section. ' ou dans une autre classe.';
			}
			else return 'La classe choisie n\'existe pas encore.';
		}
		
		}
		
	}
?>