<?php

/**
 * Moo Extension
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
 * @category   Moo
 * @package    Moo_Catalog
 * @author     Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 * @copyright  Copyright (c) 2010 Mohamed Alsharaf. (http://jamandcheese-on-phptoast.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Moo_Catalog_Model_Gallery_CloudZoom_OpacityRange
{

    public function toOptionArray()
    {
        $result = array();
        for ($i = 0; $i <= 1; $i+=0.1) {
            $result[] = array(
                'value' => ($i*100),
                'label' => $i
            );
        }
        return $result;
    }
}