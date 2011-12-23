<?php
/**
 * MageParts
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   MageParts
 * @package    MageParts_CEM
 * @copyright  Copyright (c) 2009 MageParts (http://www.mageparts.com/)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class MageParts_CEM_Model_Licenses extends Mage_Core_Model_Abstract
{

	/**
	 * Constructor
	 */
	protected function _construct()
    {
        $this->_init('cem/licenses');
    }
    
    
    /**
	 * Validate a email address
	 *
	 * @param string $email
	 * @return boolean
	 */
	public function validateEmail( $email ) 
	{
  		// First, we check that there's one @ symbol, 
  		// and that the lengths are right.
  		if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) {
    		// Email invalid because wrong number of characters 
    		// in one section or wrong number of @ symbols.
   			 return false;
  		}
  
  		// Split it into sections to make life easier
  		$email_array = explode("@", $email);
  		$local_array = explode(".", $email_array[0]);
  
  		for ($i = 0; $i < sizeof($local_array); $i++) {
    		if(!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$",$local_array[$i])) {
      			return false;
   			}
  		}
  
  		// Check if domain is IP. If not, 
  		// it should be valid domain name
  		if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) {
    		$domain_array = explode(".", $email_array[1]);
    
    		if (sizeof($domain_array) < 2) {
        		return false; // Not enough parts to domain
    		}
    
    		for ($i = 0; $i < sizeof($domain_array); $i++) {
      			if(!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$",$domain_array[$i])) {
        			return false;
      			}
    		}
  		}
  	
  		return true;
	}
	
}