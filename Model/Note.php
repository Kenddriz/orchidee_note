<?php 
class Note extends AppModel {
	public $validate = [
		'num_matricule'=>array(
			'rule1'=>array(
				'rule'=>'/^[0-9]{1,7}[GF]{1}$/', 
				'message'=>'N°Matricule à verifier!'
				)
				),
		'code_mat' => array(
			'rule1' => array(
				'rule' => '/^MAT[0-9]{3}$/',
				'message' => 'Code matière à verifier!'
				)
				),
		"num_exam" => array(
		    'rule1' => array(
		        'rule' => array('between', 1, 18),
		        'message' => '1<= Numéro d\'examen <= 99]',
		         )
				),
		'des_annee_scolaire'=> array(
			'rule1' => array(
				'rule'=>'/^[0-9]{4}[-]{1}[0-9]{4}$/',
				'message'=>'Année scolaire à verifier!'
				)
				)

		];
	public function non_valider($col=2) {
		$erreurs_tab = array_values($this->validationErrors);
		$erreurs  = '';
		for($i  = 0; $i < sizeof($erreurs_tab); $i++)$erreurs.=$erreurs_tab[$i][0].' ';
		return ('<th colspan="'.$col.'" class="text-center text-danger bg-light small">'.$erreurs.'</th>');	
		}
	public function getNotePromos($classe, $section, $scolaire, $exam) {

		$lists = $this->query("SELECT notes.num_matricule,notes.code_mat,notes.note_obtenue, coefficients.valeur FROM coefficients, notes, promotions WHERE promotions.num_matricule=notes.num_matricule 
			AND promotions.num_matricule NOT IN(SELECT eleves.num_matricule FROM eleves WHERE eleves.flague = 1)
			AND promotions.des_annee_scolaire =notes.des_annee_scolaire
			AND notes.des_annee_scolaire = '".$scolaire."' 
			AND notes.code_mat=coefficients.code_mat 
			AND notes.num_exam='".$exam."' 
			AND promotions.des_classe = coefficients.des_classe 
			AND promotions.des_classe='".$classe."' 
			AND promotions.code_section='".$section."' 
			GROUP BY notes.code_mat,notes.num_matricule 
			ORDER BY notes.code_mat ASC");

		$conduite = array('-100'=>'A', '-200'=>'B', '-300'=>'C');
		$array_list = array(); $i = 0;

		foreach ($lists as $list) {
			$note_obtenue = $list['notes']['note_obtenue'];
			if(is_null($list['coefficients']['valeur'])) {
				$note_obtenue = (array_key_exists($note_obtenue, $conduite)) ? 
								$conduite[$note_obtenue] : $note_obtenue;
			}
			else {
				$note_obtenue *= $list['coefficients']['valeur'];
				$note_obtenue = number_format($note_obtenue, 2);
				 }
			$array_list[$i++] = array(
				'Note'=>array(
					'num_matricule'=>$list['notes']['num_matricule'],
					'code_mat'=>$list['notes']['code_mat'],
					'note_obtenue'=>$note_obtenue
				)
			) ;
		}
		return $array_list;
	}
	//Moyenne classe
	public function getMoyenneClassePromos($classe, $section, $scolaire, $exam) {
		$note = $this->getNotePromos($classe, $section, $scolaire, $exam);
		$array_mat = $this->getNombreMatiere1Exam($classe, $section, $scolaire,$exam);
		$tab = array("A"=>-100, "B"=>-200, "C"=>-300);
		$m_classe = array();
		$type = 0;
		foreach ($note as $val) {
			$key = $val['Note']['code_mat'];
			if(!array_key_exists(trim($val['Note']['note_obtenue']), $tab)) {
				if(array_key_exists(trim($key), $m_classe)){
					$m_classe[$key]+=floatval($val['Note']['note_obtenue']/$array_mat[$key]);
					$m_classe[$key] = $m_classe[$key];
				}
				else {
					$m_classe[$key]=floatval($val['Note']['note_obtenue']/$array_mat[$key]);
					$m_classe[$key] = $m_classe[$key];
				}
			}
			else {
					if(array_key_exists($key, $m_classe))$m_classe[$key] += $tab[$val['Note']['note_obtenue']];
					else $m_classe[$key] = $tab[$val['Note']['note_obtenue']];
					$type = 1;//bonus
				}
		}
		//conduite
		if($type == 1) {
					$m_classe['MAT997'] = round($m_classe['MAT997']/($array_mat['MAT997'] * 100), 0, PHP_ROUND_HALF_UP) * 100;
					//si la frequence est infrieure à -100
					$m_classe['MAT997'] = array_search($m_classe['MAT997'], $tab);
			}
		return $m_classe;
	}
	//Les élèves peuvent avoir une excuse,il aura son propre coefficient total
	public function getCoefficient1Exam($classe, $section, $scolaire, $exam) {
		$nbr = $this->query("SELECT SUM(coefficients.valeur) AS coeff, notes.num_matricule FROM promotions, notes, coefficients WHERE promotions.num_matricule=notes.num_matricule 
			AND promotions.num_matricule NOT IN(SELECT eleves.num_matricule FROM eleves WHERE eleves.flague = 1)
			AND promotions.des_annee_scolaire =notes.des_annee_scolaire
			AND promotions.des_classe=coefficients.des_classe 
			AND promotions.code_section='".$section."' 
			AND promotions.des_classe='".$classe."' 
			AND notes.des_annee_scolaire='".$scolaire."' 
			AND coefficients.code_mat=notes.code_mat 
			AND notes.num_exam='".$exam."' 
			GROUP BY notes.num_matricule");
		$arry_exam_nbr = array();
		foreach ($nbr as $val) {
			$arry_exam_nbr[$val['notes']['num_matricule']]=$val[0]['coeff'];
		}
		return $arry_exam_nbr;	
	}
	public function getRangePromos($classe, $section, $scolaire, $exam) {

		$rang = $this->query("SELECT notes.num_matricule,SUM(notes.note_obtenue*coefficients.valeur) AS total FROM coefficients, notes, promotions WHERE promotions.num_matricule=notes.num_matricule
			AND promotions.num_matricule NOT IN(SELECT eleves.num_matricule FROM eleves WHERE eleves.flague = 1)
			AND promotions.des_annee_scolaire =notes.des_annee_scolaire
		    AND notes.des_annee_scolaire = '".$scolaire."' 
		    AND notes.code_mat=coefficients.code_mat 
		    AND notes.num_exam='".$exam."' 
		    AND promotions.des_classe = coefficients.des_classe 
		    AND promotions.des_classe='".$classe."' 
		    AND promotions.code_section='".$section."' 
		    GROUP BY notes.num_matricule");
		//coefficient pour chaque élève
		$tab = array('-100'=>'A', '-200'=>'B', '-300'=>'C');
		$conduite=$this->getConduite1Exam($classe, $section, $scolaire, $exam);
		$coeff=$this->getCoefficient1Exam($classe, $section, $scolaire, $exam);
		$array_rang = array();
		foreach ($rang as $val) {
			$total = $val[0]['total'];
			if(array_key_exists($val['notes']['num_matricule'], $conduite))
				$total += (array_key_exists($conduite[$val['notes']['num_matricule']], $tab)? 0 : $conduite[$val['notes']['num_matricule']]);
			$array_rang[$val['notes']['num_matricule']] = ($coeff[$val['notes']['num_matricule']] > 0) ?
				$total/$coeff[$val['notes']['num_matricule']] : $total;
		}
		arsort($array_rang);
		return($array_rang);
	}

	public function updater($note, $matricule, $matiere, $examen, $anneescolaire) {
		return ($this->updateAll(
				['note_obtenue' => "'".floatval($note)."'"],
				[
					'num_matricule' => $matricule,
					'code_mat' => $matiere,
					'num_exam'=> $examen,
					'des_annee_scolaire' => $anneescolaire
				]
			));
	}
	public function getNotePromosByMat($classe, $section, $scolaire) {
		$list_note = $this->query("SELECT notes.num_matricule,notes.code_mat,SUM(notes.note_obtenue*coefficients.valeur) AS note_obt FROM coefficients, notes, promotions 
			WHERE promotions.num_matricule=notes.num_matricule 
			AND promotions.num_matricule NOT IN(SELECT eleves.num_matricule FROM eleves WHERE eleves.flague = 1)
			AND promotions.des_annee_scolaire =notes.des_annee_scolaire
			AND notes.des_annee_scolaire = '".$scolaire."' 
			AND notes.code_mat=coefficients.code_mat 
			AND promotions.des_classe = coefficients.des_classe 
			AND promotions.des_classe='".$classe."' 
			AND promotions.code_section='".$section."' 
			GROUP BY notes.code_mat,notes.num_matricule 
			ORDER BY notes.code_mat ASC");
		//chaque élève aura ses propres coefficients
		$tab = array('-100'=>'A', '-200'=>'B', '-300'=>'C');
		//conduite sans coefficient
		$conduite = $this->getConduiteGenerale($classe, $section, $scolaire);
		$nbr_exam = $this->getNombreExam($classe, $section, $scolaire);
		$array_note = array();
		$i=0;
		foreach ($list_note as $note) {
			$key = $note['notes']['num_matricule'].$note['notes']['code_mat'];
			if(array_key_exists($key, $nbr_exam)) {
				$note_obtenue = number_format($note[0]['note_obt']/$nbr_exam[$key], 2);
				if(array_key_exists($key, $conduite)) {
					//pour les bonnus ou malus
					$note_obtenue = number_format($conduite[$key]/$nbr_exam[$key], 2);
					if($note_obtenue <= -100) {//bonus ou malus aannuel ne peut pas être <=100 normalement
						$note_obtenue = round($conduite[$key]/100, 0, PHP_ROUND_HALF_UP) * 100;
						$note_obtenue  = $tab[$note_obtenue];
					}
				}

				$list_note[$i++][0]['note_obt']= $note_obtenue;
			}
		}
		
		return $list_note;
	}
	public function getRangePromosGenerale($classe, $section, $scolaire) {
		//On recupère les moyennes annuels pour chaque matière
		$global = $this->getNotePromosByMat($classe, $section, $scolaire);
		//On récupère les coefficients correspondant à un élève
		$coeff = $this->getCoefficientparExamEtEleve($classe, $section, $scolaire);
		$tab1 = array('A'=>'', 'B'=>'', 'C'=>'');
		$tab2 = array('MAT998'=>'', 'MAT999'=>'');
		$array_moyenne = array();
		foreach ($global as $val) {
			$key = $val['notes']['num_matricule'];
			$ok = (!array_key_exists($val[0]['note_obt'], $tab1) && !array_key_exists($val['notes']['code_mat'], $tab2));
			$note_obt = ($ok) ? $val[0]['note_obt'] : 0;
				if(array_key_exists($key, $array_moyenne)) {
					$array_moyenne[$key]+=$note_obt/(($coeff[$key] > 0) ? $coeff[$key] : 1);
				}
				else $array_moyenne[$key]=($coeff[$key] > 0) ? $note_obt/$coeff[$key] : $note_obt;

			
		}
		arsort($array_moyenne);
		return $array_moyenne;
	}
	//Nombre examen
	public function getNombreExam($classe, $section, $scolaire) {
		$nbr = $this->query("SELECT notes.num_matricule,notes.code_mat,count(notes.code_mat) AS nbr FROM promotions, coefficients,notes 
			WHERE coefficients.code_mat=notes.code_mat 
			AND coefficients.des_classe='".$classe."' 
			AND promotions.des_classe=coefficients.des_classe 
			AND promotions.code_section='".$section."' 
			AND promotions.num_matricule=notes.num_matricule 
			AND promotions.num_matricule NOT IN(SELECT eleves.num_matricule FROM eleves WHERE eleves.flague = 1)
			AND promotions.des_annee_scolaire =notes.des_annee_scolaire
			AND notes.des_annee_scolaire='".$scolaire."' 
			GROUP BY notes.code_mat, notes.num_matricule 
			ORDER BY notes.code_mat");
		$arry_exam_nbr = array();
		foreach ($nbr as $val) {
			$key = $val['notes']['num_matricule'].$val['notes']['code_mat'];
			$arry_exam_nbr[$key]=$val[0]['nbr'];
		}
		return $arry_exam_nbr;
	}
	//Nombre de note d'une matière annuelle
	public function getNombreMatiere1Exam($classe, $section, $scolaire,$exam) {
		$nbr = $this->query("SELECT notes.code_mat,count(DISTINCT notes.num_matricule) AS nbr FROM promotions,coefficients, notes 
			WHERE coefficients.code_mat=notes.code_mat 
			AND promotions.des_classe=coefficients.des_classe 
			AND promotions.num_matricule=notes.num_matricule 
			AND promotions.num_matricule NOT IN(SELECT eleves.num_matricule FROM eleves WHERE eleves.flague = 1)
			AND promotions.des_annee_scolaire =notes.des_annee_scolaire
			AND promotions.code_section='".$section."' 
			AND coefficients.des_classe='".$classe."' 
			AND notes.num_exam='".$exam."' 
			AND notes.des_annee_scolaire='".$scolaire."' 
			GROUP BY notes.code_mat 
			ORDER BY notes.code_mat");
		$arry_candidat_nbr = array();
		$i = 0;
		foreach ($nbr as $val) {
			$arry_candidat_nbr[$val['notes']['code_mat']]=$val[0]['nbr'];
		}
		return $arry_candidat_nbr;
	}
	//Coefficient total par élève à la fin d'année
	public function getCoefficientparExamEtEleve($classe, $section, $scolaire) {
		$coeff = $this->query("SELECT notes.num_matricule,SUM(coefficients.valeur) AS coeff, COUNT(notes.code_mat) AS nbr FROM promotions,coefficients,notes 
			WHERE coefficients.code_mat=notes.code_mat 
			AND promotions.des_classe = coefficients.des_classe 
			AND promotions.num_matricule = notes.num_matricule
			AND promotions.num_matricule NOT IN(SELECT eleves.num_matricule FROM eleves WHERE eleves.flague = 1)
			AND promotions.des_annee_scolaire =notes.des_annee_scolaire
			AND promotions.code_section='".$section."' 
			AND coefficients.des_classe='".$classe."' 
			AND notes.des_annee_scolaire='".$scolaire."' GROUP 
			BY notes.num_exam, notes.num_matricule ORDER BY notes.code_mat");
		$array_coeff = array();
		$array_verif = array();
		foreach ($coeff as $val) {
			$key = $val['notes']['num_matricule'];
			if(array_key_exists($key, $array_coeff) && array_key_exists($key, $array_verif)) {
				if($array_verif[$key] < $val[0]['nbr']) {
					$array_coeff[$key] = $val[0]['coeff'];
					$array_verif[$key] = $val[0]['nbr']; 
				}
			}
			else {
					$array_coeff[$key] = $val[0]['coeff'];
					$array_verif[$key] = $val[0]['nbr']; 	
			}
			# code...
		}
		return $array_coeff;
	}
	public function getMoyenneClassePromosGenerale($classe, $section, $scolaire) {
		$note = $this->getNotePromosByMat($classe, $section, $scolaire);
		$array_mat = $this->getNombreMatiereFinale($classe, $section, $scolaire);
		$tab = array("A"=>-100, "B"=>-200, "C"=>-300);
		$m_classe = array();
		$type = 0 ;//type de conduite:bonus/ matières
		foreach ($note as $val) {
			$key = $val['notes']['code_mat'];
			if(!array_key_exists(trim($val[0]['note_obt']), $tab)) {
				if(array_key_exists(trim($key), $m_classe)){
					$m_classe[$key]+=floatval($val[0]['note_obt']/$array_mat[$key]);
					$m_classe[$key] = $m_classe[$key];
				}
				else {
					$m_classe[$key]=floatval($val[0]['note_obt']/$array_mat[$key]);
					$m_classe[$key] = $m_classe[$key];
				}
			}
			else {
					if(array_key_exists($key, $m_classe))$m_classe[$key] += $tab[$val[0]['note_obt']];
					else $m_classe[$key] = $tab[$val[0]['note_obt']];
					$type = 1;//bonus
				}
		}
		//conduite
		if($type == 1) {
					$m_classe['MAT997'] = round($m_classe['MAT997']/($array_mat['MAT997'] * 100), 0, PHP_ROUND_HALF_UP) * 100;
					//si la frequence est infrieure à -100
					$m_classe['MAT997'] = array_search($m_classe['MAT997'], $tab);
			}
		return $m_classe;
	}
	public function getNombreMatiereFinale($classe, $section, $scolaire) {
		$nbr = $this->query("SELECT notes.code_mat,count(DISTINCT notes.num_matricule) AS nbr FROM promotions, coefficients,notes 
			WHERE coefficients.code_mat=notes.code_mat 
			AND coefficients.des_classe='".$classe."' 
			AND promotions.des_classe=coefficients.des_classe 
			AND promotions.code_section='".$section."' 
			AND promotions.num_matricule=notes.num_matricule 
			AND promotions.num_matricule NOT IN(SELECT eleves.num_matricule FROM eleves WHERE eleves.flague = 1)
			AND promotions.des_annee_scolaire =notes.des_annee_scolaire
			AND notes.des_annee_scolaire='".$scolaire."' 
			GROUP BY notes.code_mat 
			ORDER BY notes.code_mat");
		$arry_candidat_nbr = array();
		foreach ($nbr as $val) {
			$arry_candidat_nbr[$val['notes']['code_mat']]=$val[0]['nbr'];
		}
		return $arry_candidat_nbr;
	}
  //Recupération de conduite dans un examen
	public function getConduite1Exam($classe, $section, $scolaire, $exam) {

		$lists = $this->query("SELECT notes.num_matricule, notes.note_obtenue FROM notes, coefficients, promotions WHERE promotions.num_matricule=notes.num_matricule 
			AND promotions.num_matricule NOT IN(SELECT eleves.num_matricule FROM eleves WHERE eleves.flague = 1)
			AND promotions.des_annee_scolaire =notes.des_annee_scolaire
			AND promotions.des_classe='".$classe."' 
			AND promotions.code_section='".$section."' 
			AND promotions.des_classe=coefficients.des_classe 
			AND coefficients.code_mat=notes.code_mat AND coefficients.valeur IS NULL 
			AND notes.num_exam='".$exam."' 
			AND notes.code_mat = 'MAT997'
			AND notes.des_annee_scolaire='".$scolaire."'");
		$array_list = array();
		foreach ($lists as $list)
			$array_list[$list['notes']['num_matricule']]=$list['notes']['note_obtenue'];
		return $array_list;
	} 
	public function getConduiteGenerale($classe, $section, $scolaire) {

		$lists = $this->query("SELECT notes.num_matricule, SUM(notes.note_obtenue) AS cond,notes.code_mat FROM notes, coefficients, promotions WHERE promotions.num_matricule=notes.num_matricule 
			AND promotions.num_matricule NOT IN(SELECT eleves.num_matricule FROM eleves WHERE eleves.flague = 1)
			AND promotions.des_annee_scolaire =notes.des_annee_scolaire
			AND promotions.des_classe='".$classe."' 
			AND promotions.code_section='".$section."' 
			AND promotions.des_classe=coefficients.des_classe 
			AND coefficients.code_mat=notes.code_mat AND coefficients.valeur IS NULL 
		    AND notes.code_mat IN ('MAT997', 'MAT998', 'MAT999')
			AND notes.des_annee_scolaire='".$scolaire."' GROUP BY notes.code_mat,notes.num_matricule");
		$array_list = array();
		foreach ($lists as $list)
			$array_list[$list['notes']['num_matricule'].$list['notes']['code_mat']] = $list[0]['cond'];
		return $array_list;

	}
	//SOmme conduite annuelle
	public function getConduiteClasseGenerale($classe, $section, $scolaire) {

		$lists = $this->query("SELECT SUM(notes.note_obtenue) AS cond FROM notes, coefficients, promotions WHERE promotions.num_matricule=notes.num_matricule 
			AND promotions.num_matricule NOT IN(SELECT eleves.num_matricule FROM eleves WHERE eleves.flague = 1)
			AND promotions.des_annee_scolaire =notes.des_annee_scolaire
			AND promotions.des_classe='".$classe."' 
			AND promotions.code_section='".$section."' 
			AND promotions.des_classe=coefficients.des_classe 
			AND coefficients.code_mat=notes.code_mat AND coefficients.valeur=NULL 
		    AND notes.code_mat='MAT999' AND notes.des_annee_scolaire='".$scolaire."'
		    AND notes.note_obtenue < 20 GROUP BY notes.code_mat");
		$array_list = array();
		foreach ($lists as $list)
			$array_list['MAT999'] = $list[0]['cond'];
		return $array_list;

	}
public function ajout($note_obtenue, $matricule, $matiere, $examen, $anneescolaire) {

		$not_exist = empty($this->find('first', [
			'conditions'=> [
				'Note.num_matricule'=>$matricule,
				'Note.code_mat' => $matiere,
				'Note.num_exam'=> $examen,
				'Note.des_annee_scolaire' => $anneescolaire,
			]
		]));

		if($this->validates()) {
			$this->create([
				'num_matricule' => $matricule,
				'code_mat' => $matiere,
				'num_exam' => $examen,
				'des_annee_scolaire' => $anneescolaire,
				'note_obtenue' => floatval(number_format($note_obtenue, 2))
			]);
			if($not_exist){ 
				if($this->save()) return 1; 
				else return ('<th class="text-center text-danger bg-light small">Echec</th>');
			}
			else return $this->updater($note_obtenue, $matricule, $matiere, $examen, $anneescolaire);

		}
		else return ($this->non_valider(4));
}
}
?>