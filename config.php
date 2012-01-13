<?php

/**
 * @file
 * A single location to store configuration.
 */

//twitter API vars:
define('CONSUMER_KEY', 'your key');
define('CONSUMER_SECRET', 'your secret');
define('OAUTH_CALLBACK', 'http://yourdomain.com/callback.php');

//lets make a persistant connection to the DB:
mysql_connect("localhost", "your user", "user pass") or die(mysql_error());
mysql_select_db("db name")or die(mysql_error());

?>