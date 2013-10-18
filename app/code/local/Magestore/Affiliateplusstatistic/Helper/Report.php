<?php

class Magestore_Affiliateplusstatistic_Helper_Report extends Mage_Core_Helper_Abstract
{
	const REPORT_PERIOD_TYPE_DAY	= 'day';
	const REPORT_PERIOD_TYPE_MONTH  = 'month';
	const REPORT_PERIOD_TYPE_YEAR   = 'year';
	
	/**
	 * Retrieve array of intervals
	 *
	 * @param string $from
	 * @param string $to
	 * @param string $period
	 * @return array
	 */
	public function getIntervals($from, $to, $period = self::REPORT_PERIOD_TYPE_DAY){
		$intervals = array();
		if (!$from && !$to){
			return $intervals;
		}

		$start = new Zend_Date($from, Varien_Date::DATE_INTERNAL_FORMAT);

		if ($period == self::REPORT_PERIOD_TYPE_DAY) {
			$dateStart = $start;
		}

		if ($period == self::REPORT_PERIOD_TYPE_MONTH) {
			$dateStart = new Zend_Date(date("Y-m", $start->getTimestamp()), Varien_Date::DATE_INTERNAL_FORMAT);
		}

		if ($period == self::REPORT_PERIOD_TYPE_YEAR) {
			$dateStart = new Zend_Date(date("Y", $start->getTimestamp()), Varien_Date::DATE_INTERNAL_FORMAT);
		}

		$dateEnd = new Zend_Date($to, Varien_Date::DATE_INTERNAL_FORMAT);

		while ($dateStart->compare($dateEnd) <= 0) {
			switch ($period) {
				case self::REPORT_PERIOD_TYPE_DAY :
					$t = $dateStart->toString('yyyy-MM-dd');
					$dateStart->addDay(1);
					break;
				case self::REPORT_PERIOD_TYPE_MONTH:
					$t = $dateStart->toString('yyyy-MM');
					$dateStart->addMonth(1);
					break;
				case self::REPORT_PERIOD_TYPE_YEAR:
					$t = $dateStart->toString('yyyy');
					$dateStart->addYear(1);
					break;
			}
			$intervals[] = $t;
		}
		return  $intervals;
	}

	public function prepareIntervalsCollection($collection, $from, $to, $periodType = self::REPORT_PERIOD_TYPE_DAY, $column = 'created_time'){
		$intervals = $this->getIntervals($from, $to, $periodType);
		foreach ($intervals as $interval) {
			$item = Mage::getModel('adminhtml/report_item');
			$item->setData($column,$interval);
			$item->setIsEmpty();
			$collection->addItem($item);
		}
	}
}