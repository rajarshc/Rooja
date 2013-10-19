<?php
class Magestore_Affiliateplus_Block_Account_Navigation extends Mage_Customer_Block_Account_Navigation
{
	protected $_navigation_title = '';
	
	public function setNavigationTitle($title){
		$this->_navigation_title = $title;
		return $this;
	}
	
	public function getNavigationTitle(){
		return $this->_navigation_title;
	}
	
	public function addLink($name, $path, $label, $disabled = false, $order = 0, $urlParams=array())
    {
    	if (isset($this->_links[$order])) $order++;
    	
    	$link = new Varien_Object(array(
            'name' 		=> $name,
            'path' 		=> $path,
            'label' 	=> $label,
            'disabled'	=> $disabled,
            'order'		=> $order,
            'url' 		=> $this->getUrl($path, $urlParams),
        ));
        
        Mage::dispatchEvent('affiliateplus_account_navigation_add_link',array(
        	'block'		=> $this,
        	'link'		=> $link,
        ));
    	
        $this->_links[$order] = $link;
        return $this;
    }
	
	public function getLinks(){
		$links = new Varien_Object(array(
			'links'	=> $this->_links,
		));
		
		Mage::dispatchEvent('affiliateplus_account_navigation_get_links',array(
			'block'		=> $this,
			'links_obj'	=> $links,
		));
		
		$this->_links = $links->getLinks();
		
		ksort($this->_links);
		
		return $this->_links;
	}
	
	public function isActive($link){
		if (parent::isActive($link)) return true;
		if (in_array($this->_activeLink,array(
				'affiliatepluslevel/index/listTierTransaction',
				'affiliatepluspayperlead/index/listleadcommission'
			)) && $this->_completePath($link->getPath()) == 'affiliateplus/index/listTransaction')
				return true;
		return false;
	}
}