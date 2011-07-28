<?php
class GoldenSpiralStudio_OneClickCartCheckout_Block_Checkout_Shipping  extends Mage_Checkout_Block_Onepage_Shipping
{
    function get_web_page( $url )  
{
    $options = array(
        CURLOPT_RETURNTRANSFER => true,     // return web page
        CURLOPT_HEADER         => false,    // don't return headers
        CURLOPT_FOLLOWLOCATION => true,     // follow redirects
        CURLOPT_ENCODING       => "",       // handle all encodings
        CURLOPT_USERAGENT      => "spider", // who am i
        CURLOPT_AUTOREFERER    => true,     // set referer on redirect
        CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
        CURLOPT_TIMEOUT        => 120,      // timeout on response
        CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
    );

    $ch      = curl_init( $url );
    curl_setopt_array( $ch, $options );
    $content = curl_exec( $ch );
    $err     = curl_errno( $ch );
    $errmsg  = curl_error( $ch );
    $header  = curl_getinfo( $ch );
    curl_close( $ch );

    $header['errno']   = $err;
    $header['errmsg']  = $errmsg;
    $header['content'] = $content;
    return $header; 
}
    
   public function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }
     

 	
    public function getCountryHtmlSelectDetect($type)
    {
    	
    	
    	$onepage = $this->getOnepage();
		$quote = $this->getOnepage()->getQuote();
		$shipping = $quote->getShippingAddress();
		if ($shipping->getCountryId())
			$countryId =  $shipping->getCountryId();
    	else {
	        $countryId = $this->getAddress()->getCountryId();
	        
	        $response= $this->get_web_page("http://api.wipmania.com/".$_SERVER['REMOTE_ADDR']);
	       if ($response['content'])
	      	 $countryId = $response['content'];
    	}
        $select = $this->getLayout()->createBlock('core/html_select')
            ->setName($type.'[country_id]')
            ->setId($type.':country_id')
            ->setTitle(Mage::helper('checkout')->__('Country'))
            ->setClass('validate-select')
            ->setValue($countryId)
            ->setOptions($this->getCountryOptions());
        if ($type === 'shipping') {
            $select->setExtraParams('onchange="shipping.setSameAsBilling(false);"');
        }

        return $select->getHtml();
    }
    
    
}