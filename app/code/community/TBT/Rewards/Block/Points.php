<?php
/**
 * WDCA - Sweet Tooth
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the WDCA SWEET TOOTH POINTS AND REWARDS 
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth License is available at this URL: 
 * http://www.wdca.ca/sweet_tooth/sweet_tooth_license.txt
 * The Open Software License is available at this URL: 
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * DISCLAIMER
 * 
 * By adding to, editing, or in any way modifying this code, WDCA is 
 * not held liable for any inconsistencies or abnormalities in the 
 * behaviour of this code. 
 * By adding to, editing, or in any way modifying this code, the Licensee
 * terminates any agreement of support offered by WDCA, outlined in the 
 * provided Sweet Tooth License. 
 * Upon discovery of modified code in the process of support, the Licensee 
 * is still held accountable for any and all billable time WDCA spent 
 * during the support process.
 * WDCA does not guarantee compatibility with any other framework extension. 
 * WDCA is not responsbile for any inconsistencies or abnormalities in the
 * behaviour of this code if caused by other framework extension.
 * If you did not receive a copy of the license, please send an email to 
 * contact@wdca.ca or call 1-888-699-WDCA(9322), so we can send you a copy 
 * immediately.
 * 
 * @category   [TBT]
 * @package    [TBT_Rewards]
 * @copyright  Copyright (c) 2009 Web Development Canada (http://www.wdca.ca)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

// This is a simple way to get around the change in the method signature of the __toString method.
$code = <<<FEED

class TBT_Rewards_Block_Points extends Mage_Core_Block_Template
{
	/**
	 * Data model for this block
	 *
	 * @var TBT_Rewards_Model_Points
	 */
	protected \$data_model = null;
	
    /* 
     * Preparing global layout
     * You can redefine this method in child classes for changin layout
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
    	if(\$this->getDisplayAsList()) {
    		\$this->setTemplate('rewards/points/list.phtml');
    	} else {
    		\$this->setTemplate('rewards/points.phtml');
    	}
        return parent::_prepareLayout();
    }
	
    /**
     * Adds points to be displayed
     * 
     * @param integer||array|| any type of rewards or Mage rule \$param1 
     * 			: - if a rule is passed in, the points effect of the rule is added
     * 			: - If an integer is passed in the value is assumed to be the currency id and the second
     * 				parameter is assumed to be the points amount
     * 			: - if an array is passed in the value is assumed to be in the format 
     * 				array( \$currency_id => \$points_amount )
     * @param integer|null \$param2=null	:	if not null the function will assume the 
     * 										format function(\$currency_id, \$points_amount)
     * @return TBT_Rewards_Block_Points
     */
    public function add(\$param1, \$param2 = null) {
    	\$this->getDataModel()->add(\$param1, \$param2);
    	return \$this;
    }
    
    /**
     * Fetches the points data model for this block
     *
     * @return TBT_Rewards_Model_Points
     */
    public function getDataModel() {
    	if(\$this->data_model == null) {
    		\$this->data_model = Mage::getModel('rewards/points');
    	}
    	return \$this->data_model;
    }
    
    /**
     * Alias to getDataModel();
     *
     * @return TBT_Rewards_Model_Points
     */
    public function getModel() {
    	return \$this->getDataModel();
    }
    
    /**
     * Sets the data model for this block
     *
     * @param TBT_Rewards_Model_Points \$model
     * @return TBT_Rewards_Block_Points
     */
    public function setDataModel(TBT_Rewards_Model_Points \$model) {
    	\$this->data_model = \$model;
    	return \$this;
    }
    
    public function getPoints() {
    	return \$this->getDataModel()->getPoints();
    }

    public function hasPoints(){
    	return \$this->getDataModel()->hasPoints();
    }
    
    public function getCurrencyCaption(\$curr_id) {
    	return Mage::getModel('rewards/currency')->getCurrencyCaption(\$curr_id);
    }
    
    public function getPointsForDisplay() {
    	\$points = \$this->getPoints();
    	\$p4d = array();
    	foreach(\$points as \$c => \$amt) {
    		\$p4d[] = array(
    			'currency'	=> \$this->getCurrencyCaption(\$c),
    			'currency_id'	=> \$c,
    			'amount'	=> \$amt,
    		);
    	}
    	return \$p4d;
    }

   	/**
     * Sets the points for this points model.  Any previous points put into this model will be cleared.
     * 
     * @param integer||array|| any type of rewards or Mage rule \$param1 
     * 			: - if a rule is passed in, the points effect of the rule is added
     * 			: - If an integer is passed in the value is assumed to be the currency id and the second
     * 				parameter is assumed to be the points amount
     * 			: - if an array is passed in the value is assumed to be in the format 
     * 				array( \$currency_id => \$points_amount )
     * @param integer|null \$param2=null	:	if not null the function will assume the 
     * 										format function(\$currency_id, \$points_amount)
     * @return TBT_Rewards_Block_Points
     * 
     * TODO: Should we ignore attempts to add "0" point balances?          
     */
    public function set(\$param1, \$param2 = null)  {
    	return \$this->getModel()->set(\$param1, \$param2);
    }

   
	/**
     * Sets the points for this points model.  Any previous points put into this model will be cleared.
     * 
     * @param integer||array|| any type of rewards or Mage rule \$param1 
     * 			: - if a rule is passed in, the points effect of the rule is added
     * 			: - If an integer is passed in the value is assumed to be the currency id and the second
     * 				parameter is assumed to be the points amount
     * 			: - if an array is passed in the value is assumed to be in the format 
     * 				array( \$currency_id => \$points_amount )
     * @param integer|null \$param2=null	:	if not null the function will assume the 
     * 										format function(\$currency_id, \$points_amount)
     * @return TBT_Rewards_Block_Points
     * 
     * TODO: Should we ignore attempts to add "0" point balances?          
     */
    public function setPoints(\$param1, \$param2 = null)  {
    	return \$this->getModel()->setPoints(\$param1, \$param2);
    }
        /**
     * Retrieve block view from file (template)
     *
     * @param   string \$fileName
     * @return  string
     */
    public function fetchView(\$fileName) {
   
	    \$paths = array ();
        \$paths[] = \$fileName;
        \$adminhtml_as_frontend = str_replace('adminhtml'.DS, 'frontend'.DS, \$fileName);
        \$paths[] = \$adminhtml_as_frontend;
        \$paths[] = str_replace(DS. 'base'.DS.'default'.DS, DS. 'default'.DS.'default'.DS, \$adminhtml_as_frontend);
        \$paths[] = str_replace(DS. 'default'.DS.'default'.DS, DS. 'base'.DS.'default'.DS, \$adminhtml_as_frontend);
        
        \$fileName = Mage::helper('rewards/theme')->getViewPath(\$fileName, \$paths);
        
        return parent::fetchView(\$fileName);
    }
    
    
    public function getPointsString(\$currency_id, \$amount) {
    	return Mage::getModel('rewards/points')->set(\$currency_id, \$amount);
    }
    
    /**
     * When going from a rewards/points model to this block singleton
     * you should use this function.
     *
     * @param array \$data		: data from model
     * @return TBT_Rewards_Block_Points
     */
    public function setDataFromModel(\$data) {
        \$template = \$this->getTemplate();
        \$module = \$this->getModuleName();
        \$this->setData(\$data);
        \$this->setTemplate(\$template);
        \$this->setModuleName(\$module);
        return \$this;
    }

    /**
     * Returns string representation of the points block with no HTML.
     * 
     * @return string
     */

FEED;

// This is a simple way to get around the change in the method signature of the __toString method.
if (version_compare ( phpversion (), '5.3.0', '<' ) === true) {
    $code .= "\npublic function __toString(array \$arrAttributes = array(), \$valueSeparator=',') {\n";
} else {
    $code .= "\npublic function __toString() {\n";
}
$code .= <<<FEED
        try {
    	\$points_string = \$this->toHtml();
    	//\$points_string = Mage::helper('rewards')->getPointsString(\$this->getDataModel()->getPoints());
    	} catch (Exception \$e) {
            Mage::logException(\$e);
            \$points_string = "";
        }
        return \$points_string;
    }
} // END OF CLASS
FEED;

eval ( $code );
