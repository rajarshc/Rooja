<?php
class Magestore_Affiliateplusstatistic_Block_Diagrams_Totals extends Mage_Adminhtml_Block_Dashboard_Bar
{
	protected function _construct(){
		parent::_construct();
		$this->setTemplate('affiliateplusstatistic/totalbar.phtml');
	}
	
	protected function _prepareLayout(){
		$storeId = $this->getRequest()->getParam('store');
		$collection = Mage::getResourceModel('affiliateplusstatistic/sales_collection')
			->prepareTotal($this->getRequest()->getParam('period','24h'),0,0,$storeId);
		if ($storeId) $collection->addFieldToFilter('store_id',$storeId);
		$totals = $collection->load()->getFirstItem();
		
		$this->addTotal($this->__('Sales Amount'),$totals->getTotalAmount(), false,
            $this->__('Total amount of Sales through Affiliate system')
        );
		$this->addTotal($this->__('Transactions'),$totals->getTotalTransaction(),true,
            $this->__('Total transactions (from sales, click, view or lead)')
        );
		$this->addTotal($this->__('Commission'),$totals->getTotalCommission(), false,
            $this->__('Total commissions (from sales, click, view or lead)')
        );
		
		$collection = Mage::getResourceModel('affiliateplusstatistic/statistic_collection')
			->prepareTotal($this->getRequest()->getParam('period','24h'),0,0,$storeId);
		if ($storeId) $collection->addFieldToFilter('store_id',$storeId);
		$totals = $collection->load()->getFirstItem();
		
        $totalUnique = $totals->getTotalUniques() ? $totals->getTotalUniques() : '0';
        $totalRaw = $totals->getTotalClicks() ? $totals->getTotalClicks() : '0';
		$this->addTotal(
			$this->__('Clicks (Unique / Raw)')
			,$totalUnique . ' / ' . $totalRaw
			,true
            , $this->__('Total clicks to Affiliate link')
		);
        
        $collection = Mage::getResourceModel('affiliateplusstatistic/statistic_collection')
			->prepareTotal($this->getRequest()->getParam('period','24h'),0,0,$storeId,1);
        if ($storeId) $collection->addFieldToFilter('store_id',$storeId);
		$totals = $collection->load()->getFirstItem();
        
        $totalUnique = $totals->getTotalUniques() ? $totals->getTotalUniques() : '0';
        $totalRaw = $totals->getTotalClicks() ? $totals->getTotalClicks() : '0';
        $this->addTotal(
			$this->__('Impressions (Unique / Raw)')
			,$totalUnique . ' / ' . $totalRaw
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
