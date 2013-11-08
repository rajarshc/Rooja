<?php
class Aurigait_Banner_Adminhtml_BannerfooterController extends Mage_Adminhtml_Controller_Action
{
 	protected function _initAction() {
          $this->loadLayout()->_setActiveMenu('aurigait/banner')
            ->_addBreadcrumb(Mage::helper('banner')->__('Banner'), Mage::helper('banner')->__('Footer Blocks'));		
		return $this;
        }
        public function indexAction() {
	   
           $this->_title($this->__('banner'))->_title($this->__('Footer Banners'));
           $this->_initAction();
    	   $this->_addContent($this->getLayout()->createBlock('banner/adminhtml_bannerfooter'));
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
            
            if($data['position']=='left'){
                $imgPath=$path.$_FILES['bannerimage']['name'];
                $imageObj = new Varien_Image($imgPath);
                $imageObj->constrainOnly(TRUE);
                $imageObj->keepAspectRatio(TRUE);
                $imageObj->resize(466,521);
                $imageObj->save($imgPath);
            }
            else{
               $imgPath=$path.$_FILES['bannerimage']['name'];
                $imageObj = new Varien_Image($imgPath);
                $imageObj->constrainOnly(TRUE);
                $imageObj->keepAspectRatio(TRUE);
                $imageObj->resize(269,258);
                $imageObj->save($imgPath); 
            }
                
	 
	    $data['bannerimage'] = $_FILES['bannerimage']['name'];
		$file_nm=str_replace(" ","_",$_FILES['bannerimage']['name']);
              $imgPath = Mage::getBaseUrl('media')."footer_banner/".$file_nm;
            $data['filethumbgrid'] = '<img src="'.$imgPath.'" border="0" width="75" height="75" />';  
	  }catch(Exception $e) {
	 
	  }
	}
            
            $banner_id=$this->getRequest()->getParam('id');
            
            
 		if ($banner_id) {
			 $resource = Mage::getSingleton('core/resource');
			$write = $resource->getConnection('core_write');
			
			if($file_nm)
			{
				$file="bannerimage = '".$file_nm."',";
			}
			else
			{
				$file='';
			}
			
			
			if($data['filethumbgrid'])
			 $img_filed="filethumbgrid='".$data['filethumbgrid']."', ";
			else {
				$img_filed='';
			}
			
			
			$sql = "UPDATE banner SET block_id='".$data['block_id']."',".$file.$img_filed."gender='".$data['gender']."',link='".$data['link']."',image_text='".$data['image_text']."',position='".$data['position']."' ".
				"WHERE banner_id = '".$banner_id."'";
			$write->query($sql);
			$message = 'Banner Settings updated !!';
		}
		else
		{
		Mage::getModel('banner/banner')->setBlockId($data['block_id'])->setBannerimage($file_nm)->setFilethumbgrid($data['filethumbgrid'])->setGender($data['gender'])->setImageText($data['image_text'])->setLink($data['link'])->setBanner_type('1')->setPosition($data['position'])->save();	
		$message = 'Banner Settings saved !!';
		}		
		Mage::getSingleton('adminhtml/session')->addSuccess($message);			

		$this->_redirect('*/*/index');
	}
	
        public function newAction() {
		$this->_forward('edit');
	}
        
        public function editAction() {
            
            $this->_title($this->__('banner'))->_title($this->__('Single Image Banner'));
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
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Middle Banner'), Mage::helper('adminhtml')->__('Middle Banner'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('banner/adminhtml_bannerfooter_edit'))
				->_addLeft($this->getLayout()->createBlock('banner/adminhtml_bannerfooter_edit_tabs'));

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

	
