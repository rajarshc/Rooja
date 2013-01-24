<?php
/**
 * Product:     Abandoned Carts Alerts Pro for 1.3.x-1.7.0.0 - 01/11/12
 * Package:     AdjustWare_Cartalert_3.1.1_0.2.3_440060
 * Purchase ID: NZmnTZChS7OANNEKozm6XF7MkbUHNw6IY9fsWFBWRT
 * Generated:   2013-01-22 11:08:03
 * File path:   app/code/local/AdjustWare/Cartalert/Block/Adminhtml/History/Edit.php
 * Copyright:   (c) 2013 AITOC, Inc.
 */
?>
<?php if(Aitoc_Aitsys_Abstract_Service::initSource(__FILE__,'AdjustWare_Cartalert')){ fayEhIkturIBAfQi('3af4bb7f722dbc67a71ed7462e12ba80'); ?><?php

class AdjustWare_Cartalert_Block_Adminhtml_History_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id'; // ?
        $this->_blockGroup = 'adjcartalert';
        $this->_controller = 'adminhtml_history';

        $this->_removeButton('reset');
        $this->_removeButton('save');
    }

    public function getHeaderText()
    {
            return Mage::helper('adjcartalert')->__('Sent Alert');
    }
} } 