<?php
/*
Plugin Name: TubePress
Plugin URI: http://ehough.com/youtube/tubepress
Description: Displays configurable YouTube galleries in WordPress
Author: Eric Hough
Version: 1.0
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

/*
 * ENABLE THICKBOX HERE!
 * Change the following line to: $tubepress_enable_thickbox = true;
 * to enable ThickBox
* */
$tubepress_enable_thickbox = false;


/* Imports */
defined('TP_OPT_DEVID') ||							require("tubepress_strings.php");
class_exists('tubepressVideo') || 					require("tubepress_classes.php");
function_exists('tubepress_add_options_page') ||	require("tubepress_options.php");
function_exists('tubepress_get_youtube_xml') || 	require("tubepress_utility.php");
function_exists('tubepress_printSingleVideo') || 	require("tubepress_html.php");
class_exists('IsterXmlSimpleXMLImpl') || 			require("lib/simpleXML/IsterXmlSimpleXMLImpl.php");
class_exists('Snoopy') || 							require(ABSPATH . "wp-includes/class-snoopy.php");
class_exists('Net_URL') || 							require("lib/PEAR/Net_URL/URL.php");

/*
 * Main filter hook. Looks for  keyword
 * and replaces it with YouTube gallery if it's found
*/
function tubepress_showgallery ($content = '') {
	
	/* Get out fast if we're not needed */
	$quickOpts = get_option(TP_OPTION_NAME);
	if ($quickOpts == NULL) return $content;
	
	$adv = $quickOpts[TP_OPTS_ADV];
	$keyword = $adv[TP_OPT_KEYWORD]->value;
 	if (!strpos($content, '[' . $keyword->value)) return $content;

	/* Parse the tag  */
	$options = tubepress_parse_tag($content, $keyword);

	/* get css */
	$css = new tubepressCSS();

	/* Set up the header no matter what */
	$newcontent = tubepress_printHTML_videoheader($css);
	
	/* Are we printing a single video? */
	if (tubepress_printingSingleVideo($options)) {
		$newcontent .= tubepress_printHTML_singleVideo($css, $options);
		return tubepress_finish($newcontent, $content, $options, $css);
	}
	
	/* are we paging? */
	$paging = tubepress_areWePaging($options);

	/* Grab the XML from YouTube's API */
	$youtube_xml = tubepress_get_youtube_xml($options);

	/* count how many we got back */
	$videosReturnedCount = tubepress_count_videos($youtube_xml);

	$error = false;
	/* Check for a YouTube timeout */
	if ($youtube_xml == TP_XMLERR) {
		$error = true;
		$newcontent .= "<div>" . TP_MSG_TIMEOUT;
	}
	/* Did we get any videos? */
	if ($videosReturnedCount == 0) {
		$error = true;
		$newcontent .= "<div>" . TP_MSG_YTERR;
	}

	/* Loop through each video */
	if ($error == false) {
		$vidLimit = ($paging? $options->get_option(TP_OPT_VIDSPERPAGE) : $videosReturnedCount);
		if ($videosReturnedCount < $vidLimit) $vidLimit = $videosReturnedCount;
		
		for ($x = 0; $x < $vidLimit; $x++) {
			$video = new tubepressVideo($youtube_xml->video[$x]);
			if ($videoCount++ == 0) {
				$newcontent .= tubepress_printHTML_bigvid($video, $css, $options);
				if ($paging) $newcontent .= tubepress_printHTML_pagination($videosReturnedCount, $options, $css);
				$newcontent .= '<div class="' . $css->thumb_container_class . '">';
			}
			$newcontent .= tubepress_printHTML_smallvid($video, $css, $options);
		}
		$newcontent .= '</div>';
		if ($paging) $newcontent .= tubepress_printHTML_pagination($videosReturnedCount, $options, $css);
	}
	return tubepress_finish($newcontent, $content, $options, $css);
}

function tubepress_finish($newcontent, $content, $options, $css) {
	/* push out the footer */
	$newcontent .= tubepress_printHTML_videofooter($css);

	/* We're done, so let's insert the gallery (or single video) where the keyword is */
	return str_replace($options->tagString, $newcontent, $content);
}

function tubepress_insert_cssjs() {
	$url = get_settings('siteurl') . "/wp-content/plugins/tubepress";
	echo '<script type="text/javascript" src="' . $url . '/tubepress.js"></script>';
	echo '<link rel="stylesheet" href="' . $url . '/tubepress.css" type="text/css" />';
}

function tubepress_insert_thickbox() {
	$url = get_settings('siteurl') . "/wp-content/plugins/tubepress";
	echo '<script type="text/javascript" src="' . $url . '/lib/thickbox/jquery.js"></script>';
	echo '<script type="text/javascript" src="' . $url . '/lib/thickbox/thickbox.js"></script>';
	echo '<link rel="stylesheet" href="' . $url . '/lib/thickbox/thickbox.css" media="screen" type="text/css" />';
}

/* ACTIONS */
add_action('admin_menu', 	'tubepress_add_options_page');
add_action('wp_head', 		'tubepress_insert_cssjs');
if ($tubepress_enable_thickbox) add_action('wp_head', 'tubepress_insert_thickbox');

/* FILTERS */
add_filter('the_content', 'tubepress_showgallery');

?>
