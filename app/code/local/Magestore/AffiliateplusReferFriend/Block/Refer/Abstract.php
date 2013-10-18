<?php
class Magestore_AffiliateplusReferFriend_Block_Refer_Abstract extends Mage_Core_Block_Template
{
	/**
	 * get Contacts List to show for select
	 * 
	 * @return array
	 */
	public function getContacts(){
		return array();
	}
	
	public function getEmailValue($contact){
		if (isset($contact['name']) && trim($contact['name'])){
			return $contact['name'] . '<' . $contact['email'] . '>';
		}
		return $contact['email'];
	}
}
