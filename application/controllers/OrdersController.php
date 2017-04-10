<?php 

class OrdersController extends Controller
{
	//affichage du formulaire des coordonnées de facturation
	public function billingFormAction() 
	{
		//récupération des informations de connexion
		$customersManager = new CustomersManager();
		$customer = $customersManager -> getLoginInfo();
		
		//redirection vers la page de connexion pour les utilisateurs non-connectés
		if (empty($customer))
		{
			header('Location:'.CLIENT_ROOT.'customers/account');
		}

		//si le panier est vide
		if (!isset($_SESSION['shoppingCart']) OR !isset($_SESSION['shoppingCart']['products']) OR empty($_SESSION['shoppingCart']['products']))
		{
			header('Location:'.CLIENT_ROOT.'shoppingCart/show');
		}

		//récupération des éventuelles données entrées erronées
		if(isset($_SESSION['forms']['billingForm']['fieldsContent']))
		{
			$this->viewData['enteredData'] = $_SESSION['forms']['billingForm']['fieldsContent'];
		}

		$this->viewData['customer'] = $_SESSION['customer'];
		$this -> generateView('orders/billing-form.phtml');
	}

	//enregistrement des coordonnées de facturation
	public function saveBillingDetailsAction() 
	{
		//récupération des informations de connexion
		$customersManager = new CustomersManager();
		$customer = $customersManager -> getLoginInfo();
		
		//redirection vers la page de connexion pour les utilisateurs non-connectés
		if (empty($customer))
		{
			header('Location:customers/account');
		}

		//si le panier est vide
		if (!isset($_SESSION['shoppingCart']) OR !isset($_SESSION['shoppingCart']['products']) OR empty($_SESSION['shoppingCart']['products']))
		{
			header('Location:'.CLIENT_ROOT.'shoppingCart/show');
		}

		//vérification des données reçues
		$billingDetails =
		[
			'civility' => trim($_POST['civility']),
			'firstName' => trim($_POST['firstName']),
			'lastName' => trim($_POST['lastName']),
			'address' => trim($_POST['address']),
			'zipCode' => trim($_POST['zipCode']),
			'city' => trim($_POST['city']),
			'country' => trim($_POST['country']),
			'phoneNumber' => trim($_POST['phoneNumber'])
		];

		$requiredFields = ['civility', 'firstName', 'lastName', 'address', 'zipCode', 'city', 'country'];
		$requiredFieldsNumber = count($requiredFields);

		for ($i=0; $i < $requiredFieldsNumber; $i++) { 
			if(!array_key_exists($requiredFields[$i], $_POST))
			{
				$_SESSION['alertMessages']['error'][] ='Veuillez entrer des informations valides.';
				//mémorisation pour ré-afficher les données entrées en cas d'erreur
				$_SESSION['forms']['billingForm']['fieldsContent'] = $billingDetails;
				header('Location:orders/billingForm');
			}
			if(empty($_POST[$requiredFields[$i]]))
			{
				$_SESSION['alertMessages']['error'][] = 'Veuillez remplir tous les champs obligatoires.';
				//mémorisation pour ré-afficher les données entrées en cas d'erreur
				$_SESSION['forms']['billingForm']['fieldsContent'] = $billingDetails;
				header('Location:orders/billingForm');
			}
		}

		//enregistrement des données dans la session
		$this->viewData['billing'] = $_SESSION['order']['billingDetails'] = $billingDetails;

		//récupération des éventuelles données entrées erronées
		if(isset($_SESSION['forms']['deliveryForm']['fieldsContent']))
		{
			$this->viewData['enteredData'] = $_SESSION['forms']['deliveryForm']['fieldsContent'];
		}

		//affichage du formulaire des coordoonées de livraison
		$this -> generateView('orders/delivery-form.phtml');
	}

