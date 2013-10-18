<?php

class Magestore_Affiliateplus_Adminhtml_BannerController extends Mage_Adminhtml_Controller_Action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('affiliateplus/banner')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Banners Manager'), Mage::helper('adminhtml')->__('Banner Manager'));
		
		return $this;
	}   
 
	public function indexAction() {
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
		$this->_title($this->__('Affiliateplus'))->_title($this->__('Manage Banners'));
			
		$this->_initAction()
			->renderLayout();
	}

	public function gridAction(){
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
        $this->loadLayout();
        $this->renderLayout();
    }
	
	public function editAction() {
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
		$id     = $this->getRequest()->getParam('id');
		$storeId = $this->getRequest()->getParam('store');
		
		$banner  = Mage::getModel('affiliateplus/banner')
				->setStoreId($storeId)
				->load($id);
		
		$this->_title($this->__('Affiliateplus'))->_title($this->__('Manage Banners'));
	
		if($banner && $banner->getId())
			$this->_title($this->__($banner->getTitle()));
		else
			$this->_title($this->__('New Banner'));
		
		if ($banner->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$banner->setData($data);
			}

			Mage::register('banner_data', $banner);

			$this->loadLayout();
			$this->_setActiveMenu('affiliateplus/banner');
			
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Banner Manager'), Mage::helper('adminhtml')->__('Banner Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Banner News'), Mage::helper('adminhtml')->__('Banner News'));
			
			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('affiliateplus/adminhtml_banner_edit'))
				->_addLeft($this->getLayout()->createBlock('affiliateplus/adminhtml_banner_edit_tabs'));
			
			$this->renderLayout();
			
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('affiliateplus')->__('Banner does not exist'));
			$this->_redirect('*/*/', array('store' => $storeId));
		}
		
		
	}
 
	public function newAction() {
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
        $this->editAction();
		// $this->_forward('edit');
	}
 
	public function saveAction() {
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
		if ($data = $this->getRequest()->getPost()) {
			if(isset($_FILES['source_file']['name']) && $_FILES['source_file']['name'] != '') {
				try {	
					$uploader = new Varien_File_Uploader('source_file');
	           		$uploader->setAllowedExtensions(array('jpg','jpeg','gif','png', 'swf'));
					$uploader->setAllowRenameFiles(false);
					$uploader->setFilesDispersion(false);
							
					$path = Mage::getBaseDir('media') . DS . 'affiliateplus' . DS . 'banner' . DS;
					$file = $uploader->save($path, $_FILES['source_file']['name'] );
					
				} catch (Exception $e) {
		      
		        }
	        
		        //this way the name is saved in DB
	  			$data['source_file'] = $file['file'];
			}

			$bannerId = $this->getRequest()->getParam('id');
			$storeId = $this->getRequest()->getParam('store');
			$banner = Mage::getModel('affiliateplus/banner');		
			$banner->setStoreId($storeId)
				->setData($data)
				->setId($bannerId);
			//print_r($banner);die();
			
			try {
				$banner->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('affiliateplus')->__('Banner was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $banner->getId(), 'store' => $storeId));
					return;
				}
				$this->_redirect('*/*/', array('store' => $storeId));
				return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $banner->getId(), 'store' => $storeId));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('affiliateplus')->__('Unable to find banner to save'));
        $this->_redirect('*/*/', array('store' => $storeId));
	}
 
	public function deleteAction() {
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
		$bannerId = $this->getRequest()->getParam('id');
		$storeId = $this->getRequest()->getParam('store');
		
		if( $bannerId > 0 ) {
			try {
				$banner = Mage::getModel('affiliateplus/banner');
				 
				$banner->setId($bannerId)
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Banner was successfully deleted'));
				$this->_redirect('*/*/', array('store' => $storeId));
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $bannerId));
			}
		}
		$this->_redirect('*/*/', array('store' => $storeId));
	}

    public function massDeleteAction() {
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
        $bannerIds = $this->getRequest()->getParam('banner');
		$storeId = $this->getRequest()->getParam('store');
        if(!is_array($bannerIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select banner(s)'));
        } else {
            try {
                foreach ($bannerIds as $bannerId) {
                    $banner = Mage::getModel('affiliateplus/banner')->load($bannerId);
                    $banner->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($bannerIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index', array('store' => $storeId));
    }
	
    public function massStatusAction()
    {
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
        $bannerIds = $this->getRequest()->getParam('banner');
		$storeId = $this->getRequest()->getParam('store');
        if(!is_array($bannerIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select banner(s)'));
        } else {
            try {
                foreach ($bannerIds as $bannerId) {
                    $banner = Mage::getSingleton('affiliateplus/banner')
						->setStoreId($storeId)
                        ->load($bannerId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($bannerIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index', array('store' => $storeId));
    }
}