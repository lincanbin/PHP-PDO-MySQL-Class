PHP-PDO-MySQL-Class
===================

A  PHP MySQL PDO class similar to the the Python MySQLdb.

Initialize
------------
```php
<?php
define('DBHost', '127.0.0.1');
define('DBName', 'Database');
define('DBUser', 'root');
define('DBPassword', '');
require(dirname(__FILE__)."/src/PDO.class.php");
$DB = new Db(DBHost, DBName, DBUser, DBPassword);
?>
```

Preventing SQL Injection Attacks
------------
#### Safety: Use parameter binding method
Safety Example:
```php
<?php
$DB->query("SELECT * FROM fruit WHERE name=?", array($_GET['name']));
?>
```


#### Unsafety: Split joint SQL string
Unsafety Example: 
```php
<?php
$DB->query("SELECT * FROM fruit WHERE name=".$_GET['name']);
?>
```
Usage
------------

#### table "fruit"

| id | name | color
|:-----------:|:------------:|:------------:|
| 1       |      apple  |     red    
| 2       |      banana |     yellow  
| 3       |   watermelon|     green   
| 4       |        pear |     yellow    
| 5       |   strawberry|     red    

#### Fetching with Bindings (ANTI-SQL-INJECTION):

```php
<?php
$DB->query("SELECT * FROM fruit WHERE name=? and color=?",array('apple','red'));
$DB->query("SELECT * FROM fruit WHERE name=:name and color=:color",array('name'=>'apple','color'=>'red'));
?>
```

Result:

```php
Array
(
	[0] => Array
		(
			[id] => 1
			[name] => apple
			[color] => red
		)
)
```

#### WHERE IN (needs named placeholder):

```php
<?php
$DB->query("SELECT * FROM fruit WHERE name IN (:fruits)",array(array('apple','banana')));
?>
```

Result:

```php
Array
(
	[0] => Array
		(
			[id] => 1
			[name] => apple
			[color] => red
		)
	[1] => Array
		(
			[id] => 2
			[name] => banana
			[color] => yellow
		)
)
```

```php
<?php
$query = "SELECT * FROM fruit WHERE name IN (:fruits) AND color = :color";
// use multidimensional array as $params
$params = array(
	"color" => "red",
	"fruits" => array(
		"apple",
		"banana"
	)
);
$DB->query($query,$params);
?>
```

Result:

```php
Array
(
	[0] => Array
		(
			[id] => 1
			[name] => apple
			[color] => red
		)
)
```

#### Fetching Column:

```php
<?php
$DB->column("SELECT color FROM fruit WHERE name IN (:color)",array('apple','banana','watermelon'));
?>
```

Result:

```php
Array
(
	[0] => red
	[1] => yellow
	[2] => green
)
```

#### Fetching Row:

```php
<?php
$DB->row("SELECT * FROM fruit WHERE name=? and color=?",array('apple','red'));
?>
```

Result:

```php
Array
(
	[id] => 1
	[name] => apple
	[color] => red
)
```

#### Fetching single:

```php
<?php
$DB->single("SELECT color FROM fruit WHERE name=? ",array('watermelon'));
?>
```

Result:

```php
green
```

#### Delete / Update / Insert
These operations will return the number of affected result set. (integer)
```php
<?php
// Delete
$DB->query("DELETE FROM fruit WHERE id = :id", array("id"=>"1"));
$DB->query("DELETE FROM fruit WHERE id = ?", array("1"));
// Update
$DB->query("UPDATE fruit SET color = :color WHERE name = :name", array("name"=>"strawberry","color"=>"yellow"));
$DB->query("UPDATE fruit SET color = ? WHERE name = ?", array("yellow","strawberry"));
// Insert
$DB->query("INSERT INTO fruit(id,name,color) VALUES(?,?,?)", array(null,"mango","yellow"));//Parameters must be ordered
$DB->query("INSERT INTO fruit(id,name,color) VALUES(:id,:name,:color)", array("color"=>"yellow","name"=>"mango","id"=>null));//Parameters order free
?>
```

#### Get Last Insert ID

```php
<?php
$DB->lastInsertId();
?>
```

#### Get the number of queries since the object initialization

```php
<?php
$DB->querycount;
?>
```

#### Close Connection

```php
<?php
$DB->CloseConnection;
?>
```
