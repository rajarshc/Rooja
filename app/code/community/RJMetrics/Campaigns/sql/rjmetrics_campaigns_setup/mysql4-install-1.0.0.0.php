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
 * Install script file
 *
 * @category    RJMetrics
 * @package     RJMetrics_Campaigns
 * @author      Piotr Socha <support@sumoheavy.com>
 */

$installer = $this;
/* @var $installer Mage_Sales_Model_Mysql4_Setup */

$installer->startSetup();


$data = array(
    'type'               => 'varchar',
    'label'              => '__utmz::utmcsr',
    'input'              => 'text',
    'default'            => '',
    'sort_order'         => 900,
    'position'           => 900,
    'user_defined'       => 1,
);

$installer->addAttribute('customer', 'rjm_utm_source', $data);
$installer->addAttribute('order', 'rjm_utm_source', $data);

$data = array(
    'type'               => 'varchar',
    'label'              => '__utmz::utmcmd',
    'input'              => 'text',
    'default'            => '',
    'sort_order'         => 910,
    'position'           => 910,
    'user_defined'       => 1,
);

$installer->addAttribute('customer', 'rjm_utm_medium', $data);
$installer->addAttribute('order', 'rjm_utm_medium', $data);

$data = array(
    'type'               => 'varchar',
    'label'              => '__utmz::utmccn',
    'input'              => 'text',
    'default'            => '',
    'sort_order'         => 920,
    'position'           => 920,
    'user_defined'       => 1,
);

$installer->addAttribute('customer', 'rjm_utm_term', $data);
$installer->addAttribute('order', 'rjm_utm_term', $data);

$data = array(
    'type'               => 'varchar',
    'label'              => '__utmz::utmctr',
    'input'              => 'text',
    'default'            => '',
    'sort_order'         => 930,
    'position'           => 930,
    'user_defined'       => 1,
);

$installer->addAttribute('customer', 'rjm_utm_content', $data);
$installer->addAttribute('order', 'rjm_utm_content', $data);

$data = array(
    'type'               => 'varchar',
    'label'              => '__utmz::utmcct',
    'input'              => 'text',
    'default'            => '',
    'sort_order'         => 940,
    'position'           => 940,
    'user_defined'       => 1,
);

$installer->addAttribute('customer', 'rjm_utm_campaign', $data);
$installer->addAttribute('order', 'rjm_utm_campaign', $data);

$installer->endSetup();