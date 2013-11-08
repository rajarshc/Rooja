<?php   
require_once "app/code/local/Aurigait/Homepage/twitteroauth/twitteroauth.php";
class Aurigait_Banner_Block_Index extends Mage_Core_Block_Template
{
	protected $twitteruser = "roojafashion";
	protected $notweets = 15;
	protected $consumerkey = "OGsU2FCQgKtm9w7FLiFtg";
	protected $consumersecret = "uXUdpCHYECqoX0HmwMrKxohtH0Hy8gKSqtUymr3Mg";
	protected $accesstoken = "119309269-45J4jyHNk1nzXwSlPj3R7Tk7znFWOOINL5Dw4dVu";
	protected $accesstokensecret = "rwJEgS2QdOjr7NaXDnNLachFmnkPblAQcoU0LL0w";
	
	public function getTweetes()
	{
		$consumerkey=$this->consumerkey;
		$consumersecret=$this->consumersecret;
		$accesstoken=$this->accesstoken;
		$accesstokensecret=$this->accesstokensecret;
		
		$connection = $this->getConnectionWithAccessToken($consumerkey, $consumersecret, $accesstoken, $accesstokensecret);
		$tweets=$connection->get("https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name=".$twitteruser."&count=".$notweets);
		return $tweets;
	}
	function getConnectionWithAccessToken($cons_key,$cons_secret,$oauth_token,$oauth_token_secret) {
  		$connection = new TwitterOAuth($cons_key, $cons_secret, $oauth_token, $oauth_token_secret);
 		return $connection;
}	
}