<?php

class Aurigait_Banner_Helper_Data extends Mage_Core_Helper_Abstract
{

	const XML_PATH_WIDTH							=	'banners/general/banner_width';
	const XML_PATH_HEIGHT					  		=	'banners/general/banner_height';
	const XML_PATH_BGCOLOR							=	'banners/general/banner_backgroundcolor';
	const XML_PATH_AUTOPLAY							=	'banners/general/auto_play';
	const XML_PATH_IMAGE_RESIZE_TO_FIT				=	'banners/general/image_resize_to_fit';
	const XML_PATH_IMAGE_RANDOMIZE_ORDER			=	'banners/general/image_randomize_order';
	
	const XML_PATH_TEXT_SIZE						=	'banners/textsettings/text_size';
	const XML_PATH_TEXT_COLOR						=	'banners/textsettings/text_color';
	const XML_PATH_TEXT_AREA_WIDTH					=	'banners/textsettings/text_area_width';
	const XML_PATH_TEXT_LINE_SPACING				=	'banners/textsettings/text_line_spacing';
	const XML_PATH_TEXT_MARGIN_LEFT					=	'banners/textsettings/text_margin_left';
	const XML_PATH_TEXT_LETTER_SPACING				=	'banners/textsettings/text_letter_spacing';
	const XML_PATH_TEXT_MARGIN_BOTTOM				=	'banners/textsettings/text_margin_bottom';
	const XML_PATH_TEXT_BACKGROUND_BLUR				=	'banners/textsettings/text_background_blur';
	const XML_PATH_TEXT_BACKGROUND_TRANSPARENCY		=	'banners/textsettings/text_background_transparency';
	const XML_PATH_TEXT_BACKGROUND_COLOR			=	'banners/textsettings/text_background_color';
	
	const XML_PATH_TRANSITION_TYPE					=	'banners/transition/transition_type';
	const XML_PATH_TRANSITION_BLUR					=	'banners/transition/transition_blur';
	const XML_PATH_TRANSITION_SPEED					=	'banners/transition/transition_speed';
	const XML_PATH_TRANSITION_DELAY_TIME_FIXED		=	'banners/transition/transition_delay_time_fixed';
	const XML_PATH_TRANSITION_RANDOM_EFFECTS		=	'banners/transition/transition_random_effects';
	const XML_PATH_TRANSITION_DELAY_TIME_PER_WORD	=	'banners/transition/transition_delay_time_per_word';


	const XML_PATH_SHOW_TIMER_CLOCK					=	'banners/showhide/show_timer_clock';
	const XML_PATH_SHOW_NEXT_BUTTON					=	'banners/showhide/show_next_button';
	const XML_PATH_SHOW_BACK_BUTTON					=	'banners/showhide/show_back_button';
	const XML_PATH_SHOW_NUMBER_BUTTONS				=	'banners/showhide/show_number_buttons';
	const XML_PATH_SHOW_NUMBER_BUTTONS_ALWAYS		=	'banners/showhide/show_number_buttons_always';
	const XML_PATH_SHOW_NUMBER_BUTTONS_HORIZONTAL	=	'banners/showhide/show_number_buttons_horizontal';
	const XML_PATH_SHOW_NUMBER_BUTTONS_ASCENDING	=	'banners/showhide/show_number_buttons_ascending';
	const XML_PATH_SHOW_PLAY_PAUSE_ON_TIMER			=	'banners/showhide/show_play_pause_on_timer';
	const XML_PATH_ALIGN_BUTTONS_LEFT				=	'banners/showhide/align_buttons_left';
	const XML_PATH_ALIGN_TEXT_TOP					=	'banners/showhide/align_text_top';
	

	/*
		General Functions
	*/
	
	public function bannerWidth()
    {
    	return (int)Mage::getStoreConfig(self::XML_PATH_WIDTH);
    }
    
    public function bannerHeight()
    {
    	return (int)Mage::getStoreConfig(self::XML_PATH_HEIGHT);    	
    }
	
