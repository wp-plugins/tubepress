<?php
/*
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

/* OPTIONS */
add_option(TP_OPT_KEYWORD,			"[tubepress]",	TP_OPT_KEYWORD_DESC);
add_option(TP_OPT_USERNAME,			"3hough",       TP_OPT_USERNAME_DESC);
add_option(TP_OPT_VIDWIDTH,			"425",         	TP_OPT_VIDWIDTH_DESC);
add_option(TP_OPT_VIDHEIGHT,		"350",         	TP_OPT_VIDHEIGHT_DESC);
add_option(TP_OPT_THUMBWIDTH,		"120",     		TP_OPT_THUMBWIDTH_DESC);
add_option(TP_OPT_THUMBEIGHT,		"90",       	TP_OPT_THUMBHEIGHT_DESC);
add_option(TP_OPT_DEVID,			"qh7CQ9xJIIc",  TP_OPT_DEVID_DESC);
add_option(TP_OPT_SEARCHBY,			TP_SRCH_FAV,	'');
add_option(TP_OPT_SEARCHBY_TAGVAL, 	"colbert",		'');
add_option(TP_OPT_SEARCHBY_USERVAL,	"3hough", 		'');
add_option(TP_OPT_TIMEOUT,			"6",			TP_OPT_TIMEOUT_DESC);
	
$metas = array(
	TP_VID_TITLE => true, 	TP_VID_LENGTH => true, 	TP_VID_VIEW => false, 
	TP_VID_AUTHOR => false, TP_VID_ID => false, 	TP_VID_RATING_AVG => false,
	TP_VID_RATING_CNT => false, 					TP_VID_DESC => false,
	TP_VID_UPLOAD_TIME => false, 					TP_VID_COMMENT_CNT =>false,
	TP_VID_TAGS => false, 							TP_VID_URL => false);
add_option("TP_VID_METAS",			$metas,			'');

function tubepress_add_options_page() {
	if (function_exists('add_options_page'))
		add_options_page(TP_MSG_OPTPANELTITLE, TP_MSG_OPTPANELMENU, 9, 'tubepress.php', 'tubepress_options_subpanel');
}

function tubepress_options_subpanel() {
	$youTubeAccountInfo = array(
		new tubepressOption(TP_OPT_DEVID, TP_OPT_DEVID_DESC, TP_OPT_DEVID_DEF),
		new tubepressOption(TP_OPT_USERNAME, TP_OPT_USERNAME_DESC, TP_OPT_USERNAME_DEF) 
	);
	$videoSearchOptions = array(
		new tubepressOption(TP_SRCH_YV, TP_SRCH_YV_DESC, ''),
		new tubepressOption(TP_SRCH_FAV, TP_SRCH_FAV_DESC, ''),
		new tubepressOption(TP_SRCH_TAG, TP_SRCH_TAG_DESC, ''),
		new tubepressOption(TP_SRCH_USER, TP_SRCH_USER_DESC, '')		
	);
	$videoDisplayOptions = array(
		new tubepressOption(TP_OPT_KEYWORD, TP_OPT_KEYWORD_DESC, TP_OPT_KEYWORD_DEF),
		new tubepressOption(TP_OPT_VIDWIDTH, TP_OPT_VIDWIDTH_DESC, TP_OPT_VIDWIDTH_DEF),
		new tubepressOption(TP_OPT_VIDHEIGHT, TP_OPT_VIDHEIGHT_DESC, TP_OPT_VIDHEIGHT_DEF),
		new tubepressOption(TP_OPT_THUMBWIDTH, TP_OPT_THUMBWIDTH_DESC, TP_OPT_THUMBWIDTH_DEF),
		new tubepressOption(TP_OPT_THUMBHEIGHT, TP_OPT_THUMBHEIGHT_DESC, TP_OPT_THUMBHEIGHT_DEF)
	);
	$advancedOptions = array(
		new tubepressOption(TP_OPT_TIMEOUT, TP_OPT_TIMEOUT_DESC, TP_OPT_TIMEOUT_DEF)
	);
	$searchOptions = array(
		new tubepressOption(TP_OPT_SEARCHBY, TP_OPT_SEARCHBY_USERVAL, TP_OPT_SEARCHBY_TAGVAL)
	);
	$allOptions = array($youTubeAccountInfo, $videoDisplayOptions, $advancedOptions, $searchOptions);
	
	/* are we updating options? */
	if (isset($_POST['tubepress_save'])) tubepress_update_options($allOptions);

	print <<<EOT
	<div class="wrap">
  		<form method="post">
		<h2>TubePress Options</h2>
EOT;

	tubepress_printHTML_genericOptionsArray($youTubeAccountInfo, "YouTube account", 30);
	tubepress_printHTML_searchArray($videoSearchOptions, "Which videos?");
	tubepress_printHTML_genericOptionsArray($videoDisplayOptions, "Video display", 20);
	printHTML_metaArray(get_option(TP_VID_METAS), "Video meta display");
	tubepress_printHTML_genericOptionsArray($advancedOptions, "Advanced", 20);

	print <<<EOT
		<input type="submit" name="tubepress_save" value="Save" />
  		</form>
 	</div>
EOT;

}
function tubepress_update_options($allOptions) {
	
	foreach ($allOptions as $k => $optionArray) {
		foreach ($optionArray as $t => $option) {
			switch ($option->name) {
				default:
					update_option($option->name, $_POST[$option->name]);
			}
		}
	}
		
	$metas = get_option(TP_VID_METAS);
	foreach (array_keys($metas) as $meta) {
		if (isset($_POST[$meta])) $metas[$meta] = true;
		else $metas[$meta] = false;
	}
	update_option(TP_VID_METAS, $metas);
			
	$success = TP_MSG_OPTSUCCESS;
	$cssSuccess = TP_CSS_SUCCESS;
	print <<<EOT
			<div id="message" class="$cssSuccess">
				<p><strong>
	    			$success
				</strong></p>
			</div>
EOT;
}

