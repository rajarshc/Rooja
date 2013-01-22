<?php
abstract class Aitoc_Aitsys_Abstract_Controller extends Mage_Core_Controller_Front_Action
implements Aitoc_Aitsys_Abstract_Model_Interface
{
    /**
     * @return Aitoc_Aitsys_Abstract_Service
     */
    public function tool()
    {
        return Aitoc_Aitsys_Abstract_Service::get();
    }
}