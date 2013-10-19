<?php

class Magestore_Affiliatepluslevel_Block_Adminhtml_Program_Sectier
    extends Magestore_Affiliatepluslevel_Block_Adminhtml_Program_Tier
{	
	public function getHtmlId(){
		return 'grid_sec_tier_commission';
	}
    
	public function getDefaultCommission(){
		$data = $this->getProgramData();
        if (isset($data['sec_commission']) && $data['sec_commission']) {
            return isset($data['secondary_commission']) ? $data['secondary_commission'] : 0;
        }
		return isset($data['commission']) ? $data['commission'] : 0;
	}
	
	public function getDefaultCommissionType(){
		$data = $this->getProgramData();
        if (isset($data['sec_commission']) && $data['sec_commission']) {
            return isset($data['sec_commission_type']) ? $data['sec_commission_type'] : 'percentage';
        }
		return isset($data['commission_type']) ? $data['commission_type'] : 'percentage';
	}
}
