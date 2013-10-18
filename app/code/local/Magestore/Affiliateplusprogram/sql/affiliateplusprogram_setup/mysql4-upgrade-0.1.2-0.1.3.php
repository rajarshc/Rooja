<?php

$installer = $this;

$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('affiliateplusprogram'), 'valid_from', 'date NULL');
$installer->getConnection()->addColumn($installer->getTable('affiliateplusprogram'), 'valid_to', 'date NULL');
$installer->getConnection()->addColumn($installer->getTable('affiliateplusprogram'), 'use_coupon', 'tinyint(1) NOT NULL default 0');
$installer->getConnection()->addColumn($installer->getTable('affiliateplusprogram'), 'coupon_pattern', 'varchar(15) NULL');
$installer->getConnection()->addColumn($installer->getTable('affiliateplusprogram'), 'affiliate_type', 'varchar(15) NULL');
$installer->getConnection()->addColumn($installer->getTable('affiliateplusprogram'), 'description', 'text NULL');
$installer->getConnection()->addColumn($installer->getTable('affiliateplusprogram'), 'conditions_serialized', 'mediumtext NULL');
$installer->getConnection()->addColumn($installer->getTable('affiliateplusprogram'), 'actions_serialized', 'mediumtext NULL');
$installer->getConnection()->addColumn($installer->getTable('affiliateplusprogram'), 'is_process', 'tinyint(1) NOT NULL default 0');


$programs = Mage::getResourceModel('affiliateplusprogram/program_collection');
$actionTpl = array(
	'type'	=> 'salesrule/rule_condition_product_combine',
	'attribute'	=> null,
	'operator'	=> null,
	'value'	=> '1',
	'is_value_processed'	=> null,
	'aggregator'	=> 'any',
);
/*$categoryTpl = array(
	'type'	=> 'salesrule/rule_condition_product',
	'attribute'	=> 'category_ids',
	'operator'	=> '()',
	'is_value_processed'	=> false,
	'value'	=> '',
);*/
$productTpl = array(
	'type'	=> 'salesrule/rule_condition_product',
	'attribute'	=> 'sku',
	'operator'	=> '()',
	'is_value_processed'	=> false,
	'value'	=> '',
);
foreach ($programs as $program){
	$conditions = array();
/*	$categories = Mage::getResourceModel('affiliateplusprogram/category_collection')
		->addFieldToFilter('program_id',$program->getId());
	$categories->getSelect()->group('category_id');
	if ($categories->getSize()){
		$categoryIds = array();
		foreach ($categories as $category)
			$categoryIds[] = $category->getCategoryId();
		$categoryTpl['value'] = implode(', ',$categoryIds);
		$conditions[] = $categoryTpl;
	}
	*/
	$products = Mage::getResourceModel('affiliateplusprogram/product_collection')
		->addFieldToFilter('main_table.program_id',$program->getId());
	$products->getSelect()
		->joinLeft(
			array('e' => $products->getTable('catalog/product')),
			'main_table.product_id = e.entity_id',
			array('sku')
		)->group('main_table.product_id');
	if ($products->getSize()){
		$productSkus = array();
		foreach ($products as $product)
			$productSkus[] = $product->getSku();
		$productTpl['value'] = implode(', ',$productSkus);
		$conditions[] = $productTpl;
	}
	$actions = $actionTpl;
	if ($conditions) $actions['conditions'] = $conditions;
	try {
		$program->getActions()->loadArray($actions);
		$program->save();
	} catch (Exception $e){}
}

$installer->endSetup();
