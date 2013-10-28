<?php
$installer = $this;
$installer->startSetup();


$installer->addAttribute("catalog_category", "category_gender",  array(
    "type"     => "int",
    "backend"  => "",
    "frontend" => "",
    "label"    => "Gender",
    "input"    => "select",
    "class"    => "",
    "source"   => "navigation/eav_entity_attribute_source_categoryoptions13814758120",
    "global"   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    "visible"  => true,
    "required" => false,
    "user_defined"  => false,
    "default" => "",
    "searchable" => false,
    "filterable" => false,
    "comparable" => false,
	
    "visible_on_front"  => false,
    "unique"     => false,
    "note"       => ""

	));

$installer->addAttribute("catalog_category", "newin",  array(
    "type"     => "int",
    "backend"  => "",
    "frontend" => "",
    "label"    => "New In",
    "input"    => "select",
    "class"    => "",
    "source"   => "navigation/eav_entity_attribute_source_categoryoptions13814758121",
    "global"   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    "visible"  => true,
    "required" => false,
    "user_defined"  => false,
    "default" => "",
    "searchable" => false,
    "filterable" => false,
    "comparable" => false,
	
    "visible_on_front"  => false,
    "unique"     => false,
    "note"       => ""

	));

$installer->addAttribute("catalog_category", "onelinedescription",  array(
    "type"     => "varchar",
    "backend"  => "",
    "frontend" => "",
    "label"    => "One Line Description",
    "input"    => "text",
    "class"    => "",
    "source"   => "",
    "global"   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    "visible"  => true,
    "required" => false,
    "user_defined"  => false,
    "default" => "",
    "searchable" => false,
    "filterable" => false,
    "comparable" => false,
	
    "visible_on_front"  => false,
    "unique"     => false,
    "note"       => ""

	));
$installer->endSetup();
	 