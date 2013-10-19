<?php
class Magestore_Affiliateplus_Block_Account_Program extends Mage_Core_Block_Template
{
	/**
	 * get Configuration helper
	 *
	 * @return Magestore_Affiliateplus_Helper_Config
	 */
	protected function _getConfigHelper(){
		return Mage::helper('affiliateplus/config');
	}
	
	public function getMinPaymentRelease(){
		return $this->_getConfigHelper()->getPaymentConfig('payment_release');
	}
	
	public function getListProgram(){
		$programList = array();
		
		if ($this->_getConfigHelper()->getCommissionConfig('commission')
			|| $this->_getConfigHelper()->getDiscountConfig('discount')){
			$defaultProgram = new Varien_Object(array(
				'name'				=> $this->__('Affiliate Program'),
				'commission_type'	=> $this->_getConfigHelper()->getCommissionConfig('commission_type'),
				'commission'		=> $this->_getConfigHelper()->getCommissionConfig('commission'),
                'sec_commission'    => $this->_getConfigHelper()->getCommissionConfig('use_secondary'),
                'sec_commission_type'   => $this->_getConfigHelper()->getCommissionConfig('secondary_type'),
                'secondary_commission'  => $this->_getConfigHelper()->getCommissionConfig('secondary_commission'),
				'discount_type'		=> $this->_getConfigHelper()->getDiscountConfig('discount_type'),
				'discount'			=> $this->_getConfigHelper()->getDiscountConfig('discount'),
                'sec_discount'      => $this->_getConfigHelper()->getDiscountConfig('use_secondary'),
                'sec_discount_type' => $this->_getConfigHelper()->getDiscountConfig('secondary_type'),
                'secondary_discount'=> $this->_getConfigHelper()->getDiscountConfig('secondary_discount'),
			));
			Mage::dispatchEvent('affiliateplus_prepare_program',array('info' => $defaultProgram));
			$programList['default'] = $defaultProgram;
		}
		
		$programListObj = new Varien_Object(array(
			'program_list'	=> $programList,
		));
		Mage::dispatchEvent('affiliateplus_get_list_program_welcome',array(
			'program_list_object'	=> $programListObj,
		));
		return $programListObj->getProgramList();
	}
    
    public function hasSecondaryCommission($program) {
        return ($program->getData('sec_commission')
            && ($program->getData('sec_commission_type') != $program->getData('commission_type')
                || $program->getData('secondary_commission') != $program->getData('commission')
        ));
    }
    
    public function hasSecondaryDiscount($program) {
        return ($program->getData('sec_discount')
            && ($program->getData('sec_discount_type') != $program->getData('discount_type')
                || $program->getData('secondary_discount') != $program->getData('discount')
        ));
    }
}
