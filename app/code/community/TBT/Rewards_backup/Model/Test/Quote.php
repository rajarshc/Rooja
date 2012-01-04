<?php
class TBT_Rewards_Model_Test_Quote extends TBT_Rewards_Model_Test_Abstract {

    public function emptyCart() {
    	$this->_getCart()->truncate();
        return $this->_getCart();
    }
    
    
    
    public function addProductToCart($pid, $qty=1) {
        try {
        	$p = Mage::getModel('catalog/product')->load($pid);
        	$cart = $this->_getCart();
            $cart->addProduct($p, $qty);    
            $cart->save(); 
        	$msg = printf("%s x '%s' (sku=%s, price=%s) was added to the cart.", $qty, $p->getName(), $p->getSku(), $p->getFinalPrice() );
        } catch (Exception $e) {
            $msg = str_replace("\n", "<BR />", (string) "ERROR: ". $e->getMessage());
            Mage::logException($e);
            $this->o($msg);
        }
        return $msg;
    } 
    

    public function printCart() {
        $cart = $this->getQuote();
        $this->o( "Cart Contents: ");
        if(!$cart->hasItems()) {
            $this->o( "(empty)");
        } else {
            $printed_item = array();
            foreach($cart->getAllItems() as $item) {
                if(!isset($printed_item[$item->getProductId()])) {
                    $this->o( "[{$item->getQty()}x'{$item->getName()}'=={$item->getRowTotal()}] ");
                    $this->o( ", ");
                }
                $printed_item[$item->getProductId()] = true;
            }
        }
        $this->o("| <b>Grand Total: ". $this->getQuote()->getGrandTotal() . "</b>");
        $this->o( "<BR />");
        
        return $this;
        
    }
    
    public function refreshQuote() {
        $quote = $this->getQuote();
        
        $quote->getShippingAddress()->setCollectShippingRates(true);
        $quote->setTotalsCollectedFlag(false)->collectTotals()->save();
            
        
        return $this;
    }
    
    protected function resetTaxConfig() {
        $conn = Mage::getSingleton('core/resource')->getConnection('core_read');
        $conn->beginTransaction();
        
        $this->_sqlUpdateConfig($conn, 'tax/calculation/price_includes_tax', 0);// excluding tax
        $this->_sqlUpdateConfig($conn, 'tax/calculation/apply_after_discount', 0); // apply tax before discount
        $this->_sqlUpdateConfig($conn, 'row_tax_cart_display_price', 0); // apply tax before discount
        
        $this->_sqlUpdateConfig($conn, 'tax/display/type', 1); // apply tax before discount
        $this->_sqlUpdateConfig($conn, 'tax/display/shipping', 1); // apply tax before discount
        $this->_sqlUpdateConfig($conn, 'tax/cart_display/shipping', 1); // apply tax before discount
        $this->_sqlUpdateConfig($conn, 'tax/cart_display/subtotal', 1); // apply tax before discount
        $this->_sqlUpdateConfig($conn, 'tax/cart_display/price', 1); // apply tax before discount
        	
        $conn->commit();
    	return $this;
    	
    }
    
    protected function _sqlUpdateConfig($conn, $path, $val)  
    {
        $conn->query("
			UPDATE    `core_config_data`  
			SET    `value` = '{$val}'  
			WHERE    `path` = '{$path}'
			;
        ");
        return $this;
    }

    protected function _sqlDeleteConfig($conn, $path)  
    {
        $conn->query("
			DELETE FROM    `core_config_data`  
			WHERE    `path` = '{$path}'
			;
        ");
        return $this;
    }
         
    
}