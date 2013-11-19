<?php

/**
 * Export category block
 *
 * @category   Mageworks
 * @package    Mageworks_Export
 * @author     Mageworks <magentoworks.net@gmail.com>
 */
class Mageworks_Export_Block_Adminhtml_Category_Export_Categories extends Mageworks_Export_Block_Adminhtml_Category_Export
{

	public function _toHtml() {
		$type = $this->getRequest()->getParam('type');
		$title = $this->getTitle($type);

		$this->getHeadInfo($title);
		$this->getStartUpInfo();
		if ($title) {
			try {
				$this->showSuccess($this->__("Starting export profile execution."));
				$collection = Mage::getModel('catalog/category')->getCollection()
						->addAttributeToSelect('level');
				$collection = $this->addAttributesToSelect($collection);
				$collection->getSelect()->order('level');

				$Ids = $collection->getAllIds();
				$this->showSuccess($this->__("Loaded %d records", count($Ids)));

				$this->openToExport($this->getFilePath(), $this->getFile());
				$this->saveCsvHeader($this->getCsvData(''));

				foreach($collection as $row) {
					$this->setCategory($row);
					if ($row->getLevel() > 1) {
						$this->saveCsvContent($this->getCsvData(''), $row);
					} else {
						$this->showWarning($this->__('Excluded the category "%s"', $row->getName()));
					}
				}
				$this->closeFile();
				$this->showSuccess($this->__('Saved to the file "%s" successfully [%s bytes(s)]', $this->getFilePath() . DS . $this->getFile(), $this->_fileSize));

			} catch (Exception $e) {
				$this->showError($e->getMessage());
			}
		}
		$this->getEndInfo();
	}

	public function getFile() {
		//$retval = 'allCategories.csv';
		$retval = 'categories.csv';
		return $retval;
	}

	protected $_csvData;
	public function getCsvData($opt = '') {
		if ($opt == 'all') {
			if (is_null($this->_csvData)) {
				$collection = Mage::getModel('mageworks_core/attribute')->getCollection()
						->addCatalogEav()
						->setAttributeType('catalog_category');

				$this->_csvData = array();
				foreach ($collection as $attribute) {
					$this->_csvData[] = array('field' => $attribute->getAttributeCode(), 'label' => $attribute->getAttributeCode());
					//$this->_csvData[] = array('field' => $attribute->getAttributeCode(), 'label' => $attribute->getFrontendLabel());
				}
			}
			return $this->_csvData;
		} else {
			return Mage::helper('mageworks_core/category')->getAttributes();
		}
	}

	public function addAttributesToSelect($collection) {
		foreach ($this->getCsvData() as $data) {
			$collection->addAttributeToSelect($data['field']);
		}
		return $collection;
	}

	protected $_categories = array();
	public function setCategory($row) {
		$this->_categories[$row->getId()] = array('name' => $row->getName());
	}

	public function getFullPath($val) {
		$paths = explode('/', $val);
		$pathnames = array();
		for ($i=1; $i<count($paths)-1; $i++) {
			if ($name = $this->_categories[$paths[$i]]) $pathnames[] = $name['name'];
		}
		return implode('/', $pathnames);
	}

	protected $_displayModes;
	public function getDisplayMode($val) {
		if (is_null($this->_displayModes)) {
			foreach (Mage::getModel('catalog/category_attribute_source_mode')->getAllOptions() as $mode) {
				$this->_displayModes['value'][] = $mode['value'];
				$this->_displayModes['label'][] = $mode['label'];
			}
		}
		return str_replace($this->_displayModes['value'], $this->_displayModes['label'], $val);
	}

	protected $_staticBlocks = array();
	public function getStaticBlock($val) {
		if (!$val) return '';

		$id = (int) $val;
		if (!isset($this->_staticBlocks[$id])) {
			$model = Mage::getModel('cms/block')->load($id);
			$this->_staticBlocks[$id] = array ('identifier' => $model->getIdentifier());
		}
		return $this->_staticBlocks[$id]['identifier'];
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
		return str_replace($this->_productSortBy['value'], $this->_productSortBy['label'], $val);
	}

	protected $_pageLayout;
	public function getPageLayout($val) {
		if (is_null($this->_pageLayout)) {
			$layouts = Mage::getModel('catalog/category_attribute_source_layout')->getAllOptions();
			foreach ($layouts as $layout) {
				$this->_pageLayout[$layout['value']] = $layout['label'];
			}
		}
		return $this->_pageLayout[$val];
	}

}