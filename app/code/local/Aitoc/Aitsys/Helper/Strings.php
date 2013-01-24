<?php
/**
 * @copyright  Copyright (c) 2009 AITOC, Inc.
 */
class Aitoc_Aitsys_Helper_Strings extends Aitoc_Aitsys_Helper_Data
{
    //penalty message
    const PENALTY_ABUSE_TITLE     = 'AITOC License Warning!';
    const PENALTY_ABUSE_VIOLATION = 'The following AITOC module licenses are violated:';
    const PENALTY_ABUSE_PROCEED   = 'proceed to the license management section';
    const PENALTY_ABUSE_CLOSE     = 'close this window';
    const PENALTY_ABUSE_TIMER     = '(available in %s seconds)';
    const PENALTY_ABUSE_END       = 'To resolve this problem please visit the license management section.';

    //change license messages
    const CHANGE_LICENSE_AGREEMENT = 'Your license type was changed. Please read the new license agreement and proceed with the new installation' ;
    const CHANGE_LICENSE_NORIGHTS  = 'Sorry, the script was not able to install the new license files on your server. Please download a new package at <a href="%s">link</a>, extract it to your server in Magento folder and then return back to module installation';

    //install license notifications
    const INSTALL_MYSQL_TIMEOUT_NOTIFICATION = 'Due to the small value (%s) of "wait_timeout" parameter an automatic installation of the extensions can result in error. If after clicking "Proceed to install" you will get an error, you might try to increase the value of the parameter to a higher one in the MySQL configuration and then attempt to install the license once again. Or you may contact our support team asking them to configure the manual license for you instead. In case of contacting our support service make sure you have also indicated an URL to the administrative area.';
    const INSTALL_TEST_CONNECTION_FAILED = 'An established connection with the AITOC server "%s" is required in order to proceed with the installation of the extension. Please check your firewall and server configurations in order to allow outbound SSL queries to the given server.';

    //errors
    const ER_LICENSE_NF = 'License entry not found or corrupt.|||Please note that the module is still working. To disable this license violation warning you need to either install/reinstall the license under Manage License section of this module or turn the module off under Manage AITOC modules section. If the license keeps uninstalling without no apparent reason, please submit a support ticket.';
    const ER_LICENSE_LE = 'You\'ve exceeded the limitations specified in your license.|||Please note that the module is still working. To disable this license violation warning you may 1) purchase a license upgrade, 2) turn the module off under Manage AITOC modules section, or 3) bring your store to compliance with the license option you have (i.e. disable or delete exceeding products, stores or users).';
    const ER_MODULE_FC  = 'Some of required module files are not found or corrupt.|||To disable this license violation warning you need to either reinstall the module and its license or turn the module off under Manage AITOC modules section. If this error appears regularly, please submit a support ticket.';
    const ER_UNKNOWN    = 'Unknown error.|||Please note that the module is still working. To disable this license violation warning either contact AITOC support team or turn the module off under Manage AITOC modules section.';
    const ER_MODULE_CS  = 'License hasn\'t been installed. If you have trouble re-installing or if the error occurs after you re-installed the module, please submit a support ticket.';
    const ER_PERFORMER  = 'The %s file is corrupt. Please re-upload this file and be sure to use a binary transfer mode for this upload. If this error occurs again, please submit a support ticket.';
    const ER_ENT_HASH = 'The extension isn\'t compatible with this edition of Magento. Please contact us at sales@aitoc.com for further instructions. Please note that our working hours are Mon-Fri, 8am - 5pm UTC.';

    private $_errorStringDelimiter = "|||";

    public function getString($const, $translate = true, $args = array())
    {
        if(!is_array($args))
        {
            $args = array($args);
        }

        $string = constant('self::'.$const);
        $string = $translate ? $this->__($string) : $string;

        array_unshift($args, $string);

        return call_user_func_array('sprintf', $args);
    }

    public function parseErrorString($error)
    {
        return explode($this->_errorStringDelimiter, $error);
    }
}