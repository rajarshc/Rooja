<?php
class Magestore_Affiliateplus_Block_Adminhtml_Account_Edit_Tab_Customer
 extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('customergrid');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
        if ($this->getAccount()->getId()) {
            $this->setDefaultFilter(array('in_customers'=>1));
        }
    }
	
    protected function _addColumnFilterToCollection($column)
    {

    }


    protected function _prepareCollection()
    {
	
        $collection = Mage::getResourceModel('customer/customer_collection')
            ->addNameToSelect()
            ->addAttributeToSelect('email')
            ->addAttributeToSelect('created_at')
            ->addAttributeToSelect('group_id')
            ->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
            ->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
            ->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left')
            ->joinAttribute('billing_region', 'customer_address/region', 'default_billing', null, 'left')
            ->joinAttribute('billing_country_id', 'customer_address/country_id', 'default_billing', null, 'left');
		
		
        $affiliateCustomerIds = Mage::helper('affiliateplus')->getAffiliateCustomerIds();
		if(count($affiliateCustomerIds))						
			$collection->addFieldToFilter('entity_id', array('nin'=>$affiliateCustomerIds));
		
		$this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
		$this->addColumn('in_customers', array(
			'header_css_class'  => 'a-center',
			'type'              => 'radio',
			'html_name'         => 'in_customers',
			'align'             => 'center',
			'index'             => 'entity_id'
		));        
		
		$this->addColumn('entity_id', array(
            'header'    => Mage::helper('affiliateplus')->__('ID'),
            'width'     => '50px',
            'index'     => 'entity_id',
            'type'  => 'number',
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('affiliateplus')->__('Name'),
            'index'     => 'name'
        ));
        $this->addColumn('email', array(
            'header'    => Mage::helper('affiliateplus')->__('Email'),
            'width'     => '250px',
            'index'     => 'email'
        ));

        /* $groups = Mage::getResourceModel('customer/group_collection')
            ->addFieldToFilter('customer_group_id', array('gt'=> 0))
            ->load()
            ->toOptionHash();

        $this->addColumn('group', array(
            'header'    =>  Mage::helper('affiliateplus')->__('Group'),
            'width'     =>  '100',
            'index'     =>  'group_id',
            'type'      =>  'options',
            'options'   =>  $groups,
        )); */


        $this->addColumn('customer_since', array(
            'header'    => Mage::helper('affiliateplus')->__('Customer Since'),
            'type'      => 'datetime',
            'align'     => 'center',
            'index'     => 'created_at',
			'width'     => '170px',
            'gmtoffset' => true
        ));

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('website_id', array(
                'header'    => Mage::helper('affiliateplus')->__('Website'),
                'align'     => 'center',
                'width'     => '80px',
                'type'      => 'options',
                'options'   => Mage::getSingleton('adminhtml/system_store')->getWebsiteOptionHash(true),
                'index'     => 'website_id',
            ));
        }
	  
		return parent::_prepareColumns();
    }

    //return url
	public function getGridUrl()
    {
        return $this->getData('grid_url')
            ? $this->getData('grid_url')
            : $this->getUrl('*/*/customerGrid', array('_current'=>true,'id'=>$this->getRequest()->getParam('id')));
    
	}

	//return Magestore_Affiliate_Model_Referral
	public function getAccount()
	{
		return Mage::getModel('affiliateplus/account')
						->load($this->getRequest()->getParam('id'));	
	}	

}