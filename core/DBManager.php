<?php 

abstract class DBManager
{
	const DSN = DATABASE_DSN;
	const USER_NAME = DATABASE_USER_NAME;
	const PASSWORD = DATABASE_PASSWORD;

	private static $PDOInstance;
	protected function getDBConnection() 
	{
		if (self::$PDOInstance == null)
		{
			self::$PDOInstance = new PDO(
			self::DSN,
			self::USER_NAME,
			self::PASSWORD,
			[
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
			]
			);
			//var_dump('Connexion !');
		}
		
		return self::$PDOInstance;
	}
}

 ?>