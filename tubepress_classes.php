<?php
/*
tubepress_classes.php
The classes used in TubePress

THANKS:
Matt Doyle (http://notdrunk.net) was responsible for designing and developing the "option overriding"
capability of this plugin.

This plugin was based on the "mytube" plugin by Vaam Yob (http://rane.hasitsown.com/blog/plink/technical/27/wordpress-youtube-video-gallery-plugin/) and
some code samples from WaxJelly (http://www.waxjelly.com/2006/08/29/a-more-complex-php-script-using-the-youtube-api-with-video-details-part-2/). Thanks!

Copyright (C) 2007 Eric D. Hough (k2eric@gmail.com)

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

/*
 * Serves as a constant object to hold CSS info
*/
class tubepressCSS {
	
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
		$this->pagination = 			"tubepress_pagination";
		$this->nextlink = 				"tubepress_next";
		$this->prevlink = 				"tubepress_prev";
	}
}

class tubepressTag {

	var $tagString, $customOptions, $dbOptions;

	function tubepressTag($tagString, $optionsArray) {
		$this->tagString = $tagString;
		$this->customOptions = $optionsArray;
		foreach (get_option(TP_OPTION_NAME) as $dbOptionArray) {
			foreach ($dbOptionArray as $dbOption) {
				$this->dbOptions[$dbOption->name] = $dbOption->value;
			}
		}
		$this->customOptions['site_url'] = get_settings('siteurl');
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
		/*
		 * You wouldn't think I'd need to check to make sure that XML
		 * is coming in as a parameter, but for some reason every
		 * now and then some non-XML shit sneaks in from YouTube. This is
		 * kinda a temp fix until I investigate further.
		 */
		if (is_a($videoXML, 'IsterSimpleXMLElement')) {
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
