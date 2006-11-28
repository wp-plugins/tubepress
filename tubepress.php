<?php
/*
Plugin Name: TubePress
Plugin URI: http://ehough.com/youtube/tubepress
Description: Displays configurable YouTube galleries in WordPress
Author: Eric Hough
Version: 0.8
Author URI: http://ehough.com

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
	$quickOpts = get_option(TP_OPTS_ADV);
	$keyword = $quickOpts[TP_OPT_KEYWORD];

	/* Bail out fast if not found */
 	if (!strpos($content, '[' . $keyword->value)) return $content;

	/* Parse the tag  */
	$options = tubepress_parse_tag($content, $keyword->value);
	
	/* Grab the XML from YouTube's API */
	$youtube_xml = get_youtube_xml($options); 

	/* Print the header no matter what */
	$newcontent = printHTML_videoheader($css);
	
	$error = false;
	/* Check for a YouTube timeout */
	if ($youtube_xml == TP_XMLERR) {
		$error = true;
		$newcontent .= TP_MSG_TIMEOUT;
	}
	/* Did we get any videos? */
	if ($youtube_xml == "") {
		$error = true;
		$newcontent .= TP_MSG_YTERR;
	}
	/* get css */
	$css = new tubepressCSS();
		
	/* Loop through each video */
	$videoCount = 0;
	if ($error == false) {
		foreach ($youtube_xml->children() as $vid) {
			$video = new tubepressVideo($vid);
			if ($videoCount++ == 0) $newcontent .= printHTML_bigvid($video, $css, $options);
			$newcontent .= printHTML_smallvid($video, $css, $options);
		}
	}
	/* push out the footer */
	$newcontent .= printHTML_videofooter($css);

	/* We're done, so let's insert the gallery where the keyword is */
	//return str_replace('[' . $keyword->value . ']', $newcontent, $content);
	return str_replace($options->tagString, $newcontent, $content);
}

function tubepress_parse_tag($content = '', $keyword) {  

	$optionsArray = array();  

	/* Use a regular expression to match everything in square brackets after the TubePress keyword */
	$regexp = '\[' . $keyword . ' ?([A-Za-z0-9=_ ]*)\]';  
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
	return new tubepressTag($matches[0], $optionsArray);  
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
	$width = $options->get_option(TP_OPT_VIDWIDTH) . "px";
	$height = $options->get_option(TP_OPT_VIDHEIGHT) . "px";
	$header = TP_MSG_MAINVID_HEADER;
	return <<<EOT
		<div id="$css->mainVid_id" class="$css->mainVid_class">
        	<span class="$css->meta_class">$header</span> 
			<span class="$css->title_class">{$vid->metaValues[TP_VID_TITLE]}</span>
			<span class="$css->runtime_class">({$vid->metaValues[TP_VID_LENGTH]})</span>
                        
			<object type="application/x-shockwave-flash" style="width:$width; height:$height;" data="http://www.youtube.com/v/{$vid->metaValues[TP_VID_ID]}" >
				<param name="movie" value="http://www.youtube.com/v/{$vid->metaValues[TP_VID_ID]}" />
			</object>
		</div> <!-- $css->mainVid_class -->
		<div class="$css->thumb_container_class">
EOT;
}

