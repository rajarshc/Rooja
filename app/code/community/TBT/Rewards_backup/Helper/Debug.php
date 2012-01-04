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
 * Helper for Debugging
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_Helper_Debug extends Mage_Core_Helper_Abstract {
	
	public function dd($vo, $with_pre_tags = true, $die = true) {
		if ($vo instanceof Varien_Object) {
			$str = print_r ( $vo->toArray (), true );
		} else {
			$str = print_r ( $vo, true );
		}
		
		if ($with_pre_tags) {
			echo ("<PRE>" . $str . "</PRE>");
		} else {
			echo ($str);
		}
		
		if ($die) {
			die ();
		} else {
			return $this;
		}
	}
	
	/**
	 * @return  a simple backtrace string of the current position (not including
	 * any travering into this function)
	 * @nelkaake Added on Monday August 20, 2010:      
	 */
	public function getSimpleBacktrace($offset = 1) {
		$str = "";
		$bt = debug_backtrace ();
		foreach ( $bt as $index => &$btl ) {
			if ($offset > 0) {
				$offset --;
				continue;
			}
			if (isset ( $btl ['file'] ))
				$str .= "> {$btl['file']}";
			if (isset ( $btl ['function'] ))
				$str .= " in function {$btl['function']}(*)";
			if (isset ( $btl ['line'] ))
				$str .= " on line {$btl['line']}.";
			$str .= "\n";
		}
		return $str;
	}
	
	/**
	 * @return  a simple backtrace string of the current position (not including
	 * any travering into this function)  
	 * @nelkaake Added on Monday August 20, 2010:   
	 */
	public function noticeBacktrace($msg = "") {
		if ($msg != "")
			$msg .= "\n";
		$this->notice ( $msg . $this->getSimpleBacktrace ( 1 ) );
		return $this;
	}
	
	/**
	 * @nelkaake Added on Thursday May 27, 2010: Logging method for ST functions
	 * @param mixed $msg
	 */
	public function log($msg) {
		Mage::log ( $msg, null, "rewards.log" );
	}
	
	/**
	 * Logs an exception into the Sweet TOoth log file
	 * @param unknown_type $msg
	 */
	public function logException($msg) {
		if (Mage::helper ( 'rewards/developer_config' )->getLogErrorEnabled ()) {
			$this->log ( "[error] " . $msg );
		}
	}
	
	/**
	 * @nelkaake Added on Thursday May 27, 2010: Notice-level logging function
	 * @param mixed $msg
	 */
	public function notice($msg) {
		if (Mage::helper ( 'rewards/developer_config' )->getLogNoticeEnabled ()) {
			$this->log ( "[notice] " . $msg );
		}
	}
	
	/**
	 * @nelkaake Added on Thursday May 27, 2010: Notice-level logging function
	 * @param mixed $msg
	 */
	public function warn($msg) {
		if (Mage::helper ( 'rewards/developer_config' )->getLogWarningEnabled ()) {
			$this->log ( "[warning] " . $msg );
		}
	}
	
	/**
	 * @nelkaake Added on Thursday May 27, 2010: Notice-level logging function
	 * @param string|exception|mixed $msg
	 */
	public function error($msg) {
	    if($msg instanceof Exception) {
	        $e = $msg;
	    } else {
	        $e = new Exception($msg);
	    }
	    $this->logException($e);
	    return $this;
	}
	

	
	/**
	 * Returns an array of the inner data of the Varien Object that 
	 * omitting other varien objects
	 * @nelkaake -a 16/11/10: 
	 */
	public function getPrintableData($obj) {
		if ($obj instanceof Varien_Object) {
			$data = $obj->getData ();
		} elseif (is_array ( $obj )) {
			$data = $obj;
		} else {
			return ( string ) $obj;
		}
		foreach ( $data as $i => $item ) {
			if (is_object ( $item )) {
				$data [$i] = "**CLASS: " . get_class ( $item ) . "**";
			}
			if (is_array ( $item )) {
				$subitem_entry = "**ARRAY[";
				// clean inner array for objects and arrays so we dont print too much
				foreach ( $item as &$subitem ) {
					if (is_object ( $subitem )) {
						$subitem = "**CLASS: " . get_class ( $subitem ) . "**";
					}
					if (is_array ( $subitem )) {
						$subitem = "**ARRAY[**hidden**]**";
					}
				}
				// Add the inner array data separated by commas
				$data [$i] .= implode ( ", ", $item );
				$data [$i] .= "]**";
			}
		}
		return $data;
	}
}