	//enregistrement des coordonnées de livraison
	public function saveDeliveryDetailsAction() 
	{
		//récupération des informations de connexion
		$customersManager = new CustomersManager();
		$customer = $customersManager -> getLoginInfo();
		
		//redirection vers la page de connexion pour les utilisateurs non-connectés
		if (empty($customer))
		{
			header('Location:customers/account');
		}

		//si le panier est vide
		if (!isset($_SESSION['shoppingCart']) OR !isset($_SESSION['shoppingCart']['products']) OR empty($_SESSION['shoppingCart']['products']))
		{
			header('Location:'.CLIENT_ROOT.'shoppingCart/show');
		}

		//vérification des données reçues
		$deliveryDetails =
		[
			'civility' => trim($_POST['civility']),
			'firstName' => trim($_POST['firstName']),
			'lastName' => trim($_POST['lastName']),
			'address' => trim($_POST['address']),
			'zipCode' => trim($_POST['zipCode']),
			'city' => trim($_POST['city']),
			'country' => trim($_POST['country']),
			'phoneNumber' => trim($_POST['phoneNumber'])
		];

		$requiredFields = ['civility', 'firstName', 'lastName', 'address', 'zipCode', 'city', 'country'];
		$requiredFieldsNumber = count($requiredFields);

		for ($i=0; $i < $requiredFieldsNumber; $i++) { 
			if(!array_key_exists($requiredFields[$i], $_POST))
			{
				$_SESSION['alertMessages']['error'][] ='Veuillez entrer des informations valides.';
				$_SESSION['forms']['deliveryForm']['fieldsContent'] = $deliveryDetails;
				$this -> generateView('customers/delivery-form.phtml');
			}
			if(empty($_POST[$requiredFields[$i]]))
			{
				$_SESSION['alertMessages']['error'][] = 'Veuillez remplir tous les champs obligatoires.';
				$_SESSION['forms']['deliveryForm']['fieldsContent'] = $deliveryDetails;
				$this -> generateView('customers/delivery-form.phtml');
			}
		}	

		//sauvegarde des données dans la session
		$_SESSION['order']['deliveryDetails'] = $deliveryDetails;

		//récupération du contenu du panier
		$shoppingCartManager = new ShoppingCartManager();
		
		if (array_key_exists('shoppingCart', $_SESSION))
		{
			$this -> viewData['shoppingCart'] = $shoppingCart = $shoppingCartManager -> get();
			$this -> viewData['ordersNumber'] = count($shoppingCart['products']);
		}
		else
		{
			//redirection si le panier est vide
			header('Location:'.CLIENT_ROOT.'shoppingCart/show');
		}

		$this -> generateView('orders/order-recap.phtml');
	}

	//enregistrement de la commande dans la BDD
	public function saveOneAction() 
	{
		//récupération des informations de connexion
		$customersManager = new CustomersManager();
		$customer = $customersManager -> getLoginInfo();	
		if (empty($customer))
		{
			header('Location:customers/account');
		}

		//redirection si le panier est vide
		if (!isset($_SESSION['shoppingCart']) OR !isset($_SESSION['shoppingCart']['products']) OR empty($_SESSION['shoppingCart']['products']))
		{
			header('Location:'.CLIENT_ROOT.'shoppingCart/show');
		}

		//ajout dans la BDD des données de commande
		$requiredData = 
		[
			'billingCivility' => $_SESSION['order']['billingDetails']['civility'],
			'billingFirstName' => $_SESSION['order']['billingDetails']['firstName'],
			'billingLastName' => $_SESSION['order']['billingDetails']['lastName'],
			'billingAddress' => $_SESSION['order']['billingDetails']['address'],
			'billingZipCode' => $_SESSION['order']['billingDetails']['zipCode'],
			'billingCity' => $_SESSION['order']['billingDetails']['city'],
			'billingCountry' => $_SESSION['order']['billingDetails']['country'],
			'billingPhoneNumber' => $_SESSION['order']['billingDetails']['phoneNumber'],
			'deliveryCivility' => $_SESSION['order']['deliveryDetails']['civility'],
			'deliveryFirstName' => $_SESSION['order']['deliveryDetails']['firstName'],
			'deliveryLastName' => $_SESSION['order']['deliveryDetails']['lastName'],
			'deliveryAddress' => $_SESSION['order']['deliveryDetails']['address'],
			'deliveryZipCode' => $_SESSION['order']['deliveryDetails']['zipCode'],
			'deliveryCity' => $_SESSION['order']['deliveryDetails']['city'],
			'deliveryCountry' => $_SESSION['order']['deliveryDetails']['country'],
			'deliveryPhoneNumber' => $_SESSION['order']['deliveryDetails']['phoneNumber'],
			'idCustomer' => $_SESSION['customer']['id']
		];
		
		$ordersManager = new OrdersManager();
		$idOrder = $ordersManager -> addOne($requiredData);

		//si la commande a bien été enregistrée, le n° de commande a été récupéré
		if ($idOrder != 0)
		{
			//récupération des produits du panier
			$orderedProducts = $_SESSION['shoppingCart']['products'];

			//pour chaque produit commandé
			foreach ($orderedProducts as $orderedProduct) {
				$requiredData['orderLines']['products'][$orderedProduct['productId']] = 
				[
					'idProduct' => $orderedProduct['productId'],
					'idOrder' => $idOrder,
					'nameProduct' => $orderedProduct['productName'],
					'priceHTProduct' => $orderedProduct['priceHT'],
					'quantity' => $orderedProduct['quantity'],
					'VATRate' => $orderedProduct['VATRate']
				];

				//ajout de la ligne de commande dans la BDD
				$orderLinesManager = new OrderLinesManager();
				$orderAdded = $orderLinesManager -> addOne($requiredData['orderLines']['products'][$orderedProduct['productId']]);

				/*
					Variante avec une seule requête :
					$orderLinesManager = new OrderLinesManager();
					$orderAdded = $orderLinesManager -> addMany($requiredData));

					-> mettre en forme $requiredData
				*/

				//si la ligne n'a pas été ajoutée
				if ($orderAdded != 1)
				{
					$_SESSION['alertMessages']['error'][] = 'La création de la commande a échoué !';
					/*throw new Exception('La commande n\'a pas été ajoutée : erreur dans addOrderLines');*/
					$this -> generateView('orders/order-recap.phtml');
				}
			}

			$_SESSION['alertMessages']['success'][] = 'La commande a bien été enregistrée !';
			//vidage du panier
			unset($_SESSION['shoppingCart']);
			//affichage de la page de succès de la commande
			header('Location:success');
		}
		else
		{
			$_SESSION['alertMessages']['error'][] = 'La création de la commande a échoué !';
			/*throw new Exception('La commande n\'a pas été ajoutée : erreur dans addOne');*/
			$this -> generateView('orders/order-recap.phtml');
		}
	}

