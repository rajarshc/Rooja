<?php
class Magestore_Affiliatepluslevel_Block_Adminhtml_Program_Tier
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element){
		$this->setElement($element);
		return $this->_toHtml();
	}
	
	public function render(Varien_Data_Form_Element_Abstract $element){
		$id = $element->getHtmlId();
		$html = '<tr><td class="label"><label for="'.$id.'">'.$element->getLabel().'</label></td>';
		$html .= '<td class="value">'.$this->_getElementHtml($element).$element->getAfterElementHtml().'</td>';
		return $html;
	}
	
	/**
	 * Constructor for block 
	 * 
	 */
	public function __construct(){
		parent::__construct();
		$this->setTemplate('affiliatepluslevel/tier.phtml');
	}
	
	public function getHtmlId(){
		return 'grid_tier_commission';
	}
	
	public function getMaxLevel(){
		$data = $this->getProgramData();
		$_maxLevel = isset($data['max_level']) ? intval($data['max_level']) : 1;
		return ($_maxLevel > 0) ? $_maxLevel : 1;
	}
	
	public function getArrayRows(){
		if ($this->hasData('_array_rows_cache')) return $this->getData('_array_rows_cache');
		
		$result = array();
		$element = $this->getElement();
		if ($element->getValue() && is_array($element->getValue())){
			foreach ($element->getValue() as $rowId => $row){
				foreach ($row as $key => $value) {
					$row[$key] = $this->htmlEscape($value);
				}
				$row['_id'] = $rowId;
				$result[$rowId] = new Varien_Object($row);
			}
		}
		$this->setData('_array_rows_cache',$result);
		
		return $this->getData('_array_rows_cache');
	}
	
	public function getDefaultCommission(){
		$data = $this->getProgramData();
		return isset($data['commission']) ? $data['commission'] : 0;
	}
	
	public function getDefaultCommissionType(){
		$data = $this->getProgramData();
		return isset($data['commission_type']) ? $data['commission_type'] : 'percentage';
	}
}