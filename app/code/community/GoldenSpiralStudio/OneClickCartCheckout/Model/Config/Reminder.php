<?php 

class GoldenSpiralStudio_OneClickCartCheckout_Model_Config_Reminder
{
    public function toOptionArray()
    {

    	
    	
    	try {
	
			$object = Mage::getModel("newsletter/template");
			$object->setData(array(
				"template_code"=>"Reminder Default",
				"template_type"=>2,
				"template_subject"=>"",
				"template_sender_name"=>"CustomerSupport",
				"template_sender_email"=>Mage::getStoreConfig("trans_email/ident_general/email"),
				"template_text"=>'<p>Dear Customer Name!</p>
							<p>Your shopping cart contains items, which you forget to buy.<br/>
							Please, follow this <a href="{{config path="web/secure/base_url"}}checkout/cart/">link</a> to go to your shopping cart, look which products was been forgotten and finish your order.
							</p>
							Best wishes,
							<br/>
							{{config path="general/store_information/name"}}',
			));
			$object->save();
    	
    	}catch(Exception $e)
    	{
    		
    	}
    	
    	
    	$options =array(     
    	   array('value'=>'0', 'label'=>Mage::helper('oneclickcartcheckout')->__('Disabled'))
    	);

    	$object = Mage::getModel("newsletter/template");
    	
    	foreach($object->getCollection() as $template)
    	{
    		//d($template->getData());
    		$options[] =  array('value'=>$template->getTemplateId(), 'label'=>$template->getTemplateCode());
    	}
    		
    	return $options;   
    }

} ?>