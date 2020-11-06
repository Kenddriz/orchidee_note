<?php 
	App::import("Vendor", "dompdf", array("file" => "dompdf/autoload.inc.php"));
	use Dompdf\Dompdf;
class SuivisController extends AppController {
	public $uses = array('Student','Promotion','Anneescolaire','Classer','Section',
		'Matiere', 'Examen', 'Note','Coefficient', 'Niveau');
	
	public function index() {
		$this->layout = 'gestion';
		//debug($this->Promotion->getMaxMatricule('2019-2020'));die();
		//debug($this->getChartDataExam('2849G', '2019-2020'));die();
		//debug($this->Note->getRangePromos('TD', '', '2019-2020', 1));die();
		//debug($this->getChartDataMat(1, '2115G', '2019-2020'));die();
		$this->calibrage();
	}
	public function histogramme() {
		$this->autoRender=false;
		$this->request->onlyAllow('ajax');
		if($this->request->is('post')) {
			$fic = fopen('../tmp/search/suivi.txt', 'w');
			fputs($fic, $this->request->data['num_matricule']."\n");
			fputs($fic, $this->request->data['annee_scolaire']."\n");
			fputs($fic, $this->request->data['code_exam']."\n");
			fclose($fic);
		}
		$fic = fopen('../tmp/search/suivi.txt', 'r');
		$num_matricule = str_replace('/', '', trim(fgets($fic)));
		$annee_scolaire = trim(fgets($fic));
		$num_exam = trim(fgets($fic));
		fclose($fic);
		$sexe = array('G', 'F');
		//$NumMatri = $this->Promotion->getMaxMatricule($annee_scolaire);
		for($i = 0; $i < 5; $i++) {
			$num = rand(1, 5000).$sexe[rand(0, 1)];
			if(!empty($this->Student->getEleveInfos($num, $annee_scolaire))) {
				if(!$this->request->is('post')){
					$num_matricule = $num;
					break;
					}
			}
		}
		$infos = $this->Student->getEleveInfos($num_matricule, $annee_scolaire);
		$msg = '<div class="text-center text-danger">L\'élève n°'.$num_matricule.
					' n\'existe pas dans ';
		$msg.='l\'année scolaire '.$annee_scolaire.'</div>';

		return (!empty($infos) ? json_encode(array([
			'infos'=> $this->infosEleve($infos, $num_matricule, $annee_scolaire), 
			'hist1'=> $this->getChartDataMat($num_exam, $num_matricule, $annee_scolaire),
			'hist2'=> $this->getChartDataExam($num_matricule, $annee_scolaire)
		])) : json_encode(array([
			'infos'=> $msg, 
			'hist1'=> array(array('label' => '', 'y' => 0)),
			'hist2'=> array(array('label' => '', 'y' => 0))
		])));
		//return json_encode($this->infosEleve());
	}
	private function infosEleve($info, $num_matricule, $annee_scolaire) {
				return '<table class="table-sm table-warning">
				<tbody>
					<tr>
						<td class="small text-success">'
							.$info['Student']['nom'].' * '.$info['Promotion']['des_classe'].' '.
							$info['Promotion']['code_section'].' * '.$annee_scolaire.' * 
							N°matricule : '.$num_matricule.'
						</td>
					</tr>
				</tbody>
			</table>';
	}
	private function getChartDataMat($num_exam, $num_matricule, $annee_scolaire) {
		$note = $this->Note->find('all', [
		    'joins' => array(
		        array(
		            'table' => 'matieres',
		            'alias' => 'Matiere',
		            'type' => 'INNER',
		            'conditions' => array(
		                'Note.code_mat=Matiere.code_mat',
		                'Note.code_mat NOT IN'=>array('MAT998', 'MAT999'),
		                'Note.num_exam'=>$num_exam,
		                'Note.num_matricule'=> $num_matricule,
		                'Note.des_annee_scolaire'=>$annee_scolaire,
		            )
		        )
		    ),
		    'fields' => array('Matiere.libelle_mat', 'Note.note_obtenue'),
		   // 'order' => 'Note.note_obtenue ASC',
		]);
		$conduite = array('-100'=>'A', '-200'=>'B', '-300'=>'C');
		$array_note = array();
		foreach ($note as $val) {
			$not = $val['Note']['note_obtenue'];
			$lab = $val['Matiere']['libelle_mat'];
			if(array_key_exists($not, $conduite)) {
				$lab.=' ['.$conduite[$not].']';
				$not/=50;
				$not = abs($not);
			}
			array_push($array_note , array('label'=>$lab, 'y'=>floatval(number_format($not ,2))));
		}
		return $array_note;
	}
	private function getChartDataExam($num_matricule, $annee_scolaire) {
		$infos=$this->Student->getEleveInfos($num_matricule,$annee_scolaire);
		$coeff_total = 1;
		$list_note = array();
		if(!empty($infos)) {
			$coeff_total=$this->Coefficient->getCoeffitientTotal($infos['Promotion']['des_classe'],
						 $infos['Promotion']['code_section']);

			$list_note = $this->Note->find('all', [
			    'joins' => array(
			        array(
			            'table' => 'coefficients',
			            'alias' => 'Coefficient',
			            'type' => 'INNER',
			            'conditions' => array(
			                'Note.code_mat=Coefficient.code_mat',
			                'Note.code_mat NOT IN' => ['MAT998', 'MAT999'],
			                'Coefficient.des_classe'=>$infos['Promotion']['des_classe'],
			                'Note.num_matricule'=>$num_matricule,
			                'Note.des_annee_scolaire'=>$annee_scolaire,
			            )
			        )
			    ),
			    'fields' => array('Note.num_exam','Note.note_obtenue', 'Coefficient.valeur'),
			    'order' => 'Note.num_exam ASC',
			]);
		}
		//Liste exam
		$list_exam = $this->Examen->getListExamClasse($infos['Promotion']['des_classe'], $annee_scolaire) ;
		$array_exam = array();

		$conduite = array('-100'=>'A', '-200'=>'B', '-300'=>'C');
		foreach ($list_exam as $exam) {
			$total = 0; $coeff = 0;
			foreach ($list_note as $note) {
				if($exam['examens']['num_exam'] == $note['Note']['num_exam']) {
					if(!array_key_exists($note['Note']['note_obtenue'], $conduite)) {
						$total += (is_null($note['Coefficient']['valeur'])) ? //bonnus ou malus
						$note['Note']['note_obtenue'] : $note['Coefficient']['valeur'] * $note['Note']['note_obtenue'];
						$coeff += $note['Coefficient']['valeur'];
					}
				}
			}
			$coeff = ($coeff > 0) ? $coeff : 1; //pour éviter l'erreur de division par zéro
			$total = number_format($total/$coeff, 2);
			$libelle_exam = $exam['examens']['libelle_exam']."[".explode('/', $exam['examens']['num_exam'])[0]."]";
			array_push($array_exam, array('y'=>floatval($total),'label'=>$libelle_exam));
		}
		return $array_exam;
	}

