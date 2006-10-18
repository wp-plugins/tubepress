<?php
/*
Plugin Name: TubePress
Plugin URI: http://ehough.com/youtube/tubepress
Description: Displays configurable YouTube galleries in WordPress
Author: Eric Hough
Version: 0.6
Author URI: http://ehough.com

THANKS:
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

/* Imports */
require("tubepress_strings.php");
class_exists('IsterXmlSimpleXMLImpl') ||	require("simpleXML/IsterXmlSimpleXMLImpl.php");
class_exists('Snoopy') || 					require(ABSPATH . "wp-includes/class-snoopy.php");

/*
 * Main filter hook. Looks for  keyword
 * and replaces it with YouTube gallery if it's found
*/
function tubepress_showgallery ($content = '') {
	$keyword = get_option(TP_OPT_KEYWORD);
	/* Bail out fast if not found */
	if (!strpos($content,$keyword)) return $content;

	/* backwards compatability for previous versions of tubepress */
	if (substr($keyword, 0, 1) != "[") $keyword = "[" . $keyword;
	if (substr($keyword, -1, 1) != "]") $keyword .= "]";

	/* Grab the XML from YouTube's API */
	$youtube_xml = get_youtube_xml(get_option(TP_OPT_DEVID)); 

	/* convert to TubePressVideos and get which meta tags we're displaying */
	//$videoResults = processVideos($metaMap, $youtube_xml);
	
	/* Check for a YouTube timeout */
	if ($youtube_xml == TP_XMLERR)
		return str_replace($keyword, TP_MSG_TIMEOUT, $content);

	/* Loop through each video */
	$videoCount = 0;
	$newcontent = printHTML_videoheader();
	foreach ($youtube_xml->children() as $vid) {
		$video = new tubepressVideo($vid);
		if ($videoCount++ ==0) $newcontent .= printHTML_bigvid($video);
		$newcontent .= printHTML_smallvid($video);
	}
	
	/* Did we get any videos? */
	if ($videoCount == 0) $newcontent .= TP_MSG_YTERR;
	
	/* push out the footer */
	$newcontent .= printHTML_videofooter();

	/* We're done, so let's insert the gallery where the keyword is */
	return str_replace($keyword, $newcontent, $content);
}

function printHTML_videoheader() {
	$cssContainer = TP_CSS_CONTAINER;
	return <<<EOT
		</p><!-- for XHTML validation -->
		<div class="$cssContainer">
EOT;
}

function printHTML_videofooter() {
	return <<<EOT
			</div>
		</div>
		<p><!-- for XHTML validation -->
EOT;
}

function printHTML_bigvid($vid) {

	$cssMainVidID = 		TP_CSS_MAINVIDID;
	$cssMainVid =   		TP_CSS_MAINVID;
	$cssMeta =  			TP_CSS_META;
	$cssThumbContainer =  	TP_CSS_THUMBS;
	$mainVideoHeader = 		TP_MAINVID_HEADER;
	$cssRunTime = 			TP_CSS_RUNTIME;
	$cssTitle = 			TP_CSS_TITLE;

	return <<<EOT
		<div id="$cssMainVidID" class="$cssMainVid">
        	<span class="$cssMeta">$mainVideoHeader</span> 
			<span class="$cssTitle">$vid->title</span> 
			<span class="$cssRunTime">($vid->length)</span>
                        
			<object type="application/x-shockwave-flash" style="width:$vid->width; height:$vid->height;" data="http://www.youtube.com/v/$vid->id" >
				<param name="movie" value="http://www.youtube.com/v/$vid->id" />
			</object>
		</div> <!-- $cssMainVid -->
		<div class="$cssThumbContainer">
EOT;
}

