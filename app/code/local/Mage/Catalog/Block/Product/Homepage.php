<?php

/**

 * Magento

 *

 * NOTICE OF LICENSE

 *

 * This source file is subject to the Open Software License (OSL 3.0)

 * that is bundled with this package in the file LICENSE.txt.

 * It is also available through the world-wide-web at this URL:

 * http://opensource.org/licenses/osl-3.0.php

 * If you did not receive a copy of the license and are unable to

 * obtain it through the world-wide-web, please send an email

 * to license@magentocommerce.com so we can send you a copy immediately.

 *

 * DISCLAIMER

 *

 * Do not edit or add to this file if you wish to upgrade Magento to newer

 * versions in the future. If you wish to customize Magento for your

 * needs please refer to http://www.magentocommerce.com for more information.

 *

 * @category    Mage

 * @package     Mage_Catalog

 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)

 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)

 */





/**

 * Product View block

 *

 * @category   Mage

 * @package    Mage_Catalog

 * @module     Catalog

 */

class Mage_Catalog_Block_Product_Homepage extends Mage_Catalog_Block_Product_Abstract

{

	/**

	 * Add meta information from product to head block

	 *

	 * @return Mage_Catalog_Block_Product_Homepage

	 */

	

	protected function _construct()

	{

		

		$this->addData(array(

					'cache_lifetime'    => false,

					'cache_tags'        => array("product_homepage")

					));

	}

	public function getCacheKeyInfo()

	{

		

		$shortCacheId =  array(

				'Product Homepage',

				Mage::app()->getStore()->getId(),

				Mage::getDesign()->getPackageName(),

				Mage::getDesign()->getTheme('template'),

				Mage::getSingleton('customer/session')->getCustomer()->getData('gender'),

				'template' => $this->getTemplate(),
				$this->getCurrenCategoryKey()

				);
				
		$cacheId = $shortCacheId;
		
		$shortCacheId = array_values($shortCacheId);
		$shortCacheId = implode('|', $shortCacheId);
		$shortCacheId = md5($shortCacheId);
		
		$cacheId['category_path'] = $this->getCurrenCategoryKey();
		$cacheId['short_cache_id'] = $shortCacheId;
		
		return $cacheId;

	}
	/**
		 * Get current category key
		 *
		 * @return mixed
		 */
	public function getCurrenCategoryKey()
	{
		if (!$this->_currentCategoryKey) {
			$category = Mage::registry('current_category');
			if ($category) {
				$this->_currentCategoryKey = $category->getPath();
			} else {
				$this->_currentCategoryKey = Mage::app()->getStore()->getRootCategoryId();
			}
		}
		
		return $this->_currentCategoryKey;
	}
	
	

	public function homePageTopSales($gender,$category_id)

