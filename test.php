<?php
$mTime     = explode(' ', microtime());
$startTime = $mTime[1] + $mTime[0];

define('DBHost', '127.0.0.1');
define('DBPort', 3306);
define('DBName', 'test');
define('DBUser', 'root');
define('DBPassword', '');
require( __DIR__ . "/src/PDO.class.php");
$DB = new Db(DBHost, DBPort, DBName, DBUser, DBPassword);

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
");

var_dump($AffectedRows);

$it = $DB->iterator("SELECT * FROM fruit limit 0, 1000000;");
$colorCountMap = array(
    'red' => 0,
    'yellow' => 0,
    'green' => 0
);
foreach($it as $key => $value) {
    // sendDataToElasticSearch($key, $value);
    var_dump($key);
    var_dump($value);
    $colorCountMap[$value['color']]++;
}
var_dump($colorCountMap);


$mTime     = explode(' ', microtime());
echo '<br>'.(number_format(($mTime[1] + $mTime[0] - $startTime), 6)*1000).'ms';
echo '<br>'.(memory_get_usage(false)/1024).'KiB';