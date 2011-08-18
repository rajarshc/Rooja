<?php
require("libfuncs.php");

class Mage_Avenues_StandardController extends Mage_Core_Controller_Front_Action
{
    
    protected $_order;

    
    public function getDebug ()
    {
        return Mage::getSingleton('Avenues/config')->getDebug();
    }

   
    public function getOrder ()
    {
        if ($this->_order == null) {
            $session = Mage::getSingleton('checkout/session');
            $this->_order = Mage::getModel('sales/order');
            $this->_order->loadByIncrementId($session->getLastRealOrderId());
        }
        return $this->_order;
    }

    
    public function redirectAction()
    {
    
        $session = Mage::getSingleton('checkout/session');
        $session->setAvenuesStandardQuoteId($session->getQuoteId());

        $order = $this->getOrder();

        if (!$order->getId()) {
            $this->norouteAction();
            return;
        }

        $order->addStatusToHistory(
            $order->getStatus(),
            Mage::helper('Avenues')->__('Customer was redirected to Avenues')
        );
        $order->save();

        $this->getResponse()
            ->setBody($this->getLayout()
                ->createBlock('Avenues/standard_redirect')
                ->setOrder($order)
                ->toHtml());

        $session->unsQuoteId();
    }

	
public function successAction()
	{
		$response = $this->getRequest()->getPost();


	$WorkingKey = '80dok7geej2kvvd90b'; //put in the 32 bit working key in the quotes provided here
	$Merchant_Id= $_REQUEST['Merchant_Id'];
	$Amount= $_REQUEST['Amount'];
	$Order_Id= $_REQUEST['Order_Id'];
	$Merchant_Param= $_REQUEST['Merchant_Param'];
	$Checksum= $_REQUEST['Checksum'];
	$AuthDesc=$_REQUEST['AuthDesc'];
	
		$Avenues = Mage::getModel('Avenues/Avenues');

		$Checksum =verifyChecksum($Merchant_Id, $Order_Id,$Amount,$AuthDesc,$Checksum,$WorkingKey);


	if($Checksum=="true" && $AuthDesc=="Y")

	{
		
		$order = Mage::getModel('sales/order');
        $order->loadByIncrementId($Order_Id);
		$_mail = false;
		$f_passed_status=Mage::getStoreConfig('payment/Avenues/orderStatus');
		$order->setState($f_passed_status,$f_passed_status, Mage::helper('Avenues')->__('The payment is AUTHORIZED by Avenues.'),$_mail);
		$order->sendOrderUpdateEmail($_mail, Mage::helper('Avenues')->__('Your payment is authorized.')); 
	
		$session = Mage::getSingleton('checkout/session');
        $session->setQuoteId($session->getAvenuesStandardQuoteId(true));
       
        Mage::getSingleton('checkout/session')->getQuote()->setIsActive(false)->save();

        $this->_redirect('checkout/onepage/success', array('_secure'=>true));
    }
		
	
	else if($Checksum=="true" && $AuthDesc=="B")
	{
		echo "<br>Thank you for shopping with us.We will keep you posted regarding the status of your order through e-mail";
		
		//Here you need to put in the routines/e-mail for a  "Batch Processing" order
		//This is only if payment for this transaction has been made by an American Express Card
		//since American Express authorisation status is available only after 5-6 hours by mail from ccavenue and at the "View Pending Orders"
	}
	else if($Checksum=="true" && $AuthDesc=="N")

	{
		
		Mage::getSingleton('checkout/session')->addError("Thank you for shopping with us.However,the transaction has been declined");
		$this->_redirect('checkout/cart');
		
	
		//Here you need to put in the routines for a failed
		//transaction such as sending an email to customer
		//setting database status etc etc
	}
	else
	
	{
		Mage::getSingleton('checkout/session')->addError("Security Error. Illegal access detected");
		$this->_redirect('checkout/cart');
		
	
	}
	}
}