<?php 

class CustomersController extends Controller
{
	//affichage admin des données d'un client et de ses commandes
	public function showOneAction()
	{
		//vérification du statut d'admin
		$customersManager = new CustomersManager();
		$customer = $customersManager -> getLoginInfo();
		if (empty($customer) OR $customer['email'] != ADMIN_EMAIL)
		{
			header('Location:'.CLIENT_ROOT.'customers/account');
			exit();
		}

		//sécurisation CSRF
		if(!array_key_exists('CSRFToken', $customer) OR !array_key_exists('CSRFToken', $_GET) OR $_GET['CSRFToken'] != $customer['CSRFToken'])
		{
			header('Location:'.CLIENT_ROOT.'customers/account');
            exit();
		}
		$CSRFToken = $customer['CSRFToken'];

		//récupération des infos client
		$id = $_GET['id'];
		$customer = $customersManager -> getOneById($id);
		unset($customer['passwordHash']);

		//récupération des commandes du client (avec pagination)
		$ordersManager = new OrdersManager();
		$currentPage = array_key_exists('page', $_GET)? $_GET['page'] : 1;
		$numberByPage = ORDERS_BY_PAGE;
		$paginationStart = array_key_exists('page', $_GET)? ($_GET['page']-1)*$numberByPage : 0;
		$paginationStartShowed = array_key_exists('page', $_GET)? $paginationStart+1 : 1;
		$ordersNumber = $ordersManager -> countByCustomerId($id);
		$pagesNumber = ceil($ordersNumber / $numberByPage);
		$paginationEnd = $paginationStart + $numberByPage > $ordersNumber? $ordersNumber : $paginationStart + $numberByPage;
		$orders = $ordersManager -> getPageByCustomerId($id, $paginationStart, $numberByPage);

		//récupération des lignes de commande pour chaque commande affichée
		$ordersNumberOnPage = count($orders);
		$orderLinesManager = new OrderLinesManager();
		for ($i=0; $i < $ordersNumberOnPage; $i++) { 
			$idOrder = $orders[$i]['orderId'];
			$orders[$i]['orderLines'] = $orderLinesManager -> getByIdOrder($idOrder);
			$orders[$i]['totalAmount'] = 0;
			$orderLinesNumber = count($orders[$i]['orderLines']);
			for ($j=0; $j < $orderLinesNumber; $j++) { 
				$orders[$i]['orderLines'][$j]['subTotal'] = $orders[$i]['orderLines'][$j]['TTCPrice']*$orders[$i]['orderLines'][$j]['quantity'];
				$orders[$i]['totalAmount'] += $orders[$i]['orderLines'][$j]['subTotal'];
			}
		}

		$this->viewData['pagination'] = $pagination = 
		[
			'currentPage' => $currentPage,
			'paginationStart' => $paginationStart,
			'paginationStartShowed' => $paginationStartShowed,
			'numberByPage' => $numberByPage,
			'ordersNumber' => $ordersNumber,
			'pagesNumber' => $pagesNumber,
			'paginationEnd' => $paginationEnd,
			'orders' => $orders
		];

		//vérification du numéro de page
		if ($pagination['currentPage'] < 1 OR $pagination['currentPage'] > $pagination['pagesNumber']) 
		{
			header('Location: customers/showOne?id='.$id.'&CSRFToken='.$CSRFToken);
		}

		//récupération de toutes les commandes client
		$customer['orders'] = $ordersManager -> getByCustomerId($customer['id']);
		$ordersNumber = count($customer['orders']);

		//affichage des lignes de commande
		for ($i=0; $i < $ordersNumber; $i++) 
		{ 
			$customer['orders'][$i]['orderLines'] = $orderLinesManager -> getByIdOrder($customer['orders'][$i]['orderId']);	
			//initialisation du total global des commandes
			$customer['orders'][$i]['totalAmount'] = 0;
			$orderLinesNumber = count($customer['orders'][$i]['orderLines']);

			//calcul du total par commande et mise à jour du total global
			for ($j=0; $j < $orderLinesNumber; $j++) 
			{ 
				$customer['orders'][$i]['orderLines'][$j]['subTotal'] = $customer['orders'][$i]['orderLines'][$j]['TTCPrice']*$customer['orders'][$i]['orderLines'][$j]['quantity'];
				$customer['orders'][$i]['totalAmount'] += $customer['orders'][$i]['orderLines'][$j]['subTotal'];
			}
		}

		$this -> viewData['customer'] = $customer;
		$this -> viewData['CSRFToken'] = $CSRFToken;
		$this -> generateView('customers/show-one.phtml');
	}

