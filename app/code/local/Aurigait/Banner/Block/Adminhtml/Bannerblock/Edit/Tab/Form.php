<?php
class Aurigait_Banner_Block_Adminhtml_Bannerblock_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
		protected function _prepareForm()
		{

				$form = new Varien_Data_Form();
				$this->setForm($form);
				$fieldset = $form->addFieldset("banner_form", array("legend"=>Mage::helper("banner")->__("Item information")));

				
						$fieldset->addField("block_title", "text", array(
						"label" => Mage::helper("banner")->__("Title"),
						"name" => "block_title",
						));
					
						$fieldset->addField("view_more_text", "text", array(
						"label" => Mage::helper("banner")->__("View More Text"),
						"name" => "view_more_text",
						));
					
						$fieldset->addField("view_more_url", "text", array(
						"label" => Mage::helper("banner")->__("View More URL"),
						"name" => "view_more_url",
						));
					
						$fieldset->addField("block_position", "text", array(
						"label" => Mage::helper("banner")->__("Sort Order"),
						"name" => "block_position",
						));
						$fieldset->addField('gender', 'select', array(
				          'label'     => Mage::helper('banner')->__('Gender'),
				          'name'      => 'gender',
				          'values'    => array(
				              array(
				                  'value'     => 2,
				                  'label'     => Mage::helper('banner')->__('Men'),
				              ),
				
				              array(
				                  'value'     => 3,
				                  'label'     => Mage::helper('banner')->__('Women'),
				              ),
				              array(
				                  'value'     => 1,
				                  'label'     => Mage::helper('banner')->__('Both'),
				              )
				          ),
				      ));
					

				if (Mage::getSingleton("adminhtml/session")->getBannerblockData())
				{
					$form->setValues(Mage::getSingleton("adminhtml/session")->getBannerblockData());
					Mage::getSingleton("adminhtml/session")->setBannerblockData(null);
				} 
				elseif(Mage::registry("bannerblock_data")) {
				    $form->setValues(Mage::registry("bannerblock_data")->getData());
				}
				return parent::_prepareForm();
		}
}
