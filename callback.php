<?php
/**
 * @file
 * Take the user when they return from Twitter. Get access tokens.
 * Verify credentials and redirect to based on response from Twitter.
 */

/* Start session and load lib */
session_start();

require_once('twitteroauth/twitteroauth.php');
require_once('config.php');

/* If the oauth_token is old redirect to the connect page. */
if (isset($_REQUEST['oauth_token']) && $_SESSION['oauth_token'] !== $_REQUEST['oauth_token']) {
  header('Location: ./clearsessions.php?errorOnAuth');
}

/* Create TwitteroAuth object with app key/secret and token key/secret from default phase */
$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);

/* Request access tokens from twitter */
$access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);

/* Save the access tokens. Normally these would be saved in a database for future use. */
$_SESSION['access_token'] = $access_token;

/* Remove no longer needed request tokens */
unset($_SESSION['oauth_token']);
unset($_SESSION['oauth_token_secret']);

/* If HTTP response is 200 continue otherwise send to connect page to retry */
if (200 == $connection->http_code) {
  /* The user has been verified and the access tokens can be saved for future use */
 	$content = $connection->get('account/verify_credentials');
	$screen_name = $content->screen_name;
	$twitter_id = $content->id;
	
	$users = $db->query("SELECT user_id FROM users WHERE twitter_id='$twitter_id'");
	if(mysql_num_rows($users) == 1){
		//found
		$user = mysql_fetch_array($users);
		
		$user_id = $user["user_id"];
		
		$_SESSION["user_id"] = $user_id;
		
		$db->query("UPDATE users SET last_login = NOW() WHERE user_id='$user_id' LIMIT 1");
		
	}else{
		//not found. insert.
		$location = $content->location;
		$num_friends = $content->friends_count;		
		$status_count = $content->statuses_count; 
		$followers_count = $content->followers_count;
		
		
		$db->query("INSERT INTO 
					users 
					(`twitter_name`,`twitter_id`,`date_registered`,`last_login`,`take_snapshots`,`location`,`friends_count`,`status_count`,`followers_count`)
					VALUES
					('$screen_name','$twitter_id', NOW(), NOW(), '0','$location','$num_friends','$status_count','$followers_count')");
		
		
		$_SESSION["user_id"] = mysql_insert_id();
	}

	$_SESSION["twitter_logged_in"] = true;
	$_SESSION['status'] = 'verified';
  
  header('Location: ./analyze.php');
} else {
  /* Save HTTP status for error dialog on connnect page.*/
  header('Location: ./clearsessions.php?authOnError2');
}
