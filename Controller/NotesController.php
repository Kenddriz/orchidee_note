<?php
App::import('Vendor','PHPExcel', 
	[
		'file'=>'PHPExcel/Classes/PHPExcel.php'
		//'PHPExcel/Classes/PHPExcel/Writer/Excel5.php'
	]
);

class NotesController extends AppController {
	
	public $uses = ['Note','Matiere', 'Anneescolaire', 'Examen', 'Classer', 'Section', 'Promotion','Coefficient', 'Student'];

	public function index() {
		//debug($this->Note->getMoyenneClassePromos('11ème', 'A', '2019-2020', "1/Bimèstre")); die();
		$this->layout = 'gestion';
		if($this->request->is('post')) {
			$classe = explode('|&|', $this->request->data['Note']['CLASSE :']);
			$fic = fopen('../tmp/search/note.txt', 'w');
			fputs($fic, $this->request->data['Note']['MATIERE :']."\n");
			fputs($fic, $this->request->data['Note']['Anneescolaire']."\n");
			fputs($fic, $this->request->data['Note']['EXAMEN :']."\n");
			fputs($fic, $classe[0]."\n");
			fputs($fic, (($classe[1] != 'vide') ? $classe[1] : '')."\n");
			//fputs($fic, $this->request->data['Note']['Section']."\n");
			$matiere_etudie = $this->Coefficient->getListMatiere_saisies($classe[0]);
			$etudie = 0;
			$libelle_mat = '';
			$coefficient = '';
			foreach ($matiere_etudie as $key => $mat) {
				if($mat['Matiere']['code_mat'] == $this->request->data['Note']['MATIERE :']) {
					$libelle_mat = $mat['Matiere']['libelle_mat'];
					$coefficient = $mat['Coefficient']['valeur'];
					$etudie = 1;
					break;
				}
			}
			fputs($fic, $etudie."\n");
			fputs($fic, $libelle_mat."\n");
			fputs($fic, $coefficient."\n");
			fclose($fic);
		}
		$this->set('options', $this->getOptions());
		$this->set('liste_promos', $this->consulter());

	}
	private function getOptions(){
		$options = array();
		$options['Anneescolaire'] = $this->Anneescolaire->getListAnneescolaire();
		foreach ($options['Anneescolaire'] as $key => $value) {
			unset($options['Anneescolaire']);
			$options['Anneescolaire'] = $value;
			break;
		}
		$options['MATIERE :'] = $this->Matiere->getListMatiere();
		$options['EXAMEN :'] = $this->Examen->getListExam();
		//$options['Classer'] = $this->Classer->getListClasse();
		$options['CLASSE :'] = $this->Section->getListSection();
		return $options;	
	}
	//Saisi
	public function saisir() {
		$this->layout = 'gestion';
		//debug($this->request->data);die();
		
		if($this->request->is('post')) {
			$fic = fopen('../tmp/search/saisi.txt', 'w');
			fputs($fic, $this->request->data['Note']['CLASSE :']."\n");
			//fputs($fic, $this->request->data['Note']['Section']."\n");
			fputs($fic, $this->request->data['Note']['Anneescolaire']."\n");
			fputs($fic, $this->request->data['Note']['EXAMEN :']."\n");
			fclose($fic);
		}
		$fic = fopen('../tmp/search/saisi.txt', 'r');
		$classe = explode('|&|', trim(fgets($fic)));
		$des_classe = $classe[0];
		$code_section = ($classe[1] != 'vide') ? $classe[1] : '';
		$des_annee_scolaire = trim(fgets($fic));
		$num_exam = trim(fgets($fic));
		fclose($fic);
		//Les matières compensées
		$matiere_etudie = $this->Coefficient->getListMatiere_saisies($des_classe);
		//supprimer retards && absences && points
		foreach ($matiere_etudie as $key => $mat) {
			if($mat['Matiere']['code_mat'] == 'MAT996' || $mat['Matiere']['code_mat'] == 'MAT998' || $mat['Matiere']['code_mat'] == 'MAT999')
				unset($matiere_etudie[$key]);
		}
		//Liste promotionnelle
		$promos = $this->Promotion->list_promotion($des_classe, $code_section, $des_annee_scolaire);
		//debug($promos);die();
		//Les note
		$note = $this->Note->getNotePromos($des_classe, $code_section, $des_annee_scolaire, $num_exam) ;
		foreach ($note as $key => $mat) {
			if($mat['Note']['code_mat'] == 'MAT998' || $mat['Note']['code_mat'] == 'MAT999')
				unset($note[$key]);
		}
		
		$titre = $des_classe. ' '.$code_section.': '.$des_annee_scolaire.'/Evaluation ['.explode('/', $num_exam)[0].']';

		$options = $this->getOptions();
		unset($options['MATIERE :']);
		$this->set('editable', $this->Anneescolaire->editable($des_annee_scolaire));
		$this->set('saisi', [$matiere_etudie, $promos, $note, $titre]);
		$this->set(compact('options'));
	}
	
