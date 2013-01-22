<?php
class Mycustom_Fileupload_ZipcodemailController extends Mage_Core_Controller_Front_Action
{
   /* public function indexAction()
    {
		
   $this->loadLayout();     
   $this->renderLayout();
    }
	*/
	public function zipcodemailsendAction ()
	{
		$postData = Mage::app()->getRequest()->getParam();
		/*
		$store_id = Mage::app()->getStore()->getId();
		$template = "import_script_email_template_name";
		
		//$from =  Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $store_id);
		$from =  'Web Master';
		$to = array( "name" => "Senthil", "email" => "rsenthilbsc@gmail.com" );
		
		$template_variables = array(
			"Name" => $postData["uname"],
			"Email" => $postData["email"],
			"Mobile" => $postData["mobile"],
			"Pincode" => $postData["pincode"]
		);
		
		
		$mail = Mage::getModel("core/email_template");
		$mail->setDesignConfig( array( "area" => "frontend", "store" => $store_id ))
			 ->sendTransactional(
				 $template_name,
				 $from,
				 $to["email"],
				 $to["name"],
				 $template_variables
		);
		try {
			$mail->send();
			echo "Mail Sent";
		} catch (Exception $e) {
			echo "Mail not Sent";
		}
		*/
		$zipcodemailmessage = '<p>Hi,</p>';
		$zipcodemailmessage .= '<p>An user sent request for new zip code. Please find the details below.</p>';
		$zipcodemailmessage .= '<p>Name : '.Mage::app()->getRequest()->getParam("uname").'<br />';
		$zipcodemailmessage .= 'Email : '.Mage::app()->getRequest()->getParam("email").'<br />';
		$zipcodemailmessage .= 'Mobile : '.Mage::app()->getRequest()->getParam("mobile").'<br />';
		$zipcodemailmessage .= 'Pincode : '.Mage::app()->getRequest()->getParam("pincode").'</p>';
		$zipcodemailmessage .= '<p>Best Regards,<br />';
		$zipcodemailmessage .= 'Web Master<br />';
		$zipcodemailmessage .= 'Rooja.com</p>';


		$mail = Mage::getModel('core/email');
		$mail->setToName('Rooja Admin');
		//$mail->setToEmail('support@rooja.com');
		$mail->setToEmail('vispl.dev@gmail.com');
		//$mail->setToEmail('velansoftwaretest@gmail.com');
		$mail->setBody($zipcodemailmessage);
		$mail->setSubject('New Zipcode');
		$mail->setFromEmail('no-reply@rooja.com');
		$mail->setFromName("Web Master");
		$mail->setType('html');// YOu can use Html or text as Mail format
		
		try {
			$mail->send();
			echo "true";
		}
		catch (Exception $e) {
			echo "false";
		}
		
	}
} 
?>