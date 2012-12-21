<?php
class Fix_FixBlockCacheInvalidated_Model_Rule extends Mage_CatalogRule_Model_Rule
{
   /**
     * Apply all price rules to product
     *
     * @param int|Mage_Catalog_Model_Product $product
     * @return Mage_CatalogRule_Model_Rule
     */
    public function applyAllRulesToProduct($product)
	{
		$this->_getResource()->applyAllRulesForDateRange(NULL, NULL, $product);
		$this->_invalidateCache();
		//Notice this little line
		Mage::app()->getCacheInstance()->cleanType('block_html');
		Mage::log("cleared");
		$indexProcess = Mage::getSingleton('index/indexer')->getProcessByCode('catalog_product_price');
		if ($indexProcess) {
			$indexProcess->reindexAll();
		}
	}
}
?>