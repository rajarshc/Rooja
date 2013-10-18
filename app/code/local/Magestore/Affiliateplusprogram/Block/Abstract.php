<?php
class Magestore_Affiliateplusprogram_Block_Abstract extends Mage_Core_Block_Template
{
	/**
	 * get Account helper
	 *
	 * @return Magestore_Affiliateplus_Helper_Account
	 */
	protected function _getAccountHelper(){
		return Mage::helper('affiliateplus/account');
	}
	
	public function getNoNumber($row){
    	return sprintf('#%d',$row->getId());
    }
    
    public function getProgramName($row){
    	return sprintf('<a href="%s" title="%s">%s</a>'
    		,$this->getUrl('affiliateplusprogram/index/detail',array('id' => $row->getId()))
    		,$this->__('View Program Product List')
    		,$row->getProgramName()
    	);
    }
    
    public function getProgramDetails($row){
        $html = '';
        // Program Discount
        $discount = floatval($row->getDiscount());
        $secDiscount = floatval($row->getSecondaryDiscount());
        if ($row->getDiscountType() == 'fixed') {
            $discountText = Mage::helper('core')->currency($discount);
        } else if ($row->getDiscountType() == 'percentage') {
            $discountText = rtrim(rtrim(sprintf("%.2f",$discount),'0'),'.').'%';
        } else {
            $discountText = Mage::helper('core')->currency($discount);
            $discountText .= ' ' . $this->__('for whole cart');
        }

        if ($row->getSecDiscountType() == 'fixed') {
            $secText = Mage::helper('core')->currency($secDiscount);
        } else if ($row->getSecDiscountType() == 'percentage') {
            $secText = rtrim(rtrim(sprintf("%.2f",$secDiscount),'0'),'.').'%';
        } else {
            $secText = Mage::helper('core')->currency($secDiscount);
            $secText .= ' ' . $this->__('for whole cart');
        }
        
        if (!$this->hasSecondaryDiscount($row)) {
            $html .= $this->__('Discount: ').'<strong>'.$discountText.'</strong><br />';
        } else {
            $html .= $this->__('First Order Discount: ').'<strong>'.$discountText.'</strong><br />';
            $html .= $this->__('Discount: ').'<strong>'.$secText.'</strong><br />';
        }
        // Program Commission
        $commission = floatval($row->getCommission());
        $secCommission = floatval($row->getSecondaryCommission());
        if ($row->getCommissionType() == 'fixed') {
            $commissionText = Mage::helper('core')->currency($commission);
        } else {
            $commissionText = rtrim(rtrim(sprintf("%.2f",$commission),'0'),'.').'%';
        }
        if ($row->getSecCommissionType() == 'fixed') {
            $secText = Mage::helper('core')->currency($secCommission);
        } else {
            $secText = rtrim(rtrim(sprintf("%.2f",$secCommission),'0'),'.').'%';
        }
        $typeIsProfit = Mage::helper('affiliateplus')->affiliateTypeIsProfit();
        if ($row->getAffiliateType()) {
            $typeIsProfit = (bool)($row->getAffiliateType() == 'profit');
        }
        if ($typeIsProfit) {
            $label = $this->__('Pay-per-profit');
        } else {
            $label = $this->__('Pay-per-sales');
        }
        if (!$this->hasSecondaryCommission($row)) {
            $html .= $label . ': <strong>'.$commissionText.'</strong>';
        } else {
            $html .= $label . ' ('.$this->__('first order').')' . ': <strong>'.$commissionText.'</strong><br />';
            $html .= $label . ': <strong>'.$secText.'</strong>';
        }
        
		/** edited by blanka 18-10-2012 **/
    	$obj = new Varien_Object(array('html_view'=>$html));
    	Mage::dispatchEvent('affiliateplus_prepare_program',array('info' => $row,'obj'=>$obj));
		$html = $obj->getHtmlView();
		/** end edit by blanka**/
    	if ($row->getLevelCount()){
    		$popHtml  = '<table class="data-table"><tr>';
            
            if ($row->getSecLevelCount())
                $popHtml .= '<td rowspan="' . ($row->getLevelCount() + 1) .'">'.$this->__('for the first order of a customer').'</td>';
            
            $popHtml .= '<td><strong>'.$this->__('Level %d',1).'</strong></td><td>';
    		if ($row->getCommissionType() == 'fixed')
    			$popHtml .= $this->__('%s per sale',$commissionText);
    		else
    			$popHtml .= $this->__('%s of sales amount',$commissionText);
    		$popHtml .= '</td></tr>';
    		foreach($row->getTierCommission() as $tierCommission){
    			$popHtml .= '<tr><td><strong>'.$tierCommission['level'].'</strong></td><td>';
    			$popHtml .= $tierCommission['commission'].'</td></tr>';
    		}
            if ($row->getSecLevelCount()) {
                $popHtml .= '<td rowspan="' . ($row->getSecLevelCount() + 1) .'">'.$this->__('for next orders').'</td>';
                $popHtml .= '<td><strong>'.$this->__('Level %d',1).'</strong></td><td>';
                if ($this->hasSecondaryCommission($row))
                    $commissionText = $secText;
                if ($row->getSecCommissionType() == 'fixed')
                    $popHtml .= $this->__('%s per sale',$commissionText);
                else
                    $popHtml .= $this->__('%s of sales amount',$commissionText);
                $popHtml .= '</td></tr>';
                foreach($row->getSecTierCommission() as $tierCommission){
                    $popHtml .= '<tr><td><strong>'.$tierCommission['level'].'</strong></td><td>';
                    $popHtml .= $tierCommission['commission'].'</td></tr>';
                }
            }
			$popHtml .= '</table>';
			
			$html .= '<script type="text/javascript">var popHtml'.$row->getId().'= \''.$this->jsQuoteEscape($popHtml).'\';</script>';
    		$html .= '<br /><span class="affiliateplus-anchor" title="'.$this->__('View tier level commission amounts').'" onclick="TINY.box.show(popHtml'.$row->getId().',0,0,0,0);return false;">'.$this->__('View Tier Commission').'</span>';
    	}
    	
    	if ($row->getValidFrom())
			$html .= '<br />'.$this->__('From: ').'<strong>'.$this->formatDate($row->getValidFrom(),'medium',false).'</strong>';
		if ($row->getValidTo())
			$html .= '<br />'.$this->__('To: ').'<strong>'.$this->formatDate($row->getValidTo(),'medium',false).'</strong>';
    	
    	return $html;
    }
	
	public function getPagerHtml(){
    	return $this->getChildHtml('programs_pager');
    }
    
    public function getGridHtml(){
    	return $this->getChildHtml('programs_grid');
    }
    
    protected function _toHtml(){
    	$this->getChild('programs_grid')->setCollection($this->getCollection());
    	return parent::_toHtml();
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
