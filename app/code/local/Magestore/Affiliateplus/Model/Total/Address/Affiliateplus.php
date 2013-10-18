<?php

class Magestore_Affiliateplus_Model_Total_Address_Affiliateplus extends Mage_Sales_Model_Quote_Address_Total_Abstract
{
	public function __construct(){
		$this->setCode('affiliateplus');
	}
	
	/**
	 * get Config Helper
	 *
	 * @return Magestore_Affiliateplus_Helper_Config
	 */
	protected function _getConfigHelper(){
		return Mage::helper('affiliateplus/config');
	}
	
	public function collect(Mage_Sales_Model_Quote_Address $address){
		Mage::helper('affiliateplus/cookie')->getNumberOrdered();
        if ($this->_getConfigHelper()->getDiscountConfig('type_discount') == 'product') {
            return $this;
        }
        if ($this->_getConfigHelper()->getDiscountConfig('allow_discount') == 'system') {
            $appliedRuleIds = array();
            if (is_string($address->getAppliedRuleIds())) {
                $appliedRuleIds = explode(',', $address->getAppliedRuleIds());
                $appliedRuleIds = array_filter($appliedRuleIds);
            }
            if (count($appliedRuleIds)) {
                return $this;
            }
        }
		$items = $address->getAllItems();
		if (!count($items)) return $this;
		
		$affiliateInfo = Mage::helper('affiliateplus/cookie')->getAffiliateInfo();
		$baseDiscount = 0;
		$discountObj = new Varien_Object(array(
			'affiliate_info'	=> $affiliateInfo,
			'base_discount'		=> $baseDiscount,
			'default_discount'	=> true,
			'discounted_items'	=> array(),
		));
		Mage::dispatchEvent('affiliateplus_address_collect_total',array(
			'address'		=> $address,
			'discount_obj'	=> $discountObj,
		));
		
		$baseDiscount = $discountObj->getBaseDiscount();
		if ($discountObj->getDefaultDiscount()){
			$account = '';
			foreach ($affiliateInfo as $info)
				if (isset($info['account']) && $info['account'])
					$account = $info['account'];
			if ($account && $account->getId()){
                $discountType  = $this->_getConfigHelper()->getDiscountConfig('discount_type');
				$discountValue = floatval($this->_getConfigHelper()->getDiscountConfig('discount'));
                if (Mage::helper('affiliateplus/cookie')->getNumberOrdered()) {
                    if ($this->_getConfigHelper()->getDiscountConfig('use_secondary')) {
                        $discountType  = $this->_getConfigHelper()->getDiscountConfig('secondary_type');
                        $discountValue = floatval($this->_getConfigHelper()->getDiscountConfig('secondary_discount'));
                    }
                }
				$discountedItems = $discountObj->getDiscountedItems();
                if ($discountValue <= 0) {
                    // do nothing when no discount
                } elseif ($discountType == 'cart_fixed'){
                    $baseItemsPrice = 0;
                    foreach ($items as $item) {
                        if ($item->getParentItemId()) {
                            continue;
                        }
                        if (in_array($item->getId(),$discountedItems)) {
                            continue;
                        }
                        if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                            foreach ($item->getChildren() as $child) {
                                $baseItemsPrice += $item->getQty() * ($child->getQty() * $child->getBasePrice() - $child->getBaseDiscountAmount());
                            }
                        } elseif ($item->getProduct()) {
                            $baseItemsPrice += $item->getQty() * $item->getBasePrice() - $item->getBaseDiscountAmount();
                        }
                    }
                    if ($baseItemsPrice) {
                        $totalBaseDiscount = min($discountValue, $baseItemsPrice);
                        foreach ($items as $item) {
                            if ($item->getParentItemId()) {
                                continue;
                            }
                            if (in_array($item->getId(),$discountedItems)) {
                                continue;
                            }
                            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                                foreach ($item->getChildren() as $child) {
                                    $price = $item->getQty() * ($child->getQty() * $child->getBasePrice() - $child->getBaseDiscountAmount());
                                    $childBaseDiscount = $totalBaseDiscount * $price / $baseItemsPrice;
                                    $child->setBaseAffiliateplusAmount($childBaseDiscount)
                                        ->setAffiliateplusAmount(Mage::app()->getStore()->convertPrice($childBaseDiscount));
                                }
                            } elseif ($item->getProduct()) {
                                $price = $item->getQty() * $item->getBasePrice() - $item->getBaseDiscountAmount();
                                $itemBaseDiscount = $totalBaseDiscount * $price / $baseItemsPrice;
                                $item->setBaseAffiliateplusAmount($itemBaseDiscount)
                                    ->setAffiliateplusAmount(Mage::app()->getStore()->convertPrice($itemBaseDiscount));
                            }
                        }
                        $baseDiscount += $totalBaseDiscount;
                    }
                } elseif ($discountType == 'fixed'){
					foreach ($items as $item){
                        if ($item->getParentItemId()) {
                            continue;
                        }
						if (in_array($item->getId(),$discountedItems)) {
                            continue;
                        }
						$itemBaseDiscount = 0;
                        
                        if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                            foreach ($item->getChildren() as $child) {
                                $childBaseDiscount = $item->getQty() * $child->getQty() * $discountValue;
                                $price = $item->getQty() * ( $child->getQty() * $child->getBasePrice() - $child->getBaseDiscountAmount() );
                                $childBaseDiscount = ($childBaseDiscount < $price) ? $childBaseDiscount : $price;
                                $itemBaseDiscount += $childBaseDiscount;
                                $child->setBaseAffiliateplusAmount($childBaseDiscount)
                                    ->setAffiliateplusAmount(Mage::app()->getStore()->convertPrice($childBaseDiscount));
                            }
                        } elseif($item->getProduct()){
							$itemBaseDiscount = $item->getQty() * $discountValue;
							$price = $item->getQty() * $item->getBasePrice() - $item->getBaseDiscountAmount();
							$itemBaseDiscount = ($itemBaseDiscount < $price) ? $itemBaseDiscount : $price;
                            $item->setBaseAffiliateplusAmount($itemBaseDiscount)
                                ->setAffiliateplusAmount(Mage::app()->getStore()->convertPrice($itemBaseDiscount));
                        }
						$baseDiscount += $itemBaseDiscount;
					}
				}elseif ($discountType == 'percentage'){
					if ($discountValue > 100) $discountValue = 100;
					if ($discountValue < 0) $discountValue = 0;
					foreach ($items as $item){
                        if ($item->getParentItemId()) {
                            continue;
                        }
						if (in_array($item->getId(),$discountedItems)) {
                            continue;
                        }
                        if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                            foreach ($item->getChildren() as $child) {
                                $price = $item->getQty() * ( $child->getQty() * $child->getBasePrice() - $child->getBaseDiscountAmount() );
                                $childBaseDiscount = $price * $discountValue / 100;
                                $itemBaseDiscount += $childBaseDiscount;
                                $child->setBaseAffiliateplusAmount($childBaseDiscount)
                                    ->setAffiliateplusAmount(Mage::app()->getStore()->convertPrice($childBaseDiscount));
                            }
                        } elseif ($item->getProduct()) {
							$price = $item->getQty() * $item->getBasePrice() - $item->getBaseDiscountAmount();
							$itemBaseDiscount = $price * $discountValue / 100;
							$item->setBaseAffiliateplusAmount($itemBaseDiscount)
                                ->setAffiliateplusAmount(Mage::app()->getStore()->convertPrice($itemBaseDiscount));
						}
                        $baseDiscount += $itemBaseDiscount;
					}
				}
			}
		}
		
		// if ($baseDiscount > $address->getBaseGrandTotal())
        //     $baseDiscount = $address->getBaseGrandTotal();
		
		if ($baseDiscount) {
			$discount = Mage::app()->getStore()->convertPrice($baseDiscount);
			$address->setBaseAffiliateplusDiscount(-$baseDiscount);
			$address->setAffiliateplusDiscount(-$discount);
            
            $session = Mage::getSingleton('checkout/session');
            if ($session->getData('affiliate_coupon_code')) {
                $address->setAffiliateplusCoupon($session->getData('affiliate_coupon_code'));
            }
			
			$address->setBaseGrandTotal($address->getBaseGrandTotal() - $baseDiscount);
			$address->setGrandTotal($address->getGrandTotal() - $discount);
		}
		
		return $this;
	}
	
	public function fetch(Mage_Sales_Model_Quote_Address $address){
		$amount = $address->getAffiliateplusDiscount();
		$title = $this->_getConfigHelper()->__('Affiliate Discount');
		if ($amount != 0){
            if ($address->getAffiliateplusCoupon()) {
                $title .= ' ('.$address->getAffiliateplusCoupon().')';
            }
			$address->addTotal(array(
				'code'	=> $this->getCode(),
				'title'	=> $title,
				'value'	=> $amount,
			));
		}
		return $this;
	}
}
