# php-mysql-connector
A simple to use mysql connector for quickly connecting your php project with a mysql database

# Installation

You can install this library using composer by adding it to your composer.json file

```
"require": {
    "mincdev/mysql-connector": "^1.1"
}
```
or running the following command:

```
composer require mincdev/mysql-connector
```

# Usage

To use the connector, you need to include it in your file and intialise it with your database details.

```
use MySql\Connector;

$conn = new Connector([
    "host"      => "127.0.0.1",
    "user"      => "root",
    "passwd"    => "secret",
    "schema"    => "my_db",
]);
```

Once initialised you can use the connector in the following manner:

### Insert a record

```
$conn->prepare("INSERT INTO table_name (column_1, column_2) VALUES (:column_1, :column_2)", [
    ":column_1" => "Hello World!", 
    ":column_2" => 1234
])->modify();
```

### Row Count

```
$conn->prepare("SELECT * FROM table_name", null)->rowCount();
```

### Fetch Single Column Value

```
$conn->prepare("SELECT column_name FROM table_name", null)->query();
```

### Get last insert id

```
$conn->lastInsertId();
```

### Select row(s)

Returns a single row or multiple rows

```
$conn->prepare("SELECT * FROM table_name", null)->select()
```

### Quickly delete a record

Requires the primary key id to be passed and the table name

```
$conn->delete("table_name", 6);
```

### Quick Insert

Inserts a record to the specified table using an array as the table structure.

```
$conn->quick_insert("table_name", ["column_1" => "Hello World!", "column_2" => 123]);
```

### Quick Update

Updates a record in the specified table using an array as the table structure.

```
$conn->quick_update("table_name", 9, ["column_1" => "Hello World!", "column_2" => 123]);
```

### Bulk Insert

Inserts multiple records to a specified table using an array as the table structure.

```
$conn->bulk_insert("table_name", [
    ["column_1" => "Hello World!", "column_2" => 1],
    ["column_1" => "Hello Galaxy!", "column_2" => 2]
]);
```

### Multi Modify (Multiple Statements)

Runs multiple statements on the database one after the other. Each statement is separated by a semi-colon ";"

```
$conn->prepare("DELETE FROM table_name;INSERT INTO table_name (column_1, column_2) VALUES (:column_1, :column_2)", [
    null, // No key value pairs for the delete statement
    [":column_1" => "Hello World!", ":column_2" => 123] // Key value pairs for the second statement (insert)
])->m_modify();
```
