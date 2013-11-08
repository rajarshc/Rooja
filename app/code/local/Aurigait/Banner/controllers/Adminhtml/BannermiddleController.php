<?php
class Aurigait_Banner_Adminhtml_BannermiddleController extends Mage_Adminhtml_Controller_Action
{
 	protected function _initAction() {
       //echo 'hi';die;
        $this->loadLayout()->_setActiveMenu('aurigait/banner')
            ->_addBreadcrumb(Mage::helper('banner')->__('Banner'), Mage::helper('banner')->__('Middle Banner'));		
		return $this;
    }
	
	public function indexAction() {
	   
//		set_time_limit(30);
		//$this->_title($this->__('banner'))->_title($this->__('Banner'));
        	
		//$bannerModel  = Mage::getModel('banner/banner')->load(1);
 		//if ($bannerModel->getId()) {

            //Mage::register('banner_data', $bannerModel);}
            $this->_title($this->__('banner'))->_title($this->__('Single Image Banner'));
           $this->_initAction();
 
           
		$this->_addContent($this->getLayout()->createBlock('banner/adminhtml_bannermiddle'));
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
	   
	    $path = Mage::getBaseDir('media') . DS.'middle_banner'.DS ;
		       
	    $uploader->save($path, $_FILES['bannerimage']['name']);
            
        $data['bannerimage'] = $_FILES['bannerimage']['name'];
		$file_nm=str_replace(" ","_",$_FILES['bannerimage']['name']);
		
		$imgPath = Mage::getBaseUrl('media')."middle_banner/".$file_nm;
		
		$imgName = $_FILES['bannerimage']['name'];
		$imgPathFull = $path.$imgName;
		$imageResizedPath = $path."image".DS.$imgName;
		$imageObj = new Varien_Image($imgPathFull);
		$imageObj->constrainOnly(TRUE);
		$imageObj->keepAspectRatio(TRUE);
		$imageObj->keepFrame(FALSE);
		$imageObj->resize(500);
		$imageObj->save($imageResizedPath);
		
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
			
			$sql = "UPDATE banner SET ".$file.$img_filed."link='".$data['link']."',image_text='".$data['image_text']."',gender='".$data['gender']."',sort_order='".$data['sort_order']."',position='".$data['position']."' ".
				"WHERE banner_id = '".$banner_id."'";
			$write->query($sql);
			$message = 'Banner Settings updated !!';
		}
		else
		{
		Mage::getModel('banner/banner')->setBannerimage($file_nm)->setFilethumbgrid($data['filethumbgrid'])->setImageText($data['image_text'])->setLink($data['link'])->setSortOrder($data['sort_order'])->setBanner_type('2')->setGender($data['gender'])->setPosition($data['position'])->save();	
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

			$this->_addContent($this->getLayout()->createBlock('banner/adminhtml_bannermiddle_edit'))
				->_addLeft($this->getLayout()->createBlock('banner/adminhtml_bannermiddle_edit_tabs'));

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