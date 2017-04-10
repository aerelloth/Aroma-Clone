<?php 

class CategoriesController extends Controller
{
	//liste des catégories
	public function showAllAction() 
	{
		$categoriesManager = new CategoriesManager();
		$this->viewData['categories'] = $categoriesManager -> getAll();
		$this -> generateView('categories/show-all.phtml');
	}

	//formulaire de gestion CRUD
	public function manageFormAction()
	{
		// redirection des non-admins
		$customersManager = new CustomersManager();
		$customer = $customersManager -> getLoginInfo();
		if (empty($customer) OR $customer['email'] != ADMIN_EMAIL)
		{
			header('Location: account');
			exit();
		}
		//sécurisation CSRF des actions sensibles
		$this -> viewData['CSRFToken'] = $customer['CSRFToken'];

		//affichage de la liste des catégories
		$categoriesManager = new CategoriesManager();
		$this -> viewData['categories'] = $categoriesManager -> getAll();
		$this -> generateView('categories/manage-form.phtml');
	}

	//ajout de catégorie
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
		if(!array_key_exists('CSRFToken', $customer) OR !array_key_exists('CSRFToken', $_POST) OR $_POST['CSRFToken'] !== $customer['CSRFToken'])
		{
			header('Location:'.CLIENT_ROOT.'customers/account');
            exit();
		}

		//vérification des données reçues
		if (!isset($_POST) OR !isset($_POST['name']))
		{
			header('Location:'.CLIENT_ROOT.'customers/account');
			exit();
		}

		//ajout de la catégorie
		$categoriesManager = new CategoriesManager();
		$categoryAdded = $categoriesManager -> addOne($_POST['name']);
		if ($categoryAdded)
		{
			$_SESSION['alertMessages']['success'][] = 'La nouvelle catégorie a bien été ajoutée.';
			header('Location: manageForm');
			exit();
		}
		else 
		{
			$_SESSION['alertMessages']['error'][] = 'L\'ajout de la nouvelle catégorie a échoué.';
			header('Location: manageForm');
			exit();
		}
	}

	//mise à jour d'une catégorie via AJAX
	public function updateOneAction()
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
		if (!array_key_exists('name', $_POST) OR !array_key_exists('id', $_POST))
		{
			header('Location:'.CLIENT_ROOT.'customers/account');
            exit();
		}

		//mise à jour de la catégorie
		$requiredData =
		[
			'name' => trim($_POST['name']),
			'id' => trim($_POST['id'])
		];

		$categoriesManager = new CategoriesManager();
		$categoryUpdated = $categoriesManager -> updateOne($requiredData);

		if($categoryUpdated)
		{
			$_SESSION['alertMessages']['success'][] = 'La catégorie a bien été mise à jour !';
			header('Location: manageForm');
            exit();
			
		}
		else
		{
			$_SESSION['alertMessages']['error'][] = 'La mise à jour de la catégorie a échoué !';
			header('Location: manageForm');
            exit();
		}
	}

	//suppression d'une catégorie
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

		//suppression de la catégorie
		$id = $_GET['id'];
		$categoriesManager = new CategoriesManager();
		$categoryRemoved = $categoriesManager -> removeOne($id);
		if ($categoryRemoved)
		{
			$_SESSION['alertMessages']['success'][] = 'La catégorie a bien été supprimée.';
			header('Location: manageForm');
			exit();
		}
		else 
		{
			$_SESSION['alertMessages']['error'][] = 'La suppression de la catégorie a échoué.';
			header('Location: manageForm');
			exit();
		}
	}
}
 ?>