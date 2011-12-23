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

/**
 * Make sure that WSDL files aren't cached to avoid problems.
 */
ini_set("soap.wsdl_cache_enabled","0");

/**
 * Lets load the NuSOAP library
 */
require_once('Soap/nusoap.php');

class MageParts_CEM_Model_Soap extends Mage_Core_Model_Abstract
{
	
	/**
	 * WSDL URL
	 *
	 * @param string
	 */
	private $_wsdlUrl = '';
	
	
	/**
	 * Results from SOAP call
	 *
	 * @var string
	 */
	private $_results = null;
	
	
	/**
	 * Error message from SOAP call
	 *
	 * @var unknown_type
	 */
	private $_error = '';
	
	
	/**
	 * SOAP fault
	 *
	 * @var unknown_type
	 */
	private $_soapFault = '';
	
	
	
	/**
	 * Set WSDL URL
	 *
	 * @param string $wsdlUrl
	 */
	public function setWsdlUrl( $wsdlUrl )
	{
		$this->_wsdlUrl = $wsdlUrl;
		return $this;
	}
	
	
	/**
	 * Create a SOAP call and return results as an array
	 *
	 * @param string $function
	 * @param array $params
	 * @param bool $verbose (true to display information about the soap call)
	 * @return false|array false for communication error or array if succesful
	 */
	public function call( $function, $params, $wsdlUrl='' ) 
	{
		// Reset old value, to avoid confusion
		$this->_results = '';
		$this->_error = '';
		$this->_soapFault = '';
		
		// If no service URL was provided, try taking the one from the global variable
		$wsdlUrl = empty($wsdlUrl) ? $this->_wsdlUrl : $wsdlUrl;

		// We need a service URL to initiate a call
		if(empty($wsdlUrl)) {
			$this->_error = "No service URL provided. Please contact your softare retailer for assistance";
		}
		else {
			// Create client - server connection
			$client = new nusoap_client($wsdlUrl,'wsdl');
			
			// Execute requested SOAP call
			$results = $client->call($function, $params);
		
			// Set reuslts
			$this->_results = (!$client->getError() && !$client->faultstring) ? $results : null;
			
			// Set error messages
			$this->_error = $client->getError();
			$this->_soapFault = $client->faultstring;
		}
		
		// Return this object
		return $this;
	}
	
	
	/**
	 * Return error message
	 *
	 * @return array
	 */
	public function getErrorMessage()
	{
		// Check if there was any SOAP fault (error message from the service)
		if(trim($this->_soapFault)!='') {
			return $this->_soapFault;
		}
		
		// Check if there was any error while processing the SOAP call
		if(trim($this->_error)!='') {
			return $this->_error;
		}
		
		// There was no errors
		return false;
	}
	
	
	/**
	 * Get results
	 * 
	 * @return string
	 */
	public function getResults()
	{
		return $this->_results;
	}
	
}

?>