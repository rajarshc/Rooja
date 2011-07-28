<?php

class GoldenSpiralStudio_OneClickCartCheckout_Helper_Data extends Mage_Core_Helper_Abstract
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


function getCountry(){

	if(Mage::getStoreConfig('checkout/oneclickcartcheckout/country_status')=="detect" || !Mage::getStoreConfig('checkout/oneclickcartcheckout/country_status')):
		   $response= $this->get_web_page("http://api.wipmania.com/".$_SERVER['REMOTE_ADDR']);
       if ($response['content']);
      	return  $countryId = $response['content'];
	else:
		return "US";
	endif;	
}

}