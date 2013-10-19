<?php

class Magestore_Affiliatepluslevel_Block_Adminhtml_System_Config_Form_Field_Sectier
    extends Magestore_Affiliatepluslevel_Block_Adminhtml_System_Config_Form_Field_Tier
{
	public function getHtmlId(){
		return 'affiliateplus_commission_sec_tier_commission';
	}
	
	public function getDefaultCommission(){
        if ($this->_getConfig('affiliateplus/commission/use_secondary')) {
            return $this->_getConfig('affiliateplus/commission/secondary_commission');
        }
		return $this->_getConfig('affiliateplus/commission/commission');
	}
	
	public function getDefaultCommissionType(){
        if ($this->_getConfig('affiliateplus/commission/use_secondary')) {
            return $this->_getConfig('affiliateplus/commission/secondary_type');
        }
		return $this->_getConfig('affiliateplus/commission/commission_type');
	}
}
