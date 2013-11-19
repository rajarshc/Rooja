<?php

/**
 * Import category block
 *
 * @category   Mageworks
 * @package    Mageworks_Import
 * @author     Mageworks <magentoworks.net@gmail.com>
 */
class Mageworks_Import_Block_Adminhtml_Category_Import extends Mageworks_Import_Block_Adminhtml_Import
{

	public function getTitle($type) {
		$types = array(
			'categories' => $this->__('Import Categories'),
			'attributes' => $this->__('Import Category Attributes')
		);
		if (isset($types[$type])) return $types[$type];
		return '';
	}

	public function getFilePath() {
		return 'var' . DS . 'import' . DS . 'category';
	}

	public function getFileName($type) {
		$types = array(
			'categories' => 'categories.csv',
			'attributes' => 'attributes.csv'
		);
		if (isset($types[$type])) return $types[$type];
		return '';
	}

	public function showExportInfo($type) {
		return $this->getLayout()->createBlock('mageworks_import/adminhtml_category_import')
				->setTemplate('import/category/'.$type.'.phtml')
				->toHtml();
	}

	public function getAttributes() {
		return $this->helper('mageworks_core/category')->getAttributes();
	}

}
