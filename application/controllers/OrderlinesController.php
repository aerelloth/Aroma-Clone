<?php 

class OrderlinesController extends Controller
{
	//affichage du formulaire admin de gestion CRUD
	public function manageFormAction() 
	{
		//vérification du statut d'admin
		$customersManager = new CustomersManager();
		$customer = $customersManager -> getLoginInfo();
		if (empty($customer) OR $customer['email'] != ADMIN_EMAIL)
		{
			header('Location:'.CLIENT_ROOT.'customers/account');
			exit();
		}

		//vérification des données reçues
		if (!array_key_exists('id', $_GET))
		{
			header('Location:'.CLIENT_ROOT.'customers/account');
            exit();
		}

		//récupération des éventuelles données entrées erronées
		if (isset($_SESSION['forms']['adminAddOrderline']['fieldsContent']))
		{
			$this -> viewData['enteredData'] = $_SESSION['forms']['adminAddOrderline']['fieldsContent'];
		}

		//récupération de la commande ciblée
		$id = $_GET['id'];
		$ordersManager = new OrdersManager();
		$order = $ordersManager -> getOneById($id);

		//récupération des lignes de commande
		$orderLinesManager = new OrderLinesManager();
		$order['orderLines'] = $orderLinesManager -> getByIdOrder($id);
		//initialisation du total de la commande
		$order['totalAmount'] = 0;
		$orderLinesNumber = count($order['orderLines']);
		for ($i=0; $i < $orderLinesNumber; $i++) { 
			//calcul du sous-total
			$order['orderLines'][$i]['subTotal'] = $order['orderLines'][$i]['TTCPrice']*$order['orderLines'][$i]['quantity'];
			//mise à jour du total de la commande
			$order['totalAmount'] += $order['orderLines'][$i]['subTotal'];
		}
		$this -> viewData['order'] = $order;
		$this -> viewData['CSRFToken'] = $customer['CSRFToken'];

		//récupération de la liste des produits pour le "select"
		$productsManager = new ProductsManager();
		$allProducts = $productsManager -> getAll();
		$this -> viewData['allProducts'] = $allProducts;
		
		$this -> generateView('orderlines/manage-form.phtml');
	}

/* ---------- Opérations admin ---------- */

	//suppression admin d'une ligne de commande
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

		//suppression de la base de données
		$orderId = $_GET['orderlineId'];
		$orderLinesManager = new OrderLinesManager();
		$orderlineRemoved = $orderLinesManager -> removeOne($orderId);
		if ($orderlineRemoved)
		{
			$id = $_GET['id'];
			$location = 'manageForm?id='.$id;
			$_SESSION['alertMessages']['success'][] = 'La ligne a bien été supprimée.';
			header('Location:'.$location);
			exit();
		}
		else 
		{
			$id = $_GET['id'];
			$location = 'manageForm?id='.$id;
			$_SESSION['alertMessages']['error'][] = 'La suppression de la ligne a échoué.';
			header('Location:'.$location);
			exit();
		}
	}

	//ajout admin d'une ligne de commande
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
		$requiredData =
		[
			'idProduct' => trim($_POST['newIdProduct']),
			'idOrder' => trim($_POST['idOrder']),
			'nameProduct' => 'nameProduct',
			'priceHTProduct' => 'priceHTProduct',
			'quantity' => trim($_POST['newQuantity']),
			'VATRateProduct' => 'VATRateProduct'
		];

		$requiredFields = ['nameProduct', 'priceHTProduct', 'VATRateProduct', 'quantity', 'idProduct', 'idOrder'];
		
		$requiredFieldsNumber = count($requiredFields);

		for ($i=0; $i < $requiredFieldsNumber; $i++) { 
			if(!array_key_exists($requiredFields[$i], $requiredData))
			{
				$_SESSION['alertMessages']['error'][] ='Veuillez entrer des informations valides.';
				header('Location:'.CLIENT_ROOT.'orderlines/manageForm');
				exit();
			}
			if($requiredData[$requiredFields[$i]] === '')
			{
				$_SESSION['alertMessages']['error'][] = 'Veuillez remplir tous les champs obligatoires.';
				header('Location:'.CLIENT_ROOT.'orderlines/manageForm');
				exit();
			}
		}

		//récupération des infos produits supplémentaires dans la BDD
		$productsManager = new ProductsManager();
		$productInfo = $productsManager -> getOneById($requiredData['idProduct']);

		$requiredData['nameProduct'] = $productInfo['productName'];
		$requiredData['priceHTProduct'] = $productInfo['priceHT'];
		$requiredData['VATRateProduct'] = $productInfo['VATRate'];

		//ajout de la ligne de commandes dans la BDD
		$orderLinesManager = new OrderLinesManager();
		$idOrderline = $orderLinesManager -> addOne($requiredData);

		if ($idOrderline != 0)
		{
			$_SESSION['alertMessages']['success'][] = 'Le produit a bien été ajouté !';
			$id = $requiredData['idOrder'];
			$location = 'manageForm?id='.$id;
			header('Location:'.$location);
			exit();
		}

		else
		{
			$_SESSION['alertMessages']['error'][] = 'L\'ajout du produit a échoué !';
			$id = $requiredData['idOrder'];
			$location = 'manageForm?id='.$id;
			header('Location:'.$location);
			exit();
		}
	}

	//mise à jour admin d'une ligne de commande via AJAX
	public function updateAction()
	{
		//vérification statut admin
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
		$requiredData =
		[
			'idProduct' => trim($_POST['idProduct']),
			'idOrder' => trim($_POST['idOrder']),
			'nameProduct' => trim($_POST['nameProduct']),
			'priceHTProduct' => trim($_POST['priceHTProduct']),
			'quantity' => trim($_POST['quantity']),
			'VATRateProduct' => trim($_POST['VATRateProduct']),
			'id' => trim($_POST['id'])
		];

		$requiredFields = ['id', 'idProduct', 'idOrder', 'nameProduct', 'priceHTProduct', 'quantity', 'VATRateProduct'];
		$requiredFieldsNumber = count($requiredFields);

		// vérification des champs
		for ($i=0; $i < $requiredFieldsNumber; $i++) { 
			if(!array_key_exists($requiredFields[$i], $_POST))
			{
				$_SESSION['alertMessages']['error'][] = 'Veuillez entrer des informations valides.';
				header('Location:'.CLIENT_ROOT.'orderlines/manageForm');
				exit();
			}
			if(empty($_POST[$requiredFields[$i]]))
			{
				$_SESSION['alertMessages']['error'][] = 'Veuillez remplir tous les champs obligatoires.';
				header('Location:'.CLIENT_ROOT.'orderlines/manageForm');
				exit();
			}
		}

		//mise à jour dans la BDD
		$orderLinesManager = new OrderLinesManager();
		$orderlineUpdated = $orderLinesManager -> update($requiredData);
		if($orderlineUpdated)
		{
			$_SESSION['alertMessages']['success'][] = 'La ligne a bien été mise à jour !';
			$id = $requiredData['idOrder'];
			$location = 'manageForm?id='.$id;
			header('Location:'.$location);
			exit();
			
		}
		else
		{
			$_SESSION['alertMessages']['error'][] = 'La mise à jour de la ligne a échoué !';
			$id = $requiredData['idOrder'];
			$location = 'manageForm?id='.$id;
			header('Location:'.$location);
			exit();
		}
	}
}
 ?>