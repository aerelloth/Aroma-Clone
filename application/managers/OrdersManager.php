<?php 

class OrdersManager extends DBManager
{
	//récupération de toutes les commandes
	public function getAll() 
	{
		$query = 
		'SELECT
			id AS orderId, DATE_FORMAT(purchaseDate, "%d/%m/%Y %H:%i") AS purchaseDate, billingCivility, billingFirstName, billingLastName, billingAddress, billingZipCode, billingCity, billingCountry, billingPhoneNumber, deliveryCivility, deliveryFirstName, deliveryLastName, deliveryAddress, deliveryZipCode, deliveryCity, deliveryCountry, deliveryPhoneNumber, idCustomer
		FROM
			Orders
		';
		$resultSet = $this -> getDBConnection() -> query($query);
		$orders = $resultSet -> fetchAll();
		return $orders;
	}

	//récupération d'une commande ciblée par id
	public function getOneById($id) 
	{
		$query = 
		'SELECT
			id AS orderId, DATE_FORMAT(purchaseDate, "%d/%m/%Y") AS purchaseDate, billingCivility, billingFirstName, billingLastName, billingAddress, billingZipCode, billingCity, billingCountry, billingPhoneNumber, deliveryCivility, deliveryFirstName, deliveryLastName, deliveryAddress, deliveryZipCode, deliveryCity, deliveryCountry, deliveryPhoneNumber, idCustomer
		FROM
			Orders
		WHERE
			id = ?
		';
		$resultSet = $this -> getDBConnection() -> prepare($query);
		$resultSet -> execute([$id]);
		$order = $resultSet -> fetch();
		return $order;
	}

	//récupération des commandes d'un client ciblé par id
	public function getByCustomerId($id) 
	{
		$query = 
		'SELECT
			id AS orderId, DATE_FORMAT(purchaseDate, "%d/%m/%Y") AS purchaseDate, billingCivility, billingFirstName, billingLastName, billingAddress, billingZipCode, billingCity, billingCountry, billingPhoneNumber, deliveryCivility, deliveryFirstName, deliveryLastName, deliveryAddress, deliveryZipCode, deliveryCity, deliveryCountry, deliveryPhoneNumber, idCustomer
		FROM
			Orders
		WHERE
			idCustomer = ?
		';
		$resultSet = $this -> getDBConnection() -> prepare($query);
		$resultSet -> execute([$id]);
		$orders = $resultSet -> fetchAll();
		return $orders;
	}

	//récupération des commandes d'un client ciblé par id sur une page précise
	public function getPageByCustomerId($id, $paginationStart, $numberByPage) 
	{
		$query = 
		'SELECT
			id AS orderId, DATE_FORMAT(purchaseDate, "%d/%m/%Y") AS purchaseDate, billingCivility, billingFirstName, billingLastName, billingAddress, billingZipCode, billingCity, billingCountry, billingPhoneNumber, deliveryCivility, deliveryFirstName, deliveryLastName, deliveryAddress, deliveryZipCode, deliveryCity, deliveryCountry, deliveryPhoneNumber, idCustomer
		FROM
			Orders
		WHERE
			idCustomer = ?
		LIMIT
			?, ?
		';
		$resultSet = $this -> getDBConnection() -> prepare($query);
		$resultSet->bindValue(1, $id);
		$resultSet->bindValue(2, $paginationStart, PDO::PARAM_INT);
		$resultSet->bindValue(3, $numberByPage, PDO::PARAM_INT);
		$resultSet->execute();
		$orders = $resultSet -> fetchAll();
		return $orders;
	}

	//récupération du nombre de commandes passées par un client
	public function countByCustomerId($id) 
	{
		$query =
			'SELECT
				COUNT(id) AS ordersNumber
			FROM
				Orders
			WHERE
				idCustomer = ?
			';
		$resultSet = $this -> getDBConnection() -> prepare($query);
		$resultSet -> execute([$id]);
		$ordersNumber = $resultSet -> fetchColumn();
		return $ordersNumber;
	}

	//ajout d'une commande
	public function addOne($requiredData) 
	{
		$query =
			'INSERT INTO
				Orders(purchaseDate, billingCivility, billingFirstName, billingLastName, billingAddress, billingZipCode, billingCity, billingCountry, billingPhoneNumber, deliveryCivility, deliveryFirstName, deliveryLastName, deliveryAddress, deliveryZipCode, deliveryCity, deliveryCountry, deliveryPhoneNumber, idCustomer)
			VALUES
				(NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
			';
		$resultSet = $this -> getDBConnection() -> prepare($query);
		$resultSet -> execute(array_values($requiredData));

		$idOrder = $this -> getDBConnection() -> lastInsertId();
		return $idOrder;
	}

	//ajout admin d'une commande
	public function adminAddOne($requiredData) 
	{
		$query =
			'INSERT INTO
				Orders(purchaseDate, billingCivility, billingFirstName, billingLastName, billingAddress, billingZipCode, billingCity, billingCountry, billingPhoneNumber, deliveryCivility, deliveryFirstName, deliveryLastName, deliveryAddress, deliveryZipCode, deliveryCity, deliveryCountry, deliveryPhoneNumber, idCustomer)
			VALUES
				(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
			';
		$resultSet = $this -> getDBConnection() -> prepare($query);
		$resultSet -> execute(array_values($requiredData));

		$idOrder = $this -> getDBConnection() -> lastInsertId();
		return $idOrder;
	}

	//suppression d'une commande
	public function removeOne($id)
	{
		$query =
			'DELETE FROM
				Orders
			WHERE
				id = ?
			';
		$resultSet = $this -> getDBConnection() -> prepare($query);
		$resultSet -> execute([$id]);
		$orderRemoved = ($resultSet->rowCount() == 1);
		return $orderRemoved;
	}

	//mise à jour d'une commande
	public function update($requiredData) 
	{
		$query =
			'UPDATE
				Orders
			SET
				purchaseDate = ?, billingCivility = ?, billingFirstName = ?, billingLastName = ?, billingAddress = ?, billingZipCode = ?, billingCity = ?, billingCountry = ?, billingPhoneNumber = ?, deliveryCivility = ?, deliveryFirstName = ?, deliveryLastName = ?, deliveryAddress = ?, deliveryZipCode = ?, deliveryCity = ?, deliveryCountry = ?, deliveryPhoneNumber = ?, idCustomer = ?
			WHERE
				id = ?
			';
		$resultSet = $this -> getDBConnection() -> prepare($query);
		$resultSet -> execute(array_values($requiredData));
		$orderUpdated = ($resultSet->rowCount() == 1 OR $resultSet->rowCount() == 0);
		return $orderUpdated;
	}
}
 ?>