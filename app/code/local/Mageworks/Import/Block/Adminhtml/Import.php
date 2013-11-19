<?php

/**
 * Import product block
 *
 * @category   Mageworks
 * @package    Mageworks_Import
 * @author     Mageworks <magentoworks.net@gmail.com>
 */
class Mageworks_Import_Block_Adminhtml_Import extends Mage_Adminhtml_Block_Abstract
{

	public function getImportUrl($action, $type) {
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

	public function getImportJavascripts($url) {
		$batchConfig = array(
			'styles' => array(
				'error' => array(
					'icon' => Mage::getDesign()->getSkinUrl('images/error_msg_icon.gif'),
					'bg'   => '#FDD'
				),
				'message' => array(
					'icon' => Mage::getDesign()->getSkinUrl('images/fam_bullet_success.gif'),
					'bg'   => '#DDF'
				),
				'loader'  => Mage::getDesign()->getSkinUrl('images/ajax-loader.gif')
			),
			'template' => '<li style="#{style}" id="#{id}">'
						. '<img id="#{id}_img" src="#{image}" class="v-middle" style="margin-right:5px"/>'
						. '<span id="#{id}_status" class="text">#{text}</span>'
						. '</li>'
		);
		echo '<script>';
		echo 'var importData = [];';
		echo 'var config= '.Mage::helper('core')->jsonEncode($batchConfig).';';
		echo '
		function addImportData(data) {
			importData.push(data);
		}

		function sendAllData() {
			sendImportData();
		}';
		echo
		'var importCount = 0;
		function sendImportData(data) {
			var cImport = "async";
			if (!data) {
				if (importData.length == 0) return;
				data = importData.shift();
				cImport = "sync";
			}
			if (!data.form_key) {
				data.form_key = FORM_KEY;
			}

			new Ajax.Request("'.$url.'", {
				method: "post",
				parameters: data,
				onSuccess: function(transport) {
					importCount++;
					if (transport.responseText.isJSON()) {
						var retdata = transport.responseText.evalJSON();
						if (retdata.error) {
							$("row_content_"+retdata.count+"").style.backgroundColor = config.styles.error.bg;
							$("row_content_"+retdata.count+"_img").src = config.styles.error.icon;
							$("row_content_"+retdata.count+"_text").innerHTML = retdata.error;
						} else {
							$("row_content_"+retdata.count+"").style.backgroundColor = config.styles.message.bg;
							$("row_content_"+retdata.count+"_img").src = config.styles.message.icon;
							$("row_content_"+retdata.count+"_text").innerHTML = retdata.message;
						}
					} else {
						$("row_content_"+data.count+"").style.backgroundColor = config.styles.error.bg;
						$("row_content_"+data.count+"_img").src = config.styles.error.icon;
						//$("row_content_"+data.count+"_text").innerHTML = "'.$this->__('Problem in server.').'";
						$("row_content_"+data.count+"_text").innerHTML = transport.responseText;
					}
					if (importCount == totalCount) {
						$("finished_exec").style.display = "";
					}
					if (cImport == "sync") {
						sendImportData();
					}
				}
			});
		}';
		echo '</script>';
	}

	public function getStartUpInfo() {
		echo '<body>';
		echo '<ul id="profileRowStart">';
		$this->showNote($this->__("Starting profile execution, please wait..."));
		$this->showWarning($this->__("Warning: Please do not close the window during importing data"));
		echo '</ul>';
		echo '<ul id="profileRows">';
	}

	public function getEndInfo() {
		echo '</ul>';
		echo '<ul id="finished_exec" style="display:none;">';
		$this->showNote($this->__("Finished profile execution."));
		$this->showNote('<a href="'.$this->getUrl('*/cache').'">'.$this->__('Please refresh the cache.').'</a>');
		echo "</ul>";
		echo '<script>var totalCount = '.$this->_count.';</script>';
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

	public function showNote($text, $id = '', $style = '') {
		echo '<li id="'.$id.'" style="'.$style.'">';
		echo '<img src="'.Mage::getDesign()->getSkinUrl('images/note_msg_icon.gif').'" class="v-middle" style="margin-right:5px"/>';
		echo $text;
		echo '</li>';
	}

	public function showSuccess($text, $id = '', $style = '') {
		echo '<li id="'.$id.'" style="background-color:#DDF; '.$style.'">';
		echo '<img src="'.Mage::getDesign()->getSkinUrl('images/fam_bullet_success.gif').'" class="v-middle" style="margin-right:5px"/>';
		echo $text;
		echo '</li>';
	}

	public function showProgress($text, $id = '', $style = '') {
		echo '<li id="'.$id.'" style="background-color:#DDF; '.$style.'">';
		echo '<img id="'.$id.'_img" src="'.Mage::getDesign()->getSkinUrl('images/ajax-loader.gif').'" class="v-middle" style="margin-right:5px"/>';
		echo '<span id="'.$id.'_text">'.$text.'<span>';
		echo '</li>';
	}

	protected $_resource;
	public function openToImport($path, $file) {
		$baseDir = Mage::getBaseDir();
		$this->_resource = new Varien_Io_File();
		$filepath = $this->_resource->getCleanPath($baseDir . DS . trim($path, DS));
		$realPath = realpath($filepath);

		if ($realPath === false) {
			$message = $this->__('The destination folder "%s" does not exist.', $path);
			Mage::throwException($message);
		}
		elseif (!is_dir($realPath)) {
			$message = $this->__('Destination folder "%s" is not a directory.', $realPath);
			Mage::throwException($message);
		}
		else {
			$filepath = rtrim($realPath, DS);
		}
		try {
			$this->_resource->open(array('path' => $filepath));
			$this->_resource->streamOpen($file, 'r+');
		} catch (Exception $e) {
			$message = $this->__('An error occurred while opening file: "%s".', $e->getMessage());
			Mage::throwException($message);
		}
		$this->showSuccess($this->__("Starting import profile execution."));
		$this->showSuccess($this->__('Found <span id="row_count">0</span> rows.'));
	}

	public function readCsvData() {
		return $this->_resource->streamReadCsv();
	}

	public function closeFile($opt = '') {
		$this->_resource->streamClose();
		if ($opt == 'send') {
			echo '<script>sendAllData();</script>';
		}
	}

	protected $_csvHeader = array();
	public function addHeaderData($data) {
		foreach ($data as $val) {
			$this->_csvHeader[] = $this->getHeaderField($val);
		}
	}

	protected $_count = 0;
	public function addContentData($data, $opt = 'send') {
		$this->_count++;

		$contentData = array('count' => $this->_count);
		foreach($data as $key => $val) {
			if (isset($this->_csvHeader[$key])) $contentData[$this->_csvHeader[$key]] = $val;
		}
		$this->showProgress($this->__("Saving row no %d.", $this->_count), 'row_content_'.$this->_count);
		echo '<script>';
		echo '$("row_count").innerHTML = "'.$this->_count.'";';
		if ($opt == 'add') {
			echo 'addImportData('.Mage::helper('core')->jsonEncode($contentData).');';
		} else {
			echo 'sendImportData('.Mage::helper('core')->jsonEncode($contentData).');';
		}
		echo '</script>';
	}

}
