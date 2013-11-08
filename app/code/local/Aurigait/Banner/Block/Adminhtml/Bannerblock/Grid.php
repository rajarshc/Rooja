<?php

class Aurigait_Banner_Block_Adminhtml_Bannerblock_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

		public function __construct()
		{
				parent::__construct();
				$this->setId("bannerblockGrid");
				$this->setDefaultSort("id");
				$this->setDefaultDir("ASC");
				$this->setSaveParametersInSession(true);
		}

		protected function _prepareCollection()
		{
				$collection = Mage::getModel("banner/bannerblock")->getCollection();
				$this->setCollection($collection);
				return parent::_prepareCollection();
		}
		protected function _prepareColumns()
		{
				$this->addColumn("id", array(
				"header" => Mage::helper("banner")->__("ID"),
				"align" =>"right",
				"width" => "50px",
			    "type" => "number",
				"index" => "id",
				));
                
				$this->addColumn("block_title", array(
				"header" => Mage::helper("banner")->__("Title"),
				"index" => "block_title",
				));
				$this->addColumn("view_more_text", array(
				"header" => Mage::helper("banner")->__("View More Text"),
				"index" => "view_more_text",
				));
				$this->addColumn("view_more_url", array(
				"header" => Mage::helper("banner")->__("View More URL"),
				"index" => "view_more_url",
				));
				$this->addColumn("block_position", array(
				"header" => Mage::helper("banner")->__("Sort Order"),
				"index" => "block_position",
				));
				$this->addColumn('gender', array(
		          'header'    => Mage::helper('banner')->__('Gender'),
		          'align'     => 'left',
		          'width'     => '80px',
		          'index'     => 'gender',
		          'type'      => 'options',
		          'options'   => array(
		              2 => 'Men',
		              3 => 'Women',
		              1 => 'both'
		          ),
		       ));

				return parent::_prepareColumns();
		}

		public function getRowUrl($row)
		{
			   return $this->getUrl("*/*/edit", array("id" => $row->getId()));
		}


		
		protected function _prepareMassaction()
		{
			$this->setMassactionIdField('id');
			$this->getMassactionBlock()->setFormFieldName('ids');
			$this->getMassactionBlock()->setUseSelectAll(true);
			$this->getMassactionBlock()->addItem('remove_bannerblock', array(
					 'label'=> Mage::helper('banner')->__('Remove Bannerblock'),
					 'url'  => $this->getUrl('*/adminhtml_bannerblock/massRemove'),
					 'confirm' => Mage::helper('banner')->__('Are you sure?')
				));
			return $this;
		}
			

}