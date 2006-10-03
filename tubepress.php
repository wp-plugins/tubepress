<?php
/*
Plugin Name: TubePress
Plugin URI: http://ehough.com/?page_id=20
Description: Displays a gallery of your YouTube favorites in WordPress
Author: Eric Hough
Version: 0.1
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


/*
 * Main filter hook. Looks for [tubepress] keyword
 * and replaces it with YouTube gallery if it's found
*/
function tubepress_showgallery ($content = '') {

	/* Bail out fast if not found */
	$keyword = "[tubepress]";
	if (!strpos($content,$keyword)) return $content;

	/* Grab the XML from YouTube's API */
	$youtube_xml = get_youtube_xml(get_option('devID'), get_option('username')); 
	
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
	return str_replace($keyword, $newcontent, $content);
}

function printHTML_videoheader() {
	return <<<EOT
		<div class="tubepress_container">
EOT;
}

function printHTML_videofooter() {
	return <<<EOT
			</div>
		</div>
EOT;
}

function printHTML_bigvid($vid) {
	$id = 		$vid['id'];
	$title = 	$vid['title'];
	$length = 	humanTime($vid['length_seconds']);
	$height = 	get_option('mainVidHeight');
	$width = 	get_option('mainVidWidth');

	return <<<EOT
		<div id="tubepress_the_video" class="tubepress_video_full">
			<div class="tubepress_meta_large">
				Latest post: $title ($length)
			</div>
			<object width="$width" height="$height">
				<param name="movie" value="http://www.youtube.com/v/$id" />
				<embed src="http://www.youtube.com/v/$id" type="application/x-shockwave-flash" width="$width" height="$height" />
			</object>
		</div> <!-- tubepress_video_full -->
		<div class="tubepress_video_thumbs">
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
	$title = 		$vid['title'];
	$thumbnail_url = 	$vid['thumbnail_url'];
	$view_count = 		number_format($vid['view_count']);
	$id = 			$vid['id'];

	$thumbHeight = 	get_option('thumbHeight');
	$thumbWidth = 	get_option('thumbWidth');
	$height = 	get_option('mainVidHeight');
	$width = 	get_option('mainVidWidth');
	$caption = 	$title . "(" . $length . ")";

return <<<EOT
	<div class="tubepress_video_thumb">
		<div class="tubepress_video_thumb_img">
			<a title= href="#" onclick="javascript: playVideo('$id', '$height', '$width', '$caption'); return true;">
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
	$request = "http://www.youtube.com/api2_rest?method=youtube.users.list_favorite_videos&dev_id=" . $devID . "&user=" . $username;
	$master_node = "video_list";
	$ch = curl_init($request);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	$result=curl_exec ($ch);
	curl_close ($ch);
	$xml = new SimpleXMLElement($result);
	return (array)$xml->$master_node;
}

function insert_tubepress_js() {
	echo '\t<script type="text/javascript" src="wp-content/plugins/tubepress/tubepress.js"></script>\n';
}

function insert_tubepress_css() {
	echo '\t<link rel="stylesheet" href="wp-content/plugins/tubepress/tubepress.css" type="text/css"></link>';
}

function message($myString) {
	$msgs = get_option('msgs');
	return $msgs[$myString];
}

/* MESSAGES */
$msg['devIDlink'] = 		"http://www.youtube.com/my_profile_dev";
$msg['success'] = 		"Options updated.";
$msg['errorXML'] = 		"ERROR: Could not retrieve gallery information from YouTube";
$msg['optionsPanelTitle'] = 	"TubePress Configuration";
$msg['optionsPanelMenuItem'] = 	"TubePress";

/* ACTIONS */
add_action('admin_menu', 	'tubepress_add_options_page');
add_action('wp_head', 		'insert_tubepress_css');
add_action('wp_head', 		'insert_tubepress_js');

/* OPTIONS */
add_option("msgs",		$msg,		"Message strings");
add_option("username", 		"3hough", 	"YouTube username.");
add_option("mainVidWidth", 	"425", 		"Max width (px) of main video");
add_option("mainVidHeight", 	"350", 		"Max height (px) of main video");
add_option("thumbWidth", 	"130", 		"Max width (px) of video thumbnails");
add_option("thumbHeight", 	"97", 		"Max height (px) of video thumbnails");
add_option("devIDlink",		"http://www.youtube.com/my_profile_dev", "Link to access YouTube developer ID");
add_option("devID", 		"qh7CQ9xJIIc", 	'YouTube developer ID');

/* FILTERS */
add_filter('the_content', 'tubepress_showgallery');

