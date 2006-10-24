<?php

class tubepressCSS {
	var $mainVid_id, $mainVid_class, $meta_class,
		$thumb_container_class, $runtime_class, $title_class;

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
	}
}

class tubepressTag {  

	var $tagString, $customOptions;

	function tubepressTag($tagString, $customOptions) {  
		$this->$tagString = $tagString;  
		$this->$optionsArray = $customOptions;
	}  

	function get_option($option = '') {
		if(!empty($this->$optionsArray) && isset($this->$optionsArray[$option]))
			return $this->$optionsArray[$option];  

		$searchVariables = get_option(TP_OPTS_SRCHV);
		$tempArray = array();
		if (($option == TP_VID_TITLE) || ($option == TP_VID_LENGTH) || ($option == TP_VID_VIEW) || 
			($option == TP_VID_AUTHOR) || ($option == TP_VID_ID) || ($option == TP_VID_RATING_AVG) || 
			($option == TP_VID_RATING_CNT) || ($option == TP_VID_DESC) || ($option == TP_VID_UPLOAD_TIME) || 
			($option == TP_VID_COMMENT_CNT) || ($option == TP_VID_TAGS) || ($option == TP_VID_URL)) {
			$tempArray = get_option(TP_OPTS_META);
			}
		else if (($option == TP_OPT_DEVID) || ($option == TP_OPT_USERNAME)) {
			$tempArray = get_option(TP_OPTS_ACCT);
			}
		else if (($option == TP_SRCH_YV) || ($option == TP_SRCH_FAV) || ($option == TP_SRCH_TAG) || 
			($option == TP_SRCH_USER)) {
			$tempArray = get_option(TP_OPTS_SEARCH);
			}
		else if (($option == TP_OPT_KEYWORD) || ($option == TP_OPT_VIDWIDTH) || ($option == TP_OPT_VIDHEIGHT) || 
			($option == TP_OPT_THUMBWIDTH) || ($option == TP_OPT_THUMBHEIGHT)) {
			$tempArray = get_option(TP_OPTS_DISP);
			}
		else if ($option == TP_OPT_TIMEOUT) {
			$tempArray = get_option(TP_OPTS_ADV);
			}
		else if (($option == TP_OPT_SEARCHBY) || ($option == TP_OPT_SEARCHBY_TAGVAL) || ($option == TP_OPT_SEARCHBY_USERVAL)) {
			$tempArray = get_option(TP_OPTS_SRCHV);
			}
		return $tempArray[$option]->value;
	}  
}  

class tubepressVideo {
	var $title, $length, $view_count, $author, $id, $rating_avg,
		$rating_count, $description, $upload_time, $comment_count,
		$tags, $url, $thumbnail_url;
		
	function tubepressVideo($videoXML) {
		$this->author = 		$videoXML->author->CDATA();
		$this->id = 			$videoXML->id->CDATA();
		$this->title = 			htmlentities($videoXML->title->CDATA(), ENT_QUOTES);
		$this->length = 		humanTime($videoXML->length_seconds->CDATA());
		$this->rating_avg = 	$videoXML->rating_avg->CDATA();
		$this->rating_count = 	$videoXML->rating_count->CDATA();
		$this->description = 	$videoXML->description->CDATA();
		$this->view_count = 	number_format($videoXML->view_count->CDATA());
		$this->upload_time = 	date("M j, Y", $videoXML->upload_time->CDATA());
		$this->comment_count = 	$videoXML->comment_count->CDATA();
		$this->tags = 			$videoXML->tags->CDATA();
		$this->url = 			$videoXML->url->CDATA();
		$this->thumbnail_url = 	$videoXML->thumbnail_url->CDATA();
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