<?php 

class ShoppingCartController extends Controller
{
	//affichage du panier
	public function showAction() 
	{
		$shoppingCartManager = new ShoppingCartManager();

		//si le panier existe
		if (array_key_exists('shoppingCart', $_SESSION))
		{
			//récupération des infos du panier
			$this -> viewData['shoppingCart'] = $shoppingCart = $shoppingCartManager -> get();
			$this -> viewData['productsNumber'] = count($shoppingCart['products']);
		}
		else 
		{
			//sinon, le panier est vide
			$this -> viewData['productsNumber'] = 0;
		}
		$this -> generateView('shoppingCart/show.phtml');
	}

	//ajout d'un produit dans le panier
	public function addProductAction()
	{
		//vérification des données reçues
		if(!array_key_exists('id', $_GET))
		{
			$_SESSION['alertMessages']['error'][] = 'Vous n\'avez sélectionné aucun produit.';
			header('Location:'.CLIENT_ROOT);
			exit();
		}

		//ajout du produit correspondant à l'id
		$id = $_GET['id'];
		$shoppingCartManager = new ShoppingCartManager();
		$shoppingCartManager -> addProduct($id);
		header('Location:'.CLIENT_ROOT);
		exit();
	}

	//ajout d'un produit dans le panier depuis l'historique des commandes
	public function addFromOrderAction()
	{
		//vérification des données reçues
		if(!array_key_exists('id', $_GET))
		{
			$_SESSION['alertMessages']['error'][] = 'Vous n\'avez sélectionné aucun produit.';
			header('Location:'.CLIENT_ROOT);
			exit();
		}

		//récupération de l'id des produits de la commande
		$orderLinesManager = new OrderLinesManager();
		$orderLines = $orderLinesManager -> getByIdOrder($_GET['id']);

		//ajout dans le panier des produits correspondant en même quantité
		$shoppingCartManager = new ShoppingCartManager();
		$productsNumber = count($orderLines);
		for ($i=0; $i < $productsNumber; $i++) { 
			$id = $orderLines[$i]['idProduct'];
			$shoppingCartManager -> addProduct($id);
			$_SESSION['shoppingCart']['products'][$id]['quantity'] = $orderLines[$i]['quantity'];
		}

		header('Location:'.CLIENT_ROOT.'shoppingCart/show');
		exit();
	}

	//suppression d'un produit du panier
	public function removeProductAction()
	{
		//vérification des données reçues
		if(!array_key_exists('id', $_GET))
		{
			header('Location:'.CLIENT_ROOT.'shoppingCart/show');
			exit();
		}

		//suppression du produit
		$id = $_GET['id'];
		$shoppingCartManager = new ShoppingCartManager();
		$shoppingCartManager -> removeProduct($id);
		header('Location:'.CLIENT_ROOT.'shoppingCart/show');
		exit();
	}
	
	//mise à jour du panier 
	public function updateAction()
	{
		//vérification des données reçues
		if (array_key_exists('shoppingCart', $_POST) AND array_key_exists('products', $_POST['shoppingCart']))
		{
			//mise à jour du panier
			$newQuantities = $_POST['shoppingCart']['products'];
			$shoppingCartManager = new ShoppingCartManager();
			$shoppingCartManager -> update($newQuantities);
		}
		else
		{
			header('Location:'.CLIENT_ROOT.'shoppingCart/show');
			exit();
		}
		header('Location:'.CLIENT_ROOT.'shoppingCart/show');
		exit();
	}
}
 ?>