	private function consulter() {

		$fic = fopen('../tmp/search/note.txt', 'r');
		$matiere = trim(fgets($fic));
		$anneescolaire = trim(fgets($fic));
		$examen = trim(fgets($fic));
		$classe = trim(fgets($fic));
		$section = trim(fgets($fic));
		$etudie = trim(fgets($fic));
		$libelle_mat = trim(fgets($fic));
		$coefficient = trim(fgets($fic));
		$coefficient = (is_numeric($coefficient)) ? $coefficient : 'vide';
		fclose($fic);
		$titre = 'Liste des notes ';
		$entete = '	<th></th>
					<th class=\'bg-secondary small\'>N°MATRICULE</th>
					<th class=\'bg-secondary small\'>NOM ET PRENOMS</th>
					<th class=\'bg-secondary small\'>N°APPEL</th>';
					
		switch ($matiere) {
			case 'MAT998': $entete .= '<th class=\'bg-secondary small\'>+ RETARDS(en minute)</th>';
				# code...
				break;
			case 'MAT999': $entete .= '<th class=\'bg-secondary small\'>+ ABSENCES(en jour)</th>';
				# code...
				break;
			default: $entete .= '<th class=\'bg-secondary small\'>NOTE DEFINIE</th>';
				# code...
				break;
		}
			
			//Les matières compensées
		//$matiere_etudie = $this->Coefficient->getListMatiere_saisies($classe);
		//Liste promotionnelle
		$promos = $this->Promotion->list_promotion($classe, $section, $anneescolaire);
		$note = $this->Note->getNotePromos($classe, $section, $anneescolaire, $examen) ;
		$lib_exam = explode('/', $examen);
		$titre.=(preg_match('/^[abceiyh]$/', strtolower(substr($libelle_mat, 0,1)))) ? 'd\'' : 'de ';
		$titre .= $libelle_mat.'[coefficient : '.$coefficient.'] de '.$classe.' ';
		$titre .= $section.' / '.$lib_exam[1].' '.$lib_exam[0].' /'.$anneescolaire;
		$titre = ($etudie == 1) ? $titre : "La classe ".$classe.' n\'apprend pas la matière '.$this->Matiere->getListMatiere()[$matiere].'.';
		//return array($titre, $entete, $lists, $editable);
		//debug($this->Anneescolaire->editable()); die();
		return array($titre, $entete, $promos, $this->Anneescolaire->editable($anneescolaire), $matiere,
		 $note, $etudie);
	}
	//Ajouter une note
	public function ajouter() {
		$this->autoRender = false;
		$this->request->onlyAllow('ajax');
		$fic = fopen('../tmp/search/saisi.txt', 'r');
		$classe = explode('|&|', trim(fgets($fic)));
		$des_classe = $classe[0];
		$code_section = ($classe[1] != 'vide') ? $classe[1] : '';
		$des_annee_scolaire = trim(fgets($fic));
		$num_exam = trim(fgets($fic));
		fclose($fic);
		$tab = array('5'=>-100, '10'=>-200, '15'=>-300);
		$num_matricule = $this->request->data['num_matricule'];
		$code_mat = $this->request->data['code_mat'];
		$note_obtenue = $this->request->data['note_obtenue'];
		$note_obtenue = ($code_mat == 'MAT997') ? 
		(array_key_exists(intval($note_obtenue), $tab) ? $tab[intval($note_obtenue)] : $note_obtenue) : $note_obtenue;
		//$eleve_sup = $this->Student->getListEleveSupprime();
		return $this->Note->ajout($note_obtenue, $num_matricule, $code_mat, $num_exam, $des_annee_scolaire);

	}
	//Mettre à jour
	public function mettre_jour() {
		$this->autoRender = false;
		$this->request->onlyAllow('ajax');
		$fic = fopen('../tmp/search/note.txt', 'r');
		$matiere = trim(fgets($fic));
		$anneescolaire = trim(fgets($fic));
		$examen = trim(fgets($fic));
		fgets($fic);fgets($fic);fgets($fic);fgets($fic);
		$coefficient = trim(fgets($fic));
		fclose($fic);
		$tab = array('A'=>-100, 'B'=>-200, 'C'=>-300);
		$matricule = $this->request->data['num_matricule'];
		$note_obtenue = $this->request->data['note_obtenue'];
		if(is_numeric($coefficient) && is_numeric($note_obtenue)) $note_obtenue /= $coefficient;
		$note_obtenue = ($matiere == 'MAT997') ? 
		(array_key_exists($note_obtenue, $tab) ? $tab[$note_obtenue] : $note_obtenue) : $note_obtenue;
		if(!is_numeric($note_obtenue) && $matiere == 'MAT997')return 0;
		else if(!is_numeric($note_obtenue) && $matiere != 'MAT997') return 0;
		else {
			if($matiere != 'MAT996' && $matiere != 'MAT998' && 
				$matiere != 'MAT999' &&  $note_obtenue > 20)
				return 0;
		}
		//return 0;
		return $this->Note->ajout($note_obtenue, $matricule, $matiere, $examen, $anneescolaire);
	}
	//Suppression
	public function supprimer() {
		$this->autoRender = false;
		$this->request->onlyAllow('ajax');
		$fic = fopen('../tmp/search/note.txt', 'r');
		$matiere = trim(fgets($fic));
		$anneescolaire = trim(fgets($fic));
		$examen = trim(fgets($fic));
		fclose($fic);
		if($this->Note->deleteAll([
			'num_matricule' => $this->request->data['num_matricule'],
			'code_mat' => $matiere,
			'num_exam'=> $examen,
			'des_annee_scolaire' => $anneescolaire
			], false))return 1;
		else return 0;	
	}