	{

		$i = 0;

		$categories = Mage::helper('catalog/category')->getStoreCategories(); 

		$catelog_enabled = Mage::helper('catalog/category_flat')->isEnabled(); 

		

		$first_index = 0;

		$second_index = 0;

		

		foreach ($categories as $_category) 

		{ 

			

			if($_category->hasChildren() && $_category->getId() !=5)

			{

				

				$subcategory = $_category->getChildren();

				

				

				$child_id_explode = explode(",",$subcategory);		

				

				// Sales orders are arranging the Subcategories position wise.

				$subCategoriesSorted = array(); //We're going to make an array of the sub cats, with the array key being the position (set in the back end by dragging), then we can sort by key.

				foreach($child_id_explode as $subCategoryId)

				{

					$cat = Mage::getModel('catalog/category')->load($subCategoryId);

					if($cat->getIsActive())

					{

						$subCategoriesSorted[$cat->getPosition()] = $cat;

					}

				}

				ksort($subCategoriesSorted);																							

				

				foreach($subCategoriesSorted as $c)

				{    

					$product_parent_id = $c['parent_id'];

					

					/*//code for current user gender get

					$session = Mage::getSingleton('customer/session'); 

					$customer = $session->getCustomer();

					if($customer['gender']=='1')

					{

						$gender='1';

						$category_id='4';

					}

					else if($customer['gender']=='2')

					{

						$gender='2';

						$category_id='58';

					}

					else

					{

						$gender='2';

						$category_id='58';

					}*/

					/*echo $gender;

					echo $category_id;*/

					

					$fivedays = strtotime(date('y-m-d H:G:00', strtotime("+2 days")));

					$today = strtotime(date('y-m-d H:i:s'));

					if (!$c->getSale_start_date() == null) 

					{

						$startdate = strtotime(date('y-m-d', strtotime($c->getSale_start_date())) . ' ' . $c->getSale_start_time() . ':00');

						$enddate = strtotime(date('y-m-d', strtotime($c->getSale_end_date())) . ' ' . $c->getSale_end_time() . ':00'); 

						

						if($this->timeBetweenNowAndDeadline($enddate) != 0)

						{

							if (($enddate > $today && $enddate < $fivedays && $startdate < $today) || ($enddate > $today && $enddate > $fivedays && $startdate < $today)) 

							{  

								

								//Filter the data's gender wise with category

								if($product_parent_id==$category_id)

								{  

									$first_catogory[$first_index]='<article class="homeSale size2 fl masonry">

											<a href="'.$c->getURL().'" title="View sale details for:'.$c->getName().'" class="product-image"><img src="'. Mage::getBaseUrl('media') . 'catalog/category/'.$c->getThumbnail().'" alt="" width="236px" /></a>

											<h2';

									if($i%2==1)

									{

										$first_catogory[$first_index] = $first_catogory[$first_index].' class="blackBg"';

									}

									$first_catogory[$first_index] = $first_catogory[$first_index].'>'.$c->getName().'</h2><p>Sale ends in '.$this->timeBetweenNowAndDeadline($enddate).'</p></article>'; 

									

									$i++;

									$first_index++; 

									

								}   //gender filter if condition end

								else

								{ 

									$another_catogory[$second_index]='<article class="homeSale size2 fl masonry">

											<a href="'.$c->getURL().'" title="View sale details for:'.$c->getName().'" class="product-image"><img src="'. Mage::getBaseUrl('media') . 'catalog/category/'.$c->getThumbnail().'" alt="" width="236px" /></a>

											<h2';

									if($i%2==1)

									{

										$another_catogory[$second_index] = $another_catogory[$second_index].' class="blackBg"';

									}

									

									$another_catogory[$second_index] = $another_catogory[$second_index].'>'.$c->getName().'</h2><p>Sale ends in '.$this->timeBetweenNowAndDeadline($enddate).'</p></article>';

									

									$i++;

									$second_index++; 

								}   //gender filter else condition end 

								

							}

						}

					}

				}

			} 

		}

		

		// Printing Home Top sales Colletion for men and women vice versa.			

		$count = 0;

		$first_collection = $first_catogory;	 

		

		$love_sym = $this->getSkinUrl('images/graphics/spreadthelove3.jpg');

		$love_categoty ='<article class="homeAd2 fl masonry"><a href="/rewardsref/customer/index/"><img src="'.$love_sym.'" alt="Invite Friends and get cash" /></a></article>';

		

		//printing  first colletion for loged in gender

		foreach($first_collection as $colletion)

		{ 

			echo $colletion;

			$count++;

			if($count == 3)

			{ 

				// printing invite friend box

				echo $love_categoty;

			}

		}

		

		//print the home page top colletion for oppsite gender colletion

		$another_colletions = $another_catogory; 

		$count_one = count($another_catogory);

		foreach($another_colletions as $var)

		{

			echo $var;

			$count++;

			if($count == 3)

			{ 

				// printing invite friend box

				echo $love_categoty;

			}

		}

		

		

	}

	

	/*

	Boutique Collction on Home page 

	*/

	

	public function homePageBoutique()

	{

		

		$i = 1;

		$categories = Mage::helper('catalog/category')->getStoreCategories();

		foreach ($categories as $_category){

			if($_category->hasChildren() && $_category->getId() ==5){

				

				// foreach($_category->getChildren() as $subcategory){

				//	$c = Mage::getModel('catalog/category')->load($subcategory->getId());

				$subcategory = $_category->getChildren();

				$child_id_explode = explode(",",$subcategory);

				

				// Sales orders are arranging the Subcategories position wise.

				$subCategoriesSorted = array(); //We're going to make an array of the sub cats, with the array key being the position (set in the back end by dragging), then we can sort by key.

				foreach($child_id_explode as $subCategoryId)

				{

					$cat = Mage::getModel('catalog/category')->load($subCategoryId);


					if($cat->getIsActive())

					{

						$subCategoriesSorted[$cat->getPosition()] = $cat;

					}

				}

				ksort($subCategoriesSorted);																							

				

				foreach($subCategoriesSorted as $c)

				{    

					if ($c->getSale_start_time() == 'Y' || $c->getSale_start_time() == 'Yes' ) {

						

						echo '<article class="boutiqueDetails fl noMargin">';

						

						echo '<a href="'. $c->getURL() .'"><span><img src="'. Mage::getBaseUrl('media') . 'catalog/category/'. $c->getImage() .'"></span></a>';

						

						$i++;

						

						echo '</article>';

					}

				}

			}

		}

		

	}

	

	/*

	Sales Ending Soon Collction on Home page 

	*/

	

	public function homePageSalesEnding()

