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
		public static function connect() {
			$DB_HOST 			= '127.0.0.1'; // Your host IP or name
			$DB_USER_ACCOUNT 	= 'root'; // Your username for the database
			$DB_USER_PASSWORD 	= ''; // Your password for the database
			$DB_INSTANCE 		= 'connector'; // The database name

			$Database = new \PDO("mysql:host=$DB_HOST;dbname=$DB_INSTANCE", $DB_USER_ACCOUNT, $DB_USER_PASSWORD);
			$Database->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
			$Database->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
			return $Database;
		}
	}
}
