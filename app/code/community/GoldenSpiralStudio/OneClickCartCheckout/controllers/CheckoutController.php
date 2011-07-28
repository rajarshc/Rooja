<?php
class GoldenSpiralStudio_OneClickCartCheckout_CheckoutController extends Mage_Core_Controller_Front_Action
{
//    public function saveOrderAction()
//    {
//    d($_POST);
/*
array(13) {
  ["address_id"]=>
  string(0) ""
  ["firstname"]=>
  string(3) "133"
  ["lastname"]=>
  string(2) "33"
  ["company"]=>
  string(3) "333"
  ["street"]=>
  array(2) {
    [0]=>
    string(2) "33"
    [1]=>
    string(2) "33"
  }
  ["city"]=>
  string(2) "33"
  ["region_id"]=>
  string(2) "19"
  ["region"]=>
  string(0) ""
  ["postcode"]=>
  string(5) "61029"
  ["country_id"]=>
  string(2) "US"
  ["telephone"]=>
  string(3) "333"
  ["fax"]=>
  string(2) "44"
  ["save_in_address_book"]=>
  string(1) "1"
}

end
*/
//    }
    
      public function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }
    
    
    

    
    
 private function _setShippingMethod($method = null){
 	
    	$onepage = $this->getOnepage();
		$quote = $this->getOnepage()->getQuote();
		$shipping = $quote->getShippingAddress();
		//$shipping->setData("country_id","US");
		
		
		
		
		if (!$method)
 			$method = $shipping->getShippingMethod(); 
		
		$shipping->setShippingMethod($method);
		$shipping->setCollectShippingRates(true)->save();  
    }
    
    private function _getReviewHtml(){
    	$this->_setShippingMethod();
    	 $this->loadLayout('oneclickcartcheckout_all');
         return ($this->getLayout()->getBlock('review.info')->toHtml());
    }
    
    public function saveShippingMethodAction(){
     if ($this->getRequest()->isPost()) {
      	$this->_setShippingMethod($this->getRequest()->getPost('shipping_method',""));
     	 die($this->_getReviewHtml());
      }
    }
    
    
    public function _getCartHtml(){
    	$this->loadLayout('oneclickcartcheckout_all');
    	return $this->getLayout()->getBlock('checkout.cart')->toHtml();
    }
    
       protected function _getCart()
    { 
        return Mage::getSingleton('checkout/cart');
    }
    
    protected function _getShippingMethodsHtml()
    {
          $this->loadLayout('oneclickcartcheckout_all');
    	 return $this->getLayout()->getBlock('oneclickcartcheckout.shipping_methods')->toHtml();
    }
    
    
 public function updateCartAction(){
 
    
    	$type = $this->getRequest()->getPost("type","");
    	if (!$type)
    	{
    		$this->_redirect('checkout/cart/');
    		
    	}else {
	  	  
    	$item_id = $this->getRequest()->getPost("item_id","");
    	
        try {
         
                $cart = $this->_getCart();
                if (! $cart->getCustomerSession()->getCustomer()->getId() && $cart->getQuote()->getCustomerId()) {
                    $cart->getQuote()->setCustomerId(null);
                }
    
                foreach($cart->getItems() as $item)
                {
                	
                	if ($item_id == $item->getProductId())
                	{
                		if ($type=="decrease"){
                			$item->setQty($item->getQty()-1);
                		}else {
                			$item->setQty($item->getQty()+1);
                		}
                		$item->save();
                		break;
                	}
                }

                
        	  //$this->getOnepage()->initCheckout();
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot update shopping cart.'));
        }
     
        
    	//$this->getReviewHtmlAction();
    	die(json_encode(array(
    		"review"=>$this->_getReviewHtml(), 
    		"cart"=>$this->_getCartHtml(),
    		"shipping_method"=>$this->_getShippingMethodsHtml()
    	)));
    	}
       
    }
    
  protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }
    
    public function saveOrderAction()
    {
           
         
    	if (Mage::helper("checkout/cart")->getItemsCount()!=0):
        $result = $this->getOnepage()->savePayment(
	        $this->getRequest()->getPost('payment',array())
	    );
	  
	    
	    if ( $this->getRequest()->getPost("register")=="on")
	    {
	    	$result = $this->getOnepage()->saveCheckoutMethod("register");
	    }
	    
        $billing = $this->getRequest()->getPost("billing",array());
        $user = Mage::getSingleton('customer/session')->getCustomer();
        if ($user)
        	$email =$user->getEmail();
        $customerAddressId = $this->getRequest()->getPost("billing_address_id");
        if ($customerAddressId)
        {
        	   $address = Mage::getModel('customer/address')->load($customerAddressId);
        	   $billing = array( 
		             	   	"firstname"=>$address->getFirstname(),
		             	   	"lastname"=>$address->getLastname(),
		             	   	"email"=>$email, 
		             	    "company"=>$address->getCompany(), 
		             	    "city"=>$address->getCity(),
		             	   	"street"=>array($address->getStreet(),"--"),
		             	   	"region"=>$address->getRegion(),
		             	   	"country_id"=>$address->getCountryId(),
		             	   	"postcode"=>$address->getPostCode(),
		               	    "telephone"=>$address->getTelephone());
        }
     if (@!$billing["city"]) {$billing["city"]="---";}
            if (@!$billing["postcode"]) {$billing["postcode"]="---";}
            if (@!$billing["telephone"]) {$billing["telephone"]="1111111";}
            if (@!$billing["company"]) {$billing["company"]="---";}
            if (@!$billing["region"]) {$billing["region"]="---";}
       $this->getOnepage()->saveBilling($billing,$customerAddressId);
       
       $shipping =  $this->getRequest()->getPost("shipping",array());
    
       if ($shipping["firstname"]!="")
       {         

           
            if (@!$shipping["city"]) {$shipping["city"]="---";}
            if (@!$shipping["postcode"]) {$shipping["postcode"]="---";}
            if (@!$shipping["telephone"]) {$shipping["telephone"]="1111111";}
            if (@!$shipping["company"]) {$shipping["company"]="---";}
            if (@!$shipping["region"]) {$shipping["region"]="---";}
           // if (count($shipping["street"])<2) {
//                $shipping["street"][] = $shipping["street"][0];
//            }
            //d($shipping);
           $this->getOnepage()->saveShipping($shipping,$customerAddressId);
       }else {
           $this->getOnepage()->saveShipping($billing,$customerAddressId);
       }

       
       
       $result = array();
        try {
            if ($requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds()) {
                $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
                if ($diff = array_diff($requiredAgreements, $postedAgreements)) {
                    $result['success'] = false;
                    $result['error'] = true;
                    $result['error_messages'] = $this->__('Please agree to all the terms and conditions before placing the order.');
                   $this->_getSession()->addError($this->__('Please agree to all the terms and conditions before placing the order.'));
                    $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                    
                    return;
                }
            }
            
           
       if ($data = $this->getRequest()->getPost('payment', false)) {
          	  $payment = $this->getOnepage()->getQuote()->getPayment();
        $payment->importData($data);
        $this->getOnepage()->getQuote()->getShippingAddress()->setPaymentMethod($payment->getMethod());
       	$this->getOnepage()->getQuote()->getPayment()->importData($data);

       }
            
            $redirectUrl = $this->getOnepage()->getQuote()->getPayment()->getCheckoutRedirectUrl();
            if ($redirectUrl){
            	header("Location: ".$redirectUrl);
            	die($redirectUrl);
            }
           
            $data = $this->getRequest()->getPost('shipping_method', '');
            $result = $this->getOnepage()->saveShippingMethod($data);
	    
            
            $or = $this->getOnepage()->saveOrder();
           	$order = Mage::getModel('sales/order')->load(Mage::getSingleton('checkout/session')->getLastOrderId());
           	$note = $this->getRequest()->getPost('customer_comment',"");
           	if ($this->getRequest()->getPost('pdd',null))
           	{
           		$note.="<br/>Prefered Delivery Date: <b>{$this->getRequest()->getPost('pdd',null)}</b>";
           	}
           	$order->setCustomerNote($note);
           	$order->save();
           
            $redirectUrl = $this->getOnepage()->getCheckout()->getRedirectUrl();
            $result['success'] = true;
            $result['error']   = false;
        } catch (Mage_Core_Exception $e) {
        	//               die("asdasd");
        	
            Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            $result['success'] = false;
            $result['error'] = true;
            $result['error_messages'] = $e->getMessage()."";
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
           Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            $result['success']  = false;
            $result['error']    = true;
            $result['error_messages'] = $this->__('There was an error processing your order. Please contact us or try again later.');
            $this->_getSession()->addError($this->__('There was an error processing your order. Please contact us or try again later.'));
        }
        if ($result['error']==true)
        {
        	//die("Asdasd");
        	  $this->getResponse()->setRedirect(Mage::getBaseUrl()."checkout/cart/"); 
        } else 
        {
        $this->getOnepage()->getQuote()->save();
          $this->getResponse()->setRedirect(Mage::getBaseUrl()."checkout/onepage/success"); 
         }
        else:
        $this->_getSession()->addSuccess($this->__('You havent any items in your cart'));
            $this->getResponse()->setRedirect("/");
        endif;
    }

    protected function _getQuote()
    {
        return $this->_getCart()->getQuote();
    }

    
    public function useCouponAction(){
        if (!$this->_getCart()->getQuote()->getItemsCount()) {
            return;
        }

        $couponCode = (string) $this->getRequest()->getParam('coupon');
        if ($this->getRequest()->getParam('remove_coupon') == 1) {
            $couponCode = '';
        }
        $oldCouponCode = $this->_getQuote()->getCouponCode();
        
         if (!strlen($couponCode) && !strlen($oldCouponCode)) {
            
               die(json_encode(array(
            "review"=>$this->_getReviewHtml(), 
            "cart"=>$this->_getCartHtml(),
            "coupon_html"=>$this->_getCouponHtml()
        )));
        }

        try {
            $this->_getQuote()->getShippingAddress()->setCollectShippingRates(true);
            $this->_getQuote()->setCouponCode(strlen($couponCode) ? $couponCode : '')
                ->collectTotals()
                ->save();

            if ($couponCode) {
                if ($couponCode == $this->_getQuote()->getCouponCode()) {
                    //die($this->__('Coupon code "%s" was applied.', Mage::helper('core')->htmlEscape($couponCode)));
                    $this->_getSession()->addSuccess(
                        $this->__('Coupon code "%s" was applied.', Mage::helper('core')->htmlEscape($couponCode))
                    );
                }
                else {
                    //die(    $this->__('Coupon code "%s" is not valid.', Mage::helper('core')->htmlEscape($couponCode)));            
                    $this->_getSession()->addError(
                        $this->__('Coupon code "%s" is not valid.', Mage::helper('core')->htmlEscape($couponCode))
                    );
                }
            } else {
                $this->_getSession()->addSuccess($this->__('Coupon code was canceled.'));
            }

        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Cannot apply the coupon code.'));
            Mage::logException($e);
        }  
        
        die(json_encode(array(
            "review"=>$this->_getReviewHtml(), 
            "cart"=>$this->_getCartHtml(),
            "coupon_html"=>$this->_getCouponHtml()
        )));
        
    }
    
    
    function _getCouponHtml(){
        $this->loadLayout('oneclickcartcheckout_all');
         return $this->getLayout()->getBlock('oneclickcartcheckout.coupon')->toHtml();
    }
    
  
    
    public function updateCountryAction()
    {
    	$country_id = $this->getRequest()->getParam('country_id');
    	 	$method = $this->getOnepage()->getQuote()->getShippingAddress()->getShippingMethod();
    	
    	 	//d($method);
    	 	
    	
    	$this->getOnepage()->getQuote()->getShippingAddress()
			->setCountryId($country_id)
			->setShippingMethod($method)
			->setCollectShippingRates(true)
			->save();
		
				
		$object = new Mage_Checkout_Block_Onepage_Shipping_Method_Available();
		
		foreach ($object->getShippingRates() as $_code => $_rate)
		{
			
			$shippingMethod = $_code."_".$_code;
	 		$this->_setShippingMethod($shippingMethod);
			break;
 		}  
	
 			//d($this->getOnepage()->getQuote()->getShippingAddress()->getCountryId());
		 
        die(json_encode(array(
   			"review"=>$this->_getReviewHtml(),
            "shipping_method"=>$this->_getShippingMethodsHtml(), 
        )));
        	//		d($this->getOnepage()->getQuote()->getShippingAddress()->getCountryId());
    }  
}