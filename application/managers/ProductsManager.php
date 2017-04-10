<?php 

class ProductsManager extends DBManager
{
	//récupération des infos de tous les produits 
	public function getAll() 
	{
		$query = 
		'SELECT
			Products.id AS productId, Products.name AS productName, description, imagePath, priceHT, VATRate, ROUND(priceHT + priceHT* VATRate/100, 2) AS priceTTC, Categories.id AS categoryId, Categories.name AS categoryName
		FROM
			Products
		INNER JOIN
			Categories
		ON
			Products.idCategory = Categories.id
		ORDER BY
			Products.name
		';
		$resultSet = $this -> getDBConnection() -> query($query);
		$products = $resultSet -> fetchAll();
		return $products;
	}

	//récupération des infos des produits sur une page précise
	public function getAllOnPage($paginationStart, $numberByPage) 
	{
		$query = 
		'SELECT
			Products.id AS productId, Products.name AS productName, description, imagePath, priceHT, VATRate, ROUND(priceHT + priceHT* VATRate/100, 2) AS priceTTC, Categories.id AS categoryId, Categories.name AS categoryName
		FROM
			Products
		INNER JOIN
			Categories
		ON
			Products.idCategory = Categories.id
		ORDER BY
			Products.name
		LIMIT
			?, ?
		
		';
		$resultSet = $this -> getDBConnection() -> prepare($query);
		$resultSet->bindValue(1, $paginationStart, PDO::PARAM_INT);
		$resultSet->bindValue(2, $numberByPage, PDO::PARAM_INT);
		$resultSet->execute();
		$products = $resultSet -> fetchAll();
		return $products;
	}

	//récupération des infos d'un produit
	public function getOneById($id) 
	{
		$query =
			'SELECT
				Products.id AS productId, Products.name AS productName, description, imagePath, priceHT, VATRate, ROUND(priceHT + priceHT* VATRate/100, 2) AS priceTTC, Categories.id AS categoryId, Categories.name AS categoryName
			FROM
				Products
			INNER JOIN
				Categories
			ON
				Products.idCategory = Categories.id
			WHERE
				Products.id = ?
			';
		$resultSet = $this -> getDBConnection() -> prepare($query);
		$resultSet -> execute([$id]);
		$product = $resultSet -> fetch();
		return $product;
	}

	//récupération des infos d'un produit pour le panier
	public function getOneForCart($id) 
	{
		$query =
			'SELECT
				id AS productId, name AS productName, priceHT, VATRate, ROUND(priceHT + priceHT* VATRate/100, 2) AS priceTTC
			FROM
				Products
			WHERE
				id = ?
			';
		$resultSet = $this -> getDBConnection() -> prepare($query);
		$resultSet -> execute([$id]);
		$product = $resultSet -> fetch();
		return $product;
	}

	//récupération des infos de tous les produits correspondant à la catégorie ciblée par id
	public function getByCategory($id) 
	{
		$query = 
		'SELECT
			Products.id AS productId, Products.name AS productName, description, imagePath, priceHT, VATRate, ROUND(priceHT + priceHT* VATRate/100, 2) AS priceTTC, Categories.id AS categoryId, Categories.name AS categoryName
		FROM
			Products
		INNER JOIN
			Categories
		ON
			Products.idCategory = Categories.id
		WHERE
			Categories.id = ?
		ORDER BY
			Products.name
		';
		$resultSet = $this -> getDBConnection() -> prepare($query);
		$resultSet -> execute([$id]);
		$products = $resultSet -> fetchAll();
		return $products;
	}