	{

		

		$i = 1;

		$categories = Mage::helper('catalog/category')->getStoreCategories();

		foreach ($categories as $_category){

			if($_category->hasChildren()){

				$_category->getId(); // category id

				if($_category->getId() !=5){

					//foreach($_category->getChildren() as $subcategory){

					//$c = Mage::getModel('catalog/category')->load($subcategory->getId());

					$subcategory = $_category->getChildren();

					$child_id_explode = explode(",",$subcategory);

					

					foreach($child_id_explode as $subcategory)

					{    

						$c = Mage::getModel('catalog/category')->load($subcategory); 

						

						if($c->getIsActive()){ // check if category is active

							$twodays = strtotime(date('y-m-d H:G:00', strtotime("+2 days")));

							$today = strtotime(date('y-m-d H:i:s'));

							if (!$c->getSale_start_date() == null) {

								$startdate = strtotime(date('y-m-d', strtotime($c->getSale_start_date())) . ' ' . $c->getSale_start_time() . ':00');

								$enddate = strtotime(date('y-m-d', strtotime($c->getSale_end_date())) . ' ' . $c->getSale_end_time() . ':00');

								

								if ($this->timeBetweenNowAndDeadline($enddate) != 0 && $enddate < $twodays) {

									if ($i % 2 == 0){

										echo '<article class="saleDetails fl noMargin">';

									} else {

										echo '<article class="saleDetails fl">';

									}

									echo '<a href="'. $c->getURL() .'"><span><img src="'. Mage::getBaseUrl('media') . 'catalog/category/'. $c->getImage() .'"></span></a>';

									echo '<h3><a href="'. $c->getURL() .'">' . $c->getName() . '</a></h3>';

									echo '<p>'. date('M jS \a\t g:ia', $enddate) .'</p>';

									echo '</article>';

									$i++;

								}

							}

						}

					}

				}

			}

		}

		

		

	}

	

	/*

	Upcomming Collction on Home page 

	*/

	

	public function homePageUpcomming()

	{

		

		$i = 1;

		$categories = Mage::helper('catalog/category')->getStoreCategories();

		$sortedArrayByDates = array();

		foreach ($categories as $_category){

			if($_category->hasChildren()){

				$_category->getId(); // category id

				if($_category->getId() !=5){

					//foreach($_category->getChildren() as $subcategory){

					//$c = Mage::getModel('catalog/category')->load($subcategory->getId());

					$subcategory = $_category->getChildren();

					$child_id_explode = explode(",",$subcategory);

					foreach($child_id_explode as $subcategory)

					{    

						$c = Mage::getModel('catalog/category')->load($subcategory); 

						

						if (!$c->getSale_start_date() == null) {

							$tendays = strtotime(date('y-m-d H:G:00', strtotime("+10 days")));

							$today = strtotime(date('y-m-d H:i:s'));

							$startdate = strtotime(date('y-m-d', strtotime($c->getSale_start_date())) . ' ' . $c->getSale_start_time() . ':00');

							$enddate = strtotime(date('y-m-d', strtotime($c->getSale_end_date())) . ' ' . $c->getSale_end_time() . ':00');

							if ($startdate > $today && $startdate < $tendays) {

								$sortedArrayByDates[strtotime($c->getSale_start_date())][] = $c->getName();

							}

						}

					}

				}

			}

		}

		

		ksort($sortedArrayByDates);

		foreach ($sortedArrayByDates as $date => $array)

		{

			if ($i % 2 == 0){

				echo '<article class="upcomingDate noMargin">';

			} else {

				echo '<article class="upcomingDate">';

			}

			echo '<h4>'.date('M d', $date).'</h4>';

			echo '<ul>';

			foreach ($array as $a)

			{

				echo '<li>'.$a.'</li>';

			}

			echo '</ul>';

			echo '</article>';

			$i++;

		}

		

		

	}

	

	public function timeBetweenNowAndDeadline($deadline) {

		//$deadline = strtotime($deadline);

		$currentDate = date("U");

		$diff = $deadline-$currentDate;

		$seconds = 0;

		$hours   = 0;

		$minutes = 0;

		

		if($diff % 86400 <= 0){$days = $diff / 86400;}  // 86,400 seconds in a day

		if($diff % 86400 > 0)

		{

			$rest = ($diff % 86400);

			$days = ($diff - $rest) / 86400;

			if($rest % 3600 > 0)

			{

				$rest1 = ($rest % 3600);

				$hours = ($rest - $rest1) / 3600;

				if($rest1 % 60 > 0)

				{

					$rest2 = ($rest1 % 60);

					$minutes = ($rest1 - $rest2) / 60;

					$seconds = $rest2;

				}

				else{$minutes = $rest1 / 60;}

			}

			else{$hours = $rest / 3600;}

		}

		

		if($days > 0){$days = $days.' days';}

		else{$days = false;}

		if($hours > 0){

			if($days!=false){

				$hours = ' and '.$hours.' hours ';

			}

			else{

				$hours = $hours.' hours ';

			}

		}

		else{$hours = false;}

		if($minutes > 0){$minutes = $minutes.' minutes';}

		else{$minutes = false;}

		$seconds = $seconds.'s'; // always be at least one second

		

		if($days==0 && $hours == 0 && $minutes == 0){

			return 0;

		}

		elseif($days==0 && $hours == 0){

			return $minutes;

		}

		else{

			return $days.''.$hours;	

		}

		

	}

	

}

