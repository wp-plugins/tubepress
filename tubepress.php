<?php
/*
Plugin Name: TubePress
Plugin URI: http://ehough.com/?page_id=20
Description: Displays a gallery of your YouTube favorites in WordPress
Author: Eric Hough
Version: 0.3
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
require("tubepress_strings.php");

/*
 * Main filter hook. Looks for [tubepress] keyword
 * and replaces it with YouTube gallery if it's found
*/
function tubepress_showgallery ($content = '') {
	/* Bail out fast if not found */
	if (!strpos($content,TP_OPT_KEYWORD)) return $content;

	/* Grab the XML from YouTube's API */
	$youtube_xml = get_youtube_xml(get_option(TP_OPT_DEVID), get_option(TP_OPT_USERNAME)); 
	
	/* Loop through each video and generate the HTML for each */
	$videoCount = 0;
	$newcontent = printHTML_videoheader();
	foreach ($youtube_xml as $k => $v) {
		if (is_array($v)) {
			foreach ($v as $k2=>$v2) {
				$vid = (array)$v2;
				if ($videoCount++ ==0) $newcontent .= printHTML_bigvid($vid);
				$newcontent .= printHTML_smallvid($vid);
			}		
		}
	}
	if ($videoCount == 0) {
		$newcontent .= message('error_xml');
	}
	$newcontent .= printHTML_videofooter();

	/* We're done, so let's insert the gallery where the keyword is */
	return str_replace(TP_OPT_KEYWORD, $newcontent, $content);
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
	$id = 		$vid['id'];
	$title = 	htmlentities($vid['title'], ENT_QUOTES);
	$length = 	humanTime($vid['length_seconds']);
	$height = 	get_option(TP_OPT_VIDHEIGHT) . "px";
	$width = 	get_option(TP_OPT_VIDWIDTH) . "px";

	$cssMainVidID = TP_CSS_MAINVIDID;
	$cssMainVid =   TP_CSS_MAINVID;
	$cssMainMeta =  TP_CSS_MAINMETA;
	$cssThumbContainer =  TP_CSS_THUMBS;

	$mainVideoHeader = TP_MAINVID_HEADER;

	return <<<EOT
		<div id="$cssMainVidID" class="$cssMainVid">
			<div class="$cssMainMeta">
				$mainVideoHeader $title ($length)
			</div>
			<object type="application/x-shockwave-flash" style="width:$width; height:$height;" data="http://www.youtube.com/v/$id" >
				<param name="movie" value="http://www.youtube.com/v/$id" />
			</object>
		</div> <!-- $cssMainVid -->
		<div class="$cssThumbContainer">
EOT;
}

function humanTime($length_seconds) {
	/* convert the time to human-friendly */
        $seconds = 	$length_seconds;
        $length = 	intval($seconds/60);
        $length .= 	":" . $seconds%60;
	return $length;
}

function printHTML_smallvid($vid) {

	$length = 		humanTime($vid['length_seconds']);
	$title = 		htmlentities($vid['title'], ENT_QUOTES);
	$thumbnail_url = 	$vid['thumbnail_url'];
	$view_count = 		number_format($vid['view_count']);
	$id = 			$vid['id'];

	$thumbHeight = 	get_option(TP_OPT_THUMBHEIGHT);
	$thumbWidth = 	get_option(TP_OPT_THUMBWIDTH);
	$height = 	get_option(TP_OPT_VIDHEIGHT);
	$width = 	get_option(TP_OPT_VIDWIDTH);
	$caption = 	$title . "(" . $length . ")";

	$cssThumb = TP_CSS_THUMB;
	$cssThumbImg = TP_CSS_THUMBIMG;

return <<<EOT
	<div class="$cssThumb">
		<div class="$cssThumbImg">
			 <a href='#' onclick="javascript: playVideo('$id', '$height', '$width', '$caption'); return true;">
			<img alt="$title"  src="$thumbnail_url" width="$thumbWidth"  height="$thumbHeight"/></a>
			<div id="tubepress_thumb_meta_$id" class="tubepress_video_thumb_meta" >
			<div class="tubepress_thumb_meta_label">
				Title: $title<br/>
				Length: $length<br/>
				Views: $view_count<br/>
			</div>
		</div>
	</div>
	</div>
EOT;
}

/*
 * Connects to YouTube and grabs gallery info over
 * REST API
*/
function get_youtube_xml($devID, $username) {
	$request = TP_YOUTUBE_RESTURL . "&dev_id=" . $devID . "&user=" . $username;
	$ch = curl_init($request);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	$result=curl_exec ($ch);
	curl_close ($ch);
	$xml = new SimpleXMLElement($result);
	$masternode = TP_MASTERNODE;
	return (array)$xml->$masternode;
}

function insert_tubepress_js() {
	echo '<script type="text/javascript" src="' . get_settings('siteurl') . '/wp-content/plugins/tubepress/tubepress.js"></script>';
}

function insert_tubepress_css() {
	echo '<link rel="stylesheet" href="' . get_settings('siteurl') . '/wp-content/plugins/tubepress/tubepress.css" type="text/css"></link>';
}

function message($myString) {
	$msgs = get_option('msgs');
	return $msgs[$myString];
}

/* MESSAGES */
$msg['success'] = 		"Options updated.";
$msg['errorXML'] = 		"ERROR: Could not retrieve gallery information from YouTube";
$msg['optionsPanelTitle'] = 	"TubePress Configuration";
$msg['optionsPanelMenuItem'] = 	"TubePress";

/* ACTIONS */
add_action('admin_menu', 	'tubepress_add_options_page');
add_action('wp_head', 		'insert_tubepress_css');
add_action('wp_head', 		'insert_tubepress_js');

/* FILTERS */
add_filter('the_content', 'tubepress_showgallery');

/* FILES */
require("tubepress_options.php");

?>