	public function exporter_note() {
		$this->autoRender = false;
	
		//$this->request->onlyAllow('ajax');
		$fic = fopen('../tmp/search/bulletin.txt', 'r');
		$classe = explode('|&|', trim(fgets($fic)));
		$des_classe = $classe[0];
		$code_section = ($classe[1] != 'vide') ? $classe[1] : '';
		$des_annee_scolaire = trim(fgets($fic));
		$num_exam = trim(fgets($fic));
		fclose($fic);
		//Les matières compensées
		$matiere_etudie = $this->Coefficient->getListMatiere_saisies($des_classe);
		//Liste promotionnelle
		$promos = $this->Promotion->list_promotion($des_classe, $code_section, $des_annee_scolaire);
		//Les note
		$note = $this->Note->getNotePromos($des_classe, $code_section, $des_annee_scolaire, $num_exam) ;
		//Les rangs selon les notes
		$rang= $this->Note->getRangePromos($des_classe, $code_section, $des_annee_scolaire, $num_exam);
		$m_classe = $this->Note->getMoyenneClassePromos($des_classe, $code_section, $des_annee_scolaire, $num_exam);
		$candidat_nbr = $this->Note->getNombreMatiere1Exam($des_classe, $code_section, $des_annee_scolaire, $num_exam);

		//$this->set('bulletin', [$matiere_etudie,$rang, $promos, $note, $coeff_total, $m_classe]);	
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setAutoSize(true);

		if(!empty($note)) {
			$alphabet = 66;
					//Ligne matière
			foreach ($matiere_etudie as $matiere) {
				$objPHPExcel->getActiveSheet()->SetCellValue(chr($alphabet).'1', $matiere['Matiere']['libelle_mat']);
				$objPHPExcel->getActiveSheet()->getColumnDimension(chr($alphabet))->setAutoSize(true);
				$objPHPExcel->getActiveSheet()->getStyle(chr($alphabet).'1')->getAlignment()->setTextRotation(90);
				$alphabet++;
			}
		$objPHPExcel->getActiveSheet()->getStyle(chr($alphabet).'1')->getAlignment()->setTextRotation(90);
		$objPHPExcel->getActiveSheet()->SetCellValue(chr($alphabet++).'1', 'Total');
		$objPHPExcel->getActiveSheet()->getStyle(chr($alphabet).'1')->getAlignment()->setTextRotation(90);
		$objPHPExcel->getActiveSheet()->SetCellValue(chr($alphabet++).'1', 'Moyenne');
		$objPHPExcel->getActiveSheet()->getStyle(chr($alphabet).'1')->getAlignment()->setTextRotation(90);
		$objPHPExcel->getActiveSheet()->SetCellValue(chr($alphabet).'1', 'Rang');
					//2ème ligne
		$coff_tot = 0;
		$objPHPExcel->getActiveSheet()->SetCellValue('A2', 'COEFFICIENT');
		$alphabet = 66;
		foreach ($matiere_etudie as $matiere) {
			$objPHPExcel->getActiveSheet()->SetCellValue(chr($alphabet).'2', $matiere['Coefficient']['valeur']);
			$coff_tot+=$matiere['Coefficient']['valeur'];
			$alphabet++;
		}
		$objPHPExcel->getActiveSheet()->SetCellValue(chr($alphabet).'2', $coff_tot);
		//Corps
		$rang_eleve = 0; $execo=-200;$T = 0; $tot_execo = 0;
	    $i=3;
		foreach ($rang as $num =>$moyenne) {
			$total=0;
			$objPHPExcel->getActiveSheet()->SetCellValue('A'.$i, $promos[$num][0]);
			unset($promos[$num]);
			$alphabet = 66;
			foreach ($matiere_etudie as $matiere) {
				$nbr = 0;
				foreach ($note as $not) {
					if ($not['Note']['num_matricule']==$num && $not['Note']['code_mat'] == $matiere['Matiere']['code_mat'])
					  {
					 	$objPHPExcel->getActiveSheet()->SetCellValue(chr($alphabet++).$i, $not['Note']['note_obtenue']);
					 	$nbr++;
					 	$total+=(is_numeric($not['Note']['note_obtenue'])) ? $not['Note']['note_obtenue'] : 0; 
					 	 $T+=(is_numeric($not['Note']['note_obtenue'])) ? $not['Note']['note_obtenue'] : 0; 
					 }
				}
				//on remplie la matière non saisie
				if($nbr==0)$alphabet++;
			}

			$objPHPExcel->getActiveSheet()->SetCellValue(chr($alphabet++).($i), $total);
			$moyenne = number_format($moyenne, 2);
			//si la moyenne précedente est égale à la suivante, on incrémente pas pour avoir execo
			if($execo  == $moyenne) $tot_execo++;
			else {$rang_eleve += $tot_execo+1; $execo=$moyenne ; $tot_execo = 0;}

			$objPHPExcel->getActiveSheet()->SetCellValue(chr($alphabet++).($i), $moyenne);
			$objPHPExcel->getActiveSheet()->SetCellValue(chr($alphabet++).($i), $rang_eleve);
			$i++;
		}
		//Les élèves n'ont pas de notes
		foreach ($promos as $eleve)
			$objPHPExcel->getActiveSheet()->SetCellValue('A'.($i++), $eleve[0]);
	$objPHPExcel->getActiveSheet()->SetCellValue('A'.(++$i), 'Moyenne de classe');
	$alphabet = 66;
	foreach ($matiere_etudie as $matiere) {
				$nbr = 0;
		foreach ($m_classe as $key => $c_val) {
			if($matiere['Matiere']['code_mat']==$key) {
				$objPHPExcel->getActiveSheet()->SetCellValue(chr($alphabet++).($i), 
					is_numeric($c_val)? number_format($c_val, 2) : $c_val);
				$nbr++;	
			}
		}
	if($nbr==0)$alphabet++;
	}
	
	$objPHPExcel->getActiveSheet()->SetCellValue(chr($alphabet++).(--$i), number_format($T,2));
	
	$objPHPExcel->getActiveSheet()->SetCellValue(chr($alphabet++).(++$i), 
		(sizeof($rang)>0) ? number_format(array_sum($rang)/sizeof($rang), 2): 0);

		}
		//Les matières et coefficients(entêtes)
		
	$filename = 'Note_'.$des_classe.' '.$code_section.'_examen n°'.$num_exam.'_'.$des_annee_scolaire.'.xlsx';
	header('Content-type: application/vnd.ms-excel');
	header('Content-Disposition: attachment; filename="'.$filename.'"');
	$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
	$objWriter->save('php://output');

	}

