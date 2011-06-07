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

class MageParts_CEM_Block_Adminhtml_Packages extends Mage_Adminhtml_Block_Widget_Grid_Container
{

	/**
	 * Constructor
	 */
    public function __construct()
    {
        $this->_controller = 'adminhtml_packages';
        $this->_blockGroup = 'cem';
        $this->_headerText = Mage::helper('cem')->__('Manage Extensions <span class="powered"><a href="http://www.mageparts.com" target="_blank">Powered by MageParts.com</a></span>');
        $this->_addButtonLabel = Mage::helper('cem')->__('Install New Extension');
        
        $this->_addButton('license_management', array(
            'label'     => Mage::helper('cem')->__('License Management'),
            'onclick'   => "setLocation('".$this->getUrl('*/*/licenseManagement')."')",
            'class'     => '',
        ));
        
        $this->_addButton('update', array(
            'label'     => Mage::helper('cem')->__('Update Extensions'),
            'onclick'   => "setLocation('".$this->getUrl('*/*/update')."')",
            'class'     => '',
        ));
        
        parent::__construct();
    }
  	
}
