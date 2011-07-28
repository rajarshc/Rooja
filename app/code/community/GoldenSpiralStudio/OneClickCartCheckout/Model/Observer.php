<?php 
class GoldenSpiralStudio_OneClickCartCheckout_Model_Observer
{
 
    public function sendReminderEmail()
    {
       	
    	try {
	    	
    		if (Mage::getStoreConfig('checkout/oneclickcartcheckout/reminder')>0)
	    	{
	    		$model = Mage::getModel("sales/quote");
	    		$quotes = $model->getCollection()->addFieldToFilter("is_active",1)
	    		->addFieldToFilter('customer_email',array('notnull'=>"ee"))
	    		->addFieldToFilter('checkout_method',array('notnull'=>"customer_id"));
	
	    		$reminderTemplate = Mage::getModel("newsletter/template")->load(Mage::getStoreConfig('checkout/oneclickcartcheckout/reminder'));
	    		
	    		$sender = Mage::getModel('core/email_template');
	       		 $sender->setSenderName($reminderTemplate->getTemplateSenderName())
	            ->setSenderEmail($reminderTemplate->getTemplateSenderEmail())
	            ->setTemplateType(2)
	            ->setTemplateSubject($reminderTemplate->getTemplateSubject())
	            ->setTemplateText($reminderTemplate->getTemplateText())
	            ->setTemplateStyles($reminderTemplate->getTemplateStyles())
	            ->setTemplateFilter(Mage::helper('newsletter')->getTemplateProcessor());
	
	    		foreach ($quotes as $quote)
		    	{
		    		$email = $quote->getCustomerEmail();
		    		$success =   $sender->send($email,"Customer Name");
		    		
		    	}
		     
	    	}
    	
    	}catch (Exception $e)
    	{
    		
    	}
    }
 
}

?>