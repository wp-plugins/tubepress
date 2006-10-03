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

function tubepress_showgallery ($content = '') {
	$keyword = "[tubepress]";
	if (!strpos($content,$keyword)) return $content;

	$devID = 		get_option('devID');
	$username = 		get_option('username');

	$youtube_xml = get_youtube_xml($devID, $username); 
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
		$newcontent .= "ERROR: Could not retrieve favorites from YouTube.";
	}
	$newcontent .= printHTML_videofooter();
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
	$id = $vid['id'];
	$title = $vid['title'];
	$length = humanTime($vid['length_seconds']);
	$height = get_option('mainVidHeight');
	$width = get_option('mainVidWidth');
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
        $seconds = $length_seconds;
        $length = intval($seconds/60);
        $length .= ":" . $seconds%60;
	return $length;
}

function printHTML_smallvid($vid) {
	/* convert the time to human-friendly */
	$length = humanTime($vid['length_seconds']);

	$title = 		$vid['title'];
	$thumbnail_url = 	$vid['thumbnail_url'];
	$view_count = 		number_format($vid['view_count']);
	$id = 			$vid['id'];

	$thumbHeight = get_option('thumbHeight');
	$thumbWidth = get_option('thumbWidth');
	$height = get_option('mainVidHeight');
	$width = get_option('mainVidWidth');
	$caption = $title . "(" . $length . ")";

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
	echo "\t<script type=\"text/javascript\" src=\"wp-content/plugins/tubepress/tubepress.js\"></script>\n";
}

function insert_tubepress_css() {
	echo "\t<link rel=\"stylesheet\" href=\"wp-content/plugins/tubepress/tubepress.css\" type=\"text/css\"></link>";
}

function tubepress_add_options_page() {
	if (function_exists('add_options_page')) {
		add_options_page('TubePress Configuation', 'TubePress', 9, 'tubepress.php', 'tubepress_options_subpanel');
    	}
}

function tubepress_options_subpanel() {

	$errors = 0;
	$errorText = "";
	if (isset($_POST['tubepress_save'])) {
		if (isset($_POST['devID'])) 		update_option('devID', 		$_POST['devID']);
		if (isset($_POST['username'])) 		update_option('username', 	$_POST['username']);
		if (isset($_POST['mainVidWidth'])) 	update_option('mainVidWidth', 	$_POST['mainVidWidth']);
		if (isset($_POST['mainVidHeight'])) 	update_option('mainVidHeight', 	$_POST['mainVidHeight']);
		if (isset($_POST['thumbWidth'])) 	update_option('thumbWidth', 	$_POST['thumbWidth']);
		if (isset($_POST['thumbHeight'])) 	update_option('thumbHeight', 	$_POST['thumbHeight']);

print <<<EOT
		<div class="updated fade">
			<p><strong>
    			Options updated.
			</strong></p>
		</div>
EOT;
	}
	$devID = 		get_option('devID');
	$username = 		get_option('username');
	$mainVidWidth = 	get_option('mainVidWidth');
	$mainVidHeight = 	get_option('mainVidHeight');
	$thumbWidth = 		get_option('thumbWidth');
	$thumbHeight = 		get_option('thumbHeight');

print <<<EOT
	<div class=wrap>
  		<form method="post">
    			<h2>TubePress Options</h2>
     			<fieldset name="set1">
				<legend>YouTube account</legend>
				<table class="editform optiontable">
					<tr valign="top">
						<th scope="row">YouTube developer ID:</th>
						<td>
							<input name="devID" type="text" id="dev_id" class="code" value="$devID" size="40" />
							<br />
							Available from <a href="http://www.youtube.com/my_profile_dev">http://www.youtube.com/my_profile_dev</a>
						</td>

					</tr>
					<tr>
						<th scope="row">YouTube username:</th>
						<td>
							<input name="username" type="text" id="dev_id" class="code" value="$username" size="40" />
						</td>

					</tr>
				</table>
     			</fieldset>
     			<fieldset name="set2">
				<legend>Video display</legend>
				<table class="editform optiontable">
					<tr valign="top">
						<th scope="row">Main video width:</th>
						<td>
							<input name="mainVidWidth" type="text" id="mainVidWidth" class="code" value="$mainVidWidth" size="40" />
							<br/>Default: 425
						</td>

					</tr>
					<tr valign="top">
						<th scope="row">Main video height:</th>
						<td>
							<input name="mainVidHeight" type="text" id="mainVidHeight" class="code" value="$mainVidHeight" size="40" />
							<br/>Default: 350
						</td>

					</tr>
					<tr valign="top">
						<th scope="row">Thumbnail width:</th>
						<td>
							<input name="thumbWidth" type="text" id="thumbWidth" class="code" value="$thumbWidth" size="40" />
							<br/>Default: 130
						</td>

					</tr>
					<tr>
						<th scope="row">Thumbnail height:</th>
						<td>
							<input name="thumbHeight" type="text" id="thumbHeight" class="code" value="$thumbHeight" size="40" />
							<br/>Default: 97
						</td>

					</tr>
				</table>
     			</fieldset>
		<input type="submit" name="tubepress_save" value="Save" />
  		</form>
 	</div>
EOT;

}
add_action('admin_menu', 'tubepress_add_options_page');
add_option("devID", "qh7CQ9xJIIc", 'YouTube developer ID. Available from <a href="http://www.youtube.com/my_profile_dev">http://www.youtube.com/my_profile_dev</a>');
add_option("username", "3hough", "YouTube username.");
add_option("mainVidWidth", "425", "Width (px) of main video");
add_option("mainVidHeight", "350", "Height (px) of main video");
add_option("thumbWidth", "130", "Width (px) of video thumbnails");
add_option("thumbHeight", "97", "Height (px) of video thumbnails");

add_action('wp_head', 'insert_tubepress_css');
add_action('wp_head', 'insert_tubepress_js');
add_filter('the_content', 'tubepress_showgallery');

