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
	}
}

class tubepressVideo {
	var $title, $length, $view_count, $author, $id, $rating_avg,
		$rating_count, $description, $upload_time, $comment_count,
		$tags, $url, $thumbnail_url, $thumbHeight, $thumbWidth,
		$height, $width;
		
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
		$this->thumbHeight = 	get_option(TP_OPT_THUMBHEIGHT) . "px";
		$this->thumbWidth = 	get_option(TP_OPT_THUMBWIDTH) . "px";
		$this->height = 		get_option(TP_OPT_VIDHEIGHT) . "px";
		$this->width = 			get_option(TP_OPT_VIDWIDTH) . "px";
	}
}

class tubepressOption {
	var $name, $description, $defaultValue;
	
	function tubePressOption($theName, $theDesc, $theValue) {
		$this->name = $theName;
		$this->description = $theDesc;
		$this->defaultValue = $theValue;	
	}
}
?>