	//ajout admin d'un client par formulaire de gestion
	public function adminAddOneAction()
	{
		//vérification du statut d'admin
		$customersManager = new CustomersManager();
		$customer = $customersManager -> getLoginInfo();
		if (empty($customer) OR $customer['email'] != ADMIN_EMAIL)
		{
			header('Location:'.CLIENT_ROOT.'customers/account');
			exit();
		}

		//sécurisation CSRF
		if(!array_key_exists('CSRFToken', $customer) OR !array_key_exists('CSRFToken', $_POST) OR $_POST['CSRFToken'] != $customer['CSRFToken'])
		{
			header('Location:'.CLIENT_ROOT.'customers/account');
            exit();
		}

		//vérification des données reçues
		$enteredData =
		[
			'email' => trim($_POST['email']),
			'password' => trim($_POST['password']),
			'civility' => trim($_POST['civility']),
			'firstName' => trim($_POST['firstName']),
			'lastName' => trim($_POST['lastName']),
			'address' => trim($_POST['address']),
			'zipCode' => trim($_POST['zipCode']),
			'city' => trim($_POST['city']),
			'country' => trim($_POST['country']),
			'phoneNumber' => (!empty($_POST['phoneNumber']))? trim($_POST['phoneNumber']) : null
		];

		//mémorisation pour ré-afficher les données entrées en cas d'erreur
		$_SESSION['forms']['adminAddOne']['fieldsContent'] = $enteredData;	
		unset($_SESSION['forms']['adminAddOne']['fieldsContent']['password']);
		$requiredFields = ['email', 'password', 'civility', 'firstName', 'lastName', 'address', 'zipCode', 'city', 'country'];
		$requiredFieldsNumber = count($requiredFields);

		for ($i=0; $i < $requiredFieldsNumber; $i++) { 
			if(!array_key_exists($requiredFields[$i], $_POST))
			{
				$_SESSION['alertMessages']['error'][] ='Veuillez entrer des informations valides.';
				header('Location:'.CLIENT_ROOT.'customers/manageForm');
				exit();
			}
			if(empty($_POST[$requiredFields[$i]]))
			{
				$_SESSION['alertMessages']['error'][] = 'Veuillez remplir tous les champs obligatoires.';
				header('Location:'.CLIENT_ROOT.'customers/manageForm');
				exit();
			}
		}

		$email = $enteredData['email'];
		if (filter_var($email, FILTER_VALIDATE_EMAIL) === false)
		{
			$_SESSION['alertMessages']['error'][] = 'L\'adresse mail n\'est pas valide.';
			header('Location:'.CLIENT_ROOT.'customers/manageForm');
			exit();
		}

		//l'e-mail n'est pas déjà présent dans la base de données
		$customer = $customersManager -> getOneByEmail($email);
		if ($customer === false)
		{
			//hashage du mot de passe
			$password = trim($_POST['password']);
			$passwordHash = password_hash($password, PASSWORD_DEFAULT);

			//ajout du client
			$requiredData = [$enteredData['email'], $passwordHash, $enteredData['civility'], $enteredData['firstName'], $enteredData['lastName'], $enteredData['address'], $enteredData['zipCode'], $enteredData['city'], $enteredData['country'], $enteredData['phoneNumber']];
			$userAdded = $customersManager -> addOne($requiredData);

			if($userAdded)
			{
				$_SESSION['alertMessages']['success'][] = 'Le compte a bien été créé !';
				unset($_SESSION['forms']['adminAddOne']['fieldsContent']);
				header('Location:'.CLIENT_ROOT.'customers/manageForm');
				exit();
				
			}
			else
			{
				$_SESSION['alertMessages']['error'][] = 'La création du compte a échoué !';
				header('Location:'.CLIENT_ROOT.'customers/manageForm');
				exit();
			}
		}
		else
	  	{
	  		$_SESSION['alertMessages']['error'][] = 'Il y a déjà un compte à cette adresse.';
	  		header('Location:'.CLIENT_ROOT.'customers/manageForm');
	  		exit();
	  	}
	}

