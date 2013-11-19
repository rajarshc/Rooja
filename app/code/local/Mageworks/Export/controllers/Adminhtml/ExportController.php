<?php

class Mageworks_Export_Adminhtml_ExportController extends Mage_Adminhtml_Controller_Action
{

	public function indexAction() {
		$this->_title(Mage::helper('mageworks_export')->__('Export Profiles'));

		$this->loadLayout()->_setActiveMenu('mageworks_core/export');
		$this->renderLayout();
	}

	public function categoryAction() {
		$type = $this->getRequest()->getParam('type');
		$this->_title(Mage::helper('mageworks_export')->__('Export Category Profiles'));
		$this->getResponse()->setBody($this->getLayout()->createBlock('mageworks_export/adminhtml_category_export_'.$type)->toHtml());
		$this->getResponse()->sendResponse();
	}

}