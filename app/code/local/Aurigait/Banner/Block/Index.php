<?php   
require_once "app/code/local/Aurigait/Homepage/twitteroauth/twitteroauth.php";
class Aurigait_Banner_Block_Index extends Mage_Core_Block_Template
{
	protected $twitteruser = "twitter@rooja.in";
	protected $notweets = 15;
	protected $consumerkey = "rQvrzKwSP6wZcZzXTCpWcA";
	protected $consumersecret = "oWwQaVdeFubiKQwiAlHtpQFu9iDuAqNz72sjxb14";
	protected $accesstoken = "247176343-cisgL2KQN6uvdImuI2j6kk9rO0a5QVXx3KzdECdC";
	protected $accesstokensecret = "gOZ0GRIgpCpY14l4ZihQ7t8VbScWZroybZ4MskKEB9HkF";
	
	public function getTweetes()
	{
		$consumerkey=$this->consumerkey;
		$consumersecret=$this->consumersecret;
		$accesstoken=$this->accesstoken;
		$accesstokensecret=$this->accesstokensecret;
		
		$connection = $this->getConnectionWithAccessToken($consumerkey, $consumersecret, $accesstoken, $accesstokensecret);
		$tweets=$connection->get("https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name=".$twitteruser."&count=".$notweets."&exclude_replies=true");
		return $tweets;
	}
	function getConnectionWithAccessToken($cons_key,$cons_secret,$oauth_token,$oauth_token_secret) {
  		$connection = new TwitterOAuth($cons_key, $cons_secret, $oauth_token, $oauth_token_secret);
 		return $connection;
}	
}