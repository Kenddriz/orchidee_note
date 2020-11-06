<?php

class Promotion extends AppModel
{
	public $validate = [
		'num_matricule'=>['rule'=>'/^[0-9]{1,7}[GF]{1}$/', 'message'=>'Nombre entier naturel <=9999999'],
		'des_classe'=>['rule'=>array('between',1, 10), 'message'=>'sélection vide'],
		'annee_scolaire'=>['rule'=>'/^[0-9]{4}[-]{1}[0-9]{4}$/', 'message'=>'Norme: xxxx-xxxx'],
		'num_appel'=>['rule'=>'/^[0-9]{1,2}$/', 'message'=>'Taille: 1 - 99'],
		];
	public function list_promotion($classe, $section, $annee_scolaire) {
		$list = $this->find('all', [
			    'joins' => array(
			        array(
			            'table' => 'eleves',
			            'alias' => 'Student',
			            'type' => 'INNER',
			            'conditions' => array(
			                'Promotion.num_matricule=Student.num_matricule',
			                'Student.flague' => 0,
			                'Promotion.des_classe'=>$classe,
			                'Promotion.code_section'=>$section,
			                'Promotion.des_annee_scolaire'=>$annee_scolaire
			            )
			        )
			    ),
			    'fields' => array('Student.nom', 'Student.num_matricule', 'Promotion.num_appel'),
			    'order' => 'Promotion.num_appel ASC',
			]);
		$array_list = array();
		foreach ($list as $val)
			$array_list[$val['Student']['num_matricule']] = array($val['Student']['nom'], $val['Promotion']['num_appel']);
		return $array_list;
	}
	public function getNombrePromotion($classe, $section, $annee_scolaire) {
		return $this->find('count',[
				'joins' => array(
			        array(
			            'table' => 'eleves',
			            'alias' => 'Student',
			            'type' => 'INNER',
			            'conditions' => array(
			                'Promotion.num_matricule=Student.num_matricule',
			                'Student.flague' => 0,
			                'Promotion.des_classe'=>$classe,
			                'Promotion.code_section'=>$section,
			                'Promotion.des_annee_scolaire'=>$annee_scolaire
			            )
			        )
			    ),
		]);
	}
	public function nombre_par_sexe($classe, $section, $annee_scolaire) {
		$G = $this->find('count',[
				'joins' => array(
			        array(
			            'table' => 'eleves',
			            'alias' => 'Student',
			            'type' => 'INNER',
			            'conditions' => array(
			                'Promotion.num_matricule=Student.num_matricule',
			                'Student.flague' => 0,
			                'Student.sexe'=>'G',
			                'Promotion.des_classe'=>$classe,
			                'Promotion.code_section'=>$section,
			                'Promotion.des_annee_scolaire'=>$annee_scolaire
			            )
			        )
			    ),
		]);
		$F = $this->find('count',[
				'joins' => array(
			        array(
			            'table' => 'eleves',
			            'alias' => 'Student',
			            'type' => 'INNER',
			            'conditions' => array(
			                'Promotion.num_matricule=Student.num_matricule',
			                'Student.flague' => 0,
			                'Student.sexe'=>'F',
			                'Promotion.des_classe'=>$classe,
			                'Promotion.code_section'=>$section,
			                'Promotion.des_annee_scolaire'=>$annee_scolaire
			            )
			        )
			    ),
		]);
		return 'Garçon: '.$G. ', Fille: '.$F;
	}
	public function update_numero($num_matricule, $classe, $section, $annee_scolaire, $num_appel) {
		return($this->updateAll(
				['num_appel' => "'".$num_appel."'"],
				[
					'num_matricule'=> $num_matricule,
					'des_classe'=>$classe,
					'code_section'=> $section,
					'des_annee_scolaire'=> $annee_scolaire
				]
			));
	}
	public function liste_eleve_par_classe($annee_scolaire, $niv) {
		return(
			$this->query("SELECT sections.*, eleves.sexe, COUNT(eleves.sexe) AS nombre FROM niveaus, classers, sections, eleves, promotions WHERE niveaus.code_niv = classers.code_niv 
				AND niveaus.code_niv='".$niv."' 
				AND classers.des_classe = promotions.des_classe 
				AND sections.des_classe = classers.des_classe 
				AND sections.code_section = promotions.code_section 
				AND promotions.num_matricule = eleves.num_matricule 
				AND eleves.flague = 0 
				AND promotions.des_annee_scolaire = '".$annee_scolaire."' 
				GROUP BY niveaus.code_niv, classers.des_classe,sections.code_section, eleves.sexe")
		);
	}
	//Numéro appel auto
	public function produire_num_appel($classe, $section, $annee_scolaire) {
		$num = $this->query("SELECT MAX(promotions.num_appel) AS num from promotions 
			WHERE promotions.des_classe='".$classe."' 
			AND promotions.code_section='".$section."'
			AND promotions.des_annee_scolaire = '".$annee_scolaire."'");
		return ($num[0][0]['num'] + 1);
	}

}
?>