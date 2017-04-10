<?php 

try
{

	define('SERVER_ROOT', realpath('../').DIRECTORY_SEPARATOR);
	define('CLIENT_ROOT', str_replace($_SERVER['DOCUMENT_ROOT'], '', str_replace('\\', '/', SERVER_ROOT)));
	include SERVER_ROOT.'application/configuration.php';
	//contient les constantes à utiliser pour la connexion à la BDD
	header('content-type: text/html; charset=utf-8');

	//auto-chargement
	spl_autoload_register(function($className)
		{
			if(file_exists(SERVER_ROOT.'application/managers/'.$className.'.php'))
			{
				include SERVER_ROOT.'application/managers/'.$className.'.php';
			}

			elseif(file_exists(SERVER_ROOT.'application/controllers/'.$className.'.php'))
			{
				include SERVER_ROOT.'application/controllers/'.$className.'.php';
			}

			elseif(file_exists(SERVER_ROOT.'core/'.$className.'.php'))
			{
				include SERVER_ROOT.'core/'.$className.'.php';
			}
			else
			{
				throw new Exception ('La classe <strong>'.$className.'</strong> ne peut être trouvée.');
			}
		});


	//	Définition du nom du contrôleur
	$controllerName = array_key_exists('controller', $_GET) ? $_GET['controller'] : DEFAULT_CONTROLLER_NAME;
	$controllerClassName = ucfirst($controllerName).'Controller';

	// Vérification de l'existence du contrôleur
	if (class_exists($controllerClassName))
	{
		$controller = new $controllerClassName();
		//	Définition du nom de l'action
		$actionName = array_key_exists('action', $_GET) ? $_GET['action'] : DEFAULT_ACTION_NAME;
		$actionMethodName = $actionName.'Action';
		// Vérification de l'existence de l'action : soit method_exists (y compris private), soit is_callable (existe + peut être appelée)
		if (method_exists($controller, $actionMethodName))
		{
			$controller -> $actionMethodName();
		}
		else
		{
			throw new Exception ('L\'action <strong>'.$actionMethodName.'</strong> n\'existe pas dans le contrôleur <strong>'.$controllerClassName.'</strong>.'); 
		}
	}

	else
	{
		throw new Exception ('Le contrôleur <strong>'.$controllerClassName.'</strong> n\'existe pas.'); 
	}
}

catch(Exception $exception)
{
	var_dump($exception);
	echo '<h1>Erreur</h1>';
		echo '<h2>Message</h2>';
		echo $exception->getMessage();
		echo '<h2>Fichier et ligne</h2>';
		echo '<em>'.$exception->getFile().' : '.$exception->getLine().'</em>';
		echo '<h2>Informations complémentaires</h2>';
		var_dump($exception->getTrace());
}

 ?>