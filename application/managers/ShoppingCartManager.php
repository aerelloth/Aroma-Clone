<?php 

class ShoppingCartManager
{
	public function __construct()
	{
		//ouverture d'une session si besoin
		if (session_status() !== PHP_SESSION_ACTIVE)
		{
			session_start();
			session_regenerate_id();
		}

		//création du panier si besoin
		if (!array_key_exists('shoppingCart', $_SESSION) OR !array_key_exists('products', $_SESSION['shoppingCart']))
		{
			$_SESSION['shoppingCart']['products'] = [];
		}

		//initialisation du total du panier
		$_SESSION['shoppingCart']['totalAmount'] = 0;

			//pour chaque produit du panier
			foreach($_SESSION['shoppingCart']['products'] as $idProduct => $product)
			{
				//calcul du sous-total du produit
				$product['subTotal'] = $product['priceTTC'] * $product['quantity'];
				//enregistrement du produit dans le panier
				$_SESSION['shoppingCart']['products'][$idProduct] = $product;
				//mise à jour du total du panier
				$_SESSION['shoppingCart']['totalAmount'] += $product['subTotal'];
			}
	}

	//récupération du contenu du panier
	public function get() 
	{
		$shoppingCart = $_SESSION['shoppingCart'];
		return $shoppingCart;
	}

	//ajout d'un produit au panier
	public function addProduct($id) 
	{
		//si le produit est déjà dans le panier
		if (array_key_exists($id, $_SESSION['shoppingCart']['products']))
		{
			//sa quantité augmente de 1
			$_SESSION['shoppingCart']['products'][$id]['quantity'] ++;
		}
		//sinon
		else
		{
			//récupération des infos produits
			$productsManager = new ProductsManager();
			$product = $productsManager -> getOneForCart($id);
			//si le produit n'existe pas
			if ($product === false)
			{
				$_SESSION['alertMessages']['error'][] = 'Produit inexistant.';
				header('Location: showSome');
				exit();
			}
			//mise à jour de la quantité et ajout dans le panier
			$product['quantity'] = 1;
			$_SESSION['shoppingCart']['products'][$id] = $product;
		}
	}

	//suppression d'un produit du panier
	public function removeProduct($id) 
	{
		unset($_SESSION['shoppingCart']['products'][$id]);
	}

	//mise à jour du panier
	public function update(array $newQuantities) 
	{
		//pour chaque produit du panier
		foreach ($newQuantities as $idProduct => $newQuantity) {

			//redirection si les données ne sont pas valides
			if ($newQuantity === false)
			{
				$_SESSION['alertMessages']['error'][] = 'Veuillez entrer des informations valides.';
				header('Location:'.CLIENT_ROOT);
				exit();
			}

			//suppression du produit si la quantité est inférieure ou égale à 0
			if ($newQuantity <= 0)
			{
				$this -> removeProduct($idProduct);
			}

			//sinon, mise à jour des quantités dans le panier
			else
			{
				$_SESSION['shoppingCart']['products'][$idProduct]['quantity'] = $newQuantity;
			}
		}
	}
}

 ?>