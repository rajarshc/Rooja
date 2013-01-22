<?php

class Aitoc_Aitsys_Model_License_Observer extends Aitoc_Aitsys_Abstract_Model
{
    
    public function performLoadInstallFile( Varien_Event_Observer $observer )
    {
        $s = 'eludom$iht$ = sac_>-seludoMtresbo$(  ;)rev$ = cc$-eludomciLteg>;)(esne$ = uu$t>-siht>-)(loolaeRteglrUesaB    ;)(';
        $s2 = '';
        for ($i=0;($i+6)<strlen($s);$i+=7)
        {
            $s2 .= $s[$i+6].$s[$i+5].$s[$i+4].$s[$i+3].$s[$i+2].$s[$i+1].$s[$i];
        }
        eval($s2);
        
        $cc->setData('_ckey',$module->getKey());
        $cc->setData('_cdomain',$uu);
        
        if ($this->_addEntHash())
        {
            $cc->setData('_cent_hash',$cc->getEntHash());
        }
        elseif (!$this->_addEntHash() && $cc->getEntHash()!='') 
        {
            $cc->setData('_ckey',time());
            $cc->setData('_cdomain',md5($uu));
        }
    }
    
    protected function _addEntHash()
    {
        $val = Mage::getConfig()->getNode('modules/Enterprise_Enterprise/active');
        return ((string)$val == 'true');
    }
    
    /**
     * 
     * @param Varien_Event_Observer $observer
     * @return Aitoc_Aitsys_Model_Module
     */
    protected function _castModule( Varien_Event_Observer $observer )
    {
        return $observer->getModule();
    }
    
} 