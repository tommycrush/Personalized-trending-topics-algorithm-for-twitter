<?php
/**
 * @file
 * User has successfully authenticated with Twitter. Access tokens saved to session and DB
 * Get past X tweets on their home timeline, and anaylze for trends
 */

/* Load required lib files. */
session_start();

require_once('twitteroauth/twitteroauth.php');
require_once('config.php');

/* If access tokens are not available redirect to connect page. */
if (empty($_SESSION['access_token']) || empty($_SESSION['access_token']['oauth_token']) || empty($_SESSION['access_token']['oauth_token_secret'])) {
    header('Location: clearsessions.php');
}

/* Get user access tokens out of the session. */
$access_token = $_SESSION['access_token'];

/* Create a TwitterOauth object with consumer/user tokens. */
$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);


//require the workhouse library:
require("analyze.class.php");
$timeline_bank = new timeline_bank($_SESSION["user_id"]);



//punc that irrelevant to trends:
$punc = array("?","!",",",";",".","\$","%","(",")","-", "'s","'",'"',":",";","&","|");
//these words are so common that we should filter them out so that they are not shown as trends
$stopwords = array("a","about", "above", "above", "across", "after", "afterwards", "again", "against", "all", "almost", "alone", "along", "already", "also","although","always","am","among", "amongst", "amoungst", "amount",  "an", "and", "another", "any","anyhow","anyone","anything","anyway", "anywhere", "are", "around", "as",  "at", "back","be","became", "because","become","becomes", "becoming", "been", "before", "beforehand", "behind", "being", "below", "beside", "besides", "between", "beyond", "bill", "both", "bottom","but", "by", "call", "can", "cannot", "cant", "co", "con", "could", "couldnt", "cry", "de", "describe", "detail", "do", "done", "down", "due", "during", "each", "eg", "eight", "either", "eleven","else", "elsewhere", "empty", "enough", "etc", "even", "ever", "every", "everyone", "everything", "everywhere", "except", "few", "fifteen", "fify", "fill", "find", "fire", "first", "five", "for", "former", "formerly", "forty", "found", "four", "from", "front", "full", "further", "get", "give", "go", "had", "has", "hasnt", "have", "he", "hence", "her", "here", "hereafter", "hereby", "herein", "hereupon", "hers", "herself", "him", "himself", "his", "how", "however", "hundred", "ie", "if", "in", "inc", "indeed", "interest", "into", "is", "it", "its", "itself", "keep", "last", "latter", "latterly", "least", "less", "ltd", "made", "many", "may", "me", "meanwhile", "might", "mill", "mine", "more", "moreover", "most", "mostly", "move", "much", "must", "my", "myself", "name", "namely", "neither", "never", "nevertheless", "next", "nine", "no", "nobody", "none", "noone", "nor", "not", "nothing", "now", "nowhere", "of", "off", "often", "on", "once", "one", "only", "onto", "or", "other", "others", "otherwise", "our", "ours", "ourselves", "out", "over", "own","part", "per", "perhaps", "please", "put", "rather", "re", "same", "see", "seem", "seemed", "seeming", "seems", "serious", "several", "she", "should", "show", "side", "since", "sincere", "six", "sixty", "so", "some", "somehow", "someone", "something", "sometime", "sometimes", "somewhere", "still", "such", "system", "take", "ten", "than", "that", "the", "their", "them", "themselves", "then", "thence", "there", "thereafter", "thereby", "therefore", "therein", "thereupon", "these", "they", "thickv", "thin", "third", "this", "those", "though", "three", "through", "throughout", "thru", "thus", "to", "together", "too", "top", "toward", "towards", "twelve", "twenty", "two", "un", "under", "until", "up", "upon", "us", "very", "via", "was", "we", "well", "were", "what", "whatever", "when", "whence", "whenever", "where", "whereafter", "whereas", "whereby", "wherein", "whereupon", "wherever", "whether", "which", "while", "whither", "who", "whoever", "whole", "whom", "whose", "why", "will", "with", "within", "without", "would", "yet", "you", "your", "yours", "yourself", "yourselves", "the","O","youre","", " ", "help" ,"u", "say","best","oh","check","come","doing","want","state","need","people","w/","way","day","going","think","dont","week","did","ive","got","2","1","6","3","4","5","6","7","8","9","0","time","new","night","know","right","make","really","far","near","rt", ">", "<", "=", "great", "good", "like", "love","i","im","i'm", "today", "just", ">");


$last_id = 0;

$current_unix_time = time();

//the max tweets returned on each call is 200. Let's loop through 3 times to get the last 600 tweets
for($current_page = 1; $current_page <= 3; $current_page++){
	
	$tweets = $connection->get('statuses/home_timeline', array('count' => 200, 'page' => $current_page, 'include_rts' => 1));
	
	//this is an error checking mechanism [sometimes twitter was returning faulty data]
	if(is_array($tweets) or is_object($tweets)){

	//lets loop through each tweet
	foreach($tweets as $tweet){
		
		if($current_page == 4 and empty($tweet->retweeted_status)){
			$last_id = $tweet->id;
			$last_date = $tweet->created_at;
		}

		//get tweet timestamp
		$created_at = strtotime($tweet->created_at);	

		//ensure that the tweet is within 1 week (change the number below to adjust [in seconds]);
		if(($current_unix_time - $created_at) < 604800){

			//lets get the tweet data
			$text = $tweet->text;
			$tweet_id = $tweet->id;
			$screen_name = $tweet->user->screen_name;
			$name = $tweet->user->name;
			$image = $tweet->user->profile_image_url;
	
			//the all-important words of the tweet
			$words_of_tweet = explode(" ",strtolower($text));
			
			//compose of package of tweet data
			$userdata = array("tweet_id" => $tweet_id, "screen_name" => $screen_name, "name" => $name, "tweet" => $text, "created_at" => $created_at, "image" => $image);
			
			//reset/start the last word variable for this tweet
			$last_word = "";
			
				//loop through each word of the tweet
				foreach($words_of_tweet as $word){
			
					//clean it
					$word = str_replace($punc, "", $word);
				
				
					switch($word[0]){//compare first character
					
					/*
					 *	The words of the tweet are divided by hashtag, mention, single word, and phrase
					 *  This allows for different weighting scales of each type
					 *  For example, a single word is weighted less than the occurance of a 2-word phrase
					 *  The idea behind this is that the more conscious the action, the more valuable we can weight it.
					 */ 
					
						case "#":
							if(strlen($word) > 1){
								$timeline_bank->insert_hashtag($userdata, $word);
							}
							$last_word = "";//reset [comment out if you are okay with a trend is "its #raining"]
						break;
						
						case "@":
							if(strlen($word) > 1){
								$timeline_bank->insert_mention($userdata, $word);
							}
							$last_word = "";//reset [comment out if you are okay with a trend is "hi @ThomasTommyTom"]
						break;			
						
						
						default:
							if(!in_array($word, $stopwords)){//filter out the noise [common words]
								
								$timeline_bank->insert_word($userdata, $word);
								
								//its a word, now lets see if what was behind it was a word
								if(!empty($last_word)){
									//the last word was not a hashtag, a mention or noise
									$phrase = $last_word." ".$word;
									$timeline_bank->insert_phrase($userdata, $phrase);
								}
								
								$last_word = $word;//set to current word
							}else{
								$last_word = "";//reset
							}
						break;
					}
				}//end foreach word
			
		}//end if within the desired time period
		
	}//end foreach tweet
	
	
	
	}else{
		//if the script lands here, it means the data twitter returned was faulty.
		
		//do a database error entry
		//$db->query("INSERT INTO `your_error_table`");
	}

}


//simple debugger built in for natural printing of ALL data [has not been filtered for trends yet]
if($_GET["debug"] == 1){
	$timeline_bank->print_discoveries();
	$timeline_bank->print_user_stats();
}

//this is the important function, it finds the trends
$timeline_bank->prioritize();

//simple debugger built in for natural printing of FINAL data
if($_GET["debug"] == 1){
	$timeline_bank->print_shortened_result();
}


//Want to building HTML or JSON from the results? [a.k.a. - using this as an ajax file], customize this functions:
//$timeline_bank->build_snapshot_and_data();

?>