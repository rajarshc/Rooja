<?php
class Magestore_AffiliateplusReferFriend_Block_Refer_Yahoo extends Magestore_AffiliateplusReferFriend_Block_Refer_Abstract
{
	/**
	 * get Contacts list to show
	 * 
	 * @return array
	 */
	public function getContacts(){
		$list = array();
		$session = Mage::getSingleton('affiliateplusreferfriend/refer_yahoo')->getSession();
		if (!$session) return $list;
		
		$sessionUser = $session->getSessionedUser();
		$contacts = $sessionUser->getContacts(0,10000);
		$contacts = $contacts->contacts->contact;
		
		foreach ($contacts as $contact){
			$fields = $contact->fields;
			$_contact = array();
			foreach ($fields as $field){
				if ($field->type == 'name'){
					$value = $field->value;
					$_contact['name'] = $value->givenName;
					if ($value->middleName) $_contact['name'] .= ' '.$value->middleName;
					if ($value->familyName) $_contact['name'] .= ' '.$value->familyName;
				}
				if ($field->type == 'email')
					$_contact['email'] = $field->value;
			}
			if (isset($_contact['email']) && $_contact['email'])
				$list[] = $_contact;
		}
		return $list;
	}
}