	//affichage de la page de succès de la commande
	public function successAction() 
	{
		//récupération des informations de connexion
		$customersManager = new CustomersManager();
		$customer = $customersManager -> getLoginInfo();

		//si l'utilisateur n'est pas connecté
		if (empty($customer))
		{
			header('Location:customers/account');
		}
		else
		{
			$this -> generateView('orders/success.phtml');
		}
	}

	//affichage des commandes du client
	public function showByCustomerIdAction() 
	{
		//récupération des informations de connexion
		$customersManager = new CustomersManager();
		$customer = $customersManager -> getLoginInfo();

		//si l'utilisateur n'est pas connecté
		if (empty($customer))
		{
			$_SESSION['alertMessages']['error'][] = 'Vous n\'êtes pas connecté !';
			header('Location:'.CLIENT_ROOT.'customers/account');
			exit();
		}

		//récupération des commandes
		$id = $customer['id'];
		$ordersManager = new OrdersManager();
		$orders = $ordersManager -> getByCustomerId($id);

		//récupération des lignes de commande pour chaque commande
		$orderLinesManager = new OrderLinesManager();
		$ordersNumber = count($orders);
		for ($i=0; $i < $ordersNumber; $i++) { 
			$idOrder = $orders[$i]['orderId'];
			$orders[$i]['orderLines'] = $orderLinesManager -> getByIdOrder($idOrder);
			//initialisation du total de la commande
			$orders[$i]['totalAmount'] = 0;
			$orderLinesNumber = count($orders[$i]['orderLines']);
			//pour chaque ligne de commande
			for ($j=0; $j < $orderLinesNumber; $j++) { 
				//calcul du sous-total
				$orders[$i]['orderLines'][$j]['subTotal'] = $orders[$i]['orderLines'][$j]['TTCPrice']*$orders[$i]['orderLines'][$j]['quantity'];
				//mise à jour du total de la commande
				$orders[$i]['totalAmount'] += $orders[$i]['orderLines'][$j]['subTotal'];
			}
		}
		$this -> viewData['orders'] = $orders;
		$this -> generateView('orders/show-customer-orders.phtml');
	}

