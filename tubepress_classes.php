<?php

/*
 * Serves as a constant object to hold CSS info
*/
class tubepressCSS {
	var $mainVid_id, $mainVid_class, $meta_class,
		$thumb_container_class, $runtime_class, $title_class, $meta_group;

	function tubepressCSS() {
		$this->container = 				"tubepress_container";
		$this->mainVid_id = 			"tubepress_mainvideo";
		$this->mainVid_class = 			"tubepress_mainvideo";
		$this->meta_class =				"tubepress_meta";
		$this->thumb_container_class =	"tubepress_video_thumbs";
		$this->thumb_class =			"tubepress_thumb";
		$this->thumbImg_class =			"tubepress_video_thumb_img";
		$this->runtime_class = 			"tubepress_runtime";
		$this->title_class = 			"tubepress_title";
		$this->success_class = 			"updated fade";
		$this->meta_group = 			"tubepress_meta_group";
	}
}

class tubepressTag {  

	var $tagString, $customOptions, $dbOptions;

	function tubepressTag($tagString, $optionsArray) {  
		$this->tagString = $tagString;  
		$this->customOptions = $optionsArray;
		foreach (array(get_option(TP_OPTS_META),get_option(TP_OPTS_ACCT),
			get_option(TP_OPTS_SEARCH), get_option(TP_OPTS_DISP),
			get_option(TP_OPTS_ADV), get_option(TP_OPTS_SRCHV), get_option(TP_OPTS_PLAYERMENU)
		) as $dbOptionArray) {
			foreach ($dbOptionArray as $dbOption) {
				$this->dbOptions[$dbOption->name] = $dbOption->value;
			}
		}
	}  

	function get_option($option = '') {
		if(!empty($this->customOptions) && isset($this->customOptions[$option])) {
			return $this->customOptions[$option];  
		}
		if(!empty($this->dbOptions) && isset($this->dbOptions[$option])) {
			return $this->dbOptions[$option];  
		}

	}  
} 

class tubepressVideo {
	var $metaValues;
		
	function tubepressVideo($videoXML) {
		$this->metaValues = array(
			TP_VID_AUTHOR =>		$videoXML->author->CDATA(),
		 	TP_VID_ID =>			$videoXML->id->CDATA(),
		 	TP_VID_TITLE =>			htmlentities($videoXML->title->CDATA(), ENT_QUOTES),
			TP_VID_LENGTH =>		tubepress_humanTime($videoXML->length_seconds->CDATA()),
 			TP_VID_RATING_AVG =>	$videoXML->rating_avg->CDATA(),
	 		TP_VID_RATING_CNT =>	number_format($videoXML->rating_count->CDATA()),
	 		TP_VID_DESC =>			$videoXML->description->CDATA(),
		 	TP_VID_VIEW =>			number_format($videoXML->view_count->CDATA()),
	 		TP_VID_UPLOAD_TIME =>	date("M j, Y", $videoXML->upload_time->CDATA()),
			TP_VID_COMMENT_CNT =>	number_format($videoXML->comment_count->CDATA()),
			TP_VID_TAGS =>			$videoXML->tags->CDATA(),
		 	TP_VID_URL =>			$videoXML->url->CDATA(),
		 	TP_VID_THUMBURL =>		$videoXML->thumbnail_url->CDATA()
		);
	}
}

class tubepressOption {
	var $name, $title, $description, $value;
	
	function tubePressOption($theName, $theTitle, $theDesc, $theValue) {
		$this->name = $theName;
		$this->description = $theDesc;
		$this->value = $theValue;
		$this->title = $theTitle;
	}
}
?>
