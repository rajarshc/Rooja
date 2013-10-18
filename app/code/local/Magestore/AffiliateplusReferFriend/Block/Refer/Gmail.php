<?php
class Magestore_AffiliateplusReferFriend_Block_Refer_Gmail extends Magestore_AffiliateplusReferFriend_Block_Refer_Abstract
{
	/**
	 * get Contacts list to show
	 * 
	 * @return array
	 */
	public function getContacts(){
		$list = array();
		$request = $this->getRequest();
		if (!$request->getParam('oauth_token') && !$request->getParam('oauth_verifier'))
			return $list;
		
		$google = Mage::getSingleton('affiliateplusreferfriend/refer_gmail');
		$oauthData = array(
			'oauth_token'	=> $request->getParam('oauth_token'),
			'oauth_verifier'	=> $request->getParam('oauth_verifier'),
		);
		$accessToken = $google->getAccessToken($oauthData,unserialize($google->getGmailRequestToken()));
		$oauthOptions = $google->getOptions();
		
		$httpClient = $accessToken->getHttpClient($oauthOptions);
		
		$gdata = new Zend_Gdata($httpClient);
		$query = new Zend_Gdata_Query('https://www.google.com/m8/feeds/contacts/default/full');
		$query->setMaxResults(10000);
		$feed = array();
		try {
			$feed = $gdata->getFeed($query);
		} catch (Exception $e){}
		
		foreach ($feed as $entry){
			$_contact = array();
			$xml = simplexml_load_string($entry->getXML());
			$_contact['name'] = $entry->title;
			foreach ($xml->email as $e){
				$email = '';
				if (isset($e['address'])) $email = (string)$e['address'];
				if ($email){
					$_contact['email'] = $email;
					$list[] = $_contact;
				}
			}
		}
		return $list;
	}
}