<?php
/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_AffiliateplusReferFriend
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * AffiliateplusReferFriend Observer Model
 * 
 * @category    Magestore
 * @package     Magestore_AffiliateplusReferFriend
 * @author      Magestore Developer
 */
class Magestore_AffiliateplusReferFriend_Model_Observer
{
    /**
     * process controller_action_predispatch event
     *
     * @return Magestore_AffiliateplusReferFriend_Model_Observer
     */
    public function controllerActionPredispatch($observer)
    {
        $action = $observer->getEvent()->getControllerAction();
        return $this;
    }
}