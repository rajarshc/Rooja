<?php

/**
 * Import category block
 *
 * @category   Mageworks
 * @package    Mageworks_Import
 * @author     Mageworks <magentoworks.net@gmail.com>
 */
class Mageworks_Import_Block_Adminhtml_Category_Import_Categories extends Mageworks_Import_Block_Adminhtml_Category_Import
{

	public function _toHtml() {
		$type = $this->getRequest()->getParam('type');
		$title = $this->getTitle($type);

		$this->getHeadInfo($title);
		$this->getImportJavascripts($this->getUrl('*/import_category/'.$type));
		$this->getStartUpInfo();
		if ($title) {
			try {
				$this->openToImport($this->getFilePath(), $this->getFile());
				$i = 0;
				while($csvData = $this->readCsvData()) {
					if ($i == 0) {
						$this->addHeaderData($csvData);
					} else {
						$this->addContentData($csvData, 'add');
					}
					$i++;
				}
				$this->closeFile('send');

			} catch (Exception $e) {
				$this->showError($e->getMessage());
			}
		}
		$this->getEndInfo();
	}

	public function getFile() {
		$retval = $this->getRequest()->getPost('filename');
		if (!$retval) $retval = 'categories.csv';
		return $retval;
	}

	protected $_headerCodes;
	public function getHeaderField($header) {
		if (is_null($this->_headerCodes)) {
			$this->_headerCodes = array();
			$attributes = Mage::helper('mageworks_core/category')->getAttributes();
			foreach($attributes as $attribute) {
				$this->_headerCodes[$attribute['label']] = $attribute['field'];
			}
		}
		if (isset($this->_headerCodes[$header])) return $this->_headerCodes[$header];
		else {
			Mage::throwException($this->__('The header field %s is not a valid one.', $header));
		}
	}

}