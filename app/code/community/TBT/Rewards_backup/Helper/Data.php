<?php

/**
 * WDCA - Sweet Tooth
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the WDCA SWEET TOOTH POINTS AND REWARDS 
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth License is available at this URL: 
 * http://www.wdca.ca/sweet_tooth/sweet_tooth_license.txt
 * The Open Software License is available at this URL: 
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * DISCLAIMER
 * 
 * By adding to, editing, or in any way modifying this code, WDCA is 
 * not held liable for any inconsistencies or abnormalities in the 
 * behaviour of this code. 
 * By adding to, editing, or in any way modifying this code, the Licensee
 * terminates any agreement of support offered by WDCA, outlined in the 
 * provided Sweet Tooth License. 
 * Upon discovery of modified code in the process of support, the Licensee 
 * is still held accountable for any and all billable time WDCA spent 
 * during the support process.
 * WDCA does not guarantee compatibility with any other framework extension. 
 * WDCA is not responsbile for any inconsistencies or abnormalities in the
 * behaviour of this code if caused by other framework extension.
 * If you did not receive a copy of the license, please send an email to 
 * contact@wdca.ca or call 1-888-699-WDCA(9322), so we can send you a copy 
 * immediately.
 * 
 * @category   [TBT]
 * @package    [TBT_Rewards]
 * @copyright  Copyright (c) 2009 Web Development Canada (http://www.wdca.ca)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Helper Data
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Helper_Data extends Mage_Core_Helper_Abstract {
	
	/**
	 * Return points string using TBT_Rewards_Block_Points
         * 
         * Note: at one point this returned a TBT_Rewards_Block_Points object 
         * This has been revised to force the return type as string
	 *
	 * @param array $points_array
	 * @return string
	 */
	public function getPointsString($points_array) {
            return (string) Mage::getModel('rewards/points')->add($points_array)->getRendering();
        }
	
	/**
	 * Code will be in the format [-]##[%]
	 * [-] will subtract the value from the product, 
	 * no [-] will make the value of the price equal to the number.
	 * The [%] makes the number a percent of the price
	 * 
	 * -10% reduces the price by 10%
	 * 30% makes the price 30% of the original
	 * 5 makes the price 5
	 * -15 reduce the price by 15 
	 * 
	 * @param int $price
	 * @param string $code
	 * @param boolean [$reverse_currency=true] : reverse the current store currency before making the calculation?
	 * @param boolean [$round_price=true] : round the price after making the calculation?
	 * @nelkaake 15/01/2010 6:45:20 PM : added $round_price flag to NOT round the price as in some cases needed (see Block\Product\View\Points.php)
	 * 
	 * @throws Exception
	 * 
	 * @return int $price
	 */
	public function priceAdjuster($price, $code, $reverse_currency = true, $round_price = true) {
		if (strpos ( $code, "-" ) !== false) {
			
			//Depending on the effect, it modifies a temp price to compare to
			if (strpos ( $code, "%" ) !== false) {
				$fx = ( float ) (1 + str_replace ( "%", "", $code ) / 100);
				$price = $price * $fx;
			} else {
				$fx = ( float ) $code;
				if ($reverse_currency) {
					$fx = ( float ) Mage::app ()->getStore ()->convertPrice ( ( float ) $code );
				}
				$price = $price + $fx;
			}
		} else {
			if (strpos ( $code, "%" ) !== false) {
				$fx = ( float ) (str_replace ( "%", "", $code ) / 100);
				$price = $price * $fx;
			} else {
				$fx = ( float ) $code;
				if ($reverse_currency) {
					$fx = ( float ) Mage::app ()->getStore ()->convertPrice ( ( float ) $code );
				}
				$price = $fx;
			}
		}
		$price = Mage::app ()->getStore ()->roundPrice ( $price );
		return $price;
	}
	
	/**
	 * Adjusts the price using priceAdjuster but mutliple times
	 * For exmaple if you need to add 5 x 10% discount, set the last
	 * paramter to 5.
	 * @throws Exception
	 * @see priceAdjuster($price, $code)
	 *
	 * @param int $price
	 * @param string $code
	 * @param integer $uses must be greater than 0
	 * 
	 * @return float $price
	 */
	public function priceAdjusterMulti($price, $code, $uses) {
		if (( int ) $uses <= 0) {
			return $price;
		}
		$new_price = $this->priceAdjuster ( $price, $code );
		$price_disposition = $price - $new_price;
		$final_price = $price - ($price_disposition * $uses);
		if ($final_price < 0)
			$final_price = 0;
		return $final_price;
	}
	
	/**
	 * Applifies an effect up to maximum of a product price or -100%
	 * @throws Exception
	 * @see priceAdjuster($price, $code)
	 *
	 * @param int $price
	 * @param string $effect_code
	 * @param integer $uses must be greater than 0
	 * 
	 * @return string $price new effect
	 */
	public function amplifyEffect($price, $effect_code, $uses) {
		$old_effect = $effect_code;
		if (strpos ( $old_effect, "-" ) !== false) {
			
			//Depending on the effect, it modifies a temp price to compare to
			if (strpos ( $old_effect, "%" ) !== false) {
				$new_effect = $uses * (( float ) str_replace ( "%", "", $old_effect ));
				if ($new_effect < - 100)
					$new_effect = - 100;
				$new_effect = $new_effect . "%";
			} else {
				$new_effect = $uses * $old_effect;
				if ($new_effect * - 1 > $price)
					$new_effect = $price * - 1;
			}
		} else { // YOU CAN'T AMPLIFY "% OF PRODUCT PRICE" DISCOUNTS   
			$new_effect = $old_effect;
		}
		return $new_effect;
	}
	
	/**
	 * Performs a base64_encode and json_encode on
	 * a variable then returns the result.
	 *
	 * @param mixed $arr
	 * @return string
	 */
	public function hashIt($value) {
		if (is_null ( $value )) {
			$value = array ();
		}
		return base64_encode ( json_encode ( $value ) );
	}
	
	/**
	 * Performs a base64_decode and json_decode on
	 * a variable then returns the result.
	 *
	 * @param mixed $arr
	 * @return array
	 */
	public function unhashIt($value) {
		if (is_null ( $value )) {
			return array ();
		}
		$unhashed = json_decode ( base64_decode ( $value ) );
		$unhashed = ( array ) $unhashed;
		return $unhashed;
	}
	
	/**
	 * Takes in a points string and wraps  the "#_xyz" portion(s) in bold tags..
	 * 
	 * IE: "12 A Points, 1 Zee point and maybe even your 90 xyZ points"
	 * would become "<b>12 A</b> Points, <b>1 Zee</b> point and maybe even your <b>90 xyZ</b> points"
	 *
	 * @param unknown_type $points_str
	 * @return unknown
	 */
	public function emphasizeThePoints($points_str) {
		$new_points_str = preg_replace ( "([0-9]+[ ][a-zA-Z]+)", '<span class=\'points-summary-emphasize\'>$0</span>', $points_str );
		return $new_points_str;
	}
	
	/**
	 * Get store timestamp
	 * Timstamp will be builded with store timezone settings
	 *
	 * @param   mixed $store
	 * @return  int
	 */
	public function storeTimeStamp($store = null) {
		if ($this->isBaseMageVersionAtLeast ( '1.3' )) {
			return Mage::app ()->getLocale ()->storeTimeStamp ( $store );
		} else {
			//$timezone = Mage::app()->getStore($store)->getConfig(self::XML_PATH_DEFAULT_TIMEZONE);
			$currentTimezone = @date_default_timezone_get ();
			//@date_default_timezone_set($timezone);
			$date = date ( 'Y-m-d H:i:s' );
			@date_default_timezone_set ( $currentTimezone );
			return strtotime ( $date );
		}
	}
	
	/**
	 * Returns true if the page controller is multiship or if we are in the cart controller
	 *
	 * @param $quote = null   This will either be a quote model or a address model 
	 * @return boolean
	 */
	public function isMultishipMode($quote = null) {
		if ($quote == null) {
			$quote = $this->getRS ()->getQuote ();
		}
		$quote_is_multiship = $quote->getIsMultiShipping ();
		$page_is_cart = ($this->_getRequest ()->getControllerName () == 'cart');
		$page_is_multishipping = ($this->_getRequest ()->getControllerName () == 'multishipping');
		return ($quote_is_multiship && ! $page_is_cart) || $page_is_multishipping;
	}
	
	/**
	 * Fetches the rewards session.
	 *
	 * @return TBT_Rewards_Model_Session
	 */
	public function getRS() {
		return Mage::getSingleton ( 'rewards/session' );
	}
	
	/**
	 * True if the current page path matches the specified
	 * page path.
	 *
	 * @param string $path		: ie rewards/customer/view
	 * @return boolean
	 */
	public function isCurrentPage($path) {
		$current_module = $this->_getRequest ()->getModuleName ();
		$current_controller = $this->_getRequest ()->getControllerName ();
		$current_section = $this->_getRequest ()->getActionName ();
		$current_path = $current_module . "/" . $current_controller . "/" . $current_section;
		return ($path == $current_path);
	}
	
	/**
	 * Fetches the current date in the format 'Y-m-d'
	 * and based on the currently loaded store.
	 * @nelkaake Changed on Wednesday September 22, 2010: moved to Datetime helper     
	 * @return string
	 */
	public function now($dayOnly = TRUE) {
		return Mage::helper ( 'rewards/datetime' )->now ( $dayOnly );
	}
	
	// @nelkaake Changed on Wednesday September 22, 2010: moved to Datetime helper
	public function getCurrentFromDate() {
		return Mage::helper ( 'rewards/datetime' )->getCurrentFromDate ();
	}
	
	// @nelkaake Changed on Wednesday September 22, 2010: moved to Datetime helper
	public function getCurrentToDate() {
		return Mage::helper ( 'rewards/datetime' )->getCurrentToDate ();
	}
	
	/**
	 * @nelkaake Added on Thursday May 27, 2010: Logging method for ST functions
	 * @param mixed $msg
	 */
	public function log($msg) {
		return Mage::helper ( 'rewards/debug' )->log ( $msg );
	}
	
	/**
	 * @nelkaake Added on Thursday May 27, 2010: Notice-level logging function
	 * @param mixed $msg
	 */
	public function notice($msg) {
		return Mage::helper ( 'rewards/debug' )->notice ( $msg );
	}
	
	/**
	 * start E_DEPRECATED =================================================================================
	 */
	public function isMageVersion12() {
		return Mage::helper ( 'rewards/version' )->isMageVersion12 ();
	}
	
	public function isMageVersion131() {
		return Mage::helper ( 'rewards/version' )->isMageVersion131 ();
	}
	
	public function isMageVersion14() {
		return Mage::helper ( 'rewards/version' )->isMageVersion14 ();
	}
	
	public function isMageVersionAtLeast14() {
		return Mage::helper ( 'rewards/version' )->isMageVersionAtLeast14 ();
	}
	
	public function convertVersionToCommunityVersion($version, $task = null) {
		return Mage::helper ( 'rewards/version' )->convertVersionToCommunityVersion ( $version, $task );
	}
	
	/**
	 * @deprecated use rewards/version helper
	 */
	public function isBaseMageVersionAtLeast($version, $task = null) {
		return Mage::helper ( 'rewards/version' )->isBaseMageVersionAtLeast ( $version, $task );
	}
	
	/**
	 * @deprecated use rewards/version helper
	 */
	public function isBaseMageVersion($version, $task = null) {
		return Mage::helper ( 'rewards/version' )->isBaseMageVersion ( $version, $task );
	}
	
	public function isMageVersionAtLeast($version, $task = null) {
		return Mage::helper ( 'rewards/version' )->isMageVersionAtLeast ( $version, $task );
	}

/**
 * end E_DEPRECATED =================================================================================
 */
}
