<?php

class Mage_Avenues_Block_Standard_Redirect extends Mage_Core_Block_Abstract
{

    protected function _toHtml()
    {
        $standard = Mage::getModel('Avenues/standard');
        $form = new Varien_Data_Form();
        $form->setAction($standard->getAvenuesUrl())
            ->setId('Avenues_standard_checkout')
            ->setName('Avenues_standard_checkout')
            ->setMethod('POST')
            ->setUseContainer(true);

        foreach ($standard->setOrder($this->getOrder())->getStandardCheckoutFormFields() as $field => $value) {
		
		if($field == 'return')
        	{
        		$returnurl=$value."?DR={DR}";
        	}

		
		if($field == 'product_price')
			{
				$amount=$value;
			}
		if($field == 'cs1')
			{
				$referenceno=$value;
			}
		if($field == 'f_name')
			{
				$fname=$value;
			}
		if($field == 's_name')
			{
				$lname=$value;
			}
	     if($field == 'product_name')
			{
			$desc=$value;
			}		 	
        if($field == 'zip')
			{
			$postalcode=$value;
			}
        if($field == 'street')
			{
			$street=$value;
			}
        if($field == 'street')
			{
			$street=$value;
			}
        if($field == 'city')
			{
			$city=$value;
			}	
        if($field == 'state')
			{
			$state=$value;
			}	
		   $form->addField($field, 'hidden', array('name' => $field, 'value' => $value));
        }

		$name=$fname." ".$lname;
		$address=$street.",".$city.",".$state;		
		$mode=Mage::getSingleton('Avenues/config')->getTransactionMode();
		if($mode == '1')
		{
		$mode="TEST";
		}
		else
		{
		$mode="LIVE";
	    }
		
        $form->addField('reference_no', 'hidden', array('name'=>'Order_Id', 'value'=>$referenceno));
        $form->addField('amount', 'hidden', array('name'=>'Amount', 'value'=>$amount));
        $form->addField('mode', 'hidden', array('name'=>'mode', 'value'=>$mode));
        $form->addField('return_url', 'hidden', array('name'=>'return_url', 'value'=>$returnurl));
        $form->addField('name', 'hidden', array('name'=>'name', 'value'=>$name));
        $form->addField('description', 'hidden', array('name'=>'description', 'value'=>$desc));
        $form->addField('address', 'hidden', array('name'=>'address', 'value'=>$address));
        $form->addField('postal_code', 'hidden', array('name'=>'postal_code', 'value'=>$postalcode));
      
        
		$html = '<html><body>';
        $html.= $this->__('You will be redirected to CCAVENUE PAYMENT GATEWAY  in a few seconds.');
        $html.= $form->toHtml();
        $html.= '<script type="text/javascript">document.getElementById("Avenues_standard_checkout").submit();</script>';
        $html.= '</body></html>';

        return $html;
    }
}