	public function bannerBackgroundColor()
    {
    	return Mage::getStoreConfig(self::XML_PATH_BGCOLOR);    	
    }
	
	public function autoPlay()
    {
		$value = '';
		$val = Mage::getStoreConfig(self::XML_PATH_AUTOPLAY);
		if ( $val == 1 ) {
			$value = 'yes';
		} else {
			$value = 'no';
		}
		return $value;
    }
	
	public function imageResizeToFit()
    {
		$value = '';
		$val = Mage::getStoreConfig(self::XML_PATH_IMAGE_RESIZE_TO_FIT);
		if ( $val == 1 ) {
			$value = 'yes';
		} else {
			$value = 'no';
		}
		return $value;  	
    }
	
	public function imageRandomizeOrder()
    {
		$value = '';
		$val = Mage::getStoreConfig(self::XML_PATH_IMAGE_RANDOMIZE_ORDER);
		if ( $val == 1 ) {
			$value = 'yes';
		} else {
			$value = 'no';
		}
		return $value;  	
    }
	
	
	
	/*
		Text Functions
	*/
	
	public function textSize()
    {
    	return Mage::getStoreConfig(self::XML_PATH_TEXT_SIZE);    	
    }
	
	public function textColor()
    {
    	return Mage::getStoreConfig(self::XML_PATH_TEXT_COLOR);    	
    }
	
	public function textAreaWidth()
    {
    	return Mage::getStoreConfig(self::XML_PATH_TEXT_AREA_WIDTH);    	
    }
	
	public function textLineSpacing()
    {
    	return Mage::getStoreConfig(self::XML_PATH_TEXT_LINE_SPACING);    	
    }
	
	public function textLetterSpacing()
    {
    	return Mage::getStoreConfig(self::XML_PATH_TEXT_LETTER_SPACING);    	
    }
	
	public function textMarginLeft()
    {
    	return Mage::getStoreConfig(self::XML_PATH_TEXT_MARGIN_LEFT);    	
    }
	
	public function textMarginBottom()
    {
    	return Mage::getStoreConfig(self::XML_PATH_TEXT_MARGIN_BOTTOM);    	
    }
	
	public function textBackgroundBlur()
    {
		$value = '';
		$val = Mage::getStoreConfig(self::XML_PATH_TEXT_BACKGROUND_BLUR);
		if ( $val == 1 ) {
			$value = 'yes';
		} else {
			$value = 'no';
		}
		return $value;
    }
	
	public function textBackgroundColor()
    {
    	return Mage::getStoreConfig(self::XML_PATH_TEXT_BACKGROUND_COLOR);    	
    }
	
	public function textBackgroundTransparency()
    {
    	return Mage::getStoreConfig(self::XML_PATH_TEXT_BACKGROUND_TRANSPARENCY);    	
    }
	
	/*
		Transition Functions
	*/
	
	public function transitionType()
    {
    	return Mage::getStoreConfig(self::XML_PATH_TRANSITION_TYPE);    	
    }
	
	public function transitionRandomEffects()
    {
		$value = '';
		$val = Mage::getStoreConfig(self::XML_PATH_TRANSITION_RANDOM_EFFECTS);
		if ( $val == 1 ) {
			$value = 'yes';
		} else {
			$value = 'no';
		}
		return $value;   	
    }
	
	public function transitionDelayTimeFixed()
    {
    	return Mage::getStoreConfig(self::XML_PATH_TRANSITION_DELAY_TIME_FIXED);    	
    }
	
	public function transitionDelayTimePerWord()
    {
    	return Mage::getStoreConfig(self::XML_PATH_TRANSITION_DELAY_TIME_PER_WORD);    	
    }
	
	public function transitionSpeed()
    {
    	return Mage::getStoreConfig(self::XML_PATH_TRANSITION_SPEED);    	
    }
	
	public function transitionBlur()
    {
		$value = '';
		$val = Mage::getStoreConfig(self::XML_PATH_TRANSITION_BLUR);
		if ( $val == 1 ) {
			$value = 'yes';
		} else {
			$value = 'no';
		}
		return $value;	
    }
	
