<?php
/**
 * RJMetrics Campaigns
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @category   RJMetrics
 * @package    RJMetrics_Campaigns
 * @copyright  Copyright (c) 2013 RJMetrics Inc. (http://www.rjmetrics.com)
 * @license    http://www.sumoheavy.com/LICENSE.txt
 * @author     RJ Metrics <support@rjmetrics.com>
 */

/**
 * Event Observer
 *
 * @category    RJMetrics
 * @package     RJMetrics_Campaigns
 * @author      RJ Metrics <support@rjmetrics.com>
 */
class RJMetrics_Campaigns_Model_Observer extends Mage_Core_Model_Abstract
{
    /**
     * Predispatch Controller
     *
     * @param Varien_Object $observer
     * @return $this
     */
    public function controllerActionPredispatch($observer)
    {
        /**
         *  first lets check if the cookie exists
         */
        $utmzCookie = Mage::getModel('core/cookie')->get('__utmz');
        if ($utmzCookie) {
            //  If the customer is logged in
            if (Mage::getSingleton('customer/session')->isLoggedIn()) {

                $customer = Mage::getSingleton('customer/session')
                                ->getCustomer();

                // If the customer already has the campaign data return
                if ($customer->getData('rjm_utm_source') != null) {

                    return $this;

                } else { // Save that to customer entity (eav) table

                    // First, check if we have the data in session
                    $customerSession = Mage::getSingleton('customer/session');

                    // If we do, set the customer data
                    if ($customerSession->getData('rjm_utm_source') != null) {

                        $customer->setData(
                            'rjm_utm_source',
                            $customerSession->getData('rjm_utm_source')
                        );

                        $customer->setData(
                            'rjm_utm_medium',
                            $customerSession->getData('rjm_utm_medium')
                        );

                        $customer->setData(
                            'rjm_utm_term',
                            $customerSession->getData('rjm_utm_term')
                        );

                        $customer->setData(
                            'rjm_utm_content',
                            $customerSession->getData('rjm_utm_content')
                        );

                        $customer->setData(
                            'rjm_utm_campaign',
                            $customerSession->getData('rjm_utm_campaign')
                        );

                        // Save the customer and return
                        $customer->save();

                        return $this;

                    } else { // Else we get that straight from the cookie

                        $utmzParams = array();
                        parse_str(
                            str_replace(
                                '|',
                                '&',
                                substr(
                                    $utmzCookie, strpos($utmzCookie, 'utm')
                                )
                            ), $utmzParams
                        );

                        $customer->setData(
                            'rjm_utm_source',
                            isset(
                                $utmzParams['utmcsr']
                            ) ? $utmzParams['utmcsr'] : ''
                        );

                        $customer->setData(
                            'rjm_utm_medium',
                            isset(
                                $utmzParams['utmcmd']
                            ) ? $utmzParams['utmcmd'] : ''
                        );

                        $customer->setData(
                            'rjm_utm_term',
                            isset(
                                $utmzParams['utmctr']
                            ) ? $utmzParams['utmctr'] : ''
                        );

                        $customer->setData(
                            'rjm_utm_content',
                            isset(
                                $utmzParams['utmcct']
                            ) ? $utmzParams['utmcct'] : ''
                        );

                        $customer->setData(
                            'rjm_utm_campaign',
                            isset(
                                $utmzParams['utmccn']
                            ) ? $utmzParams['utmccn'] : ''
                        );

                        // Save the customer
                        $customer->save();
                    }


                }

            } else {

                // We are not logged in so we just save that to the session
                $customerSession = Mage::getSingleton('customer/session');



                // And if it is in the session then..
                if ($customerSession->getData('rjm_utm_source') != null) {

                    // .. Return
                    return $this;

                } else { // Else  we finally save

                    // Get the cookie data
                    $utmzParams = array();
                    parse_str(
                        str_replace(
                            '|',
                            '&',
                            substr(
                                $utmzCookie,
                                strpos(
                                    $utmzCookie,
                                    'utm'
                                )
                            )
                        ), $utmzParams
                    );

                    // Adding to session
                    $customerSession->setData(
                        'rjm_utm_source',
                        isset(
                            $utmzParams['utmcsr']
                        ) ? $utmzParams['utmcsr'] : ''
                    );

                    $customerSession->setData(
                        'rjm_utm_medium',
                        isset(
                            $utmzParams['utmcmd']
                        ) ? $utmzParams['utmcmd'] : ''
                    );

                    $customerSession->setData(
                        'rjm_utm_term',
                        isset(
                            $utmzParams['utmctr']
                        ) ? $utmzParams['utmctr'] : ''
                    );

                    $customerSession->setData(
                        'rjm_utm_content',
                        isset(
                            $utmzParams['utmcct']
                        ) ? $utmzParams['utmcct'] : ''
                    );

                    $customerSession->setData(
                        'rjm_utm_campaign',
                        isset(
                            $utmzParams['utmccn']
                        ) ? $utmzParams['utmccn'] : ''
                    );
                }
            }
        }

        return $this;
    }


    /**
     * Observer: salesOrderSaveBefore
     *
     * @param Varien_Object $observer
     * @return $this
     */
    public function salesOrderSaveBefore($observer)
    {

        $order = $observer->getEvent()->getOrder();

        if (Mage::getSingleton('customer/session')->isLoggedIn()) {

            $customer = Mage::getSingleton('customer/session')->getCustomer();

            $order->setData(
                'rjm_utm_source',
                $customer->getData('rjm_utm_source')
            );

            $order->setData(
                'rjm_utm_medium',
                $customer->getData('rjm_utm_medium')
            );

            $order->setData(
                'rjm_utm_term',
                $customer->getData('rjm_utm_term')
            );

            $order->setData(
                'rjm_utm_content',
                $customer->getData('rjm_utm_content')
            );

            $order->setData(
                'rjm_utm_campaign',
                $customer->getData('rjm_utm_campaign')
            );

        } else {

            $customerSession = Mage::getSingleton('customer/session');

            $order->setData(
                'rjm_utm_source',
                $customerSession->getData('rjm_utm_source')
            );

            $order->setData(
                'rjm_utm_medium',
                $customerSession->getData('rjm_utm_medium')
            );

            $order->setData(
                'rjm_utm_term',
                $customerSession->getData('rjm_utm_term')
            );

            $order->setData(
                'rjm_utm_content',
                $customerSession->getData('rjm_utm_content')
            );

            $order->setData(
                'rjm_utm_campaign',
                $customerSession->getData('rjm_utm_campaign')
            );
        }

        return $this;
    }

}