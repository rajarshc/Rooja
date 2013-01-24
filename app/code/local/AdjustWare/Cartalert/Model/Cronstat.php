<?php
/**
 * Product:     Abandoned Carts Alerts Pro for 1.3.x-1.7.0.0 - 01/11/12
 * Package:     AdjustWare_Cartalert_3.1.1_0.2.3_440060
 * Purchase ID: NZmnTZChS7OANNEKozm6XF7MkbUHNw6IY9fsWFBWRT
 * Generated:   2013-01-22 11:08:03
 * File path:   app/code/local/AdjustWare/Cartalert/Model/Cronstat.php
 * Copyright:   (c) 2013 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'AdjustWare_Cartalert')){ cMeeqiBkIgiaIchw('dd5e00b09a46efc6f52c0752df0833a0'); ?><?php
class AdjustWare_Cartalert_Model_Cronstat extends Mage_Core_Model_Abstract
{
    const _configPath = 'catalog/adjcartalert/statconfig';
    
    private function _getConfig($key = null)
    {
        
        $configModel = Mage::getModel('core/config_data')->getCollection()
            ->addFieldToFilter('path', self::_configPath)
            ->getFirstItem();        
        
        $config = unserialize($configModel->getValue());
        
        if(is_null($key))
        {
            return $config;
        }
        
        return isset($config[$key])?$config[$key]:'';
    }


    private function _setConfig($key, $value = null)
    {
        if(is_array($key))
        {
            $config = $key;
        }
        else
        {
            $config = $this->_getConfig();
            $config[$key]=$value;
        }
        
        $configModel = Mage::getModel('core/config_data')->getCollection()
            ->addFieldToFilter('path', self::_configPath)
            ->getFirstItem();             
        $configModel->setPath(self::_configPath)->setValue(serialize($config))->save();
       
    }    
    
    public function run()
    {
        $config = $this->_getConfig();
        if(isset($config['task_isset']) && $config['task_isset']==1)
        {   
            $start = str_replace('-','',$config['task_startdate']);
            $end = str_replace('-','',$config['task_enddate']);
            while((int)$start<=(int)$end)
            {
                $config = $this->_getConfig();
                call_user_func(array($config['task_class'],$config['task_method']), $config['task_startdate']);                 
                $this->_setConfig('task_startdate', date('Y-m-d',strtotime($config['task_startdate'])+3600*24));
                $start = str_replace('-','',$config['task_startdate']);
                $end = str_replace('-','',$config['task_enddate']);                
            }
            call_user_func(array($config['task_class'],$config['task_method']), $config['task_enddate']);            
            $this->_setConfig('task_isset',0);
            return 1;
        }
        return 0;
    }    
    
    public function cron()
    {
        $config = $this->_getConfig();
        if(!isset($config['dailytask_updated']) || $config['dailytask_updated']!=date('Y-m-d'))
        {
            $taskCreated = $this->createTask('AdjustWare_Cartalert_Model_Dailystat', 'collectDay', date('Y-m-d', time()-86400*30), date('Y-m-d'));
            if($taskCreated)
            {
                $this->_setConfig('dailytask_updated',date('Y-m-d'));
            }
        }
        $this->run();
    }
    
    public function createTask($class, $method, $fromdate, $todate)
    {
        $config = $this->_getConfig();
        if(isset($config['task_isset']) && $config['task_isset']==1)
        {
            return 0;
        }
        else
        {
            $this->_setConfig(array(
                'task_startdate'    => $fromdate,
                'task_enddate'      => $todate,
                'task_isset'        => 1,
                'task_class'        => $class,
                'task_method'       => $method,
            ));
            return 1;
        }
    }
    
    public function isBusy()
    {

        $config = $this->_getConfig();
        if(isset($config['task_isset']) && $config['task_isset']==1)
        {
            return 1;
        }
        else
        {
            return 0;
        }
    
    }

} } 