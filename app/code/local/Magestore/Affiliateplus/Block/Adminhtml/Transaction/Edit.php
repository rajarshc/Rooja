<?php

class Magestore_Affiliateplus_Block_Adminhtml_Transaction_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'affiliateplus';
        $this->_controller = 'adminhtml_transaction';
        
        $this->_removeButton('save');
        $this->_removeButton('delete');
		$this->_removeButton('reset');
        
        $transaction = Mage::registry('transaction_data');
        if ($transaction && $transaction->getId()) {
            if (!$transaction->canRestore()) {
                $account = Mage::getModel('affiliateplus/account')
                    ->setStoreId($transaction->getStoreId())
                    ->setBalanceIsGlobal((Mage::getStoreConfig('affiliateplus/account/balance', $transaction->getStoreId()) == 'global'))
                    ->load($transaction->getAccountId());
                $totalCommission = $transaction->getCommission() + $transaction->getCommissionPlus()
                    + $transaction->getCommission() * $transaction->getPercentPlus() / 100 ;
                Mage::dispatchEvent('affiliateplus_adminhtml_prepare_commission', array('transaction' => $transaction));
                if ($transaction->getRealTotalCommission()) {
                    $totalCommission = $transaction->getRealTotalCommission();
                }
                if ($account->getBalance() >= $totalCommission
                    || $transaction->getStatus() != '1' // Transaction Not Completed
                ) {
                    if ($transaction->getStatus() != '3') {
                        $this->_addButton('cancel', array(
                            'label'     => Mage::helper('affiliateplus')->__('Cancel'),
                            'onclick'   => 'deleteConfirm(\''
                                        . Mage::helper('affiliateplus')->__('This action cannot be restored. Are you sure?')
                                        . '\', \''
                                        . $this->getUrl('*/*/cancel', array('id' => $transaction->getId()))
                                        . '\')',
                            'class'     => ''
                        ), 0);
                    }
                    $transaction->setData('transaction_is_can_delete', true);
                }
            }
            // update form button
            Mage::dispatchEvent('affiliateplus_adminhtml_update_transaction_action', array(
                'transaction' => $transaction,
                'form'        => $this
            ));
        }
		
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('affiliateplus_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'affiliateplus_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'affiliateplus_content');
                }
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('transaction_data') && Mage::registry('transaction_data')->getId() ) {
            return Mage::helper('affiliateplus')->__("View Transaction of '%s'", $this->htmlEscape(Mage::registry('transaction_data')->getAccountName()));
        } else {
            return Mage::helper('affiliateplus')->__('View Transaction');
        }
    }
}
