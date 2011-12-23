<?php

abstract class TBT_Testsweet_Model_Test_Render_Abstract extends TBT_Testsweet_Model_Abstract {
   
    
    protected $_filter = 0;
    

    /**
     * 
     * Filter the display to hide states
     * 
     * TBT_Testsweet_Model_Test_Report_Abstract::STATUS_PASS = 1;
     * TBT_Testsweet_Model_Test_Report_Abstract::STATUS_NOTICE = 2;
     * TBT_Testsweet_Model_Test_Report_Abstract::STATUS_WARNING = 4;
     * TBT_Testsweet_Model_Test_Report_Abstract::STATUS_FAIL = 8;
     * 
     * Example to filter all passes and notices
     * setFilter( TBT_Testsweet_Model_Test_Report_Abstract::STATUS_PASS | TBT_Testsweet_Model_Test_Report_Abstract::STATUS_NOTICE )
     * 
     * @param int $filter
     * @return TBT_Testsweet_Model_Test_Render_Abstract 
     */
    public function setFilter($filter) {
        $this->_filter = (int)$filter;
    }

    /**
     * 
     * 
     * TBT_Testsweet_Model_Test_Report_Abstract::STATUS_PASS = 1;
     * TBT_Testsweet_Model_Test_Report_Abstract::STATUS_NOTICE = 2;
     * TBT_Testsweet_Model_Test_Report_Abstract::STATUS_WARNING = 4;
     * TBT_Testsweet_Model_Test_Report_Abstract::STATUS_FAIL = 8;
     * 
     * @param int $filter
     * @return int 
     */
    public function getFilter($filter) {
        return $this->_filter;
    }
    
    
    /**
     * Takes in a STATUS and returns if it should not be rendered
     * 
     * TBT_Testsweet_Model_Test_Report_Abstract::STATUS
     * 
     * @param int $status
     * @return bool
     */
    protected function isFiltered($status) {
        return ($this->_filter & (int)$status);
    }
    
    
    /**
     * Render to a temp buffer then return the data
     */
    public function render_toString() {
        ob_flush(); //TODO: do i need this flush or does ob_start do it already?
        ob_start();
        $this->render();
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
    }

    /**
     * Render to the current out put buffer
     */
    abstract public function render();
}