	//affichage des commandes du client avec pagination
	public function showPageByCustomerIdAction() 
	{
		//récupération des informations de connexion
		$customersManager = new CustomersManager();
		$customer = $customersManager -> getLoginInfo();

		//si l'utilisateur n'est pas connecté
		if (empty($customer))
		{
			$_SESSION['alertMessages']['error'][] = 'Vous n\'êtes pas connecté !';
			header('Location:'.CLIENT_ROOT.'customers/account');
			exit();
		}

		//récupération des commandes à afficher sur la page actuelle
		$id = $customer['id'];

		$ordersManager = new OrdersManager();
		$currentPage = array_key_exists('page', $_GET)? $_GET['page'] : 1;
		$numberByPage = ORDERS_BY_PAGE;
		$paginationStart = array_key_exists('page', $_GET)? ($_GET['page']-1)*$numberByPage : 0;
		$paginationStartShowed = array_key_exists('page', $_GET)? $paginationStart+1 : 1;
		$ordersNumber = $ordersManager -> countByCustomerId($id);
		$pagesNumber = ceil($ordersNumber / $numberByPage);
		$paginationEnd = $paginationStart + $numberByPage > $ordersNumber? $ordersNumber : $paginationStart + $numberByPage;
		$orders = $ordersManager -> getPageByCustomerId($id, $paginationStart, $numberByPage);

		//récupération des lignes de commande pour chaque commande
		$ordersNumberOnPage = count($orders);
		$orderLinesManager = new OrderLinesManager();
		for ($i=0; $i < $ordersNumberOnPage; $i++) { 
			$idOrder = $orders[$i]['orderId'];
			$orders[$i]['orderLines'] = $orderLinesManager -> getByIdOrder($idOrder);
			//initialisation du total de la commande
			$orders[$i]['totalAmount'] = 0;
			$orderLinesNumber = count($orders[$i]['orderLines']);
			//pour chaque ligne de commande
			for ($j=0; $j < $orderLinesNumber; $j++) { 
				//calcul du sous-total
				$orders[$i]['orderLines'][$j]['subTotal'] = $orders[$i]['orderLines'][$j]['TTCPrice']*$orders[$i]['orderLines'][$j]['quantity'];
				//mise à jour du total de la commande
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
			header('Location: orders/showPageByCustomerId');
		}

		$this -> generateView('orders/show-customer-orders.phtml');
	}

/* ---------- Opérations admin ---------- */

	//affichage admin du formulaire de gestion CRUD
	public function manageFormAction()
	{
		//vérification du statut d'admin
		$customersManager = new CustomersManager();
		$customer = $customersManager -> getLoginInfo();
		// redirection des non-admins
		if (empty($customer) OR $customer['email'] != ADMIN_EMAIL)
		{
			header('Location: account');
			exit();
		}

		//récupération des éventuelles données entrées erronées
		if (isset($_SESSION['forms']['adminAddOrder']['fieldsContent']))
		{
			$this -> viewData['enteredData'] = $_SESSION['forms']['adminAddOrder']['fieldsContent'];
		}

		//récupération des données de commande
		$ordersManager = new OrdersManager();
		$this -> viewData['orders'] = $ordersManager -> getAll();
		//sécurisation CSRF
		$this -> viewData['CSRFToken'] = $customer['CSRFToken'];
		$this -> generateView('orders/manage-form.phtml');
	}

	//suppression admin d'une commande
	public function removeOneAction()
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
		if (!array_key_exists('CSRFToken', $customer) OR !array_key_exists('CSRFToken', $_GET) OR $_GET['CSRFToken'] !== $customer['CSRFToken'])
		{
			header('Location:'.CLIENT_ROOT.'customers/account');
            exit();
		}

		//vérification des données reçues
		if (!isset($_GET) OR !isset($_GET['id']))
		{
			header('Location:'.CLIENT_ROOT.'customers/account');
			exit();
		}

		//suppression de la commande de la BDD
		$id = $_GET['id'];
		$ordersManager = new OrdersManager();
		$orderRemoved = $ordersManager -> removeOne($id);
		if ($orderRemoved)
		{
			$_SESSION['alertMessages']['success'][] = 'La commande a bien été supprimée.';
			header('Location: manageForm');
			exit();
		}
		else 
		{
			$_SESSION['alertMessages']['error'][] = 'La suppression de la commande a échoué.';
			header('Location: manageForm');
			exit();
		}
	}

