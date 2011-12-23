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

class MageParts_CEM_Block_Adminhtml_Licenses_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

	/**
	 * Constructor
	 */
    public function __construct()
    {
        parent::__construct();
        $this->setId('cem_licenses_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('cem')->__('License Management'));
    }

    
    /**
     * Add tabs before writing HTML tabs
     */
    protected function _beforeToHtml()
    {
        $this->addTab('lost_cem_section', array(
            'label'     => Mage::helper('cem')->__('Lost CEM Key'),
            'title'     => Mage::helper('cem')->__('Lost CEM Key'),
            'content'   => $this->getLayout()->createBlock('cem/adminhtml_licenses_edit_tab_lostCem')->toHtml(),
            'active'    => true
        ));
        
        $this->addTab('lost_license_section', array(
            'label'     => Mage::helper('cem')->__('Lost License Key'),
            'title'     => Mage::helper('cem')->__('Lost License Key'),
            'content'   => $this->getLayout()->createBlock('cem/adminhtml_licenses_edit_tab_lostLicense')->toHtml()
        ));
        
        return parent::_beforeToHtml();
    }

}
