<?php

class Aurigait_Banner_Model_Bannerblock extends Mage_Core_Model_Abstract
{
    protected function _construct(){

       $this->_init("banner/bannerblock");

    }
function getBannerBlockForForm()
   {
   	  $collection=Mage::getModel('banner/bannerblock')->getCollection()->addFieldToSelect('id')->addFieldToSelect('block_title');
	  $arr=array();
	  foreach($collection as $data)
	  {
	  	$arr[]=array('value'=>$data->getId(),'label'=>$data->getBlockTitle());
	  }
	  return $arr;
	  
   }
   function getBannerBlockForFilter()
   {
   	  $collection=Mage::getModel('banner/bannerblock')->getCollection()->addFieldToSelect('id')->addFieldToSelect('block_title');
	  $arr=array();
	  foreach($collection as $data)
	  {
	  	$arr[$data->getId()]=$data->getBlockTitle();
	  }
	  return $arr;
	  
   }
}
	 