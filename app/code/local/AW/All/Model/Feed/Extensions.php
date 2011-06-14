<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/LICENSE-M1.txt
 *
 * @category   AW
 * @package    AW_All
 * @copyright  Copyright (c) 2009-2010 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/LICENSE-M1.txt
 */ 

class AW_All_Model_Feed_Extensions extends AW_All_Model_Feed_Abstract{
	
	 /**
     * Retrieve feed url
     *
     * @return string
     */
    public function getFeedUrl(){
		return AW_All_Helper_Config::EXTENSIONS_FEED_URL;
    }
	
	
	/**
	 * Checks feed
	 * @return 
	 */
	public function check(){
		if(!(Mage::app()->loadCache('aw_all_extensions_feed')) || (time()-Mage::app()->loadCache('aw_all_extensions_feed_lastcheck')) > Mage::getStoreConfig('awall/feed/check_frequency')){
			$this->refresh();
		}
	}
	
	public function refresh(){
		$exts = array();
		try{
			$Node = $this->getFeedData();
			if(!$Node) return false;
			foreach($Node->children() as $ext){
				$exts[(string)$ext->name] = array(
					'display_name' => (string)$ext->display_name,
					'version' => (string)$ext->version,
					'url'		=> (string)$ext->url
				);
			}
			
			Mage::app()->saveCache(serialize($exts), 'aw_all_extensions_feed');
			Mage::app()->saveCache(time(), 'aw_all_extensions_feed_lastcheck');
			return true;
		}catch(Exception $E){
			return false;
		}
	}

    public function checkExtensions(){
        $modules = array_keys((array)Mage::getConfig()->getNode('modules')->children());
		sort($modules);
        $magentoVersion = $this->_convertVersion(Mage::getVersion());

        foreach ($modules as $extensionName) {
        	if (strstr($extensionName,'AW_') === false) {
        		continue;
        	}
			if($extensionName == 'AW_Core' || $extensionName == 'AW_All'){
				continue;
			}
  //          echo $extensionName; die();

        	if($platform = $this->getExtensionPlatform($extensionName)){
                if($magentoVersion >= $this->_convertVersion(AW_All_Helper_Config::ENTERPRISE_VERSION)){
                    // EE
                    if($platform == 'ce' || $platform=='pe'){
                        $this->disableExtensionOutput($extensionName);
                    }

                }elseif($magentoVersion >= $this->_convertVersion(AW_All_Helper_Config::PROFESSIONAL_EDITION)){
                    // PE
                    if($platform == 'ce' ){
                        $this->disableExtensionOutput($extensionName);
                    }
                }
            }
        }
        return $this;
    }

    public function getExtensionPlatform($extensionName){
        try{
            if($platform = Mage::getConfig()->getNode("modules/$extensionName/platform")){
                $platform = strtolower($platform);
                return $platform;
            }else{
                throw new Exception();
            }
        }catch(Exception $e){
            return false;
        }
    }


    public function disableExtensionOutput($extensionName){
        $coll = Mage::getModel('core/config_data')->getCollection();
        $coll->getSelect()->where("path='advanced/modules_disable_output/$extensionName'");
        $i=0;
        foreach($coll as $cd){
            $i++;
            $cd->setValue(1)->save();
        }
        if($i==0){
             Mage::getModel('core/config_data')
                ->setPath("advanced/modules_disable_output/$extensionName")
                ->setValue(1)
                 ->save();
        }
        return $this;
    }

    protected function _convertVersion($v){
		$digits = @explode(".", $v);
		$version = 0;
		if(is_array($digits)){
			foreach($digits as $k=>$v){
				$version += ($v * pow(10, max(0, (3-$k))));
			}

		}
		return $version;
	}
}