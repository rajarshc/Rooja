<?php

class Magestore_Affiliateplusprogram_Adminhtml_ProgramController extends Mage_Adminhtml_Controller_Action
{
	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('affiliateplus/program')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Programs Manager'), Mage::helper('adminhtml')->__('Program Manager'));
		return $this;
	}
 
	public function indexAction(){
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
		$this->_title($this->__('Affiliateplus'))->_title($this->__('Manage Programs'));
		$this->_initAction()
			->renderLayout();
	}
	
	public function gridAction(){
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
		$this->getResponse()->setBody($this->getLayout()->createBlock('affiliateplusprogram/adminhtml_program_grid')->toHtml());
	}

	public function editAction(){
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
        $this->_initCategories();
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('affiliateplusprogram/program');
		if ($storeId = $this->getRequest()->getParam('store',0))
			$model->setStoreId($storeId);
		$model->load($id);

		if ($model->getId() || $id == 0){
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)){
				$model->setData($data);
			}
			
			$this->_title($this->__('Affiliateplus'))->_title($this->__('Manage Programs'));
			if ($model && $model->getId())
				$this->_title($model->getName());
			else 
				$this->_title($this->__('New Program'));

			$model->getConditions()->setJsFormObject('affiliateplusprogram_conditions_fieldset');
			$model->getActions()->setJsFormObject('affiliateplusprogram_actions_fieldset');
			Mage::register('affiliateplusprogram_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('affiliateplus/program');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Program Manager'), Mage::helper('adminhtml')->__('Program Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Program News'), Mage::helper('adminhtml')->__('Program News'));

			$this->getLayout()->getBlock('head')
				->setCanLoadExtJs(true)
				->setCanLoadRulesJs(true);

			$this->_addContent($this->getLayout()->createBlock('affiliateplusprogram/adminhtml_program_edit'))
				->_addLeft($this->getLayout()->createBlock('affiliateplusprogram/adminhtml_program_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('affiliateplusprogram')->__('Program does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction(){
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
        $this->editAction();
		// $this->_forward('edit');
	}
	
	public function transactionAction(){
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
		$this->loadLayout();
		$this->renderLayout();
	}
	
	public function transactionGridAction(){
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
		$this->loadLayout();
		$this->renderLayout();
	}
	
	public function accountAction(){
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
		$this->loadLayout();
		$this->getLayout()->getBlock('program.edit.tab.account')
			->setAccounts($this->getRequest()->getPost('oaccount',null));
		$this->renderLayout();
	}
	
	public function accountGridAction(){
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
		$this->loadLayout();
		$this->getLayout()->getBlock('program.edit.tab.account')
			->setAccounts($this->getRequest()->getPost('oaccount',null));
		$this->renderLayout();
	}
	
	protected function _initCategories(){
		if (!Mage::registry('program_categories'))
		if ($programId = $this->getRequest()->getParam('id')){
			$categoryCollection = Mage::getResourceModel('affiliateplusprogram/category_collection')
				->addFieldToFilter('program_id',$programId);
			if ($storeId = $this->getRequest()->getParam('store',0))
				$categoryCollection->addFieldToFilter('store_id',$storeId);
			$categories = array();
			foreach ($categoryCollection as $category)
				$categories[] = $category->getCategoryId();
			Mage::register('program_categories',$categories);
		}
	}
	
	public function categoriesAction(){
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
		$this->_initCategories();
		$this->getResponse()->setBody(
            $this->getLayout()->createBlock('affiliateplusprogram/adminhtml_program_edit_tab_categories')->toHtml()
        );
	}
	
	public function categoriesJsonAction(){
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
		$this->_initCategories();
		$this->getResponse()->setBody(
            $this->getLayout()->createBlock('affiliateplusprogram/adminhtml_program_edit_tab_categories')
                ->getCategoryChildrenJson($this->getRequest()->getParam('category'))
        );
	}
	
	public function productAction(){
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
		$this->loadLayout();
		$this->getLayout()->getBlock('program.edit.tab.product')
			->setProducts($this->getRequest()->getPost('oproduct',null));
		$this->renderLayout();
	}
	
	public function productGridAction(){
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
		$this->loadLayout();
		$this->getLayout()->getBlock('program.edit.tab.product')
			->setProducts($this->getRequest()->getPost('oproduct',null));
		$this->renderLayout();
	}
 
	public function saveAction(){
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
		if ($data = $this->getRequest()->getPost()) {
			$data = $this->_filterDates($data, array('valid_from', 'valid_to'));
			if (isset($data['valid_from']) && $data['valid_from'] == '') $data['valid_from'] = null;
			if (isset($data['valid_to']) && $data['valid_to'] == '') $data['valid_to'] = null;
			$model = Mage::getModel('affiliateplusprogram/program');
			
			if (isset($data['rule'])){
				$rules = $data['rule'];
				if (isset($rules['conditions']))
					$data['conditions'] = $rules['conditions'];
				if (isset($rules['actions']))
					$data['actions'] = $rules['actions'];
				unset($data['rule']);
			}
			
			// repair data
			if (isset($data['program_name']))
				$data['name'] = $data['program_name'];
			if (isset($data['customer_groups']) && is_array($data['customer_groups']))
				$data['customer_groups'] = implode(',',$data['customer_groups']);
			
			// add data to model
			if ($storeId = $this->getRequest()->getParam('store',0))
				$model->setStoreId($storeId);
			$model->load($this->getRequest()->getParam('id'));
			foreach ($model->getStoreAttributes() as $attribute)
				$model->setData($attribute.'_default',false);
			$model->addData($data)
				->setId($this->getRequest()->getParam('id'));
			
			try {
				$model->loadPost($data);
				if ($model->getActionsSerialized() != serialize($model->getActions()->asArray()))
					$model->setData('is_process',0);
				if ($model->getCreatedDate() == NULL)
					$model->setCreatedDate(now(true));
				
				// calculate number of affiliate account
                if (isset($data['program_account']) && is_string($data['program_account'])) {
					$programAccount = array();
					parse_str($data['program_account'],$programAccount);
					$programAccount = array_unique(array_keys($programAccount));
				}
				if (isset($data['autojoin']) && $data['autojoin'] && isset($data['scope']) && $data['scope'] == Magestore_Affiliateplusprogram_Model_Scope::SCOPE_GLOBAL ){
                    $collections = Mage::getResourceModel('affiliateplus/account_collection');
                    if ($model->getId()) {
                        $collections->getSelect()
                            ->joinLeft(array('j' => $collections->getTable('affiliateplusprogram/joined')),
                                'main_table.account_id = j.account_id AND j.program_id = ' . $model->getId(),
                                array()
                            )->where('j.id IS NULL');
                    }
					$data['program_account'] = $collections->getAllIds();
				}elseif (isset($data['autojoin']) && $data['autojoin'] && isset($data['scope']) && $data['scope'] == Magestore_Affiliateplusprogram_Model_Scope::SCOPE_GROUPS ){
					if ($model->getData('customer_groups')){
						$customerGroups = explode(',',$model->getData('customer_groups'));
                        $collections = Mage::getResourceModel('affiliateplus/account_collection');
                        $collections->getSelect()
                            ->joinLeft(array('c' => $collections->getTable('customer/entity')),
                                'main_table.customer_id = c.entity_id',
                                array()
                            )->where('FIND_IN_SET(c.group_id, ?)', implode(',', $customerGroups));
                        if ($model->getId()) {
                            $collections->getSelect()
                                ->joinLeft(array('j' => $collections->getTable('affiliateplusprogram/joined')),
                                    'main_table.account_id = j.account_id AND j.program_id = ' . $model->getId(),
                                    array()
                                )->where('j.id IS NULL');
                        }
                        $data['program_account'] = $collections->getAllIds();
					}
				}
                if (isset($programAccount) && $programAccount) {
                    if (isset($data['program_account']) && is_array($data['program_account'])) {
                        $data['program_account'] = array_unique(array_merge($programAccount, $data['program_account']));
                    } else {
                        $data['program_account'] = $programAccount;
                    }
				} elseif (isset($data['program_account']) && is_array($data['program_account']) && $model->getId()) {
                    $joinedAccounts = Mage::getResourceModel('affiliateplusprogram/account_collection')
                        ->addFieldToFilter('program_id', $model->getId());
                    foreach ($joinedAccounts as $joinedAccount) {
                        $data['program_account'][] = $joinedAccount->getAccountId();
                    }
                    $data['program_account'] = array_unique($data['program_account']);
                }
				
				if (isset($data['program_account']) && is_array($data['program_account']))
					$model->setData('num_account',count($data['program_account']));
				
				$model->save();
				
				// save list of affiliate account
				if (isset($data['program_account']) && is_array($data['program_account'])) {
					Mage::getModel('affiliateplusprogram/account')
						->setProgramId($model->getId())
						->setAccountIds($data['program_account'])
						->saveAll();
                    Mage::getModel('affiliateplusprogram/joined')->updateJoined($model->getId());
                }
				
				// save list of category
				if (isset($data['category_ids']) && is_string($data['category_ids'])){
					$categoryIds = array_unique(explode(',',$data['category_ids']));
				if (is_array($categoryIds))
					Mage::getModel('affiliateplusprogram/category')
						->setProgramId($model->getId())
						->setCategoryIds($categoryIds)
						->setStoreId($storeId)
						->saveAll();
				}
				
				/*/ save list of product
				if (isset($data['program_product']) && is_string($data['program_product'])){
					$programProduct = array();
					parse_str($data['program_product'],$programProduct);
					$data['program_product'] = array_unique(array_keys($programProduct));
				}
				if (isset($data['program_product']) && is_array($data['program_product']))
					Mage::getModel('affiliateplusprogram/product')
						->setProgramId($model->getId())
						->setProductIds($data['program_product'])
						->setStoreId($storeId)
						->saveAll();
				*/
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('affiliateplusprogram')->__('Program was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);
				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array(
						'id' => $model->getId(),
						'store'	=> $storeId,
					));
					return;
				}
				$this->_redirect('*/*/');
				return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array(
                	'id' 	=> $this->getRequest()->getParam('id'),
                	'store'	=> $storeId,
                ));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('affiliateplusprogram')->__('Unable to find program to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
		if( $this->getRequest()->getParam('id') > 0 ){
			try {
				$model = Mage::getModel('affiliateplusprogram/program');
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Program was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array(
					'id' 	=> $this->getRequest()->getParam('id'),
					'store' => $this->getRequest()->getParam('store'),
				));
			}
		}
		$this->_redirect('*/*/',array('store' => $this->getRequest()->getParam('store')));
	}

    public function massDeleteAction() {
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
        $affiliateplusprogramIds = $this->getRequest()->getParam('affiliateplusprogram');
        if(!is_array($affiliateplusprogramIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select program(s)'));
        } else {
            try {
                foreach ($affiliateplusprogramIds as $affiliateplusprogramId) {
                    $affiliateplusprogram = Mage::getModel('affiliateplusprogram/program')->load($affiliateplusprogramId);
                    $affiliateplusprogram->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d program(s) were successfully deleted', count($affiliateplusprogramIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index',array('store' => $this->getRequest()->getParam('store')));
    }
	
    public function massStatusAction(){
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
        $affiliateplusprogramIds = $this->getRequest()->getParam('affiliateplusprogram');
        if(!is_array($affiliateplusprogramIds)){
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select program(s)'));
        } else {
            try {
                foreach ($affiliateplusprogramIds as $affiliateplusprogramId) {
                    $affiliateplusprogram = Mage::getModel('affiliateplusprogram/program')
                        ->setStoreId($this->getRequest()->getParam('store'))
                        ->load($affiliateplusprogramId);
                    if ($this->getRequest()->getParam('store')){
	                    foreach ($affiliateplusprogram->getStoreAttributes() as $attribute)
	                    	if (!$affiliateplusprogram->getData($attribute.'_in_store'))
	                    		$affiliateplusprogram->setData($attribute.'_default',true);
	                    $affiliateplusprogram->setData('status_default',false);
                    }
                    $affiliateplusprogram->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d program(s) were successfully updated', count($affiliateplusprogramIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index',array('store' => $this->getRequest()->getParam('store')));
    }
  
    public function exportCsvAction(){
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
        $fileName   = 'affiliateplusprogram.csv';
        $content    = $this->getLayout()->createBlock('affiliateplusprogram/adminhtml_program_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction(){
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
        $fileName   = 'affiliateplusprogram.xml';
        $content    = $this->getLayout()->createBlock('affiliateplusprogram/adminhtml_program_grid')
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
    
    public function programAction(){
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
    	$this->loadLayout();
		$this->getLayout()->getBlock('account.edit.tab.program')
			->setPrograms($this->getRequest()->getPost('oprogram',null));
		$this->renderLayout();
    }
    
    public function programGridAction(){
		if(!Mage::helper('magenotification')->checkLicenseKeyAdminController($this)){ return; }
    	$this->loadLayout();
		$this->getLayout()->getBlock('account.edit.tab.program')
			->setPrograms($this->getRequest()->getPost('oprogram',null));
		$this->renderLayout();
    }
}