	//ajout d'un client par inscription
	public function addOneAction()
	{
		//vérification du statut de connexion
		$customersManager = new CustomersManager();
		$this->viewData['customer'] = $customersManager -> getLoginInfo();
		if (!empty($customer))
		{
			$_SESSION['alertMessages']['error'][] = 'Vous êtes déjà connecté !';
			header('Location: account');
			exit();
		}

		//vérification des données reçues
		$enteredData =
		[
			'email' => trim($_POST['email']),
			'civility' => trim($_POST['civility']),
			'firstName' => trim($_POST['firstName']),
			'lastName' => trim($_POST['lastName']),
			'address' => trim($_POST['address']),
			'zipCode' => trim($_POST['zipCode']),
			'city' => trim($_POST['city']),
			'country' => trim($_POST['country']),
			'phoneNumber' => (!empty($_POST['phoneNumber']))? trim($_POST['phoneNumber']) : null
		];

		//mémorisation pour ré-afficher les données entrées en cas d'erreur
		$_SESSION['forms']['createAccount']['fieldsContent'] = $enteredData;
		$requiredFields = ['email', 'password', 'password2', 'civility', 'firstName', 'lastName', 'address', 'zipCode', 'city', 'country'];
		$requiredFieldsNumber = count($requiredFields);

		for ($i=0; $i < $requiredFieldsNumber; $i++) { 
			if(!array_key_exists($requiredFields[$i], $_POST))
			{
				$_SESSION['alertMessages']['error'][] ='Veuillez entrer des informations valides.';
				header('Location: createAccount');
				exit();
			}
			if(empty($_POST[$requiredFields[$i]]))
			{
				$_SESSION['alertMessages']['error'][] = 'Veuillez remplir tous les champs obligatoires.';
				header('Location: createAccount');
				exit();
			}
		}

		if ($_POST['password'] != $_POST['password2'])
			{
				$_SESSION['alertMessages']['error'][] = 'Le mot de passe ne correspond pas.';
				header('Location: createAccount');
				exit();
			}

		$email = $enteredData['email'];
		if (filter_var($email, FILTER_VALIDATE_EMAIL) === false)
		{
			$_SESSION['alertMessages']['error'][] = 'Votre adresse mail n\'est pas valide.';
			header('Location: createAccount');
			exit();
		}

		//s'il n'y a pas encore de compte à cette adresse
		$customer = $customersManager -> getOneByEmail($email);
		if ($customer === false)
		{
			//hashage du mot de passe
			$password = trim($_POST['password']);
			$passwordHash = password_hash($password, PASSWORD_DEFAULT);

			//ajout dans la base
			$requiredData = [$enteredData['email'], $passwordHash, $enteredData['civility'], $enteredData['firstName'], $enteredData['lastName'], $enteredData['address'], $enteredData['zipCode'], $enteredData['city'], $enteredData['country'], $enteredData['phoneNumber']];
			$userAdded = $customersManager -> addOne($requiredData);

			if($userAdded)
			{
				$_SESSION['alertMessages']['success'][] = 'Le compte a bien été créé !<br>Merci de vous authentifier.';
				header('Location: account');
				exit();
				
			}
			else
			{
				$_SESSION['alertMessages']['error'][] = 'La création du compte a échoué !';
				header('Location: createAccount');
				exit();
			}
		}
		else
	  	{
	  		$_SESSION['alertMessages']['error'][] = 'Il y a déjà un compte à cette adresse.';
	  		header('Location: createAccount');
	  		exit();
	  	}
	}

	//affichage de la page client (ou admin selon le statut)
	public function accountAction()
	{
		//récupération des infos de connexion
		$customersManager = new CustomersManager();
		$this -> viewData['customer'] = $customersManager -> getLoginInfo();

		//récupération des éventuelles données entrées erronées
		if(isset($_SESSION['forms']['login']['fieldsContent']))
		{
			$this->viewData['enteredData'] = $_SESSION['forms']['login']['fieldsContent'];
		}

		$this -> generateView('customers/account.phtml');
	}

