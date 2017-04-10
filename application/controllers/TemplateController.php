<?php 

	class TemplateController extends Controller
	{
		//affichage de la liste de catégories
		public function showCategories()
		{
			$categoriesManager = new CategoriesManager();
			$this -> viewData['categories'] = $categoriesManager -> getAll();
			$this -> generateView('template-inclusions/show-categories.phtml', null);
		}

		//affichage du mini-panneau de connexion
		public function showLoginStatus()
		{
			//récupération des informations de connexion
			$customersManager = new CustomersManager();
			$this->viewData['customer'] = $customersManager -> getLoginInfo();

			//récupération des éventuelles données entrées erronées
			if(isset($_SESSION['forms']['login']['fieldsContent']))
			{
				$this->viewData['enteredData'] = $_SESSION['forms']['login']['fieldsContent'];
			}
			$this -> generateView('template-inclusions/show-login-status.phtml', null);
		}

		//affichage du mini-panier
		public function showMiniShoppingCart()
		{
			//s'il y a des articles dans le panier
			$shoppingCartManager = new ShoppingCartManager();
			if (array_key_exists('shoppingCart', $_SESSION) && array_key_exists('products', $_SESSION['shoppingCart']) && !empty($_SESSION['shoppingCart']['products']))
			{
				//récupération des infos de panier
				$this -> viewData['shoppingCart'] = $shoppingCart = $shoppingCartManager -> get();
				$this -> viewData['productsNumber'] = count($shoppingCart['products']);
				$this -> generateView('template-inclusions/show-mini-shopping-cart.phtml', null);
			}			
		}
	}

 ?>