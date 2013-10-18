<?php
class Magestore_Affiliateplusstatistic_Block_Left_Pie extends Mage_Adminhtml_Block_Dashboard_Graph
{
	protected $_google_chart_params = array();
	protected $_is_has_data = false;
	
	protected $_width = '200';
	protected $_height = '175';
	
	public function __construct(){
		parent::__construct();
		$this->setTemplate('affiliateplusstatistic/graph.phtml');
	}
	
	public function isHasData(){
		return $this->_is_has_data;
	}
	
    /**
	 * Get chart url
	 *
	 * @param bool $directUrl
	 * @return string
	 */
	public function getChartUrl($directUrl = true){
		$params = $this->_google_chart_params;
		
		// chart size
		$params['chs'] = $this->getWidth().'x'.$this->getHeight();

		// return the encoded data
		if ($directUrl) {
			$p = array();
			foreach ($params as $name => $value) {
				$p[] = $name . '=' .urlencode($value);
			}
			return self::API_URL . '?' . implode('&', $p);
		} else {
			$gaData = urlencode(base64_encode(serialize($params)));
			$gaHash = Mage::helper('adminhtml/dashboard_data')->getChartDataHash($gaData);
			$params = array('ga' => $gaData, 'h' => $gaHash);
			return $this->getUrl('*/*/tunnel', array('_query' => $params));
		}
	}
}