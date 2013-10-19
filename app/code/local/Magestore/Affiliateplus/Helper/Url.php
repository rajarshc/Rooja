<?php

class Magestore_Affiliateplus_Helper_Url extends Mage_Core_Helper_Abstract
{
	public function getBannerUrl($banner, $store = null){
		if (is_null($store)) $store = Mage::app()->getStore();
		$account = Mage::getSingleton('affiliateplus/session')->getAccount();
		
		$url = $this->getUrlLink($banner->getLink());
		
		if (strpos($url,'?'))
			$url .= '&acc='.$account->getIdentifyCode();
		else 
			$url .= '?acc='.$account->getIdentifyCode();
		
		if ($store->getId() != Mage::app()->getDefaultStoreView()->getId())
			$url .= '&___store='.$store->getCode();
		/** Thanhpv - add bannerid (2012-10-09) */
		if($banner->getId())
			$url .= '&bannerid='.$banner->getId();
		
		$urlParams = new Varien_Object(array(
			'helper'	=> $this,
			'params'	=> array(),
		));
		Mage::dispatchEvent('affiliateplus_helper_get_banner_url',array(
			'banner'	=> $banner,
			'url_params'	=> $urlParams,
		));
		
		$params = $urlParams->getParams();
		if (count($params))
			$url .= '&'.http_build_query($urlParams->getParams(),'','&');
		
		return $url;
	}
	
	/**
	 * get Full link URL
	 *
	 * @param string $url
	 * @return string
	 */
	public function getUrlLink($url){
		if (!preg_match("/^http\:\/\/|https\:\/\//",$url))
			return Mage::getUrl().trim($url,'/');
		return rtrim($url, '/');
	}
	
	/**
	 * add account param to link
	 *
	 * @param string $url
	 * @param Mage_Core_Model_Store $store
	 * @return string
	 */
	public function addAccToUrl($url, $store = null){
		if (is_null($store)) $store = Mage::app()->getStore();
		$account = Mage::getSingleton('affiliateplus/session')->getAccount();
		
		$url = $this->getUrlLink($url);
		
		if (strpos($url,'?'))
			$url .= '&acc='.$account->getIdentifyCode();
		else 
			$url .= '?acc='.$account->getIdentifyCode();
		
		if ($store->getId() != Mage::app()->getDefaultStoreView()->getId())
			$url .= '&___store='.$store->getCode();
		
		$urlParams = new Varien_Object(array(
			'helper'	=> $this,
			'params'	=> array(),
		));
		Mage::dispatchEvent('affiliateplus_helper_add_acc_to_url',array(
			// 'banner'	=> $banner,
			'url_params'	=> $urlParams,
		));
		$params = $urlParams->getParams();
		if (count($params))
			$url .= '&'.http_build_query($urlParams->getParams(),'','&');
		
		return $url;
	}
}