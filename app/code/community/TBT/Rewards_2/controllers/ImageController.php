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
 * Image Controller
 *
 * @category   TBT
 * @package    TBT_Rewards
 * @author     WDCA Sweet Tooth Team <contact@wdca.ca>
 */
class TBT_Rewards_ImageController extends Mage_Core_Controller_Front_Action {
	
	public function indexAction() {
		$points = $this->getRequest ()->get ( "quantity" );
		$currency_id = $this->getRequest ()->get ( "currency" );
		$currency = Mage::getModel ( 'rewards/currency' )->load ( $currency_id );
		
		$skin_dir = Mage::getDesign ()->getSkinBaseDir ( array ('_type' => 'skin' ) ) . "" . DS;
		$font = $skin_dir . $currency->getFont ();
		$image = $skin_dir . $currency->getImage ();
		$doPrintQty = ( int ) $currency->getImageWriteQuantity () === 1;
		$imageHeight = ( int ) $currency->getImageHeight ();
		$imageWidth = ( int ) $currency->getImageWidth ();
		$fontSize = ( int ) $currency->getFontSize ();
		$fontColor = ( int ) $currency->getFontColor ();
		
		$im = imageCreateFromPNG ( $image );
		
		$black = imagecolorallocate ( $im, 0x00, 0x00, 0x00 );
		
		// Path to our ttf font file
		$font_file = $font;
		
		if ($imageHeight > 0 && $imageWidth > 0) {
			list ( $width, $height ) = getimagesize ( $image );
			$newwidth = $imageWidth;
			$newheight = $imageHeight;
			
			// Load
			$resized_im = imagecreatetruecolor ( $newwidth, $newheight );
			$source = imagecreatefrompng ( $image );
			
			// Resize
			imagecopyresized ( $resized_im, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height );
			$im = $resized_im;
		}
		
		//TODO: Externilize customization
		// Draw the text 'PHP Manual' using font size 13
		$img_h = ($imageHeight == 0) ? imagesy ( $im ) : $imageHeight;
		$img_w = imagesx ( $im );
		$font_size = $fontSize;
		$text_color = $fontColor;
		$text = (empty ( $points ) ? "" : ( int ) $points);
		
		$offsetx = $currency->getTextOffsetX ();
		$offsety = $currency->getTextOffsetY ();
		
		if (empty ( $offsetx )) {
			$offsetx = round ( ($img_w / 2) - (strlen ( $text ) * imagefontwidth ( $font_size )) / 2 - 3, 1 );
			if (( int ) ($text) > 99) {
				$offsetx += 1;
			}
		}
		if (empty ( $offsety )) {
			$offsety = round ( ($img_h / 2) + imagefontheight ( $font_size ) / 2, 1 );
		}
		
		if ($doPrintQty) {
			imagefttext ( $im, $font_size, 0, $offsetx, $offsety, $text_color, $font_file, $text );
		}
		
		// Output image to the browser
		header ( 'Content-Type: image/png' );
		
		imagepng ( $im );
		imagedestroy ( $im );
		exit ();
	}

}