<?php
	App::uses('AppController', 'Controller');

	class UsersController extends AppController {
		public $uses = array('User','Student');
		//Page d'acceuil
		public function index() {}

		function beforeFilter() {
			parent::beforeFilter();
			$this->Auth->allow();
		}
		public function login() {
			if(!empty($this->request->data)) {
				if($this->Auth->login())$this->redirect("/Eleves/");
				else $this->set('msg','Vérifier votre login...');						
			}

		}

		public function logout() {
			$this->Auth->logout();
			$this->redirect("/");
		}
		//page d'inscription
		public function inscription() {

			if($this->request->is('post')) {
				if(empty($this->User->findByUsername($this->request->data["User"]["username"])))
				{	
					$this->User->create($this->request->data);
					//supprimer la confirmation de mot de passe
					//unset($this->User->validate['password_confirm']);
					if($this->User->validates()) {
						unset($this->User->validate['password_confirm']);
						$this->User->save(array(
							"username" => $this->request->data["User"]["username"],
							/*"nom" => $this->request->data["User"]["nom"],
							"prenom" => $this->request->data["User"]["prenom"],*/
							"password" => $this->Auth->password($this->request->data["User"]["password"])
							));	
							$this->set("add_msg", "<label style=\"color:#28a745\">Succès! Attendre la validation.</label>");	
							header('Refresh: 2;');					
					}
					else $this->set("add_msg", "<label style=\"color: #dc3545\">Echec! Verifier les champs.</label>");
				}
				else $this->set("add_msg", "<label style=\"color: #17a2b8\">Ce nom d'utilisateur existe.</label>");
			}
		}

		//Compte d'utilisateur
		public function compte() {
			$this->layout = "gestion";

			if($this->request->is('post')) {
				//Initialiser les données
				$this->User->create($this->request->data);

				/*switch (array_keys($this->request->data['User'])[0]) {
					case 'password':*/													
						unset($this->User->validate['username']);
						//unset($this->User->validate['nom']);
						//unset($this->User->validate['prenom']);
						if($this->User->validates()) {

							if($this->User->updateAll(
								array('password' => "'".$this->Auth->password($this->request->data['User']['password'])."'"),
								array('username' => $this->Auth->user('username'))
							))$this->set("upate_message", "<label style=\"color: #28a745;\">Mise à jour effectuée!</label>");
							else $this->set("upate_message", "<label style=\"color: #dc3545;\">Mis à jour impossible</label>");
							header('Refresh: 2;');				
						}

						/*break;
					default:
						unset($this->User->validate['username']);
						unset($this->User->validate['password']);
						unset($this->User->validate['password_confirm']);
						$this->request->data['User']['nom'].= ' '.$this->request->data['User']['prenom'];
						$this->User->primaryKey = 'username';
						if($this->User->validates()) {

							if($this->User->updateAll(
								array("nom" => "'".$this->request->data['User']['nom']."'"),
								array('username' => $this->Auth->user('username'))
							))$this->set("upate_message", "<label style=\"color: #28a745;\">Mis à jour effectuée!</label>");
							else $this->set("upate_message", "<label style=\"color: #dc3545;\">Mis à jour impossible</label>");					
						}
						header('Refresh: 2;');
						break;						
				}
			}
			//Récuperer l'identité 
			$IDENTITE = $this->User->find('first',array(
					'fields' => array('User.username'),
					'conditions' => array('User.username' => $this->Auth->user('username'))));
			$IDENTITE['User']['prenom'] = "";
			$nomComplet = explode(' ', $IDENTITE['User']['nom']);

			if(sizeof($nomComplet) > 1) {
				for ($i=1; $i < sizeof($nomComplet); $i++)
					$IDENTITE['User']['prenom'] .= ' '.$nomComplet[$i];
			}

			$IDENTITE['User']['nom'] = $nomComplet[0];
			$this->set(compact('IDENTITE'));*/
			}
		}

		public function liste_compte() {
			$this->layout = "gestion";
			//Accessible par admin seulement
			if($this->Auth->user('username') != 'admin')$this->redirect('/');
			
			if(isset($this->request->query['username']))
			 	{

				if(isset($this->request->query['action'])) 
				{
					$this->User->updateAll(
						["active" => "'".$this->request->query['action']."'"],
						['username' => $this->request->query['username']]
							);
				}
			}
			else if(!empty($this->request->pass[0]))
			{
				$this->User->primaryKey = 'username';
				$this->User->deleteAll(array('User.username'=>$this->request->pass[0]));
			   $this->redirect('/Users/liste_compte');
			}
			//La paginnation

		    $list = $this->User->find('all', array(
		    	'fields' => ['User.username', 'User.active'],
		    	'conditions' => ['User.username NOT' => 'admin'],
		        'order' => ['User.username' => 'ASC']
		     ));
		    $this->set('actives_users', [$list, ["text-success", "text-secondary", "text-dark", "text-primary", "text-danger", "text-warning"]]);
		    $this->rechercherCorbeille();

		}

		public function rechercherCorbeille() {

			if($this->request->is('post')) {
				$search = str_replace('/', '', $this->request->data['search']);
				$fic = fopen('../tmp/search/section.txt', 'w');
				fputs($fic, '%'.$search.'%');
				fclose($fic);
					}
				$fic = fopen('../tmp/search/section.txt', 'r');
				$search = fgets($fic);
				$this->paginate = [
					'limit' => 5,
					'fields' => array('Student.num_matricule', 'Student.nom'),
					'conditions' => [
						'Student.flague' => 1,
						'or'=>[
							'Student.num_matricule LIKE' => $search,
							'Student.nom LIKE' => $search,
						]
					],
			        'order' => ['Student.num_matricule' => 'ASC']
				];
				fclose($fic);
				$this->set('recherche', $this->paginate('Student'));
		}

		}
?>