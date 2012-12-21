<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Customer
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer account controller
 *
 * @category   local
 * @package    Activation_Authenticate
 * @author     Mani Prakash<maniprakash.c@gmail.com> 
 */
class Social_Login_FacebookController extends Mage_Core_Controller_Front_Action
{   
	/*
	Collection loading for Activation code filter
	*/
	
    public function indexAction()
    {
		$facebookUrl = Mage::getBaseDir("media") . DS . "facebook" . DS;
		
		require_once $facebookUrl.'facebook.php'; 
		
		 // Facebook App Id and App Secret
		 /*$appId       = '339343996157338';
		 $appSecret   = 'b1acca07be73767fb53b0d5cec0ccb95';*/
		 
		 /* $appId       = '541584732533673';
		 $appSecret   = '0fd3c40f0b2a786a231682f68eb9fc82';*/
		 
		 $appId       = '117824744984092';
		 $appSecret   = 'ac02494f0cb9b4d9e4352bdc45dab034';
		 $getUrl      = Mage::getUrl('login/facebook/index');

		 $callbackUrl = $getUrl;
		 
		 $code = $this->getRequest()->getParam('code');
	 
		 //authenticate user
		 
		 if(empty($code)) 
		 {
		
			$dialogUrl = 'https://www.facebook.com/dialog/oauth?client_id='.$appId.'&redirect_uri='.urlencode($callbackUrl).'&scope=email,user_birthday,user_likes,user_interests,friends_likes,friends_interests,publish_stream';
			
			echo("<script>top.location.href='".$dialogUrl."'</script>");
		 }
	
			
			//get user access_token
			$tokenUrl = 'https://graph.facebook.com/oauth/access_token?client_id='.$appId.'&redirect_uri='.urlencode($callbackUrl).'&client_secret='.$appSecret.'&code='.$code;
			
		    $accessToken = file_get_contents($tokenUrl); 
			
		
		    $fqlQuery = 'https://graph.facebook.com/me?'.$accessToken;
		    $fqlqueryResult = file_get_contents($fqlQuery);
		    $me = json_decode($fqlqueryResult, true);  
			
		if(!is_null($me)) 
		{  
		   // get the customer session
           $session = Mage::getSingleton('customer/session');
				
		   // Check the socail id in custom table
		   $data = Mage::getModel('login/login')->getCollection();
		   $data = $data->addFieldToFilter('social_id', $me['id']);
			
  		   $getCount = count($data);
		
		   $getData = $data->getData();
		   
		   $filterData = $getData[0];
		   	 
		   $customer_data = Mage::getModel('customer/customer')->load($filterData['customer_id']);  
		   $customerCheck = count($customer_data->getData());
		   
		  if($getCount) 
		  {  
			  $session->loginById($filterData['customer_id']);
	      }
		  else
		  {   
		  		// get the store infromation and etc 
				$storeId   = Mage::app()->getStore()->getStoreId();
				$websiteId = Mage::getModel('core/store')->load(Mage::app()->getStore()->getStoreId())->getWebsiteId();
				$email = $me['email'];
			 	 
				 // check the facebook email with customer table
					$customerData = Mage::getModel('customer/customer')->getCollection();
					$customerData = $customerData->addFieldToFilter('email', $email);
					$customerData = $customerData->addFieldToFilter('store_id', $storeId);
					$customerData = $customerData->addFieldToFilter('website_id', $websiteId);
					$customerData = $customerData->getData();
					
					$getCustomerData = $customerData[0];
				
     			$entityId = $getCustomerData['entity_id'];

                if($entityId) 
				{  
					$data = Mage::getModel('login/login');
					$data->setCustomerId($entityId);
					$data->setSocialId($me['id']);
					$data->setFbEmail($me['email']);										
			        $data->save();
					
                    $session->loginById($entityId);
                } 
				else 
				{ 
                    $this->_registerCustomer($me, $session);
                }
			  
		  }
		//   $this->_loginPostRedirect($session);
		
		$this->_redirectSuccess(Mage::getUrl('home', array('_secure'=>true)));
		return;
		 
		}
		
    }
	
	
	private function _registerCustomer($data, $session) 
	{  
				$customer = Mage::getModel('customer/customer')
							->setFirstname($data['first_name'])
							->setLastname($data['last_name'])
							->setEmail($data['email'])
							->setGender(
								Mage::getResourceModel('customer/customer')
									->getAttribute('gender')
									->getSource()
									->getOptionId($data['gender'])
							)
							->setIsActive(1)
							->setConfirmation(null)
							->save();


		
        Mage::getModel('customer/customer')->load($customer->getId())->setConfirmation(null)->save();
        $customer->setConfirmation(null);
        $session->setCustomerAsLoggedIn($customer);
        $customer_id = $session->getCustomerId();
		
					// store the facebook user id in login table
					$login_store = Mage::getModel('login/login')->setId(null);
					$login_store->setCustomerId($customer_id);
					$login_store->setSocialId($data['id']);
					$login_store->setFbEmail($data['email']);									
			        $login_store->save();
					
    }
	
	
	public function checkAction()
	{
			$login_store = Mage::getModel('login/login')->getCollection();		
			$login_store = $login_store->getData();
			
			echo "<pre>";
			print_r($login_store);
			echo "</pre>";			
			
		//  $login_store = $login_store->addFieldToFilter('customer_id', 33);
		/*	if(count($login_store))
			{
				$loginId = $login_store[0];	
			
				$loginId['login_id'];
				
				$loginCustomer = Mage::getModel('login/login');
				$loginCustomer->load($loginId['login_id']);		
				$loginCustomer->delete();

			}*/
		
	}
	
		
}
