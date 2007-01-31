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

/* Imports */
defined('TP_OPT_DEVID') ||							require("tubepress_strings.php");
class_exists('tubepressVideo') || 					require("tubepress_classes.php");
function_exists('tubepress_add_options_page') ||	require("tubepress_options.php");
function_exists('tubepress_get_youtube_xml') || 	require("tubepress_utility.php");
function_exists('tubepress_printSingleVideo') || 	require("tubepress_html.php");
class_exists('IsterXmlSimpleXMLImpl') || 			require("simpleXML/IsterXmlSimpleXMLImpl.php");
class_exists('Snoopy') || 							require(ABSPATH . "wp-includes/class-snoopy.php");
class_exists('Net_URL') || 							require("PEAR/Net/URL.php");


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

	/* get css */
	$css = new tubepressCSS();
	
	/* Set up the header no matter what */
	$newcontent = tubepress_printHTML_videoheader($css);
	
	/* Are we printing a single video? */
	if (tubepress_printingSingleVideo($options)) {
		$newcontent .= tubepress_printSingleVideo($css, $options);
		return tubepress_finish($newcontent, $content, $options, $css);
	}
	
	/* Grab the XML from YouTube's API */
	$youtube_xml = tubepress_get_youtube_xml($options); 

	/* count how many we got back */
	$videosReturnedCount = tubepress_count_videos($youtube_xml);
	
	/* Print any pagination */
	$newcontent .= tubepress_printHTML_pagination($videosReturnedCount, $options);
	
	$error = false;
	/* Check for a YouTube timeout */
	if ($youtube_xml == TP_XMLERR) {
		$error = true;
		$newcontent .= TP_MSG_TIMEOUT;
	}
	/* Did we get any videos? */
	if ($videosReturnedCount == 0) {
		$error = true;
		$newcontent .= TP_MSG_YTERR;
	}
		
	/* Loop through each video */
	
	if ($error == false) {
		$vidLimit = $options->get_option(TP_OPT_VIDSPERPAGE);
		
		for ($x = 0; $x < $vidLimit; $x++) {
			$video = new tubepressVideo($youtube_xml->video[$x]);
			if ($videoCount++ == 0) $newcontent .= tubepress_printHTML_bigvid($video, $css, $options);
			$newcontent .= tubepress_printHTML_smallvid($video, $css, $options);
		}
	}
	return tubepress_finish($newcontent, $content, $options, $css);
}

function tubepress_finish($newcontent, $content, $options, $css) {
	/* push out the footer */
	$newcontent .= tubepress_printHTML_videofooter($css);

	/* We're done, so let's insert the gallery (or single video) where the keyword is */
	return str_replace($options->tagString, $newcontent, $content);
}

function tubepress_insert_js() {
	$url = get_settings('siteurl') . "/wp-content/plugins/tubepress";
	print <<<EOT
		<script type="text/javascript" src="$url/tubepress.js"></script>
		<script type="text/javascript" src="$url/thickbox/jquery.js"></script>
		<script type="text/javascript" src="$url/thickbox/thickbox.js"></script>
EOT;
}

function tubepress_insert_css() {
	$url = get_settings('siteurl') . "/wp-content/plugins/tubepress";
	print <<<EOT
		<link rel="stylesheet" href="$url/tubepress.css" type="text/css" />
		<link rel="stylesheet" href="$url/thickbox/thickbox.css" media="screen" type="text/css" />
EOT;
}

/* ACTIONS */
add_action('admin_menu', 	'tubepress_add_options_page');
add_action('wp_head', 		'tubepress_insert_css');
add_action('wp_head', 		'tubepress_insert_js');

/* FILTERS */
add_filter('the_content', 'tubepress_showgallery');

?>
