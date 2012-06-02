<?php 

/** 
* Customer Attribute Setup for Mydons Customer
*/

$installer = $this; 

/* @var $installer Mydons_Customer_Model_Entity_Setup */

$installer->startSetup();

$installer->addAttribute('customer','mobile',array (

	'label'    => 'Mobile',
	'visible'  => 1,
	'required' => 0,
	'position' => 1,
	'sort_order' =>80,
)); 

$installer->endSetup();
$customerattribute= Mage::getModel('customer/attribute')->loadByCode('customer','mobile');
$forms=array('customer_account_edit','customer_account_create','adminhtml_customer','checkout_register');
$customerattribute->setData('used_in_forms',$forms);
$customerattribute->save();
?>