<?php

/**
 * Export category block
 *
 * @category   Mageworks
 * @package    Mageworks_Export
 * @author     Mageworks <magentoworks.net@gmail.com>
 */
class Mageworks_Export_Block_Adminhtml_Category_Export extends Mageworks_Export_Block_Adminhtml_Export
{

	public function getTitle($type) {
		$types = array(
			'categories' => $this->__('Export Categories'),
			'attributes' => $this->__('Export Category Attributes')
		);
		if (isset($types[$type])) return $types[$type];
		return '';
	}

	public function getFilePath() {
		return 'var' . DS . 'export' . DS . 'category';
	}

}
