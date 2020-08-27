<?php
/**
 * The database connection file. This class will simply open a database connection
 *
 * @author Christopher Smit <christopher@mincdevelopment.co.za>
 * @copyright Copyright (c) 2020, Minc Development
 * 
 * @version 1.0
 */

namespace MySQL 
{

	class Connection 
	{
		/**
		 * Static function that returns the connection that was initialised by the constructor.
		 *
		 * @return Connection The database connection.
		 */
		public static function connect(string $DB_HOST, 
									   string $DB_USER_ACCOUNT, 
									   string $DB_USER_PASSWORD, 
									   string $DB_INSTANCE): \PDO 
		{
			$Database = new \PDO("mysql:host=$DB_HOST;dbname=$DB_INSTANCE", $DB_USER_ACCOUNT, $DB_USER_PASSWORD);
			$Database->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
			$Database->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
			return $Database;
		}
	}
}
