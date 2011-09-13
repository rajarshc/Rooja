<?php

class Rbanh_Dev_IndexController extends Mage_Core_Controller_Front_Action
{
    // http://site.com/index.php/rbanh/index/
    public function indexAction()
    {
    	$id = preg_replace('/[^0-9]/', '', $_GET['id']);
    	$url = $_GET['url'];
    	
    	// if no ID is given, then just return
    	if (empty($id)) return;
    	if (empty($url)) return;
        
        // handle the decode
        $url = 'http://'.$_SERVER['HTTP_HOST'].$url;
        error_log($url);

        // fetch magento vars
        $product = Mage::getModel('catalog/product')
			->setCurrentStore(1)
			->load($id)
			->getData();

		$rl_floor = $product['rl_floor'];
		$rl_inc = $product['rl_inc'];
		$rl_price = $product['rl_price'];
		$special_price = $product['special_price'];
		
		// if no floor or inc, return
		if (empty($rl_floor) && empty($rl_inc)) return;
		
		// fetch facebook likes
		$likes = $this->fetch_facebook_data($url);
		//var_export($url);
		// error with FB server
		if ($likes === false) return;
		
		// increment the like, since you just liked it
		//$likes++;
		
        // update product price, if needed
        if (($rl_inc * $likes) < $rl_floor)
        {
        	$new_price = floatval($special_price - ($rl_inc * $likes));
        	$re = $this->update_price($id, $new_price);
        	
        	//var_export($re);
        	echo $new_price;
        	
        	return;
        }
        else
        {
        	return;
        }
        
    }
    
    // http://site.com/index.php/rbanh/index/test/
    //public function testAction()
    //{
   // 	$url = 'http://rooja.actsofphilosophie.com/women/victoria-s-secret-collection/leopard-panties.html';
   // 	var_export($this->fetch_facebook_data($url));
   // }
    
    // get FB data
    private function fetch_facebook_data($product_url)
    {
       	$sql = "SELECT like_count FROM link_stat WHERE url=\"$product_url\"";
       	
    	$url = "http://api.facebook.com/method/fql.query?query=".urlencode($sql);
    	
    	$result = new SimpleXMLElement(file_get_contents($url));
    	
    	//var_export(current($result->link_stat->like_count));
   		
       	if (isset($result->link_stat))
    		return current($result->link_stat->like_count);
    		
    	return false;
    }
    
    // update price
    private function update_price($id, $price)
    {
    	$product = Mage::getModel('catalog/product')
		    ->setCurrentStore(1)
		    ->load($id);
		
		// note: the 3rd param is the store id
		return $product->addAttributeUpdate('rl_price', $price, 1);
    }
 
}
?>