	//récupération des infos de tous les produits dont le nom correspond à la recherche
	public function search($search) 
	{
		$query =
			'SELECT
				Products.id AS productId, Products.name AS productName, description, imagePath, priceHT, VATRate, ROUND(priceHT + priceHT* VATRate/100, 2) AS priceTTC, Categories.id AS categoryId, Categories.name AS categoryName
			FROM
				Products
			INNER JOIN
				Categories
			ON
				Products.idCategory = Categories.id
			WHERE
				Products.name LIKE CONCAT(\'%\',?,\'%\')
			';
		//	Préparation de la requête
		$resultSet = $this -> getDBConnection() -> prepare($query);
		//	Exécution de la requête
		$resultSet -> execute([$search]);
		//	Récupération de l'utilisateur demandé
		$products = $resultSet -> fetchAll();
		return $products;
	}

	//récupération du nombre de produits total
	public function count() 
	{
		$query =
			'SELECT
				COUNT(id) AS productsNumber
			FROM
				Products
			';
		$resultSet = $this -> getDBConnection() -> query($query);
		$productsNumber = $resultSet -> fetchColumn();
		return $productsNumber;
	}

	//récupération des infos d'un produit avec ses statistiques de vente
	public function getOneWithStats($id)
	{
		$query =
			'SELECT
				Products.id AS productId, Products.name AS productName, description, imagePath, priceHT, VATRate, ROUND(priceHT + priceHT* VATRate/100, 2) AS priceTTC, Categories.id AS categoryId, Categories.name AS categoryName, COUNT(OrderLines.idOrder) AS ordersNumber, SUM(OrderLines.quantity) AS quantityOrdered
			FROM
				Products
			INNER JOIN
				Categories
			ON
				Products.idCategory = Categories.id
			LEFT JOIN
				OrderLines
			ON
				Products.id = OrderLines.idProduct
			WHERE
				Products.id = ?
			';
		$resultSet = $this -> getDBConnection() -> prepare($query);
		$resultSet -> execute([$id]);
		$product = $resultSet -> fetch();
		return $product;
	}

	//récupération des infos de tous les produits avec leurs statistiques de vente
	public function getAllWithStats()
	{
		$query =
			'SELECT
				Products.id AS productId, Products.name AS productName, description, imagePath, priceHT, VATRate, ROUND(priceHT + priceHT* VATRate/100, 2) AS priceTTC, Categories.id AS categoryId, Categories.name AS categoryName, COUNT(OrderLines.idOrder) AS ordersNumber, SUM(OrderLines.quantity) AS quantityOrdered
			FROM
				Products
			INNER JOIN
				Categories
			ON
				Products.idCategory = Categories.id
			LEFT JOIN
				OrderLines
			ON
				Products.id = OrderLines.idProduct
			GROUP BY
				Products.id
			ORDER BY
				Products.name
			';
		$resultSet = $this -> getDBConnection() -> query($query);
		$product = $resultSet -> fetchAll();
		return $product;
	}

	//récupération du total HT et TTC des ventes d'un produit ciblé par id
	public function getTotal($id)
	{
		$query =
			'SELECT
				SUM(priceHTProduct * quantity) AS HTTotal, ROUND(SUM((priceHTProduct*(1+VATRateProduct/100)) * quantity), 2) AS TTCTotal
			FROM
				OrderLines
			WHERE
				idProduct = ?
			';
		$resultSet = $this -> getDBConnection() -> prepare($query);
		$resultSet -> execute([$id]);
		$total = $resultSet -> fetch();
		return $total;
	}

	//ajout d'un produit
	public function addOne($requiredData)
	{
		$query =
			'INSERT INTO
				Products(name, description, imagePath, priceHT, VATRate, idCategory)
			VALUES
				(?, ?, ?, ?, ?, ?)
			';
		$resultSet = $this -> getDBConnection() -> prepare($query);
		$resultSet -> execute(array_values($requiredData));
		$productAdded = ($resultSet->rowCount() == 1);
		return $productAdded;
	}

	//suppression d'un produit
	public function removeOne($id)
	{
		$query =
			'DELETE FROM
				Products
			WHERE
				id = ?
			';
		$resultSet = $this -> getDBConnection() -> prepare($query);
		$resultSet -> execute([$id]);
		$productRemoved = ($resultSet->rowCount() == 1);
		return $productRemoved;
	}

	//mise à jour d'un produit
	public function update($requiredData) 
	{
		$query =
			'UPDATE
				Products
			SET
				name = ?, description = ?, imagePath = ?, priceHT = ?, VATRate = ?, idCategory = ?
			WHERE
				id = ?
			';
		$resultSet = $this -> getDBConnection() -> prepare($query);
		$resultSet -> execute(array_values($requiredData));
		$productUpdated = ($resultSet->rowCount() == 1 OR $resultSet->rowCount() == 0);
		return $productUpdated;
	}


}
 ?>