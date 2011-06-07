<?php
/**
 * MageParts
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   MageParts
 * @package    MageParts_Adminhtml
 * @copyright  Copyright (c) 2009 MageParts (http://www.mageparts.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class MageParts_CEM_Block_Adminhtml_Licenses_Edit_Tab_LostCem extends Mage_Adminhtml_Block_Widget_Form
{
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->setTemplate('cem/licenses/lostcem.phtml');
	}
	
	
	/**
	 * Prepare layout
	 */
	protected function _prepareLayout()
    {
        $this->setChild('lostCemKeyButton',
	        $this->getLayout()->createBlock('adminhtml/widget_button')
	            ->setData(array(
	                'label'     => Mage::helper('cem')->__('OK'),
	                'onclick'   => 'lostCem.install();',
	                'class' 	=> 'save f-right'
            ))
        );
        
        $this->setChild('backButton',
	        $this->getLayout()->createBlock('adminhtml/widget_button')
	            ->setData(array(
	                'label'     => Mage::helper('cem')->__('Back'),
	                'onclick'   => "setLocation('{$this->getUrl('*/*/')}')",
	                'class' 	=> 'back f-right'
            ))
        );
        
        return parent::_prepareLayout();
    }
        
    
    /**
     * Get lost CEM key button (declared in _prepareLayout)
     *
     * @return Mage_Adminhtml_Block_Widget_Button
     */
    public function getLostCemKeyButtonHtml()
    {
        return $this->getChildHtml('lostCemKeyButton');
    }
            
    
    /**
     * Get backbutton (declared in _prepareLayout)
     *
     * @return Mage_Adminhtml_Block_Widget_Button
     */
    public function getBackButtonHtml()
    {
        return $this->getChildHtml('backButton');
    }

}
