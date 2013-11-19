<?php
/**
 * Adminhtml base helper
 *
 * @category   Mageworks
 * @package    Mageworks_Core
 * @author     mageworks kumar <mageworksnsscoe@gmail.com>
 */
class Mageworks_Import_Helper_Category extends Mage_Core_Helper_Abstract
{

	public function getYesNo($val) {
		if (strtolower($val) == 'yes') return 1;
		else return 0;
	}

	protected $_pathids = array(1);
	public function getPathId($level, $catname) {
		$pathid = false;
		$collection = Mage::getModel('catalog/category')->getCollection()
				->addAttributeToSelect('name')
				->addFieldToFilter('level', $level);
		$collection->getSelect()->where("path like '".implode('/', $this->_pathids)."/%'");
		foreach($collection as $category) {
			if ($category->getName() == $catname) {
				$this->_pathids[] = $category->getId();
				$pathid = true;
				break;
			}
		}
		return $pathid;
	}

	public function getCategoryIdFromPath($path, $catname) {
		$paths = explode('/', $path);
		for ($i=0; $i<count($paths); $i++) {
			$pathid = $this->getPathId($i+1, $paths[$i]);
			if (!$pathid) break;
		}
		if (count($this->_pathids) == count($paths)+1) {
			$pathid = $this->getPathId($i+1, $catname);
			if (!$pathid) return 0;
			else return end($this->_pathids);
		} else return array();
	}

	public function getFullPath($val) {
		$paths = explode('/', $val);
		if (count($this->_pathids) >= count($paths)) return implode('/', $this->_pathids);
		else return false;
	}

	protected $_displayModes;
	public function getDisplayMode($val) {
		if (is_null($this->_displayModes)) {
			foreach (Mage::getModel('catalog/category_attribute_source_mode')->getAllOptions() as $mode) {
				$this->_displayModes['value'][] = $mode['value'];
				$this->_displayModes['label'][] = $mode['label'];
			}
		}
		return str_replace($this->_displayModes['label'], $this->_displayModes['value'], $val);
	}

	protected $_staticBlocks = array();
	public function getStaticBlock($val) {
		if (!$val) return '';

		if (!isset($this->_staticBlocks[$val])) {
			$model = Mage::getModel('cms/block')->load($val, 'identifier');
			$this->_staticBlocks[$val] = array ('id' => $model->getId());
		}
		return $this->_staticBlocks[$val]['id'];
	}

	protected $_productSortBy;
	public function getProductSortBy($val) {
		if (!$val) return '';

		if (is_null($this->_productSortBy)) {
			$sortby = Mage::getModel('catalog/category_attribute_source_sortby')->getAllOptions();
			foreach ($sortby as $sort) {
				$this->_productSortBy['value'][] = $sort['value'];
				$this->_productSortBy['label'][] = $sort['label'];
			}
		}
		return str_replace($this->_productSortBy['label'], $this->_productSortBy['value'], $val);
	}

	protected $_pageLayout;
	public function getPageLayout($val) {
		if (is_null($this->_pageLayout)) {
			$layouts = Mage::getModel('catalog/category_attribute_source_layout')->getAllOptions();
			foreach ($layouts as $layout) {
				$this->_pageLayout[$layout['label']] = $layout['value'];
			}
		}
		return $this->_pageLayout[$val];
	}
}