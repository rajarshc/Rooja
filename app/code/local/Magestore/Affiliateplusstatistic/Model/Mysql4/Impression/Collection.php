<?php

class Magestore_Affiliateplusstatistic_Model_Mysql4_Impression_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct(){
        parent::_construct();
        $this->_init('affiliateplusstatistic/impression');
    }
    
    public function prepareSummary($range, $customStart, $customEnd, $isFilter = 0){
    	// $this->setMainTable('affiliateplus/action');
    	$adapter = $this->getConnection();
    	$account = Mage::getSingleton('affiliateplus/session')->getAccount();
    	$this->getSelect()->reset(Zend_Db_Select::COLUMNS);
    	$this   ->getSelect()
                ->where('account_id = '.$account->getId().' AND type = 1')
                ->columns(array(
                    'clicks'	=> 'SUM(totals)',
                    'uniques'	=> 'SUM(is_unique)',
                ));
    	
    	$dateRange = $this->getDateRange($range,$customStart,$customEnd);
    	$this->getSelect()->columns(array('range' => $this->_getRangeExpressionForAttribute($range,'created_date')))//$tzRangeOffsetExpression))
    		->order('range',Zend_Db_Select::SQL_ASC)
    		->group('range');//$tzRangeOffsetExpression);
    	
    	$this->addFieldToFilter('created_date', $dateRange);
    	return $this;
    }
    
    public function prepareTotal($range, $customStart, $customEnd, $isFilter = 0){
    	// $this->setMainTable('affiliateplus/action');
    	$adapter = $this->getConnection();
    	$account = Mage::getSingleton('affiliateplus/session')->getAccount();
    	$this->getSelect()->reset(Zend_Db_Select::COLUMNS);
    	
        if (Mage::helper('affiliateplus/config')->getSharingConfig('balance') == 'store')
			$this->addFieldToFilter('store_id', Mage::app()->getStore()->getId());
        
    	$this   ->getSelect()
                ->where('account_id = '.$account->getId().' AND type = 1')
                ->columns(array(
                    'raws'	=> 'SUM(totals)',
                    'uniques'	=> 'SUM(is_unique)',
                ));
    	
    	$dateRange = $this->getDateRange($range,$customStart,$customEnd);
    	
    	$this->addFieldToFilter('created_date',$dateRange);
        //Zend_Debug::dump($this->getSelect()->__toString());
    	return $this;
    }
    
    public function prepareLifeTimeTotal(){
    	// $this->setMainTable('affiliateplus/action');
    	$this->getSelect()->reset(Zend_Db_Select::COLUMNS);
    	$account = Mage::getSingleton('affiliateplus/session')->getAccount();
    	$this   ->getSelect()
                ->where('account_id = '.$account->getId().' AND type = 1')
                ->columns(array(
                    'clicks'	=> 'SUM(totals)',
                    'uniques'	=> 'SUM(is_unique)',
                ));
    	
    	return $this;
    }
    
    /**
     * Get range expression
     *
     * @param string $range
     * @return Zend_Db_Expr
     */
    protected function _getRangeExpression($range){
        switch ($range)
        {
            case '24h':
                /*$expression = $this->getConnection()->getConcatSql(array(
                    $this->getConnection()->getDateFormatSql('{{attribute}}', '%Y-%m-%d %H:'),
                    $this->getConnection()->quote('00')
                ));*/
                $expression = 'DATE_FORMAT({{attribute}}, \'%Y-%m-%d %H:00\')';
                break;
            case '7d':
            case '1m':
                //$expression = $this->getConnection()->getDateFormatSql('{{attribute}}', '%Y-%m-%d');
                $expression = 'DATE_FORMAT({{attribute}}, \'%Y-%m-%d\')';
                break;
            case '1y':
            case '2y':
            case 'custom':
            default:
                //$expression = $this->getConnection()->getDateFormatSql('{{attribute}}', '%Y-%m');
                $expression = 'DATE_FORMAT({{attribute}}, \'%Y-%m\')';
                break;
        }

        return $expression;
    }
    
    /**
     * Retrieve range expression adapted for attribute
     *
     * @param string $range
     * @param string $attribute
     * @return string
     */
    protected function _getRangeExpressionForAttribute($range, $attribute){
        $expression = $this->_getRangeExpression($range);
        return str_replace('{{attribute}}', $this->getConnection()->quoteIdentifier($attribute), $expression);
    }
    
    /**
     * Retrieve query for attribute with timezone conversion
     *
     * @param string $range
     * @param string $attribute
     * @param mixed $from
     * @param mixed $to
     * @return string
     */
    protected function _getTZRangeOffsetExpression($range, $attribute, $from = null, $to = null){
        return str_replace(
            '{{attribute}}',
            Mage::getResourceModel('sales/report_order')
                    ->getStoreTZOffsetQuery($this->getMainTable(), $attribute, $from, $to),
            $this->_getRangeExpression($range)
        );
    }
    
    /**
     * Calculate From and To dates (or times) by given period
     *
     * @param string $range
     * @param string $customStart
     * @param string $customEnd
     * @param boolean $returnObjects
     * @return array
     */
    public function getDateRange($range, $customStart, $customEnd, $returnObjects = false){
        $dateEnd   = Mage::app()->getLocale()->date();
        $dateStart = clone $dateEnd;

        // go to the end of a day
        $dateEnd->setHour(23);
        $dateEnd->setMinute(59);
        $dateEnd->setSecond(59);

        $dateStart->setHour(0);
        $dateStart->setMinute(0);
        $dateStart->setSecond(0);

        switch ($range)
        {
            case '24h':
                $dateEnd = Mage::app()->getLocale()->date();
                $dateEnd->addHour(1);
                $dateStart = clone $dateEnd;
                $dateStart->subDay(1);
                break;

            case '7d':
                // substract 6 days we need to include
                // only today and not hte last one from range
                $dateStart->subDay(6);
                break;

            case '1m':
                $dateStart->setDay(Mage::getStoreConfig('affiliateplus/statistic/mtd_start'));
                break;

            case 'custom':
                $dateStart = $customStart ? $customStart : $dateEnd;
                $dateEnd   = $customEnd ? $customEnd : $dateEnd;
                break;

            case '1y':
            case '2y':
                $startMonthDay = explode(',', Mage::getStoreConfig('affiliateplus/statistic/ytd_start'));
                $startMonth = isset($startMonthDay[0]) ? (int)$startMonthDay[0] : 1;
                $startDay = isset($startMonthDay[1]) ? (int)$startMonthDay[1] : 1;
                $dateStart->setMonth($startMonth);
                $dateStart->setDay($startDay);
                if ($range == '2y') {
                    $dateStart->subYear(1);
                }
                break;
        }

        $dateStart->setTimezone('Etc/UTC');
        $dateEnd->setTimezone('Etc/UTC');

        if ($returnObjects) {
            return array($dateStart, $dateEnd);
        } else {
            return array('from' => $dateStart, 'to' => $dateEnd, 'datetime' => true);
        }
    }
}