<?php

class Magestore_Affiliateplus_Block_Email_Report extends Mage_Core_Block_Template
{
	public function prepareStatistic($statistic){
		$sales = 0;
		$transaction = 0;
		$commission = 0;
		foreach ($statistic as $sta){
			$sales += isset($sta['sales']) ? $sta['sales'] : 0;
			$transaction += isset($sta['transactions']) ? $sta['transactions'] : 0;
			$commission += isset($sta['transactions']) ? $sta['commissions'] : 0;
		}
		return array('sales' => $sales, 'transaction' => $transaction, 'commission' => $commission);
	}
	
	public function getOptionLabels(){
		return array(
			'complete'	=> $this->__('Completed'),
			'pending'	=> $this->__('Pending'),
			'cancel'	=> $this->__('Canceled'),
		);
	}
}