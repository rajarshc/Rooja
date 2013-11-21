<?php

class Aurigait_Navigation_Model_Observer 
{
	public function updateCategoryAttribute()
	{
		/*$read= Mage::getSingleton('core/resource')->getConnection('core_write');
		$sql="select last_run_time from category_attribute_cron order by id desc limit 1";
		$last_time=$read->fetchOne($sql);
		$currentDateTime=date('Y-m-d h:i:s');
		$todayDate  = Mage::app()->getLocale()->date()->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
		$sql="insert into category_attribute_cron set last_run_time=".$currentDateTime;
		$read->execute($sql);
		if(empty($last_time))
		{
			$products=Mage::getModel('catalog/product')->getCollection()->addAttributeToFilter('created_at', array('datetime' => true, 'to' => $todayDate));
		}
		else
		{
			$products=Mage::getModel('catalog/product')->getCollection()->addAttributeToFilter('created_at', array('datetime' => true, 'to' => $currentDateTime))
					  ->addAttributeToFilter('created_at', array('and'=> array('datetime' => true, 'from' => $last_time)), 'left');
		}
		foreach($products as $product)
		{
			$prod=Mage::getModel('catalog/product')->load($product->getId());
			$prodCat=$prod->getCategoryIds();
			if(is_array($prodCat)){
				foreach($prodCat as $cat)
				{
					$sql="select path from catalog_category_flat_store_1 where entity_id='".$cat."'";
					$path=explode('/',$read->fetchOne($sql));
					$catId=$path[1];
					if(!in_array($catId, $doneCat)){
						$doneCat[]=$catId;
						$category=Mage::getModel('catalog/category')->load($catId);
						$category->setNewin(1);
						$category->save();
					}
					
				}
			}
			else
			{
				$sql="select path from catalog_category_flat_store_1 where entity_id='".$prodCat."'";
				$path=explode('/',$read->fetchOne($sql));
				$catId=$path[1];
				$doneCat[]=$catId;
				$category=Mage::getModel('catalog/category')->load($catId);
				
			}
			
		}*/
				       
	}
	
	public function disableProducts()
	{
	     	$read= Mage::getSingleton('core/resource')->getConnection('core_read');
		$sql="select entity_id from  catalog_category_flat_store_1 where entity_id>1067 and date(sale_end_date)!='0000-00-00' and (date(sale_end_date)<'".date('y-m-d')."' or (date(sale_end_date)='".date('y-m-d')."' and sale_end_time<'".date('H:i:00')."'))";
		$results=$read->fetchAll($sql);
		foreach($results as $cat_id)
		{
			$products = Mage::getModel('catalog/category')->load($cat_id['entity_id'])
 						->getProductCollection()
 						->addAttributeToSelect('*') // add all attributes - optional
 						->addAttributeToFilter('status', 1);
	
			foreach($products as $product)
			{
				$catids=$product->getCategoryIds();
				$storeId=Mage::app()->getStore()->getId();
				if(count($catids)>1){
					Mage::getModel('catalog/product_status')->updateProductStatus($product->getId(), $storeId, Mage_Catalog_Model_Product_Status::STATUS_DISABLED);
				}
			}			
		}
		
	}
	public function hottestProduct()
	{
	//	echo 1;die;
		$category_id=1132;
		$today = time();
	    $last = $today - (60*60*24*8);
	
	    $from = date("Y-m-d", $last);
	    $to = date("Y-m-d", $today);
	
	    $storeId    = Mage::app()->getStore()->getId();
	    $cat=Mage::getModel('catalog/category')->load($category_id);
	    $productslist = $cat->getProductCollection();
	    foreach($productslist as $product)
		{
			$_product=Mage::getModel('catalog/product')->load($product->getId());
			$existing_category=$product->getCategoryIds();
			$key=  array_search($category_id, $existing_category);
			unset($existing_category[$key]);
			$_product->setCategoryIds($existing_category);
            		$_product->save();
		}
	    $visibility = array(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
				Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG);

	    $products = Mage::getResourceModel('reports/product_collection')
	        ->addAttributeToSelect('*')
	        ->addOrderedQty($from, $to)
	        ->setStoreId($storeId)
	        ->addStoreFilter($storeId)
		->addAttributeToFilter('visibility', $visibility)
	        ->setOrder('ordered_qty', 'desc');
		//age::getSingleton('catalog/product_status')->addVisibleFilterToCollection($products);
       //age::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($products);
	//  $products->load(true,true);die;
		foreach($products as $product)
		{
			$_product=Mage::getModel('catalog/product')->load($product->getId());
			$existing_category=$product->getCategoryIds();
			if($this->is_visible($existing_category)){
				array_push($existing_category, $category_id);
				$_product->setCategoryIds($existing_category);
           			$_product->save();
			}
		}
		echo "123";die;
	}
	public function is_visible($cat_ids)
	{
		$cat_id=implode(',',$cat_ids);
		if(!empty($cat_id)){
			$read= Mage::getSingleton('core/resource')->getConnection('core_read');
			$sql="select entity_id from  catalog_category_flat_store_1 where entity_id in (".$cat_id.") and (date(sale_end_date)<'".date('Y-m-d')."' or (date(sale_end_date)='".date('Y-m-d')."' and sale_end_time < '".date('H:i:00',strtotime('+330 minutes'))."')) and date(sale_end_date)!='0000-00-00'";
			$results=$read->fetchAll($sql);
			if(count($results)>0)
		 		return false;
			else
				 return true;	
		}
		return false;
	}
	
}
