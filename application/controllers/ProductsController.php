<?php 

class ProductsController extends Controller
{
	//affichage de tous les produits
	public function showAllAction() 
	{
		$productsManager = new ProductsManager();
		$this->viewData['products'] = $products = $productsManager -> getAll();
		$this->viewData['productsNumber'] = count($products);
		$this -> generateView('products/show-all.phtml');
	}

	//affichage des produits avec pagination
	public function showSomeAction() 
	{
		//récupération des produits à afficher sur la page
		$productsManager = new ProductsManager();
		$currentPage = array_key_exists('page', $_GET)? $_GET['page'] : 1;
		$numberByPage = PRODUCTS_BY_PAGE;
		$paginationStart = array_key_exists('page', $_GET)? ($_GET['page']-1)*$numberByPage : 0;
		$paginationStartShowed = array_key_exists('page', $_GET)? $paginationStart+1 : 1;
		$productsNumber = $productsManager -> count();
		$pagesNumber = ceil($productsNumber / $numberByPage);
		$paginationEnd = $paginationStart + $numberByPage > $productsNumber? $productsNumber : $paginationStart + $numberByPage;
		$products = $productsManager -> getAllOnPage($paginationStart, $numberByPage);

		$this->viewData['pagination'] = $pagination = 
		[
			'currentPage' => $currentPage,
			'paginationStart' => $paginationStart,
			'paginationStartShowed' => $paginationStartShowed,
			'numberByPage' => $numberByPage,
			'productsNumber' => $productsNumber,
			'pagesNumber' => $pagesNumber,
			'paginationEnd' => $paginationEnd,
			'products' => $products
		];

		//vérification du numéro de page
		if ($pagination['currentPage'] < 1 OR $pagination['currentPage'] > $pagination['pagesNumber']) 
		{
			header('Location:'.CLIENT_ROOT);
			exit();
		}
		$this -> generateView('products/show-some.phtml');
	}

	//affichage d'un produit en détail
	public function showOneAction()
	{
		//récupération des infos produit
		$id = $_GET['id'];
		$productsManager = new ProductsManager();
		$this->viewData['product'] = $productsManager -> getOneById($id);
		$this -> generateView('products/show-one.phtml');
	}

	//affichage des produits par catégorie
	public function showByCategoryAction() 
	{
		//vérification des données reçues
		if (!array_key_exists('id', $_GET))
		{
			header('Location:'.CLIENT_ROOT);
			exit();
		}

		//récupération des infos de la catégorie recherchée
		$categoriesManager = new CategoriesManager();
		$id = $_GET['id'];
		$this->viewData['category'] = $category = $categoriesManager -> getOneById($id);

		//redirection si la catégorie n'existe pas
		if ($category === false)
		{
			header('Location:'.CLIENT_ROOT);
			exit();
		}

		//récupération des infos produits
		$productsManager = new ProductsManager();
		$this->viewData['products'] = $productsManager -> getByCategory($id);
		$this -> generateView('products/show-by-category.phtml');
	}

	//affichage des produits correspondant à une recherche
	public function showSearchResultAction()
	{
		//vérification des données reçues
		if(!array_key_exists('search', $_GET))
		{
			header('Location'.CLIENT_ROOT);
			exit();
		}

		$this -> viewData['search'] = $search = trim($_GET['search']);

		//si la recherche est vide
		if (empty ($search))
		{
			header('Location:'.CLIENT_ROOT);
			exit();
		}

		//récupération des infos produits correspondants
		$productsManager = new ProductsManager();
		$this -> viewData['products'] = $productsManager -> search($search);
		$this -> generateView('products/show-search-result.phtml');
	}

	//affichage du formulaire admin de gestion CRUD
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
		if (isset($_SESSION['forms']['adminAddProduct']['fieldsContent']))
		{
			$this -> viewData['enteredData'] = $_SESSION['forms']['adminAddProduct']['fieldsContent'];
		}