function printHTML_smallvid($vid) {
	$caption = 		$title . "(" . $length . ")";

	$cssThumb = 	TP_CSS_THUMB;
	$cssThumbImg = 	TP_CSS_THUMBIMG;
	$cssMeta = 		TP_CSS_META;
	$cssRunTime = 	TP_CSS_RUNTIME;
	$cssTitle = 	TP_CSS_TITLE;

return <<<EOT
	<div class="$cssThumb">
		<div class="$cssThumbImg">
			<a href='#' onclick="javascript: playVideo('$vid->id', '$vid->height', '$vid->width','$vid->title', '$vid->length'); return true;">
				<img alt="$vid->title"  src="$vid->thumbnail_url" width="$vid->thumbWidth"  height="$vid->thumbHeight"  />
			</a>
		</div>
		<div class="$cssTitle">
			<a href='#' onclick="javascript: playVideo('$vid->id', '$vid->height', '$vid->width', '$vid->title', '$vid->length'); return true;">$vid->title</a><br/>
			<span class="$cssRunTime">$vid->length</span>
		</div>
		<span class="$cssMeta">Views: </span>$vid->view_count<br/>
		<span class="$cssMeta">Rating: </span>$vid->rating_avg<br/>
		<span class="$cssMeta">Author: </span>$vid->author<br/>
	</div><!-- $cssThumb -->
EOT;
}

function humanTime($length_seconds) {
	/* convert the time to human-friendly */
	$seconds = $length_seconds;
	$length = intval($seconds/60);
	$leftOverSeconds = $seconds%60;
	if ($leftOverSeconds < 10) $leftOverSeconds = "0" . $leftOverSeconds;
	$length .= 	":" . $leftOverSeconds;
	return $length;
}

/*
 * Connects to YouTube and grabs gallery info over
 * REST API
*/
function get_youtube_xml($devID) {
	$request = TP_YOUTUBE_RESTURL;
	
	switch (get_option(TP_OPT_SEARCHBY)) {
		case TP_SRCH_USER:
			$request .= "method=youtube.videos.list_by_user&user=" . get_option(TP_OPT_SEARCHBY_USERVAL);
			break;
		case TP_SRCH_FAV:
			$request .= "method=youtube.users.list_favorite_videos&user=" . get_option(TP_OPT_USERNAME);
			break;
		case TP_SRCH_TAG:
			$request .= "method=youtube.videos.list_by_tag&tag=" . get_option(TP_OPT_SEARCHBY_TAGVAL);
			break;
		case TP_SRCH_YV:
			$request .= "method=youtube.videos.list_by_user&user=" . get_option(TP_OPT_USERNAME);
			break;
	}

	$request .= "&dev_id=" . $devID;
	$snoopy = new snoopy;
	$snoopy->read_timeout = get_option(TP_OPT_TIMEOUT);
	$snoopy->fetch($request);
	if ($snoopy->results == "") return TP_XMLERR;
	$impl = new IsterXmlSimpleXMLImpl;
	$results = $impl->load_string($snoopy->results);
	return $results->ut_response->video_list;
}

function insert_tubepress_js() {
	echo '<script type="text/javascript" src="' . get_settings('siteurl') . '/wp-content/plugins/tubepress/tubepress.js"></script>';
}

function insert_tubepress_css() {
	echo '<link rel="stylesheet" href="' . get_settings('siteurl') . '/wp-content/plugins/tubepress/tubepress.css" type="text/css"></link>';
}

/* ACTIONS */
add_action('admin_menu', 	'tubepress_add_options_page');
add_action('wp_head', 		'insert_tubepress_css');
add_action('wp_head', 		'insert_tubepress_js');

/* FILTERS */
add_filter('the_content', 'tubepress_showgallery');

/* FILES */
require("tubepress_options.php");

class tubepressVideo {
	var $title, $length;
	var $view_count;
	var $author;
	var $id;
	var $rating_avg;
	var $rating_count;
	var $description;
	var $upload_time;
	var $comment_count;
	var $tags;
	var $url;
	var $thumbnail_url;
	var $thumbHeight;
	var $thumbWidth;
	var $height;
	var $width;
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
?>
