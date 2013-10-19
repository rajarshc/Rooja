<?php
class Magestore_AffiliateplusReferFriend_Block_Refer_Hotmail extends Magestore_AffiliateplusReferFriend_Block_Refer_Abstract
{
	/**
	 * get Contacts list to show
	 * 
	 * @return array
	 */
	public function getContacts(){
		$list = array();
		
		$hotmail = Mage::getSingleton('affiliateplusreferfriend/refer_hotmail');
		if (!$hotmail->isAuth()) return $list;
		$contactsData = $hotmail->getContactsData();
		
		return $list;
	}
}
