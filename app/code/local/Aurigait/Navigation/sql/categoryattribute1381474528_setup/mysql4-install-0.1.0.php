<?php
$installer = $this;
$installer->startSetup();

$installer->removeAttribute('catalog_category','category_gender');
$installer->removeAttribute('catalog_category','newin');
$installer->removeAttribute('catalog_category','onelinedescription');
/*nstaller->addAttribute("catalog_category", "category_gender",  array(
    "type"     => "text",
    "backend"  => "",
    "frontend" => "",
    "label"    => "Gender",
    "input"    => "multiselect",
    "class"    => "",
    "source"   => "navigation/eav_entity_attribute_source_categoryoptions13814745280",
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

	));*/

/*$installer->addAttribute("catalog_category", "newin",  array(
    "type"     => "int",
    "backend"  => "",
    "frontend" => "",
    "label"    => "New In",
    "input"    => "select",
    "class"    => "",
    "source"   => "navigation/eav_entity_attribute_source_categoryoptions13814745281",
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

	));*/
$installer->endSetup();
	 
