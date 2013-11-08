<?php
class Aurigait_Banner_Block_Adminhtml_Bannerblock_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
		public function __construct()
		{
				parent::__construct();
				$this->setId("bannerblock_tabs");
				$this->setDestElementId("edit_form");
				$this->setTitle(Mage::helper("banner")->__("Item Information"));
		}
		protected function _beforeToHtml()
		{
				$this->addTab("form_section", array(
				"label" => Mage::helper("banner")->__("Item Information"),
				"title" => Mage::helper("banner")->__("Item Information"),
				"content" => $this->getLayout()->createBlock("banner/adminhtml_bannerblock_edit_tab_form")->toHtml(),
				));
				return parent::_beforeToHtml();
		}

}
