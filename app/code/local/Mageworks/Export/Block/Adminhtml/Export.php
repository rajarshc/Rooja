<?php

/**
 * Export product block
 *
 * @category   Mageworks
 * @package    Mageworks_Export
 * @author     Mageworks <magentoworks.net@gmail.com>
 */
class Mageworks_Export_Block_Adminhtml_Export extends Mage_Adminhtml_Block_Abstract
{

	public function getExportUrl($action, $type) {
		return $this->getUrl('*/*/'.$action, array('type' => $type));
	}

	public function getHeadInfo($title) {
		echo '<html><head>';
		echo '<title>'.$title.'</title>';
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
		echo '<script type="text/javascript">var FORM_KEY = "'.Mage::getSingleton('core/session')->getFormKey().'";</script>';

		$headBlock = $this->getLayout()->createBlock('page/html_head');
		$headBlock->addJs('prototype/prototype.js');
		$headBlock->addJs('mage/adminhtml/loader.js');
		echo $headBlock->getCssJsHtml();

		echo '<style type="text/css">';
		echo 'ul { list-style-type:none; padding:0; margin:0; }';
		echo 'li { margin-left:0; border:1px solid #ccc; margin:2px; padding:2px 2px 2px 2px; font:normal 12px sans-serif; }';
		echo 'img { margin-right:5px; }';
		echo 'a.back {color: #ED6502; font-weight: bold; text-decoration: none; }';
		echo '</style>';
		echo '</head>';
	}

	public function getStartUpInfo() {
		echo '<body>';
		echo '<ul>';
		$this->showNote($this->__("Starting profile execution, please wait..."));
		$this->showWarning($this->__("Warning: Please do not close the window during exporting data"));
		echo '</ul>';
		echo '<ul id="profileRows">';
	}

	public function getEndInfo() {
		$this->showNote($this->__("Finished profile execution."));
		echo "</ul>";
		echo '</body></html>';
	}

	public function showError($text, $id = '') {
		echo '<li style="background-color:#FDD; " id="'.$id.'">';
		echo '<img src="'.Mage::getDesign()->getSkinUrl('images/error_msg_icon.gif').'" class="v-middle"/>';
		echo $text;
		echo "</li>";
	}

	public function showWarning($text, $id = '') {
		echo '<li id="'.$id.'" style="background-color:#FFD;">';
		echo '<img src="'.Mage::getDesign()->getSkinUrl('images/fam_bullet_error.gif').'" class="v-middle" style="margin-right:5px"/>';
		echo $text;
		echo '</li>';
	}

	public function showNote($text, $id = '') {
		echo '<li id="'.$id.'">';
		echo '<img src="'.Mage::getDesign()->getSkinUrl('images/note_msg_icon.gif').'" class="v-middle" style="margin-right:5px"/>';
		echo $text;
		echo '</li>';
	}

	public function showSuccess($text, $id = '') {
		echo '<li id="'.$id.'" style="background-color:#DDF;">';
		echo '<img src="'.Mage::getDesign()->getSkinUrl('images/fam_bullet_success.gif').'" class="v-middle" style="margin-right:5px"/>';
		echo $text;
		echo '</li>';
	}

	protected $_resource;
	protected $_fileSize;
	public function openToExport($path, $file) {
		$baseDir = Mage::getBaseDir();
		$this->_resource = new Varien_Io_File();
		$filepath = $this->_resource->getCleanPath($baseDir . DS . trim($path, DS));
		$this->_resource->checkAndCreateFolder($filepath);
		$realPath = realpath($filepath);

		if ($realPath === false) {
			$message = $this->__('The destination folder "%s" does not exist or there is no access to create it.', $path);
			Mage::throwException($message);
		}
		elseif (!is_dir($realPath)) {
			$message = $this->__('Destination folder "%s" is not a directory.', $realPath);
			Mage::throwException($message);
		}
		else {
			if (!is_writeable($realPath)) {
				$message = $this->__('Destination folder "%s" is not writable.', $realPath);
				Mage::throwException($message);
			}
			else {
				$filepath = rtrim($realPath, DS);
			}
		}
		try {
			$this->_resource->open(array('path' => $filepath));
			$this->_resource->streamOpen($file, 'w+');
			$this->_fileSize = 0;
		} catch (Exception $e) {
			$message = Mage::helper('dataflow')->__('An error occurred while opening file: "%s".', $e->getMessage());
			Mage::throwException($message);
		}
	}

	public function saveToFile($str) {
		$this->_fileSize += strlen($str);
		$this->_resource->streamWrite($str);
	}

	public function closeFile() {
		$this->_resource->streamClose();
	}

	public function saveCsvHeader($data) {
		$str = '';
		foreach ($data as $value) {
			$header = (isset($value['label'])) ? $value['label'] : $value['field'];
			$str .= '"'.$header.'"' . ',';
		}
		$string = substr($str, 0, -1) . "\n";
		$this->saveToFile($string);
	}

	public function saveCsvContent($data, $model) {
		$search = array('\"');
		$replace = array('""');

		$str = '';
		foreach ($data as $head => $value) {
			$str1 = $model->getData($value['field']);
			if (isset($value['function'])) {
				$str1 = $this->$value['function']($str1);
			}
			$csvstr = str_replace($search, $replace, addslashes($str1));
			$str .= '"'.$csvstr.'"' . ',';
		}
		$string = substr($str, 0, -1) . "\n";
		$this->saveToFile($string);
	}

	public function getYesNo($val) {
		if ($val == 1) return 'Yes';
		else return 'No';
	}

	public function getScope($val) {
		$retarray = Mage::getModel('mageworks_core/attribute')->getScopes();
		if (isset($retarray[$val])) return $retarray[$val];
		return $val;
	}

	public function getLayeredNavigation($val) {
		$retarray = Mage::getModel('mageworks_core/attribute')->getLayeredNavigations();
		if (isset($retarray[$val])) return $retarray[$val];
		return $val;
	}

	public function getInputType($val) {
		$retarray = Mage::getModel('mageworks_core/attribute')->getInputTypes();
		if (isset($retarray[$val])) return $retarray[$val];
		return $val;
	}

	public function getInputValidation($val) {
		$retarray = Mage::getModel('mageworks_core/attribute')->getInputValidations();
		if (isset($retarray[$val])) return $retarray[$val];
		return $val;
	}

	protected $_productTypes;
	public function getProductType($val) {
		if (is_null($this->_productTypes)) {
			$this->_productTypes = array();
			$retarray = Mage_Catalog_Model_Product_Type::getOptions();
			foreach (Mage_Catalog_Model_Product_Type::getOptions() as $value) {
				$this->_productTypes['value'][] = $value['value'];
				$this->_productTypes['label'][] = $value['label'];
			}
		}
		$val = str_replace($this->_productTypes['value'], $this->_productTypes['label'], $val);
		return $val;
	}

}
