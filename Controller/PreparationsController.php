<?php
class PreparationsController extends AppController {

	public $uses = array('Niveau', 'Anneescolaire', 'Classer', 'Section', 'Examen');
	public function index() {
		$this->layout = 'gestion';
		//Valeur par défaut
		$niveau  = $this->Niveau->getListNiveau();
		$annee_scolaire = $this->Anneescolaire->getListAnneescolaire();
		$classe = $this->Classer->getListClasse();
		$section = $this->Section->find('all');
		$examen = $this->Examen->getListExam();
		$this->set('preparation', [$niveau, $annee_scolaire, $classe, $section, $examen]);
		$this->set('couleur', ['text-primary', 'text-secondary', 'text-danger', 'text-success', 'text-warning']);
	}
}
?>