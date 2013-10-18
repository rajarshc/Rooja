<?php
class Magestore_Affiliateplus_Block_Adminhtml_Account_Serializer 
		extends Mage_Adminhtml_Block_Template
{
	public function __construct()
	{
		parent::__construct();
		$this->setTemplate('affiliateplus/account/serializer.phtml');
		return $this;
	}
	
	public function initSerializerBlock($gridName,$hiddenInputName)
	{
		$grid = $this->getLayout()->getBlock($gridName);
        $this->setGridBlock($grid)
                 ->setInputElementName($hiddenInputName);
	}
}