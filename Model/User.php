<?php

	class User extends AppModel
	{	

		 //public $primaryKey = "username";
		/*By default, cakephp use id as primary key, no filds shoul have this name in modale*/

		public $validate = array(
			"username" => array(
				"rule" => "/^[a-z0-9ç]{3,15}$/i",
				"required" => true,
				"allowEmpty" => false,
				//"on" => "create",
				"message" => "Longueur: [3 - 15]/Pas de caractères spéciaux"
			),
			/*"nom" => array(
				"rule" => array("between", 2, 60),
				"required" => false,
				"allowEmpty" => false,
				"message" => "Longueur: [2 - 60]"
			),
			"prenom" => array(
				"rule" => array("between", 0, 35),
				"required" => false,
				"allowEmpty" => true,
				"message" => "Longueur: [3 - 35]"
			),*/
			"password" =>array(
				"rule" => array("between", 3, 255),
				"required" => true,
				"allowEmpty" => false,
				"message" => "Longueur: [3 - 15]"		
			),
			'password_confirm'=> array(
        			'rule' => 'equaltofield',
        			'message' => "Confirmation erronnée"
				)
		);
		function equaltofield(){
			return $this->data[$this->name]['password'] === $this->data[$this->name]['password_confirm'];
			}
	}
?>