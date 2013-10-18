<?php
class Magestore_Affiliateplusstatistic_Block_Frontend_Diagrams_Totals extends Mage_Adminhtml_Block_Dashboard_Bar
{
	protected function _construct(){
		parent::_construct();
		$this->setTemplate('affiliateplusstatistic/dashboard/totalbar.phtml');
	}
    
     /**
     * get Helper
     *
     * @return Magestore_Affiliateplus_Helper_Config
     */
    public function _getHelper() {
        return Mage::helper('affiliateplus/config');
    }
	
	protected function _prepareLayout(){
		$storeId = Mage::app()->getStore()->getId();
        $account = Mage::getSingleton('affiliateplus/session')->getAccount();
		$collection = Mage::getResourceModel('affiliateplusstatistic/sales_collection')
			->prepareTotal($this->getRequest()->getParam('period','24h'),0,0,$storeId,$account->getId());
        if ($this->_getHelper()->getSharingConfig('balance') == 'store')
            if($storeId)
                $collection->addFieldToFilter('store_id',$storeId);
		//if ($storeId) $collection->addFieldToFilter('store_id',$storeId);
		$totals = $collection->load()->getFirstItem();;
		$this->addTotal($this->__('Sales Amount'),$totals->getTotalAmount(),false,
            $this->__('Total amount of Sales through Affiliate system')
        );
		$this->addTotal($this->__('Transactions'),$totals->getTotalTransaction(),true,
            $this->__('Total transactions (from sales, click, view or lead)')
        );
		$this->addTotal($this->__('Commission'),$totals->getTotalCommission(),false,
            $this->__('Total commissions (from sales, click, view or lead)')
        );
		
		$clickcollection = Mage::getResourceModel('affiliateplusstatistic/click_collection')
			->prepareTotal($this->getRequest()->getParam('period','24h'),0,0,$storeId);
        $impressioncollection = Mage::getResourceModel('affiliateplusstatistic/impression_collection')
			->prepareTotal($this->getRequest()->getParam('period','24h'),0,0,$storeId);
		//if ($storeId) $collection->addFieldToFilter('store_id',$storeId);
		$clicktotals = $clickcollection->getFirstItem();
        $uniqueClicks = $clicktotals->getUniques() ? $clicktotals->getUniques():0;
        $rawClicks = $clicktotals->getRaws() ? $clicktotals->getRaws():0;
		$this->addTotal(
			$this->__('Unique Clicks / Total Clicks')
			, $uniqueClicks.' / ' . $rawClicks 
			,true
            , $this->__('Total clicks to Affiliate link')
		);
        $impressiontotals = $impressioncollection->getFirstItem();
        $uniqueImpressions = $impressiontotals->getUniques() ? $impressiontotals->getUniques():0;
        $rawImpressions = $impressiontotals->getRaws() ? $impressiontotals->getRaws():0;
        $this->addTotal(
			$this->__('Unique Impressions / Total Impressions'),
			$uniqueImpressions . ' / ' . $rawImpressions
			,true
            , $this->__('Total banner views on Affiliate sites')
		);
	}
    
    public function addTotal($label, $value, $isQuantity=false, $title = '')
    {
        if (!$isQuantity) {
            $value = $this->format($value);
        }
        $decimals = '';
        $this->_totals[] = array(
            'label' => $label,
            'value' => $value,
            'decimals' => $decimals,
            'title' => $title,
        );
        return $this;
    }
}
