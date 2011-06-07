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

class MageParts_CEM_Block_Adminhtml_Packages_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

	/**
	 * Constructor
	 */
    public function __construct()
    {
		if(!Mage::getModel('cem/packages')->checkCemEmail()) {
			$this->setTemplate('cem/packages/register.phtml');
		}
		else {
	        parent::__construct();
	        $this->setId('cemPackagesGrid');
	        $this->setDefaultSort('title');
	        $this->setDefaultDir('ASC');
		}
    }
    
    
    /**
     * Prepare collection
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('cem/packages')->getCollection();
        /* @var $collection MageParts_CEM_Model_Mysql4_Packages_Collection */
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }
    
    
    /**
     * Prepare columns
     */
    protected function _prepareColumns()
    {
        // Title column
        $this->addColumn('title', array(
            'header'    => Mage::helper('cms')->__('Title'),
            'index'     => 'title',
        ));
        
        // Title column
        $this->addColumn('version', array(
            'header'    => Mage::helper('cem')->__('Version'),
            'width'		=> '100px',
            'index'     => 'version',
        ));
        
        // Last update date column
        $this->addColumn('last_update', array(
            'header'    => Mage::helper('cem')->__('Updated at'),
            'width'		=> '160px',
            'index'     => 'last_update',
            'type'      => 	'datetime',
            'gmtoffset' => true,
            'default'	=> 	' ---- '
        ));
        
		// Has updates
        $this->addColumn('update_available', array(
            'header'    => Mage::helper('cem')->__('Updates Available'),
            'index'     => 'update_available',
            'width'		=> '20px',
            'renderer'	=> 'cem/adminhtml_packages_grid_renderer_updateAvailable'
        ));
        
        return parent::_prepareColumns();
    }
    
    
    /**
     * Fires after collection has been loaded
     */
    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }
    
    
    /**
     * Row click url
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('package_id' => $row->getPackageId()));
    }
    
}
