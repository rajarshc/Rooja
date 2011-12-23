<?php

class Mage_Avenues_Model_Config extends Varien_Object
{
    
    public function getConfigData($key, $default=false)
    {
        if (!$this->hasData($key)) {
            $value = Mage::getStoreConfig('payment/Avenues_standard/'.$key);
            if (is_null($value) || false===$value) {
                $value = $default;
            }
            $this->setData($key, $value);
        }
        return $this->getData($key);
    }

    
    public function getTransactionMode ()
    {
        return $this->getConfigData('mode');
    }

    public function getSecretKey ()
    {
        return $this->getConfigData('secret_key');
    }


 
    public function getAccountId ()
    {
        return $this->getConfigData('account_id');
    }



    public function getDescription ()
    {
        $description = $this->getConfigData('description');
        return $description;
    }

   
    public function getNewOrderStatus ()
    {
        return $this->getConfigData('order_status');
    }

    public function getDebug ()
    {
        return $this->getConfigData('debug_flag');
    }

    
    public function getCurrency ()
    {
        return $this->getConfigData('currency');
    }

    
    public function getLanguage ()
    {
        return $this->getConfigData('language');
    }
}