		//récupération des infos produits
		$productsManager = new ProductsManager();
		$this -> viewData['products'] = $productsManager -> getAll();
		//récupération des infos de catégories
		$categoriesManager = new CategoriesManager();
		$this -> viewData['categories'] = $categoriesManager -> getAll();
		$this -> viewData['CSRFToken'] = $customer['CSRFToken'];
		$this -> generateView('products/manage-form.phtml');
	}

	//suppression admin d'un produit
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
		if(!array_key_exists('CSRFToken', $customer) OR !array_key_exists('CSRFToken', $_GET) OR $_GET['CSRFToken'] !== $customer['CSRFToken'])
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

		//suppression du produit dans la BDD
		$id = $_GET['id'];
		$productsManager = new ProductsManager();
		$productRemoved = $productsManager -> removeOne($id);
		if ($productRemoved)
		{
			$_SESSION['alertMessages']['success'][] = 'Le produit a bien été supprimé.';
			header('Location: manageForm');
			exit();
		}
		else 
		{
			$_SESSION['alertMessages']['error'][] = 'La suppression du produit a échoué.';
			header('Location: manageForm');
			exit();
		}
	}

	//ajout admin d'un produit
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
			'name' => trim($_POST['productName']),
			'description' => trim($_POST['description']),
			'imagePath' => (!empty($_POST['imagePath']))? trim($_POST['imagePath']) : null,
			'priceHT' => trim($_POST['priceHT']),
			'VATRate' => trim($_POST['VATRate']),
			'idCategory' => trim($_POST['idCategory']),
		];

		//mémorisation pour ré-afficher les données entrées en cas d'erreur
		$_SESSION['forms']['adminAddProduct']['fieldsContent'] = $enteredData;
		$requiredFields = ['name', 'description', 'imagePath', 'priceHT', 'VATRate', 'idCategory'];
		$requiredFieldsNumber = count($requiredFields);

		for ($i=0; $i < $requiredFieldsNumber; $i++) { 
			if(!array_key_exists($requiredFields[$i], $enteredData))
			{
				$_SESSION['alertMessages']['error'][] ='Veuillez entrer des informations valides.';
				header('Location:'.CLIENT_ROOT.'products/manageForm');
				exit();
			}
			if(empty($enteredData[$requiredFields[$i]]))
			{
				$_SESSION['alertMessages']['error'][] = 'Veuillez remplir tous les champs obligatoires.';
				header('Location:'.CLIENT_ROOT.'products/manageForm');
				exit();
			}
		}

		//ajout dans la BDD
		$requiredData = $enteredData;
		$productsManager = new ProductsManager();
		$productAdded = $productsManager -> addOne($requiredData);

		if($productAdded)
		{
			$_SESSION['alertMessages']['success'][] = 'Le produit a bien été ajouté !';
			unset($_SESSION['forms']['adminAddProduct']['fieldsContent']);
			header('Location:'.CLIENT_ROOT.'products/manageForm');
			exit();
			
		}
		else
		{
			$_SESSION['alertMessages']['error'][] = 'L\'ajout du produit a échoué !';
			header('Location:'.CLIENT_ROOT.'products/manageForm');
			exit();
		}
	}

	//modification admin d'un produit via AJAX
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
			'name' => trim($_POST['name']),
			'description' => trim($_POST['description']),
			'imagePath' => trim($_POST['imagePath']),
			'priceHT' => trim($_POST['priceHT']),
			'VATRate' => trim($_POST['VATRate']),
			'idCategory' => trim($_POST['idCategory']),
			'id' => trim($_POST['id'])
		];

		$requiredFields = ['id', 'name', 'description', 'imagePath', 'priceHT', 'VATRate', 'idCategory'];
		$requiredFieldsNumber = count($requiredFields);

		// vérification des champs
		for ($i=0; $i < $requiredFieldsNumber; $i++) { 
			if(!array_key_exists($requiredFields[$i], $_POST))
			{
				$_SESSION['alertMessages']['error'][] = 'Veuillez entrer des informations valides.';
				header('Location:'.CLIENT_ROOT.'products/manageForm');
				exit();
			}
			if(empty($_POST[$requiredFields[$i]]))
			{
				$_SESSION['alertMessages']['error'][] = 'Veuillez remplir tous les champs obligatoires.';
				header('Location:'.CLIENT_ROOT.'products/manageForm');
				exit();
			}
		}

		//mise à jour des infos dans la BDD
		$requiredData = $enteredData;
		$productsManager = new ProductsManager();
		$productUpdated = $productsManager -> update($requiredData);

		if($productUpdated)
		{
			$_SESSION['alertMessages']['success'][] = 'Le produit a bien été mis à jour !';
			header('Location:'.CLIENT_ROOT.'products/manageForm');
			exit();
			
		}
		else
		{
			$_SESSION['alertMessages']['error'][] = 'La mise à jour du produit a échoué !';
			header('Location:'.CLIENT_ROOT.'products/manageForm');
			exit();
		}
	}

	//affichage admin d'un produit en détail avec ses stats
	public function adminShowOneAction()
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

		//récupération des infos produit
		$id = $_GET['id'];
		$productsManager = new ProductsManager();
		$this->viewData['product'] = $productsManager -> getOneWithStats($id);
		$this->viewData['total'] = $productsManager -> getTotal($id);
		$this -> generateView('products/admin-show-one.phtml');
	}

	//affichage admin des stats sur tous les produits
	public function adminStatsAction()
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
		
		//récupération des infos produits
		$productsManager = new ProductsManager();
		$products = $productsManager -> getAllWithStats();

		//pour tous les produits, récupération du total des ventes s'ils ont déjà été vendus
		$productsNumber = $productsManager -> count();
		for ($i=0; $i < $productsNumber; $i++) { 
			if ($products[$i]['quantityOrdered'] === null) {
				$products[$i]['quantityOrdered'] = 0;
				$products[$i]['total']['HTTotal'] = 0;
				$products[$i]['total']['TTCTotal'] = 0;
			}
			else {
				$products[$i]['total'] = $productsManager -> getTotal($products[$i]['productId']); 
			}

		}
		$this -> viewData['products'] = $products;

		//récupération des infos de catégories
		$categoriesManager = new CategoriesManager();
		$this -> viewData['categories'] = $categoriesManager -> getAll();
		$this -> viewData['CSRFToken'] = $customer['CSRFToken'];
		$this -> generateView('products/admin-stats.phtml');
	}
}
 ?>