<?php
/**
 * Product:     Abandoned Carts Alerts Pro for 1.3.x-1.7.0.0 - 01/11/12
 * Package:     AdjustWare_Cartalert_3.1.1_0.2.3_440060
 * Purchase ID: NZmnTZChS7OANNEKozm6XF7MkbUHNw6IY9fsWFBWRT
 * Generated:   2013-01-22 11:08:03
 * File path:   app/code/local/AdjustWare/Cartalert/Block/Adminhtml/Dailystat.php
 * Copyright:   (c) 2013 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'AdjustWare_Cartalert')){ TeZZiqDWRjqyRTIp('969e93aaf5c5337362b30d044b54def8'); ?><?php
class AdjustWare_Cartalert_Block_Adminhtml_Dailystat extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        parent::__construct();        
        $this->_controller = 'adminhtml_dailystat';
        $this->_blockGroup = 'adjcartalert';
        $this->_headerText = Mage::helper('adjcartalert')->__('Daily Statistic');
        $this->_removeButton('add'); 
    }  
  
} } 