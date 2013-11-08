<?php
 
class Aurigait_Banner_Adminhtml_BannersingleController extends Mage_Adminhtml_Controller_Action
{
 	protected function _initAction() {
       //echo 'hi';die;
        $this->loadLayout()->_setActiveMenu('aurigait/banner')
            ->_addBreadcrumb(Mage::helper('banner')->__('Banner'), Mage::helper('banner')->__('Add Text Size Limit'));		
		return $this;
    }
	
	public function indexAction() {
	   
//		set_time_limit(30);
		$this->_title($this->__('banner'))->_title($this->__('Banner'));
        	
		//$bannerModel  = Mage::getModel('banner/banner')->load(1);
 		//if ($bannerModel->getId()) {

            //Mage::register('banner_data', $bannerModel);}
            $this->_title($this->__('banner'))->_title($this->__('Single Image Banner'));
           $this->_initAction();
 
           
		$this->_addContent($this->getLayout()->createBlock('banner/adminhtml_bannersingle'));
		$this->renderLayout();
    }
	
	public function saveAction()
	{
		$data = $this->getRequest()->getPost(); 
	if(isset($_FILES['bannerimage']['name']) and (file_exists($_FILES['bannerimage']['tmp_name']))) {
	  try {
	   
	    $uploader = new Varien_File_Uploader('bannerimage');
	    $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png')); // or pdf or anything
	 
	 
	    $uploader->setAllowRenameFiles(false);
	 
	    // setAllowRenameFiles(true) -> move your file in a folder the magento way
	    // setAllowRenameFiles(true) -> move your file directly in the $path folder
	    $uploader->setFilesDispersion(false);
	   
	    $path = Mage::getBaseDir('media') . DS.'footer_banner'.DS ;
		       
	    $uploader->save($path, $_FILES['bannerimage']['name']);
	 
	    $data['bannerimage'] = $_FILES['bannerimage']['name'];

	//Create Thumbnail and upload
						$imgName = $_FILES['bannerimage']['name'];
						$imgPathFull = $path.$imgName;
						$resizeFolder = "thumb";
						$imageResizedPath = $path.$resizeFolder.DS.$imgName;
						$imageObj = new Varien_Image($imgPathFull);
						$imageObj->constrainOnly(TRUE);
						$imageObj->keepAspectRatio(TRUE);
						$imageObj->resize(150,150);
						$imageObj->save($imageResizedPath);
						
						//Create View Size and upload
						$imgName = $_FILES['bannerimage']['name'];
						$imgPathFull = $path.$imgName;
						$resizeFolder = "medium";
						$imageResizedPath = $path.$resizeFolder.DS.$imgName;
						$imageObj = new Varien_Image($imgPathFull);
						$imageObj->constrainOnly(TRUE);
						$imageObj->keepAspectRatio(TRUE);
						$imageObj->resize(400,400);
						$imageObj->save($imageResizedPath);
	  }catch(Exception $e) {
	 
	  }
	}
$file_nm=str_replace(" ","_",$_FILES['bannerimage']['name']);
$banner_id=$this->getRequest()->getParam('id');
$imgPath = Mage::getBaseUrl('media')."footer_banner/thumb/".$file_nm;
$data['filethumbgrid'] = '<img src="'.$imgPath.'" border="0" width="75" height="75" />';
//		$bannerModel  = Mage::getModel('banner/banner')->load();
//$bannerModel=Mage::getModel('banner/banner')->getCollection()->addFieldToFilter("banner_type",1)->getFirstItem();
 		if ($banner_id) {
			 $resource = Mage::getSingleton('core/resource');
			$write = $resource->getConnection('core_write');
			$sql = "UPDATE banner SET bannerimage = '".$file_nm."',filethumbgrid='".$data['filethumbgrid']."' ,link='".$data['link']."',position='".$data['position']."' ".
				"WHERE banner_id = '".$banner_id."'";
			$write->query($sql);
			$message = 'Banner Settings updated !!';
		}
		else
		{
		Mage::getModel('banner/banner')->setBannerimage($file_nm)->setFilethumbgrid($data['filethumbgrid'])->setLink($data['link'])->setBanner_type('1')->setPosition($data['position'])->save();	
		$message = 'Banner Settings saved !!';
		}		
		Mage::getSingleton('adminhtml/session')->addSuccess($message);			

		$this->_redirect('*/*/index');
	}
	
        public function newAction() {
		$this->_forward('edit');
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

			$this->_addContent($this->getLayout()->createBlock('banner/adminhtml_bannersingle_edit'))
				->_addLeft($this->getLayout()->createBlock('banner/adminhtml_bannersingle_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('banner')->__('Banner does not exist'));
			$this->_redirect('*/*/');
		}
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
}
