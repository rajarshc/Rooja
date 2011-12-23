<?php

/**
 * J2T-DESIGN.
 *
 * @category   J2t
 * @package    J2t_Ajaxcheckout
 * @copyright  Copyright (c) 2003-2009 J2T DESIGN. (http://www.j2t-design.com)
 * @license    GPL
 */

class J2t_Ajaxcheckout_IndexController extends /*Mage_Checkout_CartController*/ Mage_Core_Controller_Front_Action
{

    const CONFIGURABLE_PRODUCT_IMAGE= 'checkout/cart/configurable_product_image';
    const USE_PARENT_IMAGE          = 'parent';

    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function cartdeleteAction()
    {
        $id = $this->getRequest()->getParam('id');
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                Mage::getSingleton('checkout/cart')->removeItem($id)
                  ->save();
            } catch (Exception $e) {
                Mage::getSingleton('checkout/session')->addError($this->__('Cannot remove item'));
            }
        }
        $this->loadLayout();
        $this->_initLayoutMessages('checkout/session');

        $this->renderLayout();
    }

    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    public function cartAction()
    {
        if ($this->getRequest()->getParam('cart')){
            if ($this->getRequest()->getParam('cart') == "delete"){
                $id = $this->getRequest()->getParam('id');
                if ($id) {
                    try {
                        Mage::getSingleton('checkout/cart')->removeItem($id)
                          ->save();
                    } catch (Exception $e) {
                        Mage::getSingleton('checkout/session')->addError($this->__('Cannot remove item'));
                    }
                }
            }
        }
		
		/* Customizing */
		$session = Mage::getSingleton("core/session",  array("name"=>"frontend"));
		$pid = $this->getRequest()->getParam('product');
		
		$model = Mage::getModel('catalog/product'); 
		$_product = $model->load($pid); 
		$stocklevel = (int)Mage::getModel('cataloginventory/stock_item')
                ->loadByProduct($_product)->getQty();
				
		/* Code for checking the current associated attribute */
		$params1 = $this->getRequest()->getParams();
		$attribute1 = $params1['super_attribute'];
		$associatedProductID = 0;
		foreach($attribute1 as $attr=>$key)
		{
		 $associatedSize = $attribute1[$attr];
		}
		
		if(isset($params1['super_attribute']))	  
		{
		  $childProducts = Mage::getModel('catalog/product_type_configurable')
						  ->getUsedProducts(null,$_product);
	  
		  foreach($childProducts as $child)
		  {
		   if($associatedSize==$child->getSize())
		   {
		   $associatedProductID = $child->getEntityId();
		   }
		  }
		  
		}
		/* Code for checking the current associated attribute ends */
		
		if(isset($params1['super_attribute']))	
		{
		$_associated = $model->load($associatedProductID); 
		$stocklevel = (int)Mage::getModel('cataloginventory/stock_item')
                ->loadByProduct($_associated)->getQty();
		}
		else
		{
		}
		
		$cartHelper = Mage::helper('checkout/cart');
		$items = $cartHelper->getCart()->getItems();
		
		$currentRequestCount = 0;
		
		$session->setData("isValidAjaxAdd", "yes");
		$session->setData("currentProduct", $pid);
		
		$skip = "nskip";
		$rearray = array();
		
		if(isset($params1['super_attribute']))	
		{
			$isNewAssociated = "yes";
			$aCount = 0;
			
			  $items = Mage::getSingleton('checkout/cart')->getQuote()->getAllItems();

			  $associatedArray = array();
			  $assCount = 0;
			  $previous = "";
			  $sp_qty = -1;

foreach($items as $item) {

	if($previous=="configurable")
	{
	 $associatedArray[$assCount]['pid'] = $item->getProductId();
	 $associatedArray[$assCount]['count'] = $sp_qty;
	 $associatedArray[$assCount]['name'] = $item->getName();
	 $sp_qty = -1;
	 $assCount++;
	 $previous = "";
	}
	
	if($item->getProductType()=="configurable")
	{
	 $sp_qty = $item->getQty();
	 $previous = "configurable";
	}
	
}

$currentAssociatedCount = 0;

foreach($associatedArray as $ass=>$key)
{
	if($associatedArray[$ass]['pid']==$associatedProductID)
	{
		$currentAssociatedCount = $associatedArray[$ass]['count'];
		echo "Product Name : ".$associatedArray[$ass]['name']."  ";
	}
}

$assRequested = 1+$currentAssociatedCount;

$assObject = $model->load($associatedProductID); 
$assStock = (int)Mage::getModel('cataloginventory/stock_item')
		->loadByProduct($assObject)->getQty();

$detailsArray = array();
$detailsCount = 0;

if($assRequested<=$assStock)
{
 //echo "Allow Associated Processing";
}
else if($assRequested>$assStock)
{
 //echo "Deny Associated Processing";
 
 $detailsArray[$detailsCount]['id'] = $this->getRequest()->getParam('product');
 
 $attri = $assObject->getResource()->getAttribute("size");
 
 $detailsArray[$detailsCount]['size'] = Mage::getModel('catalog/product')
                            ->load($assObject->getId())
                            ->getAttributeText('size');
 $detailsArray[$detailsCount]['allow'] = "no";
 $skip = "skip";
}

$session->setData("detailsArray",$detailsArray);
			  
			  
		}
		else
		{
		/* Simple Product Processing */
  			foreach ($items as $item) {

        	$itemId = $item->getProductId();
        	$itemCount=$item->getQty();
			
			if($itemId==$pid)
			{
				$paramsa = $this->getRequest()->getParams();
				if (!isset($paramsa['qty']))
				{
					$currentRequestCount = 1; 
				}
				else
				if (isset($paramsa['qty']))
				{
					$currentRequestCount = $paramsa['qty'];
				}
				
				$requested = $currentRequestCount+$itemCount;
				
				if($requested<=$stocklevel)
				{
				 echo "<strong>Allow Processing</strong>";
				}
				else if($requested>$stocklevel)
				{
				 echo "<strong>Deny Processing</strong>";
				 $session->setData("isValidAjaxAdd", "no");
				 $skip = "skip";
				}
			}
			
  		}
		/* Simple Product Check ends */
		}
			  		
		if($skip=="skip")
		{
		 //$session->setData("isValidAjaxAdd", "no");   		 
		}
		else if($skip=="nskip")
		{
			
        	if ($this->getRequest()->getParam('product')) {
            $cart   = Mage::getSingleton('checkout/cart');
            $params = $this->getRequest()->getParams();
			
            $related = $this->getRequest()->getParam('related_product');

            $productId = (int) $this->getRequest()->getParam('product');


            if ($productId) {
                $product = Mage::getModel('catalog/product')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->load($productId);
                try {

                    if (!isset($params['qty'])) {
                        $params['qty'] = 1;
                    }

                    $cart->addProduct($product, $params);
						
                    if (!empty($related)) {
                        $cart->addProductsByIds(explode(',', $related));
                    }
					
                    $cart->save();
                    $this->_getSession()->setCartWasUpdated(true);

                    Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
                    Mage::getSingleton('checkout/session')->setCartInsertedItem($product->getId());

                    $img = '';
                    Mage::dispatchEvent('checkout_cart_add_product_complete', array('product'=>$product, 'request'=>$this->getRequest()));

                    $photo_arr = explode("x",Mage::getStoreConfig('j2tajaxcheckout/default/j2t_ajax_cart_image_size', Mage::app()->getStore()->getId()));

                    $prod_img = $product;
                    if($product->isConfigurable() && isset($params['super_attribute'])){
						
                        $attribute = $params['super_attribute'];
						
                        if (Mage::getStoreConfig(self::CONFIGURABLE_PRODUCT_IMAGE) != self::USE_PARENT_IMAGE) {
                            $prod_img_temp = Mage::getModel("catalog/product_type_configurable")->getProductByAttributes($attribute, $product);
                            if ($prod_img_temp->getImage() != 'no_selection' && $prod_img_temp->getImage()){
                                $prod_img = $prod_img_temp;
                            }
                        }
                    }
                    $img = '<img src="'.Mage::helper('catalog/image')->init($prod_img, 'thumbnail')->resize($photo_arr[0],$photo_arr[1]).'" width="'.$photo_arr[0].'" height="'.$photo_arr[1].'" />';
                    $message = $this->__('%s was successfully added to your shopping cart.', $product->getName());

                    Mage::getSingleton('checkout/session')->addSuccess('<div class="j2tajax-checkout-img">'.$img.'</div><div class="j2tajax-checkout-txt">'.$message.'</div>');
                }
                catch (Mage_Core_Exception $e) {
                    if (Mage::getSingleton('checkout/session')->getUseNotice(true)) {
                        Mage::getSingleton('checkout/session')->addNotice($e->getMessage());
                    } else {
                        $messages = array_unique(explode("\n", $e->getMessage()));
                        foreach ($messages as $message) {
                            Mage::getSingleton('checkout/session')->addError($message);
                        }
                    }
                }
                catch (Exception $e) {
                    Mage::getSingleton('checkout/session')->addException($e, $this->__('Can not add item to shopping cart'));
                }

            }
        }		
	} // else for valid ajax check
	
		/* Customizing ends*/
		
        $this->loadLayout();
        $this->_initLayoutMessages('checkout/session');
        $this->renderLayout();
    }


    public function productoptionAction()
    {
        //getProductUrlSuffix
        echo 'ici';
        die;
    }

    public function addtocartAction()
    {
        $this->indexAction();
    }



    public function preDispatch()
    {
        parent::preDispatch();
        $action = $this->getRequest()->getActionName();
    }


}