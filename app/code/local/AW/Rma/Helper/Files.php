<?php

/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 * 
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * aheadWorks does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * aheadWorks does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Rma
 * @copyright  Copyright (c) 2010-2011 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE-COMMUNITY.txt
 */
class AW_Rma_Helper_Files extends Mage_Core_Helper_Abstract {
     const FOLDERNAME = 'aw_rma';

     const SIZE_1KB = 1024;
     const SIZE_2KB = 2048;
     const SIZE_1MB = 1048576;
     const SIZE_2MB = 2097152;

     const SIZE_BYTES = 'b';
     const SIZE_KBYTES = 'kb';
     const SIZE_MBYTES = 'mb';
     const SIZE_UNKNOWN = 'unknown';

     /**
      * Returns folder name
      * @return string
      */
     public static function getFolderName() {
         return self::FOLDERNAME;
     }

     /**
      * Returns full path to uploads storage
      * @return string
      */
     public static function getPath() {
         return Mage::getBaseDir('media').DS.self::getFolderName().DS;
     }

     /**
      * Returns file extension
      * @param string $fname
      * @return string
      */
     public static function getExtension($fname) {
         $_pi = pathinfo($fname);
         return $_pi['extension'];
     }

     /**
     * Downloads file to client
     * @param string $filename The name of the file to download
     * @param $content The contents downloaded
     */
    public static function downloadFile($filename, $content=null)
    {
        $filename = self::getPath().$filename;
        
        if(is_null($content)) {
            $handle = @fopen($filename, "r");
            if($handle) {
                $content = @fread($handle, filesize($filename));
                @fclose($handle);
            }
        }

        $contentType='application/octet-stream';

        $response = Mage::app()->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', TRUE);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', TRUE);
        $response->setHeader('Content-Disposition', "attachment; filename=\"".basename($filename)."\"");
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }

    /**
     * Calculate file size and returns it in text view like
     * 2,56mb, 2kb and etc.
     * @param string $fname
     * @return string
     */
    public static function getTextSize($fname) {
        $fsize = filesize(self::getPath().$fname);
        if($fsize) {
            $tFsize = $fsize;

            if($fsize<=self::SIZE_2KB) {
                $tFsize .= self::SIZE_BYTES;
            } elseif($fsize <= self::SIZE_2MB) {
                $tFsize = round($fsize / self::SIZE_1KB, 2).self::SIZE_KBYTES;
            } else {
                $tFsize = round($fsize / self::SIZE_1MB, 2).self::SIZE_MBYTES;
            }

            return $tFsize;
        } else
            return self::SIZE_UNKNOWN;
    }
}
