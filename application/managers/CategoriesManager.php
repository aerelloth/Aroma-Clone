<?php 

class CategoriesManager extends DBManager
{
	//récupération de toutes les catégories
	public function getAll() 
	{
		$query = 
		'SELECT
			Categories.id AS categoryId, Categories.name AS categoryName, COUNT(Products.id) AS productsNumber
		FROM
			Categories
		LEFT JOIN
			Products
		ON
			Categories.id = Products.idCategory
		GROUP BY
			Categories.id
		ORDER BY
			Categories.name
		';
		$resultSet = $this -> getDBConnection() -> query($query);
		$category = $resultSet -> fetchAll();
		return $category;
	}

	//récupération d'une catégorie
	public function getOneById($id) 
	{
		$query = 
		'SELECT
			id AS categoryId, name AS categoryName
		FROM
			Categories
		WHERE
			id = ?
		';
		$resultSet = $this -> getDBConnection() -> prepare($query);
		$resultSet -> execute([$id]);
		$category = $resultSet -> fetch();
		return $category;
	}

	//ajout d'une catégorie
	public function addOne($name)
	{
		$query =
			'INSERT INTO
				Categories(name)
			VALUES
				(?)
			';
		$resultSet = $this -> getDBConnection() -> prepare($query);
		$resultSet -> execute([$name]);
		$categoryAdded = ($resultSet->rowCount() == 1);
		return $categoryAdded;
	}

	//suppression d'une catégorie
	public function removeOne($id)
	{
		$query =
			'DELETE FROM
				Categories
			WHERE
				id = ?
			';
		$resultSet = $this -> getDBConnection() -> prepare($query);
		$resultSet -> execute([$id]);
		$categoryRemoved = ($resultSet->rowCount() == 1);
		return $categoryRemoved;
	}

	//mise à jour d'une catégorie
	public function updateOne($requiredData)
	{
		$query =
			'UPDATE
				Categories
			SET
				name = ?
			WHERE
				id = ?
			';
		$resultSet = $this -> getDBConnection() -> prepare($query);
		$resultSet -> execute(array_values($requiredData));
		$categoryUpdated = ($resultSet->rowCount() == 1);
		return $categoryUpdated;
	}
	
}
 ?>