	/*
		Show Hide Functions
	*/
	
	public function showTimerClock()
    {
		
		$value = '';
		$val = Mage::getStoreConfig(self::XML_PATH_SHOW_TIMER_CLOCK);
		if ( $val == 1 ) {
			$value = 'yes';
		} else {
			$value = 'no';
		}
		return $value;	
    }
	
	public function showNextButton()
    {
		$value = '';
		$val = Mage::getStoreConfig(self::XML_PATH_SHOW_NEXT_BUTTON);
		if ( $val == 1 ) {
			$value = 'yes';
		} else {
			$value = 'no';
		}
		return $value;	
    }
	
	public function showBackButton()
    {
		$value = '';
		$val = Mage::getStoreConfig(self::XML_PATH_SHOW_BACK_BUTTON);
		if ( $val == 1 ) {
			$value = 'yes';
		} else {
			$value = 'no';
		}
		return $value;	 	
    }
	
	public function showNumberButtons()
    {
		$value = '';
		$val = Mage::getStoreConfig(self::XML_PATH_SHOW_NUMBER_BUTTONS);
		if ( $val == 1 ) {
			$value = 'yes';
		} else {
			$value = 'no';
		}
		return $value;	
    }
	
	public function showNumberButtonsAlways()
    {
		$value = '';
		$val = Mage::getStoreConfig(self::XML_PATH_SHOW_NUMBER_BUTTONS_ALWAYS);
		if ( $val == 1 ) {
			$value = 'yes';
		} else {
			$value = 'no';
		}
		return $value;	
    }
	
	public function showNumberButtonsHorizontal()
    {
		$value = '';
		$val = Mage::getStoreConfig(self::XML_PATH_SHOW_NUMBER_BUTTONS_HORIZONTAL);
		if ( $val == 1 ) {
			$value = 'yes';
		} else {
			$value = 'no';
		}
		return $value;	   	
    }
	
	public function showNumberButtonsAscending()
    {
		$value = '';
		$val = Mage::getStoreConfig(self::XML_PATH_SHOW_NUMBER_BUTTONS_ASCENDING);
		if ( $val == 1 ) {
			$value = 'yes';
		} else {
			$value = 'no';
		}
		return $value;	   	
    }
	
	public function showPlayPauseOnTimer()
    {
		$value = '';
		$val = Mage::getStoreConfig(self::XML_PATH_SHOW_PLAY_PAUSE_ON_TIMER);
		if ( $val == 1 ) {
			$value = 'yes';
		} else {
			$value = 'no';
		}
		return $value;	  	
    }
	
	public function alignButtonsLeft()
    {
		$value = '';
		$val = Mage::getStoreConfig(self::XML_PATH_ALIGN_BUTTONS_LEFT);
		if ( $val == 1 ) {
			$value = 'yes';
		} else {
			$value = 'no';
		}
		return $value;	
    }
	
	public function alignTextTop()
    {
		$value = '';
		$val = Mage::getStoreConfig(self::XML_PATH_ALIGN_TEXT_TOP);
		if ( $val == 1 ) {
			$value = 'yes';
		} else {
			$value = 'no';
		}
		return $value;	
    }
	
