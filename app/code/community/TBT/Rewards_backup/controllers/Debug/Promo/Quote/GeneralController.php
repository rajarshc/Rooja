<?php

/**
 * @nelkaake 22/01/2010 3:54:41 AM : points expiry
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */

include_once ("TBT".DS."Rewards".DS."controllers".DS."Debug".DS."AbstractController.php");
include_once ("Mage".DS."Checkout".DS."controllers" .DS."CartController.php");
class TBT_Rewards_Debug_Promo_Quote_GeneralController extends TBT_Rewards_Debug_AbstractController
{

    public function indexAction() {
    	//$this->_startPointsSpendingTest();
    	//$this->_endPointsSpendingTestCase();
    	$this->loadLayout();
    	$tester = $this->getLayout()->createBlock('core/template')->setTemplate('rewards/debug/test.phtml');
    	$this->getLayout()->getBlock('content')->append($tester);
        $this->renderLayout();
    	return $this;
    }
    

    public function clearAction() {
        $result = $this->getRequest()->getParam("result", false);
        if($result) {
        	echo urldecode($result);
        }
        $this->_refreshQuote();
        exit;
        
    }
    
    public function prepareCart1Action() {
    	$this->_startPointsSpendingTest();
    	//$this->_endPointsSpendingTestCase();
        //$this->_refreshQuote();
        $this->_echoRedo();
    	return $this;
    }
    
    public function assertZeroPointsAction() {
    	$testCase = str_replace('Action', '', __METHOD__);
    	echo  "&nbsp;&nbsp; BEGIN {$testCase}<BR />";
    	
        Mage::getSingleton('rewards/session')->setPointsSpending(0);
        $this->_refreshQuote();
       	echo "&nbsp;&nbsp; &nbsp;&nbsp; {$this->getQuote()->getGrandTotal()} == 149.99:  ";
        if($this->getQuote()->getGrandTotal() == 149.99) {
        	echo "PASS! ";
        } else {
        	echo "FAIL! ";
        }
        //echo "done. <BR />"; 
    	//echo "&nbsp;&nbsp; END a. 0 Points spending. ------------- <BR />";
    	
        $this->_echoRedo();
    	return $this;
    }

    public function assert100PointsAction() {
    	$testCase = str_replace('Action', '', __METHOD__);
    	echo  "&nbsp;&nbsp; BEGIN {$testCase}.<BR />";
    	
        Mage::getSingleton('rewards/session')->setPointsSpending(1);
        $this->_refreshQuote();
       	echo "&nbsp;&nbsp; &nbsp;&nbsp; {$this->getQuote()->getGrandTotal()} == 148.99:  ";
        if($this->getQuote()->getGrandTotal() == 148.99) {
        	echo "PASS! ";
        } else {
        	echo "FAIL! ";
        }
        //echo "done. <BR />";
    	//echo "&nbsp;&nbsp; END a. 1000 Points spending. ------------- <BR />";
        $this->_echoRedo();
    	return $this;
    }
    public function assertFullDiscountAction() {
    	$testCase = str_replace('Action', '', __METHOD__);
    	echo  "&nbsp;&nbsp; BEGIN {$testCase}<BR />";
    	
        Mage::getSingleton('rewards/session')->setPointsSpending(15000);
        $this->_refreshQuote();
       	echo "&nbsp;&nbsp; &nbsp;&nbsp; {$this->getQuote()->getGrandTotal()} == 0:  ";
        if($this->getQuote()->getGrandTotal() == 0 && sizeof($this->getQuote()->getAllItems()) > 0) {
        	echo "PASS! ";
        } else {
        	echo "FAIL! ";
        }
    	
        $this->_echoRedo();
    	return $this;
    }
    
    
    
    
    ///////////////////////////// HELPER METHODS:
    
    
    
    protected function _echoRedo() {
    	echo "<a href='#' onclick='redoTest(this)'>redo</a>";
    	return $this;
    }
    
    protected function _redirectWithResult($result) {
    	$this->_redirect('*/*/clear', array('result'=>urlencode($result)));
    }
    
    
    protected function _startPointsSpendingTest($pid=16, $rid=3) {
    	echo "========<BR />";
    	$cust = $this->ensureCustomerLoggedIn();
    	echo "{$cust->getName()} is now logged in with points: {$cust->getPointsSummaryFull()}. <BR />";
    	echo "resetting config..."; $this->resetConfig(); echo "done. <BR />";
    	echo "empty cart (qid={$this->getQuote()->getId()})..."; $this->_emptyCart();
    	if($pid != null) { echo "add pid 16..."; echo $this->addProductToCart($pid); echo "done. <BR />"; }
    	echo "loading rule #4..."; $this->enableOnlyAndReturn($rid); echo "done. <BR />";
    	echo "========<BR />";
    	//$this->_getCart()->save();
    	//$this->_refreshQuote();
    	echo "<BR />";
    	return $this;
    }
    protected function _endPointsSpendingTestCase() {
    	echo  "========END Data #1: Product in cart: id #16 nokia 2610. ======<BR />";
    	return $this;
    }
    
    
    
    protected function resetConfig() {
    	$this->resetTaxConfig();
    	return $this;
    }
    
    protected function resetTaxConfig() {
        $conn = Mage::getSingleton('core/resource')->getConnection('core_read');
        $conn->beginTransaction();
        
        $this->_sqlUpdateConfig($conn, 'tax/calculation/price_includes_tax', 0);// excluding tax
        $this->_sqlUpdateConfig($conn, 'tax/calculation/apply_after_discount', 0); // apply tax before discount
        	
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

    /**
     *
     * @param int $rule_id
     * @return TBT_Rewards_Model_Salesrule_Rule
     */
    protected function disableAllCartRules() {
    	Mage::helper('rewards/debug_salesrule')->disableAllCartRules();
    	return $this;
    }
    
    /**
     *
     * @param int $rule_id
     * @return TBT_Rewards_Model_Salesrule_Rule
     */
    protected function enableOnlyAndReturn($rule_id=1) {
    	return Mage::helper('rewards/debug_salesrule')->enableOnlyAndReturn();
    }
    
    /**
     *
     * @return TBT_Rewards_Model_Mysql4_Salesrule_Rule_Collection
     */
    protected function _getAllSalesrules() {
    	return Mage::helper('rewards/debug_salesrule')->getAllRules();
    }
    
    
    
    protected function _refreshQuote() {
        return Mage::getSingleton('rewards/test_quote')->refreshQuote();
    }
    
    
    
    public function addProductToCart($pid, $qty=1) {
        return Mage::getSingleton('rewards/test_quote')->addProductToCart($pid, $qty);
    }      
    

    protected function _emptyCart() {
        return Mage::getSingleton('rewards/test_quote')->emptyCart();
    }
    
    public function emptyCartAction() {
    	$this->_emptyCart();
    	$this->_redirect('checkout/cart/index');
    }
    
    

}