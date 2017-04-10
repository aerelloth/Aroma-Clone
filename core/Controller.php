<?php 

abstract class Controller
{
	protected $viewData = [];
	public function generateView($viewPath, $templatePath=DEFAULT_TEMPLATE_PATH)
	{
		if (session_status() !== PHP_SESSION_ACTIVE)
			{
				session_start();
				session_regenerate_id();
			}
		//	var_dump($_SESSION);

		//	S'il y a des messages d'alerte
			if(array_key_exists('alertMessages', $_SESSION))
			{
				//	Récupération des messages d'alerte
				$this -> viewData['alertMessages'] = $_SESSION['alertMessages'];
				//	Suppression des messages d'alerte
				unset($_SESSION['alertMessages']);
			}

		$view = new View($viewPath, $this -> viewData, $templatePath);
		$view -> generate();
	}
}


 ?>