	public function generateXML() {
			
			$resource = Mage::getSingleton('core/resource');
			$read= $resource->getConnection('core_read');
			$bannersTable = $resource->getTableName('banner');
			
			$banners = Mage::getModel('banner/banner')->getCollection()
							//->addStoreFilter(Mage::app()->getStore(true)->getId())
							//->addOrder('main_table.sort_order', 'ASC')
							->getData();
							
			
			$bannersXML = '';
			$bannersXML .= '<?xml version="1.0" encoding="utf-8" ?>';
			$bannersXML .= '<Banner ';
			
			$bannersXML .= 'bannerWidth="'.$this->bannerWidth().'" ';
			$bannersXML .= 'bannerHeight="'.$this->bannerHeight().'" ';
			$bannersXML .= 'bannerBackgroundColor="'.$this->bannerBackgroundColor().'" ';
			$bannersXML .= 'autoPlay="'.$this->autoPlay().'" ';
			$bannersXML .= 'imageResizeToFit="'.$this->imageResizeToFit().'" ';
			$bannersXML .= 'imageRandomizeOrder="'.$this->imageRandomizeOrder().'" ';
			
			
			$bannersXML .= 'textSize="'.$this->textSize().'" ';
			$bannersXML .= 'textColor="'.$this->textColor().'" ';
			$bannersXML .= 'textAreaWidth="'.$this->textAreaWidth().'" ';
			$bannersXML .= 'textLineSpacing="'.$this->textLineSpacing().'" ';
			$bannersXML .= 'textLetterSpacing="'.$this->textLetterSpacing().'" ';
			$bannersXML .= 'textMarginLeft="'.$this->textMarginLeft().'" ';
			$bannersXML .= 'textMarginBottom="'.$this->textMarginBottom().'" ';
			$bannersXML .= 'textBackgroundBlur="'.$this->textBackgroundBlur().'" ';
			$bannersXML .= 'textBackgroundColor="'.$this->textBackgroundColor().'" ';
			$bannersXML .= 'textBackgroundTransparency="'.$this->textBackgroundTransparency().'" ';
			
			$bannersXML .= 'transitionType="'.$this->transitionType().'" ';
			$bannersXML .= 'transitionRandomEffects="'.$this->transitionRandomEffects().'" ';
			$bannersXML .= 'transitionDelayTimeFixed="'.$this->transitionDelayTimeFixed().'" ';
			$bannersXML .= 'transitionDelayTimePerWord="'.$this->transitionDelayTimePerWord().'" ';
			$bannersXML .= 'transitionSpeed="'.$this->transitionSpeed().'" ';
			$bannersXML .= 'transitionBlur="'.$this->transitionBlur().'" ';
			
			$bannersXML .= 'showTimerClock="'.$this->showTimerClock().'" ';
			$bannersXML .= 'showNextButton="'.$this->showNextButton().'" ';
			$bannersXML .= 'showBackButton="'.$this->showBackButton().'" ';
			$bannersXML .= 'showNumberButtons="'.$this->showNumberButtons().'" ';
			$bannersXML .= 'showNumberButtonsAlways="'.$this->showNumberButtonsAlways().'" ';
			$bannersXML .= 'showNumberButtonsHorizontal="'.$this->showNumberButtonsHorizontal().'" ';
			$bannersXML .= 'showNumberButtonsAscending="'.$this->showNumberButtonsAscending().'" ';
			$bannersXML .= 'showPlayPauseOnTimer="'.$this->showPlayPauseOnTimer().'" ';
			$bannersXML .= 'alignButtonsLeft="'.$this->alignButtonsLeft().'" ';
			$bannersXML .= 'alignTextTop="'.$this->alignTextTop().'" ';
			
			$bannersXML .= '> ';
			
			
			foreach ($banners as $_banner) {
				
				$bannerImage = Mage::getBaseUrl('media')."Banners/images/".$_banner["bannerimage"]; 
				$bannersXML .= '<item buttonLabel="" ';
				$bannersXML .= 'image="'.$bannerImage.'" ';
				$bannersXML .= 'link="'.$_banner["link"].'" ';
				$bannersXML .= 'target="'.$_banner["target"].'" ';
				$bannersXML .= 'delay="" ';
				$bannersXML .= 'textBlend="'.$_banner["textblend"].'"> ';
				$bannersXML .= '<![CDATA['.$_banner["content"].']]> ';
				$bannersXML .= '</item> ';	
				
			}
			$bannersXML .= '</Banner>';
			
			$fileName = Mage::getBaseDir('media') . "/Banners/". DS ."data.xml";
			$file= fopen($fileName, "w");
			fwrite($file, $bannersXML);
			fclose($file);
	}
	
	
}
