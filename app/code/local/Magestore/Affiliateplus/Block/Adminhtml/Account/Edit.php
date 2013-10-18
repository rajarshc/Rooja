<?php

class Magestore_Affiliateplus_Block_Adminhtml_Account_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        
        $this->_objectId = 'id';
        $this->_blockGroup = 'affiliateplus';
        $this->_controller = 'adminhtml_account';
		
        $this->_updateButton('save', 'label', Mage::helper('affiliateplus')->__('Save Account'));
        $this->_updateButton('delete', 'label', Mage::helper('affiliateplus')->__('Delete Account'));
		
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);
		
		$alert = '';
		$paymentUrl = '';

		$data = Mage::registry('account_data');
		$storeId = $this->getRequest()->getParam('store');
		if($data && $data['status'] == 1){
			// $methodPaypalPayment = Mage::getStoreConfig('affiliateplus/payment/payment_method');
			$accountId = $this->getRequest()->getParam('id');
			
			// $waitingPayment = Mage::getModel('affiliateplus/payment')->getCollection()
				// ->addFieldToFilter('account_id', $accountId)
				// ->addFieldToFilter('status', 1) //waiting
				// ->getFirstItem(); 
			
			// if($methodPaypalPayment != 'api' || count(Mage::helper('affiliateplus/payment')->getAvailablePaymentCode()) != 1){
				// if($waitingPayment->getId()){
					// $paymentUrl = $this->getUrl('affiliateplusadmin/adminhtml_payment/edit', array('id' => $waitingPayment->getId(), 'store' => $storeId));
					// Mage::getSingleton('core/session')->addNotice(Mage::helper('affiliateplus')->__('This account has one payment waiting need to be processed.'));	
				// }else
					// $paymentUrl = $this->getUrl('affiliateplusadmin/adminhtml_payment/new', array('account_id' => $accountId, 'store' => $storeId));
				
				// $label = Mage::helper('affiliateplus')->__('Add payout');
				
			// }
			// else{
				// if($waitingPayment->getId()){
					// $paymentUrl = $this->getUrl('affiliateplusadmin/adminhtml_payment/edit', array('id' => $waitingPayment->getId(), 'store' => $storeId));
					// Mage::getSingleton('core/session')->addNotice(Mage::helper('affiliateplus')->__('This account has one payment waiting need to be processed.'));	
				// }else
					// $paymentUrl = $this->getUrl('affiliateplusadmin/adminhtml_payment/new', array('account_id' => $accountId, 'store' => $storeId, 'method' => 'api'));
					
				// $label = Mage::helper('affiliateplus')->__('Payout');
				
			// }
	
			$paymentRelease = Mage::getStoreConfig('affiliateplus/payment/payment_release', $storeId);
			// $isBalanceIsGlobal = Mage::helper('affiliateplus')->isBalanceIsGlobal();
			//$alert = Mage::helper('affiliateplus')->__('Are you sure of paying out for this account?');
            $paymentUrl = $this->getUrl('affiliateplusadmin/adminhtml_payment/new', array(
                'account_id'    => $accountId,
                'store'         => $storeId,
            ));
			if($data['balance'] > 0 && $data['balance'] >= $paymentRelease){
				$this->_addButton('payment', array(
					'label'     => Mage::helper('affiliateplus')->__('Payout'),// $label,
					'onclick'	=> "location.href = '$paymentUrl'",
				), -200);
			}
		}
		
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('affiliateplus_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'affiliateplus_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'affiliateplus_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
			
			function payment(isAddPayout){
				if(isAddPayout){
					location.href = '$paymentUrl';
				}else{
					var answer = confirm('$alert');
					if (answer){
						location.href = '$paymentUrl';
					}
				}
			}
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('account_data') && Mage::registry('account_data')->getId() ) {
            return Mage::helper('affiliateplus')->__("Edit Account '%s'", $this->htmlEscape(Mage::registry('account_data')->getName()));
        } else {
            return Mage::helper('affiliateplus')->__('Add Account');
        }
    }
}