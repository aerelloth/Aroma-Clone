<?php 

class CustomersManager extends DBManager
{
	//récupération des infos de connexion
	public function getLoginInfo()
	{
		if (session_status() !== PHP_SESSION_ACTIVE)
		{
			session_start();
			session_regenerate_id();
		}

		if (isset($_SESSION['customer']))
		{
			$customer = $_SESSION['customer'];
			return $customer;
		}
	}

	//récupération de la liste des clients
	public function getAll() 
	{
		$query = 
		'SELECT
			id, email, passwordHash, civility, firstName, lastName, address, zipCode, city, country, phoneNumber
		FROM
			Customers
		ORDER BY
			lastName
		';
		$resultSet = $this -> getDBConnection() -> query($query);
		$customers = $resultSet -> fetchAll();
		return $customers;
	}

	//récupération des infos du client ciblé par email
	public function getOneByEmail($email) 
	{
		$query =
			'SELECT
				id, email, passwordHash, civility, firstName, lastName, address, zipCode, city, country, phoneNumber
			FROM
				Customers
			WHERE
				email = ?
			ORDER BY
				lastName
			';
		$resultSet = $this -> getDBConnection() -> prepare($query);
		$resultSet -> execute([$email]);
		$customer = $resultSet -> fetch();
		return $customer;
	}

	//récupération des infos du client ciblé par id
	public function getOneById($id) 
	{
		$query =
			'SELECT
				id, email, passwordHash, civility, firstName, lastName, address, zipCode, city, country, phoneNumber
			FROM
				Customers
			WHERE
				id = ?
			ORDER BY
				lastName
			';
		$resultSet = $this -> getDBConnection() -> prepare($query);
		$resultSet -> execute([$id]);
		$customer = $resultSet -> fetch();
		return $customer;
	}

	//ajout d'un client
	public function addOne($requiredData) 
	{
		$query =
			'INSERT INTO
				Customers(email, passwordHash, civility, firstName, lastName, address, zipCode, city, country, phoneNumber)
			VALUES
				(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
			';
		$resultSet = $this -> getDBConnection() -> prepare($query);
		$resultSet -> execute($requiredData);
		$userAdded = ($resultSet->rowCount() == 1);
		return $userAdded;
	}

	//suppression d'un client
	public function deleteOne($id) 
	{
		$query =
			'DELETE FROM
				Customers
			WHERE
				id = ?
			';
		$resultSet = $this -> getDBConnection() -> prepare($query);
		$resultSet -> execute([$id]);
		$accountDeleted = ($resultSet->rowCount() == 1);
		return $accountDeleted;
	}

	//mise à jour des infos client
	public function update($requiredData) 
	{
		$query =
			'UPDATE
				Customers
			SET
				email = ?, civility = ?, firstName = ?, lastName = ?, address = ?, zipCode = ?, city = ?, country = ?, phoneNumber = ?
			WHERE
				id = ?
			';
		$resultSet = $this -> getDBConnection() -> prepare($query);
		$resultSet -> execute($requiredData);
		$userUpdated = ($resultSet->rowCount() == 1 OR $resultSet->rowCount() == 0);
		return $userUpdated;
	}

	//mise à jour du mot de passe client
	public function passwordUpdate($requiredData) 
	{
		$query =
			'UPDATE
				Customers
			SET
				passwordHash = ?
			WHERE
				id = ?
			';
		$resultSet = $this -> getDBConnection() -> prepare($query);
		$resultSet -> execute($requiredData);
		$userUpdated = ($resultSet->rowCount() == 1 OR $resultSet->rowCount() == 0);
		return $userUpdated;
	}
}
 ?>