	//connexion
	public function loginAction()
	{
		//récupération des infos de connexion
		$customersManager = new CustomersManager();
		$this -> viewData['customer'] = $customersManager -> getLoginInfo();
		if (!empty($customer))
		{
			$_SESSION['alertMessages']['error'][] = 'Vous êtes déjà connecté !';
			throw new Exception('Vous êtes déjà connecté');
		}

		//vérification des données reçues
		if(array_key_exists('email', $_POST) && array_key_exists('password', $_POST))
	 	{
	 		//ouverture de la session
	 		if (session_status() !== PHP_SESSION_ACTIVE)
	 			{
	 				session_start();
	 				session_regenerate_id();
	 			}

			if (!empty($_POST['email'])&&!empty($_POST['password']))
			{	
				$enteredData =
				[
					'email' => trim($_POST['email']),
					'password' => trim($_POST['password'])
				];

				//mémorisation pour ré-afficher les données entrées en cas d'erreur
				$_SESSION['forms']['login']['fieldsContent']['email'] = $enteredData['email'];

				//récupération des infos compte associées à l'e-mail entré
				$customersManager = new CustomersManager();
				$customer = $customersManager -> getOneByEmail($enteredData['email']);

				//si le compte existe et que le mot de passe correspond
				if ($customer !== false && password_verify($enteredData['password'], $customer['passwordHash']))
				{
					//enregistrement des infos client dans la session
					$_SESSION['customer'] = 
						[
							'id' => $customer['id'],
							'email' => $customer['email'],
							'civility' => $customer['civility'],
							'firstName' => $customer['firstName'],
							'lastName' => $customer['lastName'],
							'address' => $customer['address'],
							'zipCode' => $customer['zipCode'],
							'city' => $customer['city'],
							'country' => $customer['country'],
							'phoneNumber' => $customer['phoneNumber'],
							'CSRFToken' => bin2hex(openssl_random_pseudo_bytes(10))
						];

					header('Location: account');
					exit();
				}
				else
				{
					$_SESSION['alertMessages']['error'][] = 'Vos identifiants ne correspondent pas.';
				}
			}
			else 
			{
				$_SESSION['alertMessages']['error'][] = 'Veuillez entrer une adresse e-mail et un mot de passe valides.';
			}
		}

		if(isset($_SESSION['forms']['login']['fieldsContent']))
		{
			$this->viewData['enteredData'] = $_SESSION['forms']['login']['fieldsContent'];
		}
		header('Location: account');
		exit();
	}

	//déconnexion
	public function logOutAction()
	{
		//ouverture de la session
		if (session_status() !== PHP_SESSION_ACTIVE)
		{
			session_start();
			session_regenerate_id();
		}

		//suppression des infos clients dans la session
		unset($_SESSION['customer']);

		$_SESSION['alertMessages']['success'][] = 'Vous êtes bien déconnecté !';
		
		//récupération des informations de connexion
		$customersManager = new CustomersManager();
		$this -> viewData['customer'] = $customersManager -> getLoginInfo();
		header('Location: account');
		exit();
	}

	//affichage du formulaire de création de compte
	public function createAccountAction()
	{
		//récupération des informations de connexion
		$customersManager = new CustomersManager();
		$this -> viewData['customer'] = $customersManager -> getLoginInfo();

		//récupération des éventuelles données entrées erronées
		if(isset($_SESSION['forms']['createAccount']['fieldsContent']))
		{
			$this -> viewData['enteredData'] = $_SESSION['forms']['createAccount']['fieldsContent'];
		}
		$this -> generateView('customers/create-account.phtml');
	}

	//affichage du formulaire de mise à jour des données client
	public function updateFormAction()
	{
		//récupération des informations de connexion
		$customersManager = new CustomersManager();
		$this -> viewData['customer'] = $customer = $customersManager -> getLoginInfo();

		//si l'utilisateur n'est pas connecté
		if (empty($customer))
		{
			$_SESSION['alertMessages']['error'][] = 'Vous n\'êtes pas connecté !';
			header('Location: account');
			exit();
		}

		//récupération des éventuelles données entrées erronées
		if(isset($_SESSION['forms']['update']['fieldsContent']))
		{
			$this->viewData['enteredData'] = $_SESSION['forms']['update']['fieldsContent'];
		}
		else
		{
			//pré-remplissage du formulaire avec les données client
			$this->viewData['enteredData'] = $customer;
		}

		$this -> generateView('customers/update-form.phtml');
	}

