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
 * etc/modules xml file
 *
 * @category    RJMetrics
 * @package     RJMetrics_Campaigns
 * @author      RJ Metrics <support@rjmetrics.com>
 */
if (Mage::getVersion() >= '1.6.0.0') {
    class RJMetrics_Campaigns_Model_Resource_Setup extends Mage_Sales_Model_Resource_Setup
    {

    }
} else {
    class RJMetrics_Campaigns_Model_Resource_Setup extends Mage_Sales_Model_Mysql4_Setup
    {

    }
}

