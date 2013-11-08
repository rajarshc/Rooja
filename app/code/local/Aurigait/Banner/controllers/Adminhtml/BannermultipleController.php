<?php
 
class Aurigait_Banner_Adminhtml_BannermultipleController extends Mage_Adminhtml_Controller_Action
{
	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('banners/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Banners Manager'), Mage::helper('adminhtml')->__('Banners Manager'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction();
$this->_addContent($this->getLayout()->createBlock('banner/adminhtml_bannermultiple'));
			$this->renderLayout();
	}
	
	/**
     * Get categories fieldset block
     *
     */
    public function categoriesAction()
    {
        $this->_initProduct();

        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('adminhtml/catalog_product_edit_tab_categories')->toHtml()
        );
    }

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('banner/banner')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}
			Mage::register('banners_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('banners/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Banner Manager'), Mage::helper('adminhtml')->__('Banner Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Banner News'), Mage::helper('adminhtml')->__('Banner News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
			$this->_addContent($this->getLayout()->createBlock('banner/adminhtml_bannermultiple_edit'))
				->_addLeft($this->getLayout()->createBlock('banner/adminhtml_bannermultiple_edit_tabs'));
			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('banner')->__('Banner does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {
						
			if(isset($_FILES['bannerimage']['name']) && $_FILES['bannerimage']['name'] != '') {
	  			 //this way the name is saved in DB
	  			$data['bannerimage'] = $_FILES['bannerimage']['name'];
				
				//Save Image Tag in DB for GRID View
				$imgName = $_FILES['bannerimage']['name'];
				$imgPath = Mage::getBaseUrl('media')."Banners/images/thumb/".$imgName;
				$data['filethumbgrid'] = '<img src="'.$imgPath.'" border="0" width="75" height="75" />';
				// nks code */
				$imgLargePath = Mage::getBaseUrl('media')."Banners/images/".$imgName;
				$data['filelargegrid'] = '<img src="'.$imgLargePath.'" border="0" width="100%" style="margin: 0 auto;" />';
				// end: nks code */
			}$data['banner_type']=3;
			$model = Mage::getModel('banner/banner');	
			$model->setData($data)
				->setId($this->getRequest()->getParam('id')); 
			
//var_dump($model);die;
			try {
				if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
					$model->setCreatedTime(now())
						->setUpdateTime(now());
				} else {
					$model->setUpdateTime(now());
				}	
				
				$model->save();
				
				if(isset($_FILES['bannerimage']['name']) && $_FILES['bannerimage']['name'] != '') {
					try {	
						
						$path = Mage::getBaseDir('media')."/Banners". DS ."images". DS ;
						/* Starting upload */							
						$uploader = new Varien_File_Uploader('bannerimage');
						// Any extention would work
						$uploader->setAllowedExtensions(array('jpg','JPG','jpeg','gif','GIF','png','PNG'));
						$uploader->setAllowRenameFiles(false);
						$uploader->setFilesDispersion(false);
						// We set media as the upload dir
						$uploader->save($path, $_FILES['bannerimage']['name'] );
						
						
						//Create Thumbnail and upload
						/*$imgName = $_FILES['bannerimage']['name'];
						$imgPathFull = $path.$imgName;
						$resizeFolder = "thumb";
						$imageResizedPath = $path.$resizeFolder.DS.$imgName;
						$imageObj = new Varien_Image($imgPathFull);
						$imageObj->constrainOnly(TRUE);
						$imageObj->keepAspectRatio(TRUE);
						$imageObj->resize(150,150);
						$imageObj->save($imageResizedPath);*/
						
						//Create View Size and upload
						/*$imgName = $_FILES['bannerimage']['name'];
						$imgPathFull = $path.$imgName;
						$imageResizedPath = $path.$imgName;
						$imageObj = new Varien_Image($imgPathFull);
						$imageObj->constrainOnly(TRUE);
						$imageObj->keepAspectRatio(TRUE);
						$imageObj->resize(500,500);
						$imageObj->save($imageResizedPath);*/
						
						
					} catch (Exception $e) {}
				}
				
				Mage::helper('banner')->generateXML();
				
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('banner')->__('Banner was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}
				$this->_redirect('*/*/');
				return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('banner')->__('Unable to find Banner to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('banner/banner');
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Banner was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {//var_dump($this->getRequest()->getParam('banner'));die;
        $bannersIds = $this->getRequest()->getParam('banner');
        if(!is_array($bannersIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select Banner(s)'));
        } else {
            try {
                foreach ($bannersIds as $bannersId) {
                    $banners = Mage::getModel('banner/banner')->load($bannersId);
                    $banners->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($bannersIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	
    public function massStatusAction()
    {
        $bannersIds = $this->getRequest()->getParam('banner');
        if(!is_array($bannersIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select Banner(s)'));
        } else {
            try {
                foreach ($bannersIds as $bannersId) {
                    $banners = Mage::getSingleton('banner/banner')
                        ->load($bannersId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($bannersIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
  
    public function exportCsvAction()
    {
        $fileName   = 'banners.csv';
        $content    = $this->getLayout()->createBlock('banners/adminhtml_bannermultiple_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'banners.xml';
        $content    = $this->getLayout()->createBlock('banners/adminhtml_bannermultiple_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }
}