	//Moyenne générale
	public function exporter_generale() {
		$this->autoRender = false;
		$fic = fopen('../tmp/search/bulletin.txt', 'r');
		$classe = explode('|&|', trim(fgets($fic)));
		$des_annee_scolaire = trim(fgets($fic));
		$des_classe = $classe[0];
		$code_section = ($classe[1] != 'vide') ? $classe[1] : '';
		fclose($fic);

		$matiere_etudie = $this->Coefficient->getListMatiere_saisies($des_classe);
		//Liste promotionnelle
		$promos = $this->Promotion->list_promotion($des_classe, $code_section, $des_annee_scolaire);
		$note = $this->Note->getNotePromosByMat($des_classe, $code_section, $des_annee_scolaire) ;
		$rang= $this->Note->getRangePromosGenerale($des_classe, $code_section, $des_annee_scolaire);
		$m_classe = $this->Note->getMoyenneClassePromosGenerale($des_classe, $code_section, $des_annee_scolaire);

		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setAutoSize(true);

		if(!empty($note)) {
			$alphabet = 66;
					//Ligne matière
			foreach ($matiere_etudie as $matiere) {
				$objPHPExcel->getActiveSheet()->SetCellValue(chr($alphabet).'1', $matiere['Matiere']['libelle_mat']);
				$objPHPExcel->getActiveSheet()->getColumnDimension(chr($alphabet))->setAutoSize(true);
				$objPHPExcel->getActiveSheet()->getStyle(chr($alphabet).'1')->getAlignment()->setTextRotation(90);
				$alphabet++;
			}
			$objPHPExcel->getActiveSheet()->getStyle(chr($alphabet).'1')->getAlignment()->setTextRotation(90);
			$objPHPExcel->getActiveSheet()->SetCellValue(chr($alphabet++).'1', 'Total');
			$objPHPExcel->getActiveSheet()->getStyle(chr($alphabet).'1')->getAlignment()->setTextRotation(90);
			$objPHPExcel->getActiveSheet()->SetCellValue(chr($alphabet++).'1', 'Moyenne');
			$objPHPExcel->getActiveSheet()->getStyle(chr($alphabet).'1')->getAlignment()->setTextRotation(90);
			$objPHPExcel->getActiveSheet()->SetCellValue(chr($alphabet).'1', 'Rang');
				//2ème ligne
			$objPHPExcel->getActiveSheet()->SetCellValue('A2', 'COEFFICIENT');
			$alphabet = 66; $coff_tot = 0;
			foreach ($matiere_etudie as $matiere) {
				$objPHPExcel->getActiveSheet()->SetCellValue(chr($alphabet).'2', $matiere['Coefficient']['valeur']);
				$alphabet++;
				$coff_tot += $matiere['Coefficient']['valeur'];
			}
			$objPHPExcel->getActiveSheet()->SetCellValue(chr($alphabet).'2', $coff_tot);
					//Corps
//$this->set('bulletin', [$matiere_etudie,$rang, $promos, $note, $m_classe, $titre]);
			$rang_eleve = 0; $T=0;$execo=-200; $tot_execo = 0;
		    $i=3;
			foreach ($rang as $num =>$moyenne) {
				$total = 0;
				$objPHPExcel->getActiveSheet()->SetCellValue('A'.$i, $promos[$num][0]);
				$alphabet = 66;
				foreach ($matiere_etudie as $matiere) {
					$nbr = 0;
					foreach ($note as $not) {
						if ($not['notes']['num_matricule']==$num && $not['notes']['code_mat'] == $matiere['Matiere']['code_mat'])
						  {
						 	$objPHPExcel->getActiveSheet()->SetCellValue(chr($alphabet++).$i,
						 		(is_numeric($not[0]['note_obt']) ? number_format($not[0]['note_obt'], 2) : $not[0]['note_obt']));
						 	$nbr++;
						 	$total += is_numeric($not[0]['note_obt']) ? $not[0]['note_obt'] : 0; 
						 	$T += $total;
						 }
					}
					//on remplie la matière non saisie
					if($nbr==0)$alphabet++;
				}

				$objPHPExcel->getActiveSheet()->SetCellValue(chr($alphabet++).($i), number_format($total, 2));
				$moyenne = number_format($moyenne, 2);
				//si la moyenne précedente est égale à la suivante, on incrémente pas pour avoir execo;
				if($execo  == $moyenne) $tot_execo++;
				else {$rang_eleve += $tot_execo+1; $execo=$moyenne ; $tot_execo = 0;}

				$objPHPExcel->getActiveSheet()->SetCellValue(chr($alphabet++).($i), number_format($moyenne, 2));
				$objPHPExcel->getActiveSheet()->SetCellValue(chr($alphabet++).($i), $rang_eleve);
				$i++;
			}
			$objPHPExcel->getActiveSheet()->SetCellValue('A'.(++$i), 'Moyenne de classe');
			$alphabet = 66;
			foreach ($matiere_etudie as $matiere) {
						$nbr = 0;
				foreach ($m_classe as $key => $c_val) {
					if($matiere['Matiere']['code_mat']==$key) {
					$objPHPExcel->getActiveSheet()->SetCellValue(chr($alphabet++).($i), 
						is_numeric($c_val) ? number_format($c_val, 2) : $c_val);
					$nbr++;	
					}
				}
			if($nbr==0)$alphabet++;
			}
			$objPHPExcel->getActiveSheet()->SetCellValue(chr($alphabet++).(--$i), number_format($T, 2));
			$objPHPExcel->getActiveSheet()->SetCellValue(chr($alphabet++).(++$i), 
				(sizeof($rang)>0) ? number_format(array_sum($rang)/sizeof($rang), 2): 0);

		}
		//Les matières et coefficients(entêtes)
		
	$filename = 'Moyenne générale de la classe '.$des_classe.' '.$code_section.'_'.$des_annee_scolaire.'.xlsx';
	header('Content-type: application/vnd.ms-excel');
	header('Content-Disposition: attachment; filename="'.$filename.'"');
	$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
	$objWriter->save('php://output');
	}
	
}
?>