	//mise à jour des coordonnées client
	public function updateAction()
	{
		//récupération des informations de connexion
		$customersManager = new CustomersManager();
		$customer = $customersManager -> getLoginInfo();
		if (empty($customer))
		{
			$_SESSION['alertMessages']['error'][] = 'Vous n\'êtes pas connecté !';
			throw new Exception('Vous n\'êtes pas connecté');
		}

		//sécurisation CSRF
		if(!array_key_exists('CSRFToken', $customer) OR !array_key_exists('CSRFToken', $_POST) OR $_POST['CSRFToken'] !== $customer['CSRFToken'])
		{
			header('Location: account');
            exit();
		}

		//vérification de l'existence du compte
		$id = $customer['id'];
		$customer = $customersManager -> getOneById($id);
		if ($customer !== false)
		{
			//vérification des données reçues
			$enteredData =
			[
				'email' => trim($_POST['email']),
				'civility' => trim($_POST['civility']),
				'firstName' => trim($_POST['firstName']),
				'lastName' => trim($_POST['lastName']),
				'address' => trim($_POST['address']),
				'zipCode' => trim($_POST['zipCode']),
				'city' => trim($_POST['city']),
				'country' => trim($_POST['country']),
				'phoneNumber' => (!empty($_POST['phoneNumber']))? trim($_POST['phoneNumber']) : null
			];

			//mémorisation pour ré-afficher les données entrées en cas d'erreur
			$_SESSION['forms']['update']['fieldsContent'] = $enteredData;
			$enteredData['password'] = trim($_POST['password']);
			$requiredFields = ['email', 'password', 'civility', 'firstName', 'lastName', 'address', 'zipCode', 'city', 'country'];
			$requiredFieldsNumber = count($requiredFields);

			// vérification des champs
			for ($i=0; $i < $requiredFieldsNumber; $i++) { 
				if(!array_key_exists($requiredFields[$i], $_POST))
				{
					$_SESSION['alertMessages']['error'][] = 'Veuillez entrer des informations valides.';
					header('Location: updateForm');
					exit();
				}
				if(empty($_POST[$requiredFields[$i]]))
				{
					$_SESSION['alertMessages']['error'][] = 'Veuillez remplir tous les champs obligatoires.';
					header('Location: updateForm');
					exit();
				}
			}

			//si le mot de passe correspond
			if (password_verify($enteredData['password'], $customer['passwordHash']))
			{
				//si l'e-mail entré n'est pas valide
				$email = $enteredData['email'];
				if (filter_var($email, FILTER_VALIDATE_EMAIL) === false)
				{
					$_SESSION['alertMessages']['error'][] = 'Votre adresse mail n\'est pas valide.';
					header('Location: updateForm');
					exit();
				}

				//mise à jour des données
				$requiredData = [$enteredData['email'], $enteredData['civility'], $enteredData['firstName'], $enteredData['lastName'], $enteredData['address'], $enteredData['zipCode'], $enteredData['city'], $enteredData['country'], $enteredData['phoneNumber'], $id];
				$userUpdated = $customersManager -> update($requiredData);

				if($userUpdated)
				{
					$_SESSION['alertMessages']['success'][] = 'Vos coordonnées ont bien été mises à jour !';
					//enregistrement des nouvelles informations dans la session
					$_SESSION['customer'] = 
						[
							'id' => $id,
							'email' => $enteredData['email'],
							'civility' => $enteredData['civility'],
							'firstName' => $enteredData['firstName'],
							'lastName' => $enteredData['lastName'],
							'address' => $enteredData['address'],
							'zipCode' => $enteredData['zipCode'],
							'city' => $enteredData['city'],
							'country' => $enteredData['country'],
							'phoneNumber' => $enteredData['phoneNumber'],
							'CSRFToken' => bin2hex(openssl_random_pseudo_bytes(10))
						];
					header('Location: account');
					exit();
					
				}
				else
				{
					$_SESSION['alertMessages']['error'][] = 'La mise à jour de vos coordonnées a échoué !';
					header('Location: updateForm');
					exit();
				}

			}
			else
			{
				$_SESSION['alertMessages']['error'][] = 'Le mot de passe ne correspond pas.';
				header('Location: updateForm');
				exit();
			}
	  	}
	  	else
	  	{
	  		$_SESSION['alertMessages']['error'][] = 'Vous n\'avez pas de compte !';
		  	header('Location: updateForm');
		  	exit();
	  	}	
	}

