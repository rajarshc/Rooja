<?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc. 
 */
class Aitoc_Aitsys_Model_Module_Install_Light extends Aitoc_Aitsys_Model_Module_Install
{
    /**
     * @return Aitoc_Aitsys_Model_Module_Install_Light
     */
    public function uninstall( $kill = false )
    {
        $this->_uninstall($kill);
        return $this;
    }
}