	public function faire_bulletin() {
		$this->layout = null;

		if($this->request->is('post')) {
			$fic = fopen('../tmp/search/bulletin.txt', 'w');
			fputs($fic, $this->request->data['des_classe']."\n");
			//fputs($fic, $this->request->data['code_section']."\n");
			fputs($fic, $this->request->data['des_annee_scolaire']."\n");
			fputs($fic, $this->request->data['num_exam']."\n");
			fclose($fic);
		}

		$fic = fopen('../tmp/search/bulletin.txt', 'r');
		$classe = explode('|&|', trim(fgets($fic)));
		$des_classe = $classe[0];
		$code_section = ($classe[1] != 'vide') ? $classe[1] : '';
		$des_annee_scolaire = trim(fgets($fic));
		$num_exam = trim(fgets($fic));
		fclose($fic);

			//Les matières compensées
			$matiere_etudie = $this->Coefficient->getListMatiere_saisies($des_classe, $code_section);
			//Liste promotionnelle
			$promos = $this->Promotion->list_promotion($des_classe, $code_section, $des_annee_scolaire);
			//Les note
			$note = $this->Note->getNotePromos($des_classe, $code_section, $des_annee_scolaire, $num_exam) ;
			//Les rangs selon les notes
			$rang= $this->Note->getRangePromos($des_classe, $code_section, $des_annee_scolaire, $num_exam);
			//Moyenne classe/matière
			$m_classe = $this->Note->getMoyenneClassePromos($des_classe, $code_section, $des_annee_scolaire, $num_exam);
			$candidat_nbr = $this->Note->getNombreMatiere1Exam($des_classe, $code_section, $des_annee_scolaire, $num_exam);
			$list_exam=$this->Examen->getListExam();
			$titre=array_key_exists($num_exam, $list_exam) ? 'Note d\'examen: '.$list_exam[$num_exam].'-'.$des_classe.' '.$code_section.'/'.$des_annee_scolaire : '';

			$this->set('bulletin', [$matiere_etudie,$rang, $promos, $note, $m_classe, $candidat_nbr, $titre]);
			
	}

