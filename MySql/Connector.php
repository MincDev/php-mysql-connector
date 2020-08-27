<?php
/**
 * This class will handle all communication to the database.
 *
 * @author Christopher Smit <christopher@mincdevelopment.co.za>
 * @copyright Copyright (c) 2020, Minc Development
 *
 * @version 1.0
 */

namespace MySQL 
{
	use MySql\Connection;
	
	class Connector 
	{
		/**
		 * The connection object
		 * @var PDO
		 */
		private \PDO $connection;

		/**
		 * The SQL string to execute
		 * @var string
		 */
		private string $strSql;

		/**
		 * The SQL parameters to bind
		 */
		private ?array $arrSql;

		/**
		 *	Constructor that opens a new database connection
		*/
		function __construct() 
		{
			$this->connection = Connection::connect();
		}

		/**
		 * Sets the SQL string and parameters to be bound and returns the connector instance
		 *
		 * @param string $strSql The SQL string to execute
		 * @param array $arrSql The parameters to be bound with the SQL string
		 * @return Connector
		 */
		public function prepare(string $strSql, ?array $arrSql): Connector
		{
			$this->strSql = $strSql;
			$this->arrSql = $arrSql;
			return $this;
		}

		/**
		 * Returns the number of rows for the specified query.
		 *
		 * @return integer
		 */
		public function rowCount(): int 
		{
			$stmt = $this->connection->prepare($this->strSql);
			$stmt->execute($this->arrSql);
			return $stmt->rowCount();
		}

		/**
		 * Modifies a table by either inserting, updating or deleting a record. 
		 * Requires the connector to be prepared first by using prepare()
		 *
		 * @return boolean
		 */
		public function modify(): bool 
		{
			return $this->connection->prepare($this->strSql)
									->execute($this->arrSql);
		}

		/**
		 * Returns a single column value from a table. 
		 * Requires the connector to be prepared first using prepare()
		 *
		 * @return string
		 */
		public function query(): string 
		{
			$stmt = $this->connection->prepare($this->strSql);
			$stmt->execute($this->arrSql);
			if ($stmt->rowCount() > 0) {
				return $stmt->fetchColumn();
			} else {
				return null;
			}
		}

		/**
		 * Returns the last inserted record's id.
		 *
		 * @return integer
		 */
		public function lastInsertId(): int
		{
			return $this->prepare("SELECT LAST_INSERT_ID()", null)->query();
		}

		/**
		 * Selects records from a table.
		 * Requires the connector to be prepared using prepare()
		 *
		 * @param boolean $single If TRUE, the connector will only return one row, everything otherwise
		 * @return array
		 */
		public function select(): array 
		{
			$stmt = $this->connection->prepare($this->strSql);
			$stmt->execute($this->arrSql);

			$rows = [];
			while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
				\array_push($rows, $row);
			}
			
			return count($rows) > 1 ? $rows : (count($rows) > 0 ? $rows[0] : []);
		}

		/**
		 * Quickly delete a record from a table
		 *
		 * @param string $table The table to delete the record from
		 * @param integer $id The primary key id of the record to delete
		 * @return boolean
		 */
		public function delete(string $table, int $id): bool
		{
			if ($this->prepare("SHOW TABLES LIKE '$table'", null)->rowCount() > 0) {
				$primary_key = $this->prepare("SHOW KEYS FROM $table WHERE Key_name = 'PRIMARY'", null)
									->select()['Column_name'];
				return $this->prepare("DELETE FROM $table WHERE $primary_key = :id", [":id" => $id])->modify();
			} else throw new \Exception("Connector Fatal Error: Table '$table' does not exist in this database.");
		}

		/**
		 * Quickly insert a record to any table by specifying key value pairs
		 *
		 * @param string $table The table to insert the record into
		 * @param array $key_value_pairs The column values to be inserted as a key value pair array
		 * @return boolean
		 */
		public function quick_insert(string $table, array $key_value_pairs): bool 
		{
			if ($this->prepare("SHOW TABLES LIKE '$table'", null)->rowCount() > 0) {
				$strSql = "INSERT INTO $table (";
				$arrSql = [];
				foreach ($key_value_pairs as $column => $value) {
					$strSql .= "$column, ";
				}
				$strSql = \rtrim($strSql, ', ').") VALUES (";
				foreach ($key_value_pairs as $column => $value) {
					$strSql .= ":$column, ";
					$arrSql[":$column"] = $value;
				}
				$strSql = \rtrim($strSql, ', ').");";

				return $this->prepare($strSql, $arrSql)->modify();
			} else throw new \Exception("Connector Fatal Error: Table '$table' does not exist in this database.");
		}

		/**
		 * Quickly update a record in a table by specifying key value pairs
		 *
		 * @param string $table The table to update
		 * @param integer $id The primary key id of the record to update
		 * @param array $key_value_pairs The column values to update as a key value pair array
		 * @return boolean
		 */
		public function quick_update(string $table, int $id, array $key_value_pairs): bool 
		{
			if ($this->prepare("SHOW TABLES LIKE '$table'", null)->rowCount() > 0) {
				$primary_key = $this->prepare("SHOW KEYS FROM $table WHERE Key_name = 'PRIMARY'", null)
									->select()['Column_name'];

				$strSql = "UPDATE $table SET ";
				$arrSql = [];
				foreach ($key_value_pairs as $column => $value) {
					$strSql .= "$column = :$column, ";
					$arrSql[":$column"] = $value;
				}
				$strSql = \rtrim($strSql, ', ')." WHERE $primary_key = $id";

				return $this->prepare($strSql, $arrSql)->modify();
			} else throw new \Exception("Connector Fatal Error: Table '$table' does not exist in this database.");
		}

		/**
		 * Inserts records in bulk using a multidimentional array as key value pairs
		 *
		 * @param string $table The table to insert data into
		 * @param array $data The data to be inserted as a multidimentional key value pairs array
		 * @return boolean
		 */
		public function bulk_insert(string $table, array $data): bool 
		{
			if ($this->prepare("SHOW TABLES LIKE '$table'", null)->rowCount() > 0) {
				$i = 0;
				$strSql = "";
				$arrSql = [];
				foreach ($data as $row) {
					if (!empty($strSql)) {
						$strSql .= ", ( ";
					} else {
						$ii = 0;
						foreach ($row as $column => $value) {
							if ($ii == 0) {
								$strSql .= "INSERT INTO $table ($column, ";
							} else {
								$strSql .= "$column, ";
							}
							$ii++;
						}
						$strSql = rtrim($strSql, ', '). ") VALUES ( ";
					}

					foreach ($row as $column => $value) {
						$strSql .= sprintf(":%s_$i, ", $column);
						$arrSql[sprintf(":%s_$i", $column)] = $value;
					}
					$strSql = rtrim($strSql, ', ').')';
					$i++;
				}
				return $this->prepare($strSql, $arrSql)->modify();
			} else throw new \Exception("Connector Fatal Error: Table '$table' does not exist in this database.");
		}

		/**
		 * Execute multiple insert, update or delete statements
		 * This function requires the connector to be prepared using prepare()
		 * Statements are separated by ";"
		 *
		 * @return boolean
		 */
		public function m_modify(): bool 
		{
			$statements = explode(";", $this->strSql);

			$executed = 0;
			$stmt_count = 0;
			foreach ($statements as $strSql) {
				$stmt = $this->connection->prepare($strSql);
			
				if ($stmt->execute($this->arrSql[$stmt_count])) {
					$executed++;
				}
				$stmt_count++;
			}

			return $executed == $stmt_count;
		}
	}
}