	//mise à jour admin des coordonnées client via AJAX
	public function adminUpdateAction()
	{
		//vérification du statut d'admin
		$customersManager = new CustomersManager();
		$customer = $customersManager -> getLoginInfo();
		if (empty($customer) OR $customer['email'] != ADMIN_EMAIL)
		{
			header('Location:'.CLIENT_ROOT.'customers/account');
			exit();
		}

		//sécurisation CSRF
		if(!array_key_exists('CSRFToken', $customer) OR !array_key_exists('CSRFToken', $_POST) OR $_POST['CSRFToken'] != $customer['CSRFToken'])
		{
			header('Location:'.CLIENT_ROOT.'customers/account');
            exit();
		}

		//vérification des données reçues
		$enteredData =
		[
			'email' => trim($_POST['email']),
			'civility' => trim($_POST['civility']),
			'firstName' => trim($_POST['firstName']),
			'lastName' => trim($_POST['lastName']),
			'address' => trim($_POST['address']),
			'zipCode' => trim($_POST['zipCode']),
			'city' => trim($_POST['city']),
			'country' => trim($_POST['country']),
			'phoneNumber' => (!empty($_POST['phoneNumber']))? trim($_POST['phoneNumber']) : null,
			'id' => trim($_POST['id'])
		];

		$requiredFields = ['email', 'civility', 'firstName', 'lastName', 'address', 'zipCode', 'city', 'country'];
		$requiredFieldsNumber = count($requiredFields);

		// vérification des champs
		for ($i=0; $i < $requiredFieldsNumber; $i++) { 
			if(!array_key_exists($requiredFields[$i], $_POST))
			{
				$_SESSION['alertMessages']['error'][] = 'Veuillez entrer des informations valides.';
				header('Location:'.CLIENT_ROOT.'customers/manageForm');
				exit();
			}
			if(empty($_POST[$requiredFields[$i]]))
			{
				$_SESSION['alertMessages']['error'][] = 'Veuillez remplir tous les champs obligatoires.';
				header('Location:'.CLIENT_ROOT.'customers/manageForm');
				exit();
			}
		}

		//vérification de l'e-mail
		$email = $enteredData['email'];
		if (filter_var($email, FILTER_VALIDATE_EMAIL) === false)
		{
			$_SESSION['alertMessages']['error'][] = 'L\'adresse mail n\'est pas valide.';
			header('Location:'.CLIENT_ROOT.'customers/manageForm');
			exit();
		}

		//mise à jour des données
		$requiredData = [$enteredData['email'], $enteredData['civility'], $enteredData['firstName'], $enteredData['lastName'], $enteredData['address'], $enteredData['zipCode'], $enteredData['city'], $enteredData['country'], $enteredData['phoneNumber'], $enteredData['id']];
		$userUpdated = $customersManager -> update($requiredData);

		if($userUpdated)
		{
			$_SESSION['alertMessages']['success'][] = 'Les coordonnées ont bien été mises à jour !';
			header('Location:'.CLIENT_ROOT.'customers/manageForm');
			exit();
			
		}
		else
		{
			$_SESSION['alertMessages']['error'][] = 'La mise à jour de vos coordonnées a échoué !';
			header('Location:'.CLIENT_ROOT.'customers/manageForm');
			exit();
		}
	}

	//affichage du formulaire de mise à jour du mot de passe
	public function passwordUpdateFormAction()
	{
		//récupération des informations de connexion
		$customersManager = new CustomersManager();
		$this -> viewData['customer'] = $customer = $customersManager -> getLoginInfo();
		if (empty($customer))
		{
			$_SESSION['alertMessages']['error'][] = 'Vous n\'êtes pas connecté !';
			header('Location: account');
			exit();
		}

		//récupération des éventuelles données entrées erronées
		if(isset($_SESSION['forms']['passwordUpdate']['fieldsContent']))
		{
			$this->viewData['enteredData'] = $_SESSION['forms']['passwordUpdate']['fieldsContent'];
			unset($_SESSION['forms']['passwordUpdate']);
		}
		else
		{
			$this->viewData['enteredData'] = null;
		}

		$this -> generateView('customers/password-update-form.phtml');
	}

