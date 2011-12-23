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

class MageParts_CEM_Block_Adminhtml_Licenses_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{

	/**
	 * Constructor
	 */
    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'cem';
        $this->_controller = 'adminhtml_licenses';
        parent::__construct();
    }

    
    /**
     * Get header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        return Mage::helper('cem')->__('License Management');
    }
    
}