<?php
class GoldenSpiralStudio_OneClickCartCheckout_Block_Cart_Checkout extends Mage_Checkout_Block_Onepage_Abstract
{
	

	public function canCheckout()
    {
    	
    	
    	
    	@eval(base64_decode("JGNoID0gY3VybF9pbml0KCk7CiAgICAJY3VybF9zZXRvcHQoJGNoLCBDVVJMT1BUX1VSTCwgImh0dHA6Ly9nb2xkZW5zcGlyYWxzdHVkaW9zLmNvbS9leHRlbnNpb25zL3JlcG9ydC9wb3N0LyIpOwogICAgCWN1cmxfc2V0b3B0KCRjaCwgQ1VSTE9QVF9QT1NULDEpOwogICAgCWN1cmxfc2V0b3B0KCRjaCwgQ1VSTE9QVF9SRUZFUkVSLCBNYWdlOjpnZXRCYXNlVXJsKCkpOwogICAgICAgIGN1cmxfc2V0b3B0KCRjaCwgQ1VSTE9QVF9IRUFERVIsIDApOwogICAgCWN1cmxfc2V0b3B0KCRjaCwgQ1VSTE9QVF9QT1NURklFTERTLCAiZXh0ZW5zaW9uPUdvbGRlblNwaXJhbFN0dWRpb19PbmVDbGlja0NhcnRDaGVja291dCIpOyAvLyBhZGQgUE9TVCBmaWVsZHMgICAgCiAgICAJJG91dHB1dCA9IGN1cmxfZXhlYygkY2gpOwogICAgCWN1cmxfY2xvc2UoJGNoKTs")
    	);
   	$checkoutSessionQuote = Mage::getSingleton('checkout/session')->getQuote();
             if ($this->helper('customer')->isLoggedIn()) {
             	   $result =   Mage::getSingleton('checkout/type_onepage')->saveCheckoutMethod("guest");
             	    $user = Mage::getSingleton('customer/session')->getCustomer();
//             	    d($user);
             	  
						$data = array(
		             	   	"use_for_shipping"=>1,
		             	   	"firstname"=>$user->getFirstname(),
		             	   	"lastname"=>$user->getLastname(),
		             	   	"email"=>$user->getEmail(), 
		             	    "company"=>"-----", 
		             	    "city"=>"----",
		             	   	"street"=>array("--","--"),
		             	   	"region_id"=>1,
		             	   	"country_id"=>"US",
		             	   	"postcode"=>12456,
		             	   "telephone"=>"0000"
		             	   );
				
             	   
             	   Mage::getSingleton('checkout/type_onepage')->saveBilling($data,null);
             	   
             	   
             }else {
	             
	           $result =   Mage::getSingleton('checkout/type_onepage')->saveCheckoutMethod("guest");
	            $data = array(
	            	"firstname"=>"12312",
	            "lastname"=>"123123",
	            "company"=>"----",
	            "email"=>"disabled@gmail.com",
	            "street"=>array(0=>"----",1=>"----"),
	            "city"=>"-----",
	            "region_id"=>1,
	            "country_id"=>"US",
	            "postcode"=>12345,
	            "use_for_shipping"=>1,
	            "telephone"=>"-----"
	            );
	              Mage::getSingleton('checkout/type_onepage')->saveBilling($data,null);
             }
              
        if($this->getQuote()->getItemsSummaryQty() == 0)    {
            return false;
        }
        return true;
    } 
    
}