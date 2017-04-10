<?php 

class OrderLinesManager extends DBManager
{
	//récupération des lignes de commandes pour une commande ciblée par id
	public function getByIdOrder($idOrder) 
	{
		$query = 
		'SELECT
			id AS orderLineId, idProduct, idOrder, nameProduct, priceHTProduct, quantity, VATRateProduct, ROUND(priceHTProduct*(1+VATRateProduct/100), 2) AS TTCPrice
		FROM
			OrderLines
		WHERE
			idOrder = ?
		ORDER BY
			nameProduct
		';
		$resultSet = $this -> getDBConnection() -> prepare($query);
		$resultSet -> execute([$idOrder]);
		$orderLines = $resultSet -> fetchAll();
		return $orderLines;
	}

	//ajout d'une ligne de commande
	public function addOne($requiredData) 
	{
		$query =
		'INSERT INTO
			OrderLines(idProduct, idOrder, nameProduct, priceHTProduct, quantity, VATRateProduct)
		VALUES
			(?, ?, ?, ?, ?, ?)
		';

		$resultSet = $this -> getDBConnection() -> prepare($query);
		$resultSet -> execute(array_values($requiredData));
		$orderAdded = ($resultSet->rowCount() == 1);
		return $orderAdded;
	}

	//ajout de plusieurs lignes de commande
	public function addMany($requiredData)
	{
		$query =
		'INSERT INTO
			OrderLines(idProduct, idOrder, nameProduct, priceHTProduct, quantity, VATRateProduct)
		
		VALUES
			'.implode(',', array_fill(0, count($products), '(?, ?, ?, ?, ?, ?)'));
			/*Correspond à :

		VALUES
			(?, ?, ?, ?, ?, ?), 
			(?, ?, ?, ?, ?, ?) 		
		etc*/

		$resultSet = $this -> getDBConnection() -> prepare($query);
		$resultSet -> execute(array_values($requiredData));
		$orderAdded = ($resultSet->rowCount() == 1);
		return $orderAdded;
	}

	//suppression d'une ligne de commande
	public function removeOne($id)
	{
		$query =
			'DELETE FROM
				OrderLines
			WHERE
				id = ?
			';
		$resultSet = $this -> getDBConnection() -> prepare($query);
		$resultSet -> execute([$id]);
		$orderlineRemoved = ($resultSet->rowCount() == 1);
		return $orderlineRemoved;
	}

	//mise à jour d'une ligne de commande
	public function update($requiredData) 
	{
		$query =
			'UPDATE
				OrderLines
			SET
				idProduct = ?, idOrder = ?, nameProduct = ?, priceHTProduct = ?, quantity = ?, VATRateProduct = ?
			WHERE
				id = ?
			';
		$resultSet = $this -> getDBConnection() -> prepare($query);
		$resultSet -> execute(array_values($requiredData));
		$orderlineUpdated = ($resultSet->rowCount() == 1 OR $resultSet->rowCount() == 0);
		return $orderlineUpdated;
	}
}

?>