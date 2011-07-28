<?php
class GoldenSpiralStudio_OneClickCartCheckout_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	
    	/*
    	 * Load an object by id 
    	 * Request looking like:
    	 * http://site.com/oneclickcartcheckout?id=15 
    	 *  or
    	 * http://site.com/oneclickcartcheckout/id/15 	
    	 */
    	/* 
		$oneclickcartcheckout_id = $this->getRequest()->getParam('id');

  		if($oneclickcartcheckout_id != null && $oneclickcartcheckout_id != '')	{
			$oneclickcartcheckout = Mage::getModel('oneclickcartcheckout/oneclickcartcheckout')->load($oneclickcartcheckout_id)->getData();
		} else {
			$oneclickcartcheckout = null;
		}	
		*/
		
		 /*
    	 * If no param we load a the last created item
    	 */ 
    	/*
    	if($oneclickcartcheckout == null) {
			$resource = Mage::getSingleton('core/resource');
			$read= $resource->getConnection('core_read');
			$oneclickcartcheckoutTable = $resource->getTableName('oneclickcartcheckout');
			
			$select = $read->select()
			   ->from($oneclickcartcheckoutTable,array('oneclickcartcheckout_id','title','content','status'))
			   ->where('status',1)
			   ->order('created_time DESC') ;
			   
			$oneclickcartcheckout = $read->fetchRow($select);
		}
		Mage::register('oneclickcartcheckout', $oneclickcartcheckout);
		*/

			
		$this->loadLayout();     
		$this->renderLayout();
    }
}