	//mise à jour du mot de passe
	public function passwordUpdateAction()
	{
		//récupération des informations de connexion
		$customersManager = new CustomersManager();
		$customer = $customersManager -> getLoginInfo();
		if (empty($customer))
		{
			$_SESSION['alertMessages']['error'][] = 'Vous n\'êtes pas connecté !';
			throw new Exception('Vous n\'êtes pas connecté');
		}

		//sécurisation CSRF
		if(!array_key_exists('CSRFToken', $customer) OR !array_key_exists('CSRFToken', $_POST) OR $_POST['CSRFToken'] !== $customer['CSRFToken'])
		{
			header('Location: account');
            exit();
		}

		//si l'utilisateur a bien un compte
		$id = $customer['id'];
		$customer = $customersManager -> getOneById($id);
		if ($customer !== false)
		{ 
			//vérification des données reçues
			if(!array_key_exists('password', $_POST) OR !array_key_exists('newPassword', $_POST) OR !array_key_exists('newPassword2', $_POST))
			{
				$_SESSION['alertMessages']['error'][] = 'Veuillez entrer des informations valides.';
				header('Location: passwordUpdateForm');
				exit();
			}

			$enteredData =
			[
				'password' => trim($_POST['password']),
				'newPassword' => trim($_POST['newPassword']),
				'newPassword2' => trim($_POST['newPassword2'])
			];
			//mémorisation pour ré-afficher les données entrées en cas d'erreur
			$_SESSION['forms']['passwordUpdate']['fieldsContent'] = $enteredData;

			//si les champs ne sont pas remplis correctement
			if(empty($_POST['password']) OR empty($_POST['newPassword']) OR empty($_POST['newPassword2']))
			{
				$_SESSION['alertMessages']['error'][] = 'Veuillez remplir tous les champs obligatoires.';
				header('Location: passwordUpdateForm');
				exit();
			}

			//si le mot de passe ne correspond pas
			if (!password_verify($enteredData['password'], $customer['passwordHash']))
			{
				$_SESSION['alertMessages']['error'][] = 'Votre mot de passe actuel ne correspond pas.';
				header('Location: passwordUpdateForm');
				exit();
			}

			//si le nouveau mot de passe est le même que le précédent
			if ($enteredData['password'] == $enteredData['newPassword'])
			{
				$_SESSION['alertMessages']['info'][] = 'Vous avez entré le même mot de passe.';
				header('Location: passwordUpdateForm');
				exit();
			}

			//si le mot de passe de confirmation ne correspond pas
			if ($enteredData['newPassword'] != $enteredData['newPassword2'])
			{
				$_SESSION['alertMessages']['error'][] = 'Erreur dans la confirmation du mot de passe.';
				header('Location: passwordUpdateForm');
				exit();
			}

			//hashage du nouveau mot de passe
			$passwordHash = password_hash($enteredData['newPassword'], PASSWORD_DEFAULT);

			//mise à jour des données
			$requiredData = [$passwordHash, $id];
			$userUpdated = $customersManager -> passwordUpdate($requiredData);
			if($userUpdated)
			{
				$_SESSION['alertMessages']['success'][] = 'Votre mot de passe a bien été mis à jour !';
				unset($_SESSION['forms']['passwordUpdate']['fieldsContent']);
				header('Location: account');
				exit();
				
			}
			else
			{
				$_SESSION['alertMessages']['error'][] = 'La mise à jour de votre mot de passe a échoué !';
				header('Location: passwordUpdateForm');
				exit();
			}

		}
	}

	//affichage du formulaire de désinscription
	public function unsuscribeAction()
	{
		//récupération des informations de connexion
		$customersManager = new CustomersManager();
		$this -> viewData['customer'] = $customer = $customersManager -> getLoginInfo();
		if (empty($customer))
		{
			$_SESSION['alertMessages']['error'][] = 'Vous n\'êtes pas connecté !';
			header('Location: account');
			exit();
		}

		$this -> generateView('customers/unsuscribe.phtml');
	}

	//désinscription
	public function deleteAccountAction()
	{
		//récupération des informations de connexion
		$customersManager = new CustomersManager();
		$customer = $customersManager -> getLoginInfo();
		if (empty($customer))
		{
			$_SESSION['alertMessages']['error'][] = 'Vous n\'êtes pas connecté !';
			header('Location: account');
			exit();
		}

		//sécurisation CSRF
		if(!array_key_exists('CSRFToken', $customer) OR !array_key_exists('CSRFToken', $_POST) OR $_POST['CSRFToken'] !== $customer['CSRFToken'])
		{
			header('Location: account');
            exit();
		}

		//vérification des données reçues
		if(!array_key_exists('password', $_POST))
	 	{
	 		$_SESSION['alertMessages']['error'][] = 'Veuillez entrer un mot de passe valide.';
	 		header('Location: unsuscribe');
	 		exit();
	 	}
		if (empty($_POST['password']))
		{
			$_SESSION['alertMessages']['error'][] = 'Veuillez entrer votre mot de passe.';
			header('Location: unsuscribe');
			exit();
		}

		//vérification du mot de passe
		$id=$customer['id'];
		$customer = $customersManager -> getOneById($id);
		if (!password_verify($_POST['password'], $customer['passwordHash']))
		{
			$_SESSION['alertMessages']['error'][] = 'Vous n\'avez pas entré le bon mot de passe.';
			header('Location: unsuscribe');
			exit();
		}	

		//suppression du compte de la base de données
		$accountDeleted = $customersManager -> deleteOne($id);
		if($accountDeleted)
		{
			$_SESSION['alertMessages']['success'][] = 'Votre compte a bien été supprimé.';
			unset($_SESSION['customer']);	//suppression des infos clients de la session
			unset($_SESSION['forms']);	//suppression des infos de formulaires de la session
			header('Location: account');
			exit();
		}
		else
		{
			$_SESSION['alertMessages']['error'][] = 'Votre compte n\'a pas pu être supprimé.';
			header('Location: unsuscribe');
			exit();
		}
	}