function printHTML_smallvid($vid, $css, $options) {
	$caption = 	$vid->metaValues[TP_VID_TITLE] . "(" . $vid->metaValues[TP_VID_LENGTH] . ")";
	$thumbWidth = 	$options->get_option(TP_OPT_THUMBWIDTH);
	$thumbHeight = 	$options->get_option(TP_OPT_THUMBHEIGHT);
	$vidWidth = 	$options->get_option(TP_OPT_VIDWIDTH);
	$vidHeight = 	$options->get_option(TP_OPT_VIDHEIGHT);
	$metaOptions = get_option(TP_OPTS_META);

$content = <<<EOT
	<div class="$css->thumb_class">
		<div class="$css->thumbImg_class">
			<a href='#' onclick="javascript: playVideo('{$vid->metaValues[TP_VID_ID]}', '$vidHeight', '$vidWidth','{$vid->metaValues[TP_VID_TITLE]}', '{$vid->metaValues[TP_VID_LENGTH]}'); return true;">
				<img alt="{$vid->metaValues[TP_VID_TITLE]}"  src="{$vid->metaValues[TP_VID_THUMBURL]}" width="$thumbWidth"  height="$thumbHeight"  />
			</a>
		</div>

		<div class="$css->meta_group">
		<div class="$css->title_class">
EOT;
	if ($options->get_option(TP_VID_TITLE) == true) {
		$content .= <<<EOP
			<a href='#' onclick="javascript: playVideo('{$vid->metaValues[TP_VID_ID]}', '$vidHeight', '$vidWidth', '{$vid->metaValues[TP_VID_TITLE]}', '{$vid->metaValues[TP_VID_LENGTH]}'); return true;">{$vid->metaValues[TP_VID_TITLE]}</a><br/>
EOP;
	}
	$content .= <<<EOT
		</div>
EOT;
	if ($options->get_option(TP_VID_LENGTH) == true) {
		$content .= <<<EOP
			<span class="$css->runtime_class">{$vid->metaValues[TP_VID_LENGTH]}</span><br/>
EOP;
	}

	foreach ($metaOptions as $option) {
		if (($option->name == TP_VID_LENGTH) || ($option->name == TP_VID_TITLE)) continue;
		if ($options->get_option($option->name) == "true") {
			$content .=  '<span class="' . $css->meta_class . '">';		
			switch($option->name) {
				case TP_VID_DESC:
					$content .= '</span>' . $vid->metaValues[$option->name];
					break;
				case TP_VID_THUMBURL:
					$content .= makeMetaLink($option->title, $vid->metaValues[$option->name]);
					break;
				case TP_VID_URL:
					$content .= makeMetaLink($option->title, $vid->metaValues[$option->name]);
					break;
				case TP_VID_AUTHOR:
					$content .= $option->title . ': ';
					$content .= makeMetaLink($vid->metaValues[$option->name], 'http://www.youtube.com/profile?user=' . $vid->metaValues[$option->name]); 
					break;
				case TP_VID_COMMENT_CNT:
					$content .= $option->title . ': ';
					$content .= makeMetaLink($vid->metaValues[$option->name], 'http://youtube.com/comment_servlet?all_comments&v=' . $vid->metaValues[$option->name]);
					break;
				case TP_VID_TAGS:
					$content .= $option->title . ': ';
					$tags = explode(" ", $vid->metaValues[$option->name]);
					$tags = implode("%20", $tags);
					$content .= makeMetaLink($vid->metaValues[$option->name], 'http://youtube.com/results?search_query=' . $tags . '&search=Search'); 
					break;
				default:
					$content .=  $option->title . ': </span>' . $vid->metaValues[$option->name];
			}
			$content .= '<br/>';
		}
	}
	$content .= '</div><!--' . $css->meta_group . ' -->';
	$content .= '</div><!--' . $css->thumb_class . '-->';
	return $content;
}

function makeMetaLink($linkText, $linkValue) {
	return '</span><a href="' . $linkValue . '">' . $linkText . '</a>';
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
			$request .= "method=youtube.videos.list_by_user&user=" . $options->get_option(TP_SRCH_USERVAL);
			break;
		case TP_SRCH_FAV:
			$request .= "method=youtube.users.list_favorite_videos&user=" . $options->get_option(TP_OPT_USERNAME);
			break;
		case TP_SRCH_TAG:
			$request .= "method=youtube.videos.list_by_tag&tag=" . $options->get_option(TP_SRCH_TAGVAL);
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

function tubepress_insert_js() {
	echo '<script type="text/javascript" src="' . get_settings('siteurl') . '/wp-content/plugins/tubepress/tubepress.js"></script>';
}

function tubepress_insert_css() {
	echo '<link rel="stylesheet" href="' . get_settings('siteurl') . '/wp-content/plugins/tubepress/tubepress.css" type="text/css"></link>';
}

/* ACTIONS */
add_action('admin_menu', 	'tubepress_add_options_page');
add_action('wp_head', 		'tubepress_insert_css');
add_action('wp_head', 		'tubepress_insert_js');

/* FILTERS */
add_filter('the_content', 'tubepress_showgallery');

/* FILES */
require("tubepress_options.php");


?>
