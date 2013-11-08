<?php 
class Aurigait_Banner_Block_Banner extends Mage_Core_Block_Template
{
	public function getBanners()
    {
    	$gender=$_SESSION['selected_gender'];
    	if($gender!='')
		{
			if($gender=='men')
			  $gendercode=array(1,2);
			elseif($gender=='women')
			  $gendercode=array(1,3);
			else 
			  $gendercode=array(1,2,3);
			
			if($gender=='both')
			{
				$bannerModel=Mage::getModel('banner/banner')->getCollection()->addFieldToFilter("banner_type",2)->addFieldToFilter('gender',array('in'=>$gendercode))->setOrder('sort_order','asc');	
			}
			else{
				$bannerModel=Mage::getModel('banner/banner')->getCollection()->addFieldToFilter("banner_type",2)->addFieldToFilter('gender',array('in'=>$gendercode))->setOrder('gender','desc');	
			}
			
			
		}
		else{
			$bannerModel=Mage::getModel('banner/banner')->getCollection()->addFieldToFilter("banner_type",2)->setOrder('sort_order','asc');
		}
            return $bannerModel->getData();
    }
        
}