	//suppression admin d'un compte client
	public function adminDeleteAccountAction()
	{
		//vérification du statut d'admin
		$customersManager = new CustomersManager();
		$customer = $customersManager -> getLoginInfo();
		if (empty($customer) OR $customer['email'] != ADMIN_EMAIL)
		{
			header('Location:'.CLIENT_ROOT.'customers/account');
			exit();
		}

		//sécurisation CSRF
		if(!array_key_exists('CSRFToken', $customer) OR !array_key_exists('CSRFToken', $_GET) OR $_GET['CSRFToken'] != $customer['CSRFToken'])
		{
			header('Location:'.CLIENT_ROOT.'customers/account');
            exit();
		}

		//vérification des données reçues
		if(!array_key_exists('id', $_GET) OR empty($_GET['id']))
	 	{
	 		header('Location:'.CLIENT_ROOT.'customers/account');
            exit();
	 	}

	 	//suppression du compte de la base de données
	 	$id=$_GET['id'];
		$accountDeleted = $customersManager -> deleteOne($id);
		if($accountDeleted)
		{
			$_SESSION['alertMessages']['success'][] = 'Le compte a bien été supprimé.';
			header('Location:'.CLIENT_ROOT.'customers/manageForm');
            exit();
		}
		else
		{
			$_SESSION['alertMessages']['error'][] = 'Le compte n\'a pas pu être supprimé.';
			header('Location:'.CLIENT_ROOT.'customers/manageForm');
            exit();
		}
	}

	//affichage du formulaire de gestion CRUD
	public function manageFormAction()
	{
		//vérification du statut d'admin
		$customersManager = new CustomersManager();
		$customer = $customersManager -> getLoginInfo();
		if (empty($customer) OR $customer['email'] != ADMIN_EMAIL)
		{
			header('Location: account');
			exit();
		}

		//récupération des infos clients
		$customers = $customersManager -> getAll();

		/* ------------------------------------------------------------------------

		Affichage déroulant des commandes clients :

		Désactivé pour améliorer les performances et affiché en individuel dans les fiches clients.
		Pour le réactiver, à décommenter ci-dessous et dans views > customers/manage-form.phtml

		---------------------------------------------------------------------------
		*/


/*	-------------------------------------------------------------------------------
		$customersNumber = count($customers);
		$ordersManager = new OrdersManager();
		$orderLinesManager = new OrderLinesManager();
		for ($i=0; $i < $customersNumber; $i++) 
		{ 
			unset($customers[$i]['passwordHash']);
			$customers[$i]['orders'] = $ordersManager -> getByCustomerId($customers[$i]['id']);
			$ordersNumber = count($customers[$i]['orders']);

			for ($j=0; $j < $ordersNumber; $j++) 
			{ 
				$customers[$i]['orders'][$j]['orderLines'] = $orderLinesManager -> getByIdOrder($customers[$i]['orders'][$j]['orderId']);

				
				$customers[$i]['orders'][$j]['totalAmount'] = 0;
				$orderLinesNumber = count($customers[$i]['orders'][$j]['orderLines']);
				for ($k=0; $k < $orderLinesNumber; $k++) 
				{ 
					$customers[$i]['orders'][$j]['orderLines'][$k]['subTotal'] = $customers[$i]['orders'][$j]['orderLines'][$k]['TTCPrice']*$customers[$i]['orders'][$j]['orderLines'][$k]['quantity'];
					$customers[$i]['orders'][$j]['totalAmount'] += $customers[$i]['orders'][$j]['orderLines'][$k]['subTotal'];
				}
			}
		}
------------------------------------------------------------------------------- */

		//récupération des éventuelles données entrées erronées
		if (isset($_SESSION['forms']['adminAddOne']['fieldsContent']))
		{
			$this -> viewData['enteredData'] = $_SESSION['forms']['adminAddOne']['fieldsContent'];
		}
		
		$this -> viewData['CSRFToken'] = $customer['CSRFToken'];
		$this -> viewData['customers'] = $customers;
		$this -> generateView('customers/manage-form.phtml');
	}
}
 ?>