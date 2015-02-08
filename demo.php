<?php
/*
 * PHP-PDO-MySQL-Class
 * https://github.com/lincanbin/PHP-PDO-MySQL-Class
 *
 * Copyright 2014, Lin Canbin
 * http://www.94cb.com/
 *
 * Licensed under the Apache License, Version 2.0:
 * http://www.apache.org/licenses/LICENSE-2.0
 * 
 * A PHP MySQL PDO class similar to the the Python MySQLdb. 
 */
?>
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>
			PHP-PDO-MySQL-Class
		</title>
	</head>
	<body marginheight="0" style="zoom: 1;">
		<h1>
			PHP-PDO-MySQL-Class
		</h1>
		<p>
			A PHP MySQL PDO class similar to the the Python MySQLdb.
		</p>
		<h2>
			Initialize
		</h2>
		<pre>
			<code class="lang-php">
&lt;?php 
	define('DBHost', '127.0.0.1'); 
	define('DBName', 'Database');
	define('DBUser', 'root'); 
	define('DBPassword', ''); 
	require(dirname(__FILE__)."/src/PDO.class.php");
	$DB = new Db(DBHost, DBName, DBUser, DBPassword); 
?&gt;
			</code>
		</pre>
<?php
/*


The following parameters must be modified before running this DEMO.


*/
define('DBHost', '127.0.0.1'); 
define('DBName', 'Database');
define('DBUser', 'root'); 
define('DBPassword', ''); 
require(dirname(__FILE__)."/src/PDO.class.php");
$DB = new Db(DBHost, DBName, DBUser, DBPassword); 
?>
		<h2>
			Preventing SQL Injection Attacks
		</h2>
		<h4>
			Safety: Use parameter binding method
		</h4>
		<p>
			Safety Example:
		</p>
		<pre>
			<code class="lang-php">
&lt;?php
	$DB-&gt;query("SELECT * FROM fruit WHERE name=?", array($_GET['name']));
?&gt;
			</code>
		</pre>
		<h4>
			Unsafety: Split joint SQL string
		</h4>
		<p>
			Unsafety Example:
		</p>
		<pre>
			<code class="lang-php">
&lt;?php 
	$DB-&gt;query("SELECT * FROM fruit WHERE name=".$_GET['name']);
?&gt;
			</code>
		</pre>
		<h2>
			Usage
		</h2>
		<h4>
			table "fruit"
		</h4>
		<p>
			<table>
				<thead>
					<tr>
						<th align="center">id</th>
						<th align="center">name</th>
						<th align="center">color</th>
					</tr>
				</thead>
				<tbody>
					<tr>
					<td align="center">1</td>
					<td align="center">apple</td>
					<td align="center">red</td>
					</tr>
					<tr>
					<td align="center">2</td>
					<td align="center">banana</td>
					<td align="center">yellow</td>
					</tr>
					<tr>
					<td align="center">3</td>
					<td align="center">watermelon</td>
					<td align="center">green</td>
					</tr>
					<tr>
					<td align="center">4</td>
					<td align="center">pear</td>
					<td align="center">yellow</td>
					</tr>
					<tr>
					<td align="center">5</td>
					<td align="center">strawberry</td>
					<td align="center">red</td>
					</tr>
				</tbody>
			</table>
		</p>
<?php
$DB->query("DROP TABLE IF EXISTS `fruit`;");

$DB->query("CREATE TABLE IF NOT EXISTS `fruit` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `color` varchar(32) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;");

