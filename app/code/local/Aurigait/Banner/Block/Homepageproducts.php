<?php
class Aurigait_Banner_Block_Homepageproducts extends Mage_Core_Block_Template{
	
	public function getProducts(){
		$category_id=$this->getCategoryId();
		$category = Mage::getModel('catalog/category')->load($category_id);
		$gender='';
		$gender=$_SESSION['selected_gender'];
		if($gender!='')
		{
			
			$attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'gender');
	        if ($attribute->usesSource()) {
	            $genderOptions=$attribute->getSource()->getAllOptions(false);
	        }
			foreach($genderOptions as $option)
			{
				if($option['label']=='Men')
				$gendercode[2]=$option['value'];
				elseif($option['label']=='Women')
				$gendercode[3]=$option['value'];
				else {
				 $gendercode[1]=$option['value'];
				}
			}
			if($gender=='men')
			  $genderoption=array($gendercode[1],$gendercode[2]);
			elseif($gender=='women')
			  $genderoption=array($gendercode[1],$gendercode[3]);
			else 
			  $genderoption=array($gendercode[1],$gendercode[2],$gendercode[3]);
			
			$collection = $category->getProductCollection()->addAttributeToFilter('gender',array('in'=>$genderoption))->setOrder('gender','desc');
		}
		else{
			$collection = $category->getProductCollection()->setOrder('gender','desc');
		}
		//
		//$collection = $category->getProductCollection()->addAttributeToSort('position');
		Mage::getModel('catalog/layer')->prepareProductCollection($collection);
		
		return $collection;
	}
}