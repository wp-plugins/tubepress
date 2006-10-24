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
require("tubepress_classes.php");
class_exists('IsterXmlSimpleXMLImpl') ||	require("simpleXML/IsterXmlSimpleXMLImpl.php");
class_exists('Snoopy') || 					require(ABSPATH . "wp-includes/class-snoopy.php");

/*
 * Main filter hook. Looks for  keyword
 * and replaces it with YouTube gallery if it's found
*/
function tubepress_showgallery ($content = '') {
	$keyword = get_option(TP_OPT_KEYWORD);
	/* Bail out fast if not found */
	if ((!strpos($content,$keyword)) || (!strpos($content, '[' . $keyword))) return $content;

	/* Parse the tag  */
	$options = tubepress_parse_tag($content);
	
	/* Grab the XML from YouTube's API */
	$youtube_xml = get_youtube_xml($options); 
	
	/* Check for a YouTube timeout */
	if ($youtube_xml == TP_XMLERR)
		return str_replace($keyword, TP_MSG_TIMEOUT, $content);

	/* get css */
	$css = new tubepressCSS();
		
	/* Loop through each video */
	$videoCount = 0;
	$newcontent = printHTML_videoheader($css);
	foreach ($youtube_xml->children() as $vid) {
		$video = new tubepressVideo($vid);
		if ($videoCount++ ==0) $newcontent .= printHTML_bigvid($video, $css, $options);
		$newcontent .= printHTML_smallvid($video, $css, $options);
	}
	
	/* Did we get any videos? */
	if ($videoCount == 0) $newcontent .= TP_MSG_YTERR;
	
	/* push out the footer */
	$newcontent .= printHTML_videofooter($css);

	/* We're done, so let's insert the gallery where the keyword is */
	return str_replace('[' . $keyword . ']', $newcontent, $content);
}

function tubepress_parse_tag($content = '') {  

	$optionsArray = array();  
	$keyword = get_option(TP_OPT_KEYWORD);  

	/* Use a regular expression to match everything in square brackets after the TubePress keyword */
	$regexp = '\[' . $keyword . ' ([A-Za-z0-9=_ ]+)\]';  
	preg_match("/$regexp/", $content, $matches);  

	/* Execute if anything was matched by the parentheses */
	if(isset($matches[1])) {  

		/* Break up the options and store them in an ASSOCIATIVE array */
		$pairs = explode(" ", $matches[1]);  
		foreach($pairs as $pair) {  
			$pieces = explode("=", $pair);  
			$optionsArray[$pieces[0]] = $pieces[1];  
			}  
	}  

	/* Create and return new tubepressTag object */
	return new tubepressTag($keyword, $matches[0], $optionsArray);  
}  

function printHTML_videoheader($css) {
	return <<<EOT
		</p><!-- for XHTML validation -->
		<div class="$css->container">
EOT;
}

function printHTML_videofooter($css) {
	return <<<EOT
			</div>
		</div>
		<p><!-- for XHTML validation -->
EOT;
}

function printHTML_bigvid($vid, $css, $options) {
	$mainVideoHeader = TP_MAINVID_HEADER;
	$width = $options->get_option(TP_OPT_VIDWIDTH) . "px";
	$height = $options->get_option(TP_OPT_VIDHEIGHT) . "px";
	return <<<EOT
		<div id="$css->mainVid_id" class="$css->mainVid_class">
        	<span class="$css->meta_class">$mainVideoHeader</span> 
			<span class="$css->title_class">$vid->title</span> 
			<span class="$css->runtime_class">($vid->length)</span>
                        
			<object type="application/x-shockwave-flash" style="width:$width; height:$height;" data="http://www.youtube.com/v/$vid->id" >
				<param name="movie" value="http://www.youtube.com/v/$vid->id" />
			</object>
		</div> <!-- $css->mainVid_class -->
		<div class="$css->thumb_container_class">
EOT;
}

function printHTML_smallvid($vid, $css, $options) {
	$caption = 		$vid->title . "(" . $vid->length . ")";
	$thumbWidth = $options->get_option(TP_OPT_THUMBWIDTH);
	$thumbHeight = $options->get_option(TP_OPT_THUMBHEIGHT);
	$vidWidth = $options->get_option(TP_OPT_VIDWIDTH);
	$vidHeight = $options->get_option(TP_OPT_VIDHEIGHT);
return <<<EOT
	<div class="$css->thumb_class">
		<div class="$css->thumbImg_class">
			<a href='#' onclick="javascript: playVideo('$vid->id', '$vidHeight', '$vidWidth','$vid->title', '$vid->length'); return true;">
				<img alt="$vid->title"  src="$vid->thumbnail_url" width="$thumbWidth"  height="$thumbHeight"  />
			</a>
		</div>
		<div class="$css->title_class">
			<a href='#' onclick="javascript: playVideo('$vid->id', '$vidHeight', '$vidWidth', '$vid->title', '$vid->length'); return true;">$vid->title</a><br/>
			<span class="$css->runtime_class">$vid->length</span>
		</div>
		<span class="$css->meta_class">Views: </span>$vid->view_count<br/>
		<span class="$css->meta_class">Rating: </span>$vid->rating_avg<br/>
		<span class="$css->meta_class">Author: </span>$vid->author<br/>
	</div><!-- $css->thumb_class -->
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
function get_youtube_xml($options) {
	$request = TP_YOUTUBE_RESTURL;
	
	switch ($options->get_option(TP_OPT_SEARCHBY)) {
		case TP_SRCH_USER:
			$request .= "method=youtube.videos.list_by_user&user=" . $options->get_option(TP_OPT_SEARCHBY_USERVAL);
			break;
		case TP_SRCH_FAV:
			$request .= "method=youtube.users.list_favorite_videos&user=" . $options->get_option(TP_OPT_USERNAME);
			break;
		case TP_SRCH_TAG:
			$request .= "method=youtube.videos.list_by_tag&tag=" . $options->get_option(TP_OPT_SEARCHBY_TAGVAL);
			break;
		case TP_SRCH_YV:
			$request .= "method=youtube.videos.list_by_user&user=" . $options->get_option(TP_OPT_USERNAME);
			break;
	}

	$request .= "&dev_id=" . $options->get_option(TP_OPT_DEVID);
	$snoopy = new snoopy;
	$snoopy->read_timeout = $options->get_option(TP_OPT_TIMEOUT);
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


?>