function printHTML_metaArray($theArray, $arrayName) {
	print <<<EOT
			<fieldset>
				<legend>$arrayName</legend>

				<table class="editform optiontable">
EOT;
	foreach ($theArray as $k => $option) {
		$optionName = $option->name;
		$optionDesc = $option->description;
		$optionDefault = $option->defaultValue;
		$optionValue = get_option($optionName);
		$selected = "";
		if ($option->name == get_option(TP_OPT_SEARCHBY))
			$selected = "CHECKED";
		if ($optionName == TP_SRCH_TAG)
			$inputBox = '<input type="text" name="' . TP_OPT_SEARCHBY_TAGVAL . '" size="' . $inputSize . '" value="' . get_option(TP_OPT_SEARCHBY_TAGVAL) . '" />';
print <<<EOT
		<tr>
			<th>$optionDesc</th>
			<td>
				<input type="radio" name="$radioName" value="$optionName" $selected /> $inputBox
			</td>
		</tr>
EOT;
	}
print <<<EOT
					</tr>
				</table>
     			</fieldset>
EOT;
}

function tubepress_printHTML_searchArray($theArray, $arrayName, $inputSize=20) {
	$radioName = TP_OPT_SEARCHBY;
	print <<<EOT
			<fieldset>
				<legend>$arrayName</legend>
				<table class="editform optiontable">
EOT;
	foreach ($theArray as $k => $option) {
		$optionName = $option->name;
		$optionDesc = $option->description;
		$optionDefault = $option->defaultValue;
		$optionValue = get_option($optionName);
		$selected = "";
		if ($option->name == get_option(TP_OPT_SEARCHBY))
			$selected = "CHECKED";
		$inputBox = "";
		if ($optionName == TP_SRCH_TAG)
			$inputBox = '<input type="text" name="' . TP_OPT_SEARCHBY_TAGVAL . '" size="' . $inputSize . '" value="' . get_option(TP_OPT_SEARCHBY_TAGVAL) . '" />';
		if ($optionName == TP_SRCH_USER)
			$inputBox = '<input type="text" name="' . TP_OPT_SEARCHBY_USERVAL . '" size="' . $inputSize . '" value="' . get_option(TP_OPT_SEARCHBY_USERVAL) . '" />';
print <<<EOT
		<tr>
			<th>$optionDesc</th>
			<td>
				<input type="radio" name="$radioName" value="$optionName" $selected /> $inputBox
			</td>
		</tr>
EOT;
	}
print <<<EOT
					</tr>
				</table>
     			</fieldset>
EOT;
}

function tubepress_printHTML_genericOptionsArray($theArray, $arrayName, $inputSize=20, $radioName='') {
	print <<<EOT

			<fieldset>
				<legend>$arrayName</legend>
				<table class="editform optiontable">
EOT;
	foreach ($theArray as $k => $option) {
		$optionName = $option->name;
		if ($inputType == 'radio') $optionName = $radioName;
		$optionDesc = $option->descriptions;
		$optionDefault = $option->defaultValue;
		$optionValue = get_option($optionName);
		print <<<EOT
					<tr valign="top">
						<th scope="row">$optionDesc:</th>
						<td>
							<input name="$optionName" type="text" id="$optionName" class="code" value="$optionValue" size="$inputSize" />
							<br />$optionDefault
						</td>

					</tr>
EOT;
	}
print <<<EOT
				</table>
     			</fieldset>
EOT;
}

?>