	public function moyenne_generale() {
		$this->layout = null;
		if($this->request->is('post')) {
			$fic = fopen('../tmp/search/bulletin.txt', 'w');
			fputs($fic, $this->request->data['des_classe']."\n");
			//fputs($fic, $this->request->data['code_section']."\n");
			fputs($fic, $this->request->data['des_annee_scolaire']."\n");
			fclose($fic);
		}

		$fic = fopen('../tmp/search/bulletin.txt', 'r');
		$classe = explode('|&|', trim(fgets($fic)));
		$des_classe = $classe[0];
		$code_section = ($classe[1] != 'vide') ? $classe[1] : '';
		$des_annee_scolaire = trim(fgets($fic));
		//$num_exam = trim(fgets($fic));
		fclose($fic);

		$matiere_etudie = $this->Coefficient->getListMatiere_saisies($des_classe, $code_section);
		//Liste promotionnelle
		$promos = $this->Promotion->list_promotion($des_classe, $code_section, $des_annee_scolaire);
		$note = $this->Note->getNotePromosByMat($des_classe, $code_section, $des_annee_scolaire) ;
		//debug($note);die();
		$rang= $this->Note->getRangePromosGenerale($des_classe, $code_section, $des_annee_scolaire);
		$m_classe = $this->Note->getMoyenneClassePromosGenerale($des_classe, $code_section, $des_annee_scolaire);
		$titre='Moyenne gérérale de la classe '.$des_classe.' '.$code_section.' en '.$des_annee_scolaire;
		$selects = array();
		//$selects[0] = $this->Classer->getListClasse();
		$selects[0] = $this->Section->getListSection();
		$selects[1] = $this->Anneescolaire->getListAnneescolaire();
		$tab = array();
		foreach ($selects[1] as $key => $value) {
			$tab[$key] = $value;
			if(sizeof($tab) == 2)break;
		}
		$selects[1] = $tab;
		$this->set(compact('selects'));
		$this->set('bulletin', [$matiere_etudie,$rang, $promos, $note, $m_classe, $titre]);
	}
	//page initial
	public function defaultPageSuivi() {
		$fic = fopen('../tmp/search/bulletin.txt', 'r');
		fgets($fic);
		fgets($fic);
		fgets($fic);//fseek($fic, 2);
		$num_exam = trim(fgets($fic));
		fclose($fic);
		if(is_numeric($num_exam)){
			$this->view = 'faire_bulletin';
			$this->faire_bulletin();
		}
		else {
			$this->view = 'moyenne_generale';
			$this->moyenne_generale();	
		}
	}
	private function calibrage(){
		$annee_scolaire = $this->Anneescolaire->getListAnneescolaire();
		//$classe = $this->Classer->getListClasse();
		$classe = $this->Section->getListSection();
		$examen = $this->Examen->getListExam();
		$examen['MG'] = "MOYENNE GENERALE";
		$this->set('calibrage', [$examen, $classe, /*$section,*/ $annee_scolaire]);
	}
	//Bulletin de chaque élève
	public function bulletin_eleve() {
		$this->layout = "gestion" ;
		if($this->request->is('post')) {
			//$fic = fopen('../tmp/search/bulletin_eleve.txt', 'r');
			$fic = fopen('../tmp/search/bulletin_eleve.txt', 'w');
			fputs($fic, $this->request->data['Suivi']['num_exam']."\n");
			fputs($fic, $this->request->data['Suivi']['des_classe']."\n");
			//fputs($fic, $this->request->data['Suivi']['code_section']."\n");
			fputs($fic, $this->request->data['Suivi']['annee_scolaire']."\n");
			fputs($fic, $this->request->data['Suivi']['num_Appel']);
			fclose($fic);
			}
		$fic = fopen('../tmp/search/bulletin_eleve.txt', 'r');
		$num_exam = trim(fgets($fic));
		$classe = explode('|&|', trim(fgets($fic)));
		$des_classe = $classe[0];
		$code_section = ($classe[1] != 'vide') ? $classe[1] : '';
		$annee_scolaire = trim(fgets($fic));
		$num_appel = trim(fgets($fic));
		fclose($fic);
		//control affichage bulletin
		$control_aff = array(substr($num_exam, 0, 1), $annee_scolaire);
		$list_exam=$this->Examen->getListExam();
		$list_exam['MG'] = 'MOYENNE GENERALE';

		$infos = array('classe' => $des_classe.' '.$code_section, 'num_appel' => $num_appel) ;
		$infos['exam'] = strtoupper(preg_replace('/(é|è)/', 'E', $list_exam[$num_exam]));
		//$promos = $this->Promotion->list_promotion($des_classe, $code_section, $annee_scolaire);
		$matiere_etudie = $this->Coefficient->getListMatiere_saisies($des_classe, $code_section);
		$note = array(); $rang = array(); $m_classe = array();
		switch ($num_exam) {
			case 'MG':
				$note = $this->Note->getNotePromosByMat($des_classe, $code_section, $annee_scolaire) ;
				$rang= $this->Note->getRangePromosGenerale($des_classe, $code_section, $annee_scolaire);
				$m_classe = $this->Note->getMoyenneClassePromosGenerale($des_classe, $code_section, $annee_scolaire);
				unset($m_classe['MAT998']);
				unset($m_classe['MAT999']);
				//debug($m_classe);die();
						# code...
				$this->set('list_exam', $this->Examen->getListExamClasse($des_classe,$annee_scolaire));
				//debug($note);	
				break;
			
			default:
									//Les note
				$note = $this->Note->getNotePromos($des_classe, $code_section, $annee_scolaire, $num_exam) ;
				//Les rangs selon les notes
				$rang= $this->Note->getRangePromos($des_classe, $code_section, $annee_scolaire, $num_exam);
						//Moyenne classe/matière
				$m_classe = $this->Note->getMoyenneClassePromos($des_classe, $code_section, $annee_scolaire, $num_exam);
				unset($m_classe['MAT998']);
				unset($m_classe['MAT999']);
				# code...
				break;
		}
		$moyenne_Classe = number_format(array_sum($rang)/(sizeof($rang) > 0 ? sizeof($rang) : 1), 2);
		$execo = -200 ; $i = 0; $tot_execo = 0;
		foreach ($rang as $key => $value) {
			if($execo  == $value) $tot_execo++;
			else {$i += $tot_execo+1; $execo=$value ; $tot_execo = 0;} 
			$rang[$key] = array($i, $value);
		}
		$conditions_page = array(
			    'Promotion.num_matricule=Student.num_matricule',
                'Student.flague' => 0,
                'Promotion.des_classe'=>$des_classe,
                'Promotion.code_section'=>$code_section,
                'Promotion.des_annee_scolaire'=>$annee_scolaire,
                //'Promotion.num_appel' => $num_appel
		);
		if(is_numeric($num_appel))$conditions_page['Promotion.num_appel'] = $num_appel;

		$this->paginate = [
				'joins' => array(
			        array(
			            'table' => 'eleves',
			            'alias' => 'Student',
			            'type' => 'INNER',
			            'conditions' => $conditions_page
			        )
			    ),
			    'limit' => 1,
			    'fields' => array('Student.nom', 'Student.num_matricule', 'Promotion.num_appel'),
			    'order' => 'Promotion.num_appel ASC',
		];
		$this->calibrage();
		$this->set('bulletin', [
			$this->paginate('Promotion'), 
			$matiere_etudie, 
			$note,
		    $rang, 
		    $infos,
		    $moyenne_Classe,
		    $this->Niveau->getNiveau($des_classe)
		 ]);
		$this->set(compact('control_aff'));
	}
	//Bulletin pdf
	public function bulletinElevePdf() {
		$this->autoRender = false;
		$this->layout = null;;

		$fic = fopen('../tmp/search/bulletin_eleve.txt', 'r');
		$num_exam = trim(fgets($fic));
		$classe = explode('|&|', trim(fgets($fic)));
		$des_classe = $classe[0];
		$code_section = ($classe[1] != 'vide') ? $classe[1] : '';
		$annee_scolaire = trim(fgets($fic));
		$num_appel = trim(fgets($fic));
		fclose($fic);
				//control affichage bulletin
		$control_aff = array(substr($num_exam, 0, 1), $annee_scolaire);
		$list_exam=$this->Examen->getListExam();
		$list_exam['MG'] = 'MOYENNE GENERALE';

		$infos = array('classe' => $des_classe.' '.$code_section, 'num_appel' => $num_appel) ;
		$infos['exam'] = strtoupper(preg_replace('/(é|è)/', 'E', $list_exam[$num_exam]));
		//$promos = $this->Promotion->list_promotion($des_classe, $code_section, $annee_scolaire);
		$matiere_etudie = $this->Coefficient->getListMatiere_saisies($des_classe, $code_section);
		$note = array(); $rang = array(); $m_classe = array();

		switch ($num_exam) {
			case 'MG':
				$note = $this->Note->getNotePromosByMat($des_classe, $code_section, $annee_scolaire) ;
				$rang= $this->Note->getRangePromosGenerale($des_classe, $code_section, $annee_scolaire);
				//debug($note);die();
				$m_classe = $this->Note->getMoyenneClassePromosGenerale($des_classe, $code_section, $annee_scolaire);
				unset($m_classe['MAT998']);
				unset($m_classe['MAT999']);
						# code...
				break;
			
			default:
				$note = $this->Note->getNotePromos($des_classe, $code_section, $annee_scolaire, $num_exam) ;
				//Les rangs selon les notes
				$rang= $this->Note->getRangePromos($des_classe, $code_section, $annee_scolaire, $num_exam);
						//Moyenne classe/matière
				$m_classe = $this->Note->getMoyenneClassePromos($des_classe, $code_section, $annee_scolaire, $num_exam);
				unset($m_classe['MAT998']);
				unset($m_classe['MAT999']);
				# code...
				break;
		}
		$moyenne_Classe = number_format(array_sum($rang)/(sizeof($rang) > 0 ? sizeof($rang) : 1), 2);
		$execo = -200 ; $i = 0; $tot_execo = 0;
		foreach ($rang as $key => $value) {
			if($execo == $value)$tot_execo++;
			else{$i += $tot_execo+1;$execo=$value ; $tot_execo = 0;} 
			$rang[$key] = array($i, $value);
		}

		$conditions_page = array(
			    'Promotion.num_matricule=Student.num_matricule',
                'Student.flague' => 0,
                'Promotion.des_classe'=>$des_classe,
                'Promotion.code_section'=>$code_section,
                'Promotion.des_annee_scolaire'=>$annee_scolaire,
                //'Promotion.num_appel' => $num_appel
		);
		if(is_numeric($num_appel))$conditions_page['Promotion.num_appel'] = $num_appel;
		$conditions_page = array(
			'joins' => array(
			        array(
			            'table' => 'eleves',
			            'alias' => 'Student',
			            'type' => 'INNER',
			            'conditions' => $conditions_page
			        )
			    ),
			'fields' => array('Student.nom', 'Student.num_matricule', 'Promotion.num_appel'),
			'order' => 'Promotion.num_appel ASC'

		);
		//On limit si on cherché un seul buletin
		if(is_numeric($num_appel))$conditions_page['limit'] = 1;
		$promos = $this->Promotion->find('all',$conditions_page);
		
		$bulletin = [
			$promos, 
			$matiere_etudie, 
			$note,
		    $rang, 
		    $infos,
		    $moyenne_Classe,
		    $this->Niveau->getNiveau($des_classe),
		    $this->Examen->getListExamClasse($des_classe, $annee_scolaire),
		 ];
		// debug($bulletin[6]);die();
		
		if(sizeof($bulletin[0]) > 0 && $bulletin[6] != 1) {
		 	if(is_numeric($control_aff[0]))
		 		$this->bulletinCollegeLycee1Examen($bulletin);
		 	else {
		 		if($bulletin[6] == 2) $this->bulletinPrimaireGenereale($bulletin);
		 		else { 
		 			if($bulletin[6] == 3 || $bulletin[6] == 4);
		 			$this->bulletinCollegeLyceeMoyGenerale($bulletin);
		 		}
		 	}
		 }
		$dompdf = new Dompdf();
		$dompdf->load_html(file_get_contents('../tmp/bulletin/bulletin.html'));
		$dompdf->setPaper('A5', 'portrait');
		$dompdf->render();
		$filename = "Bulletin des notes-".$infos['exam'].'-'.$des_classe.'.pdf';
		//header('Content-type: application/pdf');
		//header('Content-Length: ' . filesize($dompdf->output()));
		$dompdf->stream($filename, array('Attachment'=>1));
	}
	//Bulletin primaire,collège, lycée pour un examen
	private function bulletinCollegeLycee1Examen($bulletin) {
		$document= $this->entete_tableau();

		foreach($bulletin[0] as  $matricule) {

	   	$Coeff_total = 0; $T = 0; $rang = ''; $moyenne = 0; 
	   	$tab = array("MAT998" => 0, "MAT999" => 0);
  			$document.="<div><table class='bulletin'>
  			<thead>
  				<tr><th colspan='4'><h6>BULLETIN DES NOTES</h6></th></tr>
  				<tr>
  					<th colspan='2'>Nom et Prénoms : ".$matricule['Student']['nom']."</th>
  					<th>Classe de ".$bulletin[4]['classe']."</th>
  					<th>N° ".$matricule['Promotion']['num_appel']."</th>
  				</tr>
  			</thead>
  			<tbody>
  				<tr>
  					<td rowspan='2'>MATIERES</td>
  					<td rowspan='2' class='center_text'>Coeff</td>
  					<td colspan='2' class='center_text'>".$bulletin[4]['exam']."</td>";
  					//secondaire, lycée
  					if ($bulletin[6] == 3 || $bulletin[6] == 4)
	  					$document.="<tr><td class='center_text'>Notes</td><td>Appréciations</td></tr>";
	  			    elseif ($bulletin[6] == 2)
	  						$document.="<tr><td colspan='2' class='center_text'>Notes</td></tr>";
	  			$document.="</tr>";
						$nbr = 0; $total=0; $key = $matricule['Student']['num_matricule'];
						$rang = array_key_exists($key, $bulletin[3]) ? $bulletin[3][$key][0] : $rang;
						$moyenne = array_key_exists($key, $bulletin[3]) ? number_format($bulletin[3][$key][1], 2) : $moyenne;
  				foreach($bulletin[1] as $matiere) {
  					$nbr = 0;
  					if(!array_key_exists($matiere['Matiere']['code_mat'], $tab)) {
	  					$document.="<tr>
	  						<td>".$matiere['Matiere']['libelle_mat']."</td>
	  					    <td class='center_text'>".$matiere['Coefficient']['valeur']."</td>";
	  					    $Coeff_total += $matiere['Coefficient']['valeur'];

	  					    foreach($bulletin[2] as $note) {
	  					        if ($note['Note']['num_matricule']==$key && $note['Note']['code_mat'] == $matiere['Matiere']['code_mat']){
	  					    	if($nbr == 0 && ($bulletin[6] == 3 || $bulletin[6] == 4))
	  					    			$document.='<td class="center_text">'.$note['Note']['note_obtenue'].'</td><td></td>';
	  					    		else if($nbr == 0 && $bulletin[6] == 2)
	  					    			$document.='<td colspan="2" class="center_text">'.$note['Note']['note_obtenue'];
									$nbr++; 
									$T+=(is_numeric($note['Note']['note_obtenue'])) ? $note['Note']['note_obtenue'] : 0; 
	  					    	 	}
	  					    	}
	  					    if($nbr == 0 && ($bulletin[6] == 3 || $bulletin[6] == 4))
	  					    		$document.='<td></td><td></td>';
	  					    else if($nbr == 0 && $bulletin[6] == 2)
	  					    		$document.='<td colspan="2"></td>';
	  					$document.="</tr>";
	  				}
	  				else {
	  					//Absences....
	  					foreach($bulletin[2] as $note) {
	  						if ($note['Note']['num_matricule']==$key && $note['Note']['code_mat'] == $matiere['Matiere']['code_mat']) { 
	  							$tab[$note['Note']['code_mat']] = $note['Note']['note_obtenue'];
				  				}
	  					}
	  				}
  				}
  				$document.="<tr>
  					<td>TOTAL</td>
  					<td class='center_text'>".$Coeff_total."</td>".
  					(($bulletin[6] == 2)? '<td colspan="2" class="center_text">'.number_format($T, 2).'</td>' : '<td class="center_text">'.number_format($T, 2)."</td><td></td>");
  				$document.="</tr>
				<tr>
					<td rowspan='2'>
						<table style='width: 100%;'>
							<tbody>
								<tr><td rowspan='2' class='center_text'>MOYENNE</td>
								<td class='center_text'>Elève</td></tr>
								<tr><td class='center_text'>Classe</td></tr>
							</tbody>
						</table>
					</td> 
				    <td class='center_text'>20</td>".
				    (($bulletin[6] == 2)? '<td colspan="2" class="center_text">'.$moyenne.'</td>' : '<td class="center_text">'.$moyenne.'</td><td></td>');
				$document.="</tr>
				<tr>
					<td class='center_text'>20</td>"
					 .(($bulletin[6] == 2)? '<td colspan="2" class="center_text">'.$bulletin[5].'</td>' : '<td class="center_text">'.$bulletin[5].'</td><td></td>');
				$document.="</tr>
				<tr>
					<td>RANG</td>
					<td class='center_text'>".$rang."</td>".
					   (($bulletin[6] == 2)? '<td colspan="2"></td>' : '<td></td><td></td>');
				$document.="</tr>";
				if ($bulletin[6] == 3 || $bulletin[6]== 4) {
					$document.="<tr>
						<td>Retards</td>
						<td class='center_text'>".$tab['MAT998']."</td>
						<td></td>
						<td></td>
					</tr>
					<tr>
						<td>Absences</td>
						<td class='center_text'>".$tab['MAT999']."</td>
						<td></td>
						<td></td>
					</tr>
					
					<tr>
						<td>Appréciation du professeur titulaire</td>
						<td colspan='3'></td>
					</tr>
					<tr>
						<td height='40'>Signatures</td>
						<td colspan='2'><ins>Directeur:</ins></td>
						<td><ins>Parents:</ins></td>
					</tr>";
				 }
				else if($bulletin[6] == 2){
					$document.="<tr>
							<td>Absences</td>
							<td class='center_text'>".$tab['MAT999']."</td>
							<td colspan='2'></td>
						</tr>
						<tr>
							<td>Le Directeur</td>
							<td colspan='3'></td>
						</tr>
						<tr>
							<td>Les parents</td>
							<td colspan='3'></td>
						</tr>";
  				}
				$document.="</tbody></table></div>";
		  	 }
		$document.="</body></html>";
		$fic = fopen('../tmp/bulletin/bulletin.html', 'w');
		fputs($fic, $document);
		fclose($fic);
	}
	private function bulletinPrimaireGenereale($bulletin) {
		$document= $this->entete_tableau();
		foreach($bulletin[0] as $matricule) {
			$Coeff_total = 0; $T = 0; $rang = ''; $moyenne = 0; 
			$tab = array("MAT998" => 0, "MAT999" => 0);
			$document.="<div><table class='bulletin'>
			<thead><tr><th colspan='2'><h6>BULLETIN DES NOTES</h6></th></tr></thead>
			<tbody>
			  	<tr>
			  		<td rowspan='2'>
			  			<table>
			  				<thead>
								<tr>
									<th colspan='2'>Nom et Prénoms :".$matricule['Student']['nom']."</th>
									<th>Classe de ".$bulletin[4]['classe']."</th>
									<th>N° ".$matricule['Promotion']['num_appel']."</th>
								</tr>
				  			</thead>
				  			<tbody>
						  		<tr>
								  	<td rowspan='2'>MATIERES</td>
				  					<td rowspan='2' class='center_text'>Coeff</td>
				  					<td colspan='2' class='center_text'>".$bulletin[4]['exam']."</td>
					  				<tr>
					  					<td colspan='2'>Notes</td>
									</tr>
				  				</tr>";
							  	$nbr = 0; $total=0; $key = $matricule['Student']['num_matricule'];
							  	$rang = array_key_exists($key, $bulletin[3]) ? $bulletin[3][$key][0] : $rang;
							  	$moyenne = array_key_exists($key, $bulletin[3]) ? number_format($bulletin[3][$key][1], 2) : $moyenne;
					  		    foreach($bulletin[1] as $matiere) {
								  	$nbr = 0; 
								  	if(!array_key_exists($matiere['Matiere']['code_mat'], $tab)) {
								$document.="<tr>
									<td>".$matiere['Matiere']['libelle_mat']."</td>
									<td class='center_text'>".$matiere['Coefficient']['valeur']."</td>";
											$Coeff_total += $matiere['Coefficient']['valeur'];
									foreach($bulletin[2] as $note) {
										if ($note['notes']['num_matricule']==$key && $note['notes']['code_mat'] == $matiere['Matiere']['code_mat']){
									$document.="<td colspan='2' class='center_text'>".$note[0]['note_obt']."</td>";
										$nbr++; 
										$T+=(is_numeric($note[0]['note_obt'])) ? $note[0]['note_obt'] : 0; 
										}
									}
									if($nbr == 0)$document.='<td colspan="2"></td>';
								$document.="</tr>";
								}

								else {
					  					//Absences....
				  					foreach($bulletin[2] as $note) {
				  						if ($note['notes']['num_matricule']==$key && $note['notes']['code_mat'] == $matiere['Matiere']['code_mat']) { 
				  							$tab[$note['notes']['code_mat']] = $note[0]['note_obt'];
							  				}
				  					}
								}
					  		}
							  	$document.="<tr>
					  				<td>TOTAL</td>
					  				<td class='center_text'>".$Coeff_total."</td>
					  				<td colspan='2' class='center_text'>".number_format($T, 2).'</td>';
							  	$document.="</tr>
								<tr>
									<td rowspan='2'>
										<table>
										<tbody>
											<tr><td rowspan='2' class='center_text'>MOYENNE</td>
												<td class='center_text'>Elève</td>
											</tr>
												<tr>
													<td class='center_text'>Classe</td>
												</tr>
											</tbody>
										</table>
									</td> 
									<td class='center_text'>20</td>
									<td colspan='2' class='center_text'>".$moyenne.'</td>';
								$document.="</tr>
								<tr>
									<td class='center_text'>20</td>
									<td colspan='2' class='center_text'>".$bulletin[5].'</td>';
								$document.="</tr>
								<tr>
									<td>RANG</td>
									<td class='center_text'>".$rang."</td>
									<td colspan='2'></td>
								</tr>
								<tr>
									<td>Absences</td>
									<td class='center_text'>".$tab['MAT999']."</td>
									<td colspan='2'></td>
								</tr>
								<tr>
									<td>Le Directeur</td>
									<td colspan='3'></td>
								</tr>
								<tr>
									<td>Les parents</td>
									<td colspan='3'></td>
								</tr>
							</tbody>
			  			</table>
			  		</td>
  					<td>";
  						//--Appréciations--
  					$document.="<table>
  							<thead><tr><th colspan='3'>APPRECIATIONS</th></tr></thead>
  							<tbody>";
  								foreach($bulletin[7] as $exam){
								$document.="<tr>";
									$num_exam = explode('/', $exam['examens']['num_exam'])[0];
									$num_exam = ($num_exam > 1) ?$num_exam.'EME</br>' : $num_exam.'ERE</br>';
								$document.="<td>".$num_exam.' EVALUATION'."</td>
									<td></td>
									<td></td>
								</tr>";
  								}
  					$document.="</tbody>
  						</table>
  					</td>
			  	</tr>
			  	<tr>";
			  		//--Conseil--
			  	$document.="<td>"
			  		.$this->DecisionsConseil().
			  		"</td>
			  	</tr>
		</tbody>
	</table></div>";
	}
		$document.="</body></html>";//return $document;
		$fic = fopen('../tmp/bulletin/bulletin.html', 'w');
		fputs($fic, $document);
		fclose($fic);
	}
	//Bulletin Moyenne Generale collège, lycée
	private function bulletinCollegeLyceeMoyGenerale($bulletin) {
		$document= $this->entete_tableau();
		foreach($bulletin[0] as  $matricule){
			$Coeff_total = 0; $T = 0; $rang = ''; $moyenne = 0; 
			$tab = array("MAT998" => 0, "MAT999" => 0);
			$document.="<div><table class='bulletin'>
			<thead><tr><th colspan='2'><h6>BULLETIN DES NOTES</h6></th></tr></thead>
			<tbody>
				<tr>
					<td>
						<table>";
				  			$document.="<thead>
				  				<tr>
				  					<th colspan='2'>Nom et Prénoms : ".$matricule['Student']['nom']."</th>
				  					<th>Classe de ".$bulletin[4]['classe']."</th>
				  					<th>N° ".$matricule['Promotion']['num_appel']."</th>
				  				</tr>
				  			</thead>
				  			<tbody>
				  				<tr>
				  					<td rowspan='2'>MATIERES</td>
				  					<td rowspan='2' class='center_text'>Coeff</td>
				  					<td colspan='2' class='center_text'>".$bulletin[4]['exam']."</td>
				  					<tr>
				  						<td class='center_text'>Notes</td><td class='center_text'>Appréciations</td>
				  					</tr>
				  				</tr>";
		  						$nbr = 0; $total=0; $key = $matricule['Student']['num_matricule'];
		  						$rang = array_key_exists($key, $bulletin[3]) ? $bulletin[3][$key][0] : $rang;
		  						$moyenne = array_key_exists($key, $bulletin[3]) ? number_format($bulletin[3][$key][1], 2) : $moyenne;

				  				foreach($bulletin[1] as $matiere){
				  					 $nbr = 0;
				  					if(!array_key_exists($matiere['Matiere']['code_mat'], $tab)) {
					  					$document.="<tr>
					  						<td>".$matiere['Matiere']['libelle_mat']."</td>
					  					    <td class='center_text'>".$matiere['Coefficient']['valeur']."</td>";
					  					    	$Coeff_total += $matiere['Coefficient']['valeur'];
					  					    foreach($bulletin[2] as $note){
					  					    	if ($note['notes']['num_matricule']==$key && $note['notes']['code_mat'] == $matiere['Matiere']['code_mat']) {
													$document.="<td class='center_text'>".$note[0]['note_obt']."</td><td></td>";
													$nbr++; 
													$T+=(is_numeric($note[0]['note_obt'])) ? $note[0]['note_obt'] : 0; 
					  					    	}
					  					    }
					  					   if($nbr == 0)
					  					    		$document.='<td></td><td></td>';
					  					$document.="</tr>";
					  				}
									else {
					  					//Absences....
				  					foreach($bulletin[2] as $note) {
				  						if ($note['notes']['num_matricule']==$key && $note['notes']['code_mat'] == $matiere['Matiere']['code_mat']) { 
				  							$tab[$note['notes']['code_mat']] = $note[0]['note_obt'];
							  				}
				  						}
									}	
				  				}
				  				$document.="<tr>
				  					<td>TOTAL</td>
				  					<td class='center_text'>".$Coeff_total."</td>
				  					<td class='center_text'>".number_format($T, 2).'</td><td></td>';
				  				$document.='</tr>
								<tr>
									<td rowspan="2">
										<table>
											<tbody>
												<tr class="center_text"><td rowspan="2">MOYENNE</td>
												<td class="center_text">Elève</td></tr>
												<tr><td class="center_text">Classe</td></tr>
											</tbody>
										</table>
									</td> 
								    <td class="center_text">20</td>
								    <td class="center_text">'.$moyenne.'</td><td></td>';
								$document.='</tr>
								<tr>
									<td class="center_text">20</td>
									<td class="center_text">'.$bulletin[5].'</td><td></td>';
								$document.='</tr>
								<tr>
									<td>RANG</td>
									<td class="center_text">'.$rang.'</td><td></td><td></td>
								</tr>
								<tr>
									<td>Retards</td>
									<td>'.$tab['MAT998'].'</td><td></td><td></td>
								</tr>
								<tr>
									<td>Absences</td>
									<td>'.$tab['MAT999'].'</td><td></td><td></td>
								</tr>
								
								<tr>
									<td>Appréciation du professeur titulaire</td>
									<td colspan="3"></td>
								</tr>
								<tr>
									<td height="40">Signatures</td>
									<td colspan="2"><ins>Directeur:</ins></td>
									<td><ins>Parents:</ins></td>
								</tr>
			  				</tbody>
			  			</table>
					</td>
					<td>'
						.$this->DecisionsConseil().
					'</td>
				</tr>
			</tbody>
		</table></div>';
	}
		$document.="</body></html>";
		$fic = fopen('../tmp/bulletin/bulletin.html', 'w');
		fputs($fic, $document);
		fclose($fic);
	}
	//tableau décision de conseil
	private function DecisionsConseil() {
		return "<table>
		<thead><tr><th>DECISIONS DU CONSEIL</th></tr></thead>
		<tbody>
			<tr>
				<td>
					<ul>
						<li>TABLEAU D'HONNEUR</li>
						<li>FELICITATION</li>
						<li>ENCOURAGEMENT</li>
						<li>AVERTISSEMENT</li>
					</ul>
				</td>
			</tr>
			<tr>
				<td>
					<ul style='list-style-type: circle;'>
						<li><b>Admis en</b> <ins>_______</ins></li>
						<li><b>Redouble en </b><ins>_______</ins></li>
					</ul>
				</td>
			</tr>
			<tr>
				<td>
					<b>- Remis à la famille pour:</b>
					<ul style='list-style-type: square;'>
						<li>Mauvaise conduite</li>
						<li>Triplement</li>
						<li>Moyenne trop basse</li>
						<li>Absentéisme</li>
					</ul>
				</td>
			</tr>
		</tbody>
	</table>";
	}
	private function entete_tableau() {
		return "<!DOCTYPE html>
		<html>
		<head>
			<title></title>
			<meta charset='utf-8'>
	  		<style>
	  			div{height: 100%;}
				.bulletin {
				  font-family: 'Trebuchet MS', Arial, Helvetica, sans-serif;
				  border-collapse: collapse;
				}
				table {
				 border-spacing: 0;
				 width: 100%;
				}
				table td, table th {
				  border: 0.4px solid #000000;
				  font-size: 10px;
				}
				table th{text-align: center; }
				.center_text {text-align: center;}
			</style>
		</head>
		<body>";
	}
}
;?>