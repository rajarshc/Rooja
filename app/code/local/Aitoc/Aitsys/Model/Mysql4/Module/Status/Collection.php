<?php
class Aitoc_Aitsys_Model_Mysql4_Module_Status_Collection extends Aitoc_Aitsys_Abstract_Mysql4_Collection
{
    protected function _construct()
    {
        $this->_init('aitsys/module_status');
    }
    
    public function clearTable()
    {
        $this->load();
        $keys = array();
        foreach($this->getItems() as $item)
        {
            if(!in_array($item->getModule(), $keys))
            {
                $keys[] = $item->getModule();
            }
            else
            {
                $item->delete();
            }
        }
    }
}