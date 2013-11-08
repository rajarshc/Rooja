<?php

class Aurigait_Banner_Adminhtml_BannerblockController extends Mage_Adminhtml_Controller_Action
{
		protected function _initAction()
		{
				$this->loadLayout()->_setActiveMenu("banner/bannerblock")->_addBreadcrumb(Mage::helper("adminhtml")->__("Bannerblock  Manager"),Mage::helper("adminhtml")->__("Bannerblock Manager"));
				return $this;
		}
		public function indexAction() 
		{
			    $this->_title($this->__("Banner"));
			    $this->_title($this->__("Manager Bannerblock"));

				$this->_initAction();
				$this->renderLayout();
		}
		public function editAction()
		{			    
			    $this->_title($this->__("Banner"));
				$this->_title($this->__("Bannerblock"));
			    $this->_title($this->__("Edit Item"));
				
				$id = $this->getRequest()->getParam("id");
				$model = Mage::getModel("banner/bannerblock")->load($id);
				if ($model->getId()) {
					Mage::register("bannerblock_data", $model);
					$this->loadLayout();
					$this->_setActiveMenu("banner/bannerblock");
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Bannerblock Manager"), Mage::helper("adminhtml")->__("Bannerblock Manager"));
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Bannerblock Description"), Mage::helper("adminhtml")->__("Bannerblock Description"));
					$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
					$this->_addContent($this->getLayout()->createBlock("banner/adminhtml_bannerblock_edit"))->_addLeft($this->getLayout()->createBlock("banner/adminhtml_bannerblock_edit_tabs"));
					$this->renderLayout();
				} 
				else {
					Mage::getSingleton("adminhtml/session")->addError(Mage::helper("banner")->__("Item does not exist."));
					$this->_redirect("*/*/");
				}
		}

		public function newAction()
		{

		$this->_title($this->__("Banner"));
		$this->_title($this->__("Bannerblock"));
		$this->_title($this->__("New Item"));

        $id   = $this->getRequest()->getParam("id");
		$model  = Mage::getModel("banner/bannerblock")->load($id);

		$data = Mage::getSingleton("adminhtml/session")->getFormData(true);
		if (!empty($data)) {
			$model->setData($data);
		}

		Mage::register("bannerblock_data", $model);

		$this->loadLayout();
		$this->_setActiveMenu("banner/bannerblock");

		$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);

		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Bannerblock Manager"), Mage::helper("adminhtml")->__("Bannerblock Manager"));
		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Bannerblock Description"), Mage::helper("adminhtml")->__("Bannerblock Description"));


		$this->_addContent($this->getLayout()->createBlock("banner/adminhtml_bannerblock_edit"))->_addLeft($this->getLayout()->createBlock("banner/adminhtml_bannerblock_edit_tabs"));

		$this->renderLayout();

		}
		public function saveAction()
		{

			$post_data=$this->getRequest()->getPost();


				if ($post_data) {

					try {

						

						$model = Mage::getModel("banner/bannerblock")
						->addData($post_data)
						->setId($this->getRequest()->getParam("id"))
						->save();

						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Bannerblock was successfully saved"));
						Mage::getSingleton("adminhtml/session")->setBannerblockData(false);

						if ($this->getRequest()->getParam("back")) {
							$this->_redirect("*/*/edit", array("id" => $model->getId()));
							return;
						}
						$this->_redirect("*/*/");
						return;
					} 
					catch (Exception $e) {
						Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						Mage::getSingleton("adminhtml/session")->setBannerblockData($this->getRequest()->getPost());
						$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
					return;
					}

				}
				$this->_redirect("*/*/");
		}



		public function deleteAction()
		{
				if( $this->getRequest()->getParam("id") > 0 ) {
					try {
						$model = Mage::getModel("banner/bannerblock");
						$model->setId($this->getRequest()->getParam("id"))->delete();
						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item was successfully deleted"));
						$this->_redirect("*/*/");
					} 
					catch (Exception $e) {
						Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
					}
				}
				$this->_redirect("*/*/");
		}

		
		public function massRemoveAction()
		{
			try {
				$ids = $this->getRequest()->getPost('ids', array());
				foreach ($ids as $id) {
                      $model = Mage::getModel("banner/bannerblock");
					  $model->setId($id)->delete();
				}
				Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item(s) was successfully removed"));
			}
			catch (Exception $e) {
				Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
			}
			$this->_redirect('*/*/');
		}
			
}