	//ajout admin d'une commande
	public function addOneAction()
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
			'purchaseDay' => trim($_POST['purchaseDay']),
			'purchaseMonth' => trim($_POST['purchaseMonth']),
			'purchaseYear' => trim($_POST['purchaseYear']),
			'purchaseHour' => trim($_POST['purchaseHour']),
			'purchaseMinute' => trim($_POST['purchaseMinute']),
		];

		//mémorisation pour ré-afficher les données entrées en cas d'erreur
		$_SESSION['forms']['adminAddOrder']['fieldsContent'] = $enteredData;
		$requiredFields = ['purchaseDay', 'purchaseMonth', 'purchaseYear', 'purchaseHour', 'purchaseMinute'];
		
		$requiredFieldsNumber = count($requiredFields);

		for ($i=0; $i < $requiredFieldsNumber; $i++) { 
			if(!array_key_exists($requiredFields[$i], $enteredData))
			{
				$_SESSION['alertMessages']['error'][] ='Veuillez entrer des informations valides.';
				header('Location:'.CLIENT_ROOT.'orders/manageForm');
				exit();
			}
			if($enteredData[$requiredFields[$i]] === '')
			{
				$_SESSION['alertMessages']['error'][] = 'Veuillez remplir tous les champs obligatoires.';
				header('Location:'.CLIENT_ROOT.'orders/manageForm');
				exit();
			}
		}

		// Mise au format SQL de la date
		if ($enteredData['purchaseDay'] < 10) {
			$enteredData['purchaseDay'] = '0'.$enteredData['purchaseDay'];
		}
		if ($enteredData['purchaseMonth'] < 10) {
			$enteredData['purchaseMonth'] = '0'.$enteredData['purchaseMonth'];
		}
		if ($enteredData['purchaseHour'] < 10) {
			$enteredData['purchaseHour'] = '0'.$enteredData['purchaseHour'];
		}
		if ($enteredData['purchaseMinute'] < 10) {
			$enteredData['purchaseMinute'] = '0'.$enteredData['purchaseMinute'];
		}

		$purchaseDate = $enteredData['purchaseYear'].'-'.$enteredData['purchaseMonth'].'-'.$enteredData['purchaseDay'].' '.$enteredData['purchaseHour'].':'.$enteredData['purchaseMinute'].':00';

		unset($enteredData);

		//vérification des autres données reçues
		$enteredData =
		[
			'purchaseDate' => $purchaseDate,
			'billingCivility' => trim($_POST['billingCivility']),
			'billingFirstName' => trim($_POST['billingFirstName']),
			'billingLastName' => trim($_POST['billingLastName']),
			'billingAddress' => trim($_POST['billingAddress']),
			'billingZipCode' => trim($_POST['billingZipCode']),
			'billingCity' => trim($_POST['billingCity']),
			'billingCountry' => trim($_POST['billingCountry']),
			'billingPhoneNumber' => trim($_POST['billingPhoneNumber']),
			'deliveryCivility' => trim($_POST['deliveryCivility']),
			'deliveryFirstName' => trim($_POST['deliveryFirstName']),
			'deliveryLastName' => trim($_POST['deliveryLastName']),
			'deliveryAddress' => trim($_POST['deliveryAddress']),
			'deliveryZipCode' => trim($_POST['deliveryZipCode']),
			'deliveryCity' => trim($_POST['deliveryCity']),
			'deliveryCountry' => trim($_POST['deliveryCountry']),
			'deliveryPhoneNumber' => trim($_POST['deliveryPhoneNumber']),
			'idCustomer' => trim($_POST['idCustomer'])
		];

		$_SESSION['forms']['adminAddOrder']['fieldsContent'] = $enteredData;
		$requiredFields = ['purchaseDate', 
		'billingCivility', 'billingFirstName', 'billingLastName', 'billingAddress', 'billingZipCode', 'billingCity', 'billingCountry', 'billingPhoneNumber', 
		'deliveryCivility', 'deliveryFirstName', 'deliveryLastName', 'deliveryAddress', 'deliveryZipCode', 'deliveryCity', 'deliveryCountry', 'deliveryPhoneNumber', 
		'idCustomer'];

		$requiredFieldsNumber = count($requiredFields);

		for ($i=0; $i < $requiredFieldsNumber; $i++) { 
			if(!array_key_exists($requiredFields[$i], $enteredData))
			{
				$_SESSION['alertMessages']['error'][] ='Veuillez entrer des informations valides.';
				header('Location:'.CLIENT_ROOT.'orders/manageForm');
				exit();
			}
			if($enteredData[$requiredFields[$i]] === '')
			{
				$_SESSION['alertMessages']['error'][] = 'Veuillez remplir tous les champs obligatoires.';
				header('Location:'.CLIENT_ROOT.'orders/manageForm');
				exit();
			}
		}
		
		//ajout de la commande dans la BDD
		$requiredData = $enteredData;
		$ordersManager = new OrdersManager();
		$idOrder = $ordersManager -> adminAddOne($requiredData);

		if ($idOrder != 0)
		{
			$_SESSION['alertMessages']['success'][] = 'La commande a bien été enregistrée ! Vous pouvez ajouter les détails en cliquant sur le numéro d\'id de la nouvelle commande.';
		}

		else
		{
			$_SESSION['alertMessages']['error'][] = 'La création de la commande a échoué !';
			/*throw new Exception('La commande n\'a pas été ajoutée : erreur dans adminAddOne');*/
			$this -> generateView('orders/order-recap.phtml');
		}
	}

	//mise à jour admin d'une commande via AJAX
	public function updateAction()
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
			'purchaseDate' => trim($_POST['purchaseDate']),
			'billingCivility' => trim($_POST['billingCivility']),
			'billingFirstName' => trim($_POST['billingFirstName']),
			'billingLastName' => trim($_POST['billingLastName']),
			'billingAddress' => trim($_POST['billingAddress']),
			'billingZipCode' => trim($_POST['billingZipCode']),
			'billingCity' => trim($_POST['billingCity']),
			'billingCountry' => trim($_POST['billingCountry']),
			'billingPhoneNumber' => trim($_POST['billingPhoneNumber']),
			'deliveryCivility' => trim($_POST['deliveryCivility']),
			'deliveryFirstName' => trim($_POST['deliveryFirstName']),
			'deliveryLastName' => trim($_POST['deliveryLastName']),
			'deliveryAddress' => trim($_POST['deliveryAddress']),
			'deliveryZipCode' => trim($_POST['deliveryZipCode']),
			'deliveryCity' => trim($_POST['deliveryCity']),
			'deliveryCountry' => trim($_POST['deliveryCountry']),
			'deliveryPhoneNumber' => trim($_POST['deliveryPhoneNumber']),
			'idCustomer' => trim($_POST['idCustomer']),
			'id' => trim($_POST['id'])
		];

		$requiredFields = ['purchaseDate', 
		'billingCivility', 'billingFirstName', 'billingLastName', 'billingAddress', 'billingZipCode', 'billingCity', 'billingCountry', 'billingPhoneNumber', 
		'deliveryCivility', 'deliveryFirstName', 'deliveryLastName', 'deliveryAddress', 'deliveryZipCode', 'deliveryCity', 'deliveryCountry', 'deliveryPhoneNumber', 
		'idCustomer', 'id'];
		$requiredFieldsNumber = count($requiredFields);

		// vérification des champs
		for ($i=0; $i < $requiredFieldsNumber; $i++) { 
			if(!array_key_exists($requiredFields[$i], $_POST))
			{
				$_SESSION['alertMessages']['error'][] = 'Veuillez entrer des informations valides.';
				header('Location:'.CLIENT_ROOT.'orders/manageForm');
				exit();
			}
			if(empty($_POST[$requiredFields[$i]]))
			{
				$_SESSION['alertMessages']['error'][] = 'Veuillez remplir tous les champs obligatoires.';
				header('Location:'.CLIENT_ROOT.'orders/manageForm');
				exit();
			}
		}

		//mise au format SQL de la date
		$purchaseDate = $enteredData['purchaseDate'];
		$pieces = explode('/', $purchaseDate);
		$year = explode(' ', $pieces[2]);
		$enteredData['purchaseDate'] = $year[0].'-'.$pieces[1].'-'.$pieces[0].' '.$year[1].':00';

		//mise à jour dans la BDD
		$requiredData = $enteredData;
		$ordersManager = new OrdersManager();
		$orderUpdated = $ordersManager -> update($requiredData);

		if($orderUpdated)
		{
			$_SESSION['alertMessages']['success'][] = 'La commande a bien été mise à jour !';
			header('Location:'.CLIENT_ROOT.'orders/manageForm');
			exit();
			
		}
		else
		{
			$_SESSION['alertMessages']['error'][] = 'La mise à jour de la commande a échoué !';
			header('Location:'.CLIENT_ROOT.'orders/manageForm');
			exit();
		}
	}
}
 ?>