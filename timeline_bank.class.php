<?php
class timeline_bank {
	
	/*
	 * DATA STRUCTURE:
	 * 
	 * 		users => (
	 * 			"ThomasTommyToom"
	 * 				=> (
	 * 						"hashtags" => (
	 * 							"#bbs" => 5, 
	 * 							"#sw" => 1,
	 * 							"#HASHTAG" => NUMBER_OF_OCCURANCES_BY_USER
	 * 						)
	 * 			
	 * 						"mentions" => (
	 * 							"@jb" => 1
	 * 						)
	 * 					)
	 * 			"AreoSuch"
	 * 				=> (
	 * 						"hashtags" => (
	 * 							"#bc2" => 5
	 * 							"#sw" => 1
	 * 						)
	 * 			
	 * 						"mentions" => (
	 * 							"@jb" => 1
	 * 						)
	 * 					)
	 * 			)
	 */
	
	function __construct($user_id){
		
		//holds daata about how many useres are using a word
		$this->user_hashtags = array();
		$this->user_mentions = array();
		$this->user_words = array();
		$this->user_phrases = array();
		
		
		//hold data about frequency of words
		$this->discover_hashtags = array();
		$this->discover_mentions = array();
		$this->discover_words = array();
		$this->discover_phrases = array();
		
		//holds data about tweets assoicated with that word
		$this->associated_tweets = array();

		//hold data we'll need for snapshots
		$this->user_id = $user_id;
		$this->snapshot_id = 0;	
		
		//incase we need to tap the twitter api
		//$this->connection = $connection;
		
		//connect
		mysql_connect("localhost", "your user", "user pass") or die(mysql_error());
		mysql_select_db("db name")or die(mysql_error());
	}
	
	function query($query){
		$res =  mysql_query($query) or die(mysql_error());
		return $res;
	}
	
	
	public function insert_hashtag($userdata, $hashtag){
		
		$user = $userdata["screen_name"];
		
		//log user instance of hashtag
		if(isset($this->user_hashtags[$hashtag][$user])){
			$this->user_hashtags[$hashtag][$user]++;
		}else{
			$this->user_hashtags[$hashtag][$user] = 1;
		}
		
		//log hashtag
		if(isset($this->discover_hashtags[$hashtag])){
			$this->discover_hashtags[$hashtag]++;//increment
		}else{
			$this->discover_hashtags[$hashtag] = 1;//set to 1
		}
		
		
		//associate tweet with userdata
		$this->assocate_tweet_with_word($userdata, $hashtag);
	}
	
	public function insert_mention($userdata, $mention){
		
		$user = $userdata["screen_name"];
		
		//log user instance of mention
		if(isset($this->user_mentions[$mention][$user])){
			$this->user_mentions[$mention][$user]++;
		}else{
			$this->user_mentions[$mention][$user] = 1;
		}
		
		//log discovery
		if(isset($this->discover_mentions[$mention])){
			$this->discover_mentions[$mention]++;//increment
		}else{
			$this->discover_mentions[$mention] = 1;//set to 1
		}
		
		//associate tweet with userdata
		$this->assocate_tweet_with_word($userdata, $mention);		

	}	
	
	public function insert_word($userdata, $word){

		if(empty($word)){
			return true;
		}
	
		$user = $userdata["screen_name"];
		
		//log user instance of mention
		if(isset($this->user_words[$word][$user])){
			$this->user_words[$word][$user]++;
		}else{
			$this->user_words[$word][$user] = 1;
		}
		
				
		//log discovery
		if(isset($this->discover_words[$word])){
			$this->discover_words[$word]++;//increment
		}else{
			$this->discover_words[$word] = 1;//set to 1
		}	

		//associate tweet with userdata
		$this->assocate_tweet_with_word($userdata, $word);		
	
	}
	
	
	public function insert_phrase($userdata, $phrase){

		$user = $userdata["screen_name"];
		
		//log user instance of mention
		if(isset($this->user_phrases[$phrase][$user])){
			$this->user_phrases[$phrase][$user]++;
		}else{
			$this->user_phrases[$phrase][$user] = 1;
		}
		
		//log discovery
		if(isset($this->discover_phrases[$phrase])){
			$this->discover_phrases[$phrase]++;//increment
		}else{
			$this->discover_phrases[$phrase] = 1;//set to 1
		}	
		
		$this->assocate_tweet_with_word($userdata, $phrase);
	}
	
	
	
