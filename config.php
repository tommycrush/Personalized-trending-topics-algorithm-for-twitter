<?php

/**
 * @file
 * A single location to store configuration.
 */

define('CONSUMER_KEY', 'your key');
define('CONSUMER_SECRET', 'your secret');
define('OAUTH_CALLBACK', 'http://toppedin.com/callback.php');

mysql_connect("localhost", "your user", "user pass") or die(mysql_error());
mysql_select_db("db name")or die(mysql_error());

?>