$AffectedRows = $DB->query("INSERT INTO `fruit` (`id`, `name`, `color`) VALUES
(1, 'apple', 'red'),
(2, 'banana', 'yellow'),
(3, 'watermelon', 'green'),
(4, 'pear', 'yellow'),
(5, 'strawberry', 'red');
");//return 5, the number of affected rows by this INSERT/ UPDATE/ DELETE
//echo $AffectedRows;
?>
		<h4>
			Fetching with Bindings (ANTI-SQL-INJECTION):
		</h4>
		<pre>
			<code class="lang-php">
&lt;?php
	$DB-&gt;query("SELECT * FROM fruit WHERE name=? and color=?",array('apple','red'));
	$DB-&gt;query("SELECT * FROM fruit WHERE name=:name and color=:color",array('name'=&gt;'apple','color'=&gt;'red'));
?&gt;
			</code>
		</pre>
		<p>
			Result:
		</p>
		<pre>
			<code class="lang-php">
<?php
	var_export($DB->query("SELECT * FROM fruit WHERE name=:name and color=:color",array('name'=>'apple','color'=>'red')));
?>
			</code>
		</pre>
		<h4>
			WHERE IN:
		</h4>
		<pre>
			<code class="lang-php">
&lt;?php
	$DB-&gt;query("SELECT * FROM fruit WHERE name IN (?)",array('apple','banana'));
?&gt;
			</code>
		</pre>
		<p>
			Result:
		</p>
		<pre>
			<code class="lang-php">
<?php
	var_export($DB->query("SELECT * FROM fruit WHERE name IN (?)",array('apple','banana')));
?>
			</code>
		</pre>
		<h4>
			Fetching Column:
		</h4>
		<pre>
			<code class="lang-php">
&lt;?php
	$DB-&gt;column("SELECT color FROM fruit WHERE name IN (?)",array('apple','banana','watermelon'));
?&gt;
			</code>
		</pre>
		<p>
			Result:
		</p>
		<pre>
			<code class="lang-php">
<?php
	var_export($DB->column("SELECT color FROM fruit WHERE name IN (?)",array('apple','banana','watermelon')));
?>
			</code>
		</pre>
		<h4>
			Fetching Row:
		</h4>
		<pre>
			<code class="lang-php">
&lt;?php
	$DB-&gt;row("SELECT * FROM fruit WHERE name=? and color=?",array('apple','red'));
?&gt;
			</code>
		</pre>
		<p>
			Result:
		</p>
		<pre>
			<code class="lang-php">
<?php
	var_export($DB->row("SELECT * FROM fruit WHERE name=? and color=?",array('apple','red')));
?>
			</code>
		</pre>
		<h4>
			Fetching single:
		</h4>
		<pre>
			<code class="lang-php">
&lt;?php
	$DB-&gt;single("SELECT color FROM fruit WHERE name=? ",array('watermelon'));
?&gt;
			</code>
		</pre>
		<p>
			Result:
		</p>
		<pre>
			<code class="lang-php">
<?php
	echo $DB->single("SELECT color FROM fruit WHERE name=? ",array('watermelon'));
?>
			</code>
		</pre>
		<h4>
			Delete / Update / Insert
		</h4>
		<p>
			These operations will return the number of affected result set. (integer)
		</p>
		<pre>
			<code class="lang-php">
&lt;?php
	// Delete
	$DB-&gt;query("DELETE FROM fruit WHERE id = :id", array("id"=&gt;"1"));
	$DB-&gt;query("DELETE FROM fruit WHERE id = ?", array("1")); // Update
	$DB-&gt;query("UPDATE fruit SET color = :color WHERE name = :name", array("name"=&gt;"strawberry","color"=&gt;"yellow"));
	$DB-&gt;query("UPDATE fruit SET color = ? WHERE name = ?", array("yellow","strawberry"));
	// Insert
	$DB-&gt;query("INSERT INTO fruit(id,name,color) VALUES(?,?,?)",array(null,"mango","yellow"));//Parameters must be ordered
	$DB-&gt;query("INSERT INTO fruit(id,name,color) VALUES(:id,:name,:color)", array("color"=&gt;"yellow","name"=&gt;"mango","id"=&gt;null));//Parameters order free
?&gt;
			</code>
		</pre>
<?php
	// Delete
	$DB->query("DELETE FROM fruit WHERE id = :id", array("id"=>"1"));
	$DB->query("DELETE FROM fruit WHERE id = ?", array("1")); // Update
	$DB->query("UPDATE fruit SET color = :color WHERE name = :name", array("name"=>"strawberry","color"=>"yellow"));
	$DB->query("UPDATE fruit SET color = ? WHERE name = ?", array("yellow","strawberry"));
	// Insert
	$DB->query("INSERT INTO fruit(id,name,color) VALUES(?,?,?)",array(null,"mango","yellow"));//Parameters must be ordered
	$DB->query("INSERT INTO fruit(id,name,color) VALUES(:id,:name,:color)", array("color"=>"yellow","name"=>"mango","id"=>null));//Parameters order free
?>
		<h4>
			Get Last Insert ID
		</h4>
		<pre>
			<code class="lang-php">
&lt;?php
	$DB-&gt;lastInsertId();
?&gt;
			</code>
		</pre>
		<p>
			Result:
		</p>
		<pre>
			<code class="lang-php">
ID for array("color"=>"yellow","name"=>"mango","id"=>null): 
<?php
	echo $DB->lastInsertId();
?>
			</code>
		</pre>
		<h4>
			Get the number of queries since the object initialization
		</h4>
		<pre>
			<code class="lang-php">
&lt;?php
	$DB-&gt;querycount;
?&gt;
			</code>
		</pre>
		<p>
			Result:
		</p>
		<pre>
			<code class="lang-php">
<?php
	echo $DB->querycount;
?> SQL Queries in this page.
			</code>
		</pre>
	</body>

</html>