	public function assocate_tweet_with_word($userdata, $word){
		
		//userdata format: array("tweet_id" => $tweet_id, "screen_name" => $screen_name, "name" => $name);
		if(is_array($this->associated_tweets[$word])){
			array_push($this->associated_tweets[$word],$userdata);
		}else{
			$this->associated_tweets[$word] = array($userdata);
		}
		
	}
	
	
	public function sort_discoveries(){
		//use asort to keep key associations
		asort($this->discover_hashtags);
		asort($this->discover_mentions);
		asort($this->discover_words);
		asort($this->discover_phrases);
	}
	
	
	public function prioritize(){
		
		$popular_hashtags = array();
		
		foreach($this->discover_hashtags as $hashtag => $frequency){
			$uniques = count($this->user_hashtags[$hashtag]);
			
			$total = round(sqrt($frequency) * $uniques, 6);
			
			if($total > 3){
				$popular_hashtags[$hashtag] = $total;
			}
		}

	
	
		$popular_mentions = array();
		foreach($this->discover_mentions as $mention => $frequency){
			$uniques = count($this->user_mentions[$mention]);
			
			$total = round(sqrt($frequency) * $uniques, 6);
			
			if($total > 3){
				$popular_mentions[$mention] = $total;
			}
		}
		
		
		
		$popular_words = array();
		foreach($this->discover_words as $word => $frequency){
			$uniques = count($this->user_words[$word]);
			
			$total = round(sqrt($frequency/4) * $uniques/(1.1), 6);
			
			//if($total > 0){
			if($total > 3){
				$popular_words[$word] = $total;
			}	
		}
		
		
		$popular_phrases = array();
		foreach($this->discover_phrases as $phrase => $frequency){
			$uniques = count($this->user_phrases[$phrase]);
			
			$total = round(sqrt($frequency/4) * ((1.1)*$uniques), 6);
			
			//if($total > 0){
			if($total > 3){
				$popular_phrases[$phrase] = $total;
			}	
		}
		
		
		//sort them
		asort($popular_hashtags);
		asort($popular_mentions);
		asort($popular_words);
		asort($popular_phrases);
	
		/*
		echo "<pre>";
		Echo "Result";
		print_r($popular_hashtags);
		print_r($popular_mentions);
		print_r($popular_words);
		print_r($popular_phrases);
		echo "<pre>";
		*/

		

		//limit to the most popular 3 last words to filter out noise
		$limited_single_words = array_slice($popular_words, -3,3);//limit it to the most popular 2 words
				
		//combine
		$hashtags_and_mentions = array_merge($popular_hashtags, $popular_mentions);
		$words_and_phrases = array_merge($limited_single_words, $popular_phrases);
		$result = array_merge($hashtags_and_mentions,$words_and_phrases);
		
		//sort and reverse
		asort($result);
		$result = array_reverse($result);

		
		//lets go through the top 15 occurances. If there is a phrase in these occurances, we need to remove words in that phrase from the entire result.
		$x = 0;
		foreach($result as $word => $score){
			if($x < 20){
				if(strpos($word, " ") > 0){
					$words_in_phrases = explode(" ",$word);
					
					foreach($words_in_phrases as $current_word){
						unset($result[$current_word]);
					}
					
					//unset($result[$words_in_phrase[0]]);
					//unset($result[$words_in_phrase[1]]);
				}
				$x++;
			}
		}

		//check for removal of trends
		$user_id = $this->user_id;
		$hidden = $this->query("SELECT text FROM hidden_trends WHERE user_id='$user_id'");
		if(mysql_num_rows($hidden) > 0){
			while($hidden_word = mysql_fetch_array($hidden)){
				unset($result[$hidden_word["text"]]);//remove that index from the possible list
			}
		}

		
		//now its greatest to least
		$shortened_result = array_slice($result, 0, 15);
	
		
		$this->result = $shortened_result;
	}//end prioritize


	public function print_all(){
		echo "<pre>";
		Echo "Result";
		print_r($shortened_result);
		
		
		echo "<pre>";		
	}







	public function build_snapshot_and_html(){
		
		if($this->error == 1){
			//echo  "<tr class='trend' onClick=''><td class='text'>Opps. There was an error. Please let us know about this to the left.</td><td class=''></td><td class='ct'></td></tr>";
		}	
		
		$this->create_snapshot();
		
		$html = "";
		
		$saved_images = array();
		
		foreach($this->result as $word => $score){
			//for each word
			
			switch($word[0]){//first character
				
				case "@"://mention
					$type = 1;
					$users = $this->user_mentions[$word];
					$freq = $this->discover_mentions[$word];
				break;
					
				case "#"://hashtag
					$type = 0;
					$users = $this->user_hashtags[$word];
					$freq = $this->discover_hashtags[$word];
				break;
					
				default://word(s)
				
					if(strpos($word, " ") > 0){
						//is phrase	
						$type = 2;
						$freq = $this->discover_phrases[$word];
						$users = $this->user_phrases[$word];
					
					}else{
						//is single word
						$type = 2;
						$freq = $this->discover_words[$word];
						$users = $this->user_words[$word];						
					}

				break;
			}
			
			$num_unique_users = count($users);

			$trend_id = $this->create_trend($type, $word, $score, $freq);
			
			$html .= "<tr class='trend trendNum_$trend_id' onClick='whosTrendingByID($trend_id);return false;'><td class='text'><span class='trend_draggable' id='$trend_id'>$word</span></td><td class='rtg'>".round($score)."</td><td class='ct'>$freq</td></tr>";
			
			$users_tweeted = array();
			foreach($users as $screen_name => $count){
				//$html .= "<tr class='data_on_trend data_trend_$trend_id'><td colspan='2' class='whos_trending'>@$screen_name</td><td>$count</td></tr>";
				array_push($users_tweeted, array("screen_name" =>$screen_name, "count" => $count));
			}
			
			
			$this->create_trending_tweets($trend_id, $this->associated_tweets[$word]);
		}//foreach trend as word and score
				
		//echo $hmtl;
		
		echo $html;
		
	}
	
	
	
	public function build_share_key(){
		$key = $this->snapshot_share_key;
		echo "<input type='hidden' name='transfering_data' id='snapshot_share_key' value='$key' />";
	}
	
	
	
	
	
	
	public function create_snapshot(){
		//user_id
		$user_id = $this->user_id;
		
		//token key
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";	
		$size = strlen( $chars );
		$key = "";
		for( $i = 0; $i < 15; $i++ ) {
			$key .= $chars[ rand( 0, $size - 1 ) ];
		}
		
		$this->query("INSERT INTO snapshots (`user_id`,`datetime`,`method`,`share_key`) VALUES ('$user_id', NOW(),'0','$key')");
		
		$this->snapshot_id = mysql_insert_id();
		$this->snapshot_share_key = $key;
		return true;
	}
	
	
	
	public function create_trend($type, $text, $score, $count){
		$snapshot_id = $this->snapshot_id;
		$this->query("INSERT INTO trends (`snapshot_id`,`score`,`count`,`text`,`type`) VALUES ('$snapshot_id','$score','$count','$text','$type')");
		return mysql_insert_id();
	}
	
	
	public function create_trending_tweets($trend_id, $data){
		
		$insert = "";
		$x = 0;
		
		if(!is_array($data)){
			echo "not an array!";
			
			$this->print_associations();
			die();
			return;
		}
		
		
		foreach($data as $tweet){
			
			if($x > 0){
				$insert .= ",";
			}
			
			$tweet_id = $tweet["tweet_id"];
			$name = str_replace("'","", $tweet["name"]);
			$s_name = $tweet["screen_name"];
			$text = mysql_real_escape_string(str_replace("'","", $tweet["tweet"]));
			$unix = $tweet["created_at"];
			$image = $tweet["image"];
			
			
			$insert .= "('$trend_id','$tweet_id','$name','$s_name','$text','$unix','$image')";
			
			$x++;
		}
		
		$this->query("INSERT DELAYED INTO trending_tweets (`trend_id`,`twitter_tweet_id`,`twitter_name`,`twitter_screen_name`,`tweet`,`tweet_datetime`,`twitter_image`) VALUES $insert");
		
	}
	
	
	
	public function print_result(){
		echo "<pre>";
		print_r($this->result);
		echo "</pre>";		
	}
	
	
	public function print_associations(){
		echo "<pre>";
		print_r($this->associated_tweets);
		echo "</pre>";
	}
	
	public function register_array_error(){
		$this->error = 1;
	}
	
}
?>