<?php

class Magestore_Affiliatepluscoupon_Block_Affiliatepluscoupon extends Magestore_Affiliateplus_Block_Account_Program
{
	public function getAccount(){
		return Mage::registry('account_model');
	}
	
	public function getListProgram(){
        $listProgram = array();
        if (floatval($this->_getConfigHelper()->getDiscountConfig('discount')) > 0) {
            $listProgram['default'] = new Varien_Object(array(
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
                'coupon_code'       => $this->getAccount()->getCouponCode()
            ));
        }
        if ($this->isMultiProgram() && Mage::helper('core')->isModuleOutputEnabled('Magestore_Affiliateplusprogram')) {
            $collection = Mage::getResourceModel('affiliateplusprogram/program_collection')
                ->setStoreId(Mage::app()->getStore()->getId());
            $programCoupons = Mage::registry('program_coupon_codes');
            foreach ($collection as $item) {
                if ($item->getStatus() && isset($programCoupons[$item->getId()])) {
                    $item->setCouponCode($programCoupons[$item->getId()]);
                    $listProgram[$item->getId()] = $item;
                }
            }
        }
        return $listProgram;
	}
	
	public function isMultiProgram(){
		return Mage::helper('affiliatepluscoupon')->isMultiProgram();
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
