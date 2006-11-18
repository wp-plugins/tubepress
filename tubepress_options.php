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

$metaOptions = array(
	TP_VID_TITLE => 		new tubepressOption(TP_VID_TITLE, 	TP_MSG_VIDTITLE, '', true),
	TP_VID_LENGTH => 		new tubepressOption(TP_VID_LENGTH, 	TP_MSG_VIDLEN, '', true),
	TP_VID_VIEW =>	 		new tubepressOption(TP_VID_VIEW, 	TP_MSG_VIDVIEWS, '', true),
	TP_VID_AUTHOR => 		new tubepressOption(TP_VID_AUTHOR , 	TP_MSG_VIDAUTHOR, '', false),
	TP_VID_ID => 			new tubepressOption(TP_VID_ID, 		TP_MSG_VIDID, '', false),
	TP_VID_RATING_AVG => 		new tubepressOption(TP_VID_RATING_AVG, 	TP_MSG_VIDRATING, '', false),
	TP_VID_RATING_CNT => 		new tubepressOption(TP_VID_RATING_CNT, 	TP_MSG_VIDRATINGS, '', false),
	TP_VID_UPLOAD_TIME => 		new tubepressOption(TP_VID_UPLOAD_TIME, TP_MSG_VIDUPLOAD, '', false),
	TP_VID_COMMENT_CNT => 		new tubepressOption(TP_VID_COMMENT_CNT, TP_MSG_VIDCOMMENTS, '', false),
	TP_VID_TAGS => 			new tubepressOption(TP_VID_TAGS, 	TP_MSG_VIDTAGS, '', false),
	TP_VID_URL => 			new tubepressOption(TP_VID_URL, 	TP_MSG_VIDURL, '', false),
	TP_VID_THUMBURL =>		new tubePressOption(TP_VID_THUMBURL,	TP_MSG_VIDTHUMBURL, '', false),
	TP_VID_DESC => 			new tubepressOption(TP_VID_DESC, 	TP_MSG_VIDDESC, '', false));
$accountInfo = array(
	TP_OPT_DEVID => 	new tubepressOption(TP_OPT_DEVID, TP_MSG_DEVID_TITLE, TP_MSG_DEVID_DESC . ' <a href="' . TP_YOUTUBEDEVLINK . '">' . TP_YOUTUBEDEVLINK . '</a>', "qh7CQ9xJIIc"),
	TP_OPT_USERNAME => 	new tubepressOption(TP_OPT_USERNAME, TP_MSG_USERNAME_TITLE, TP_MSG_USERNAME_DESC, "3hough"));
$videoSearchOptions = array(
	TP_SRCH_YV => 	new tubepressOption(TP_SRCH_YV, 	TP_MSG_SRCH_YV_TITLE, '', ''),
	TP_SRCH_FAV => 	new tubepressOption(TP_SRCH_FAV, 	TP_MSG_SRCH_FAV_TITLE, '', ''),
	TP_SRCH_TAG => 	new tubepressOption(TP_SRCH_TAG, 	TP_MSG_SRCH_TAG_TITLE, '', ''),
	TP_SRCH_USER => new tubepressOption(TP_SRCH_USER, 	TP_MSG_SRCH_USER_TITLE, '', ''));
$videoDisplayOptions = array(
	TP_OPT_VIDWIDTH => 	new tubepressOption(TP_OPT_VIDWIDTH, TP_MSG_VIDWIDTH_TITLE, TP_MSG_VIDWIDTH_DESC, "425"),
	TP_OPT_VIDHEIGHT => 	new tubepressOption(TP_OPT_VIDHEIGHT, TP_MSG_VIDHEIGHT_TITLE, TP_MSG_VIDHEIGHT_DESC, "350"),
	TP_OPT_THUMBWIDTH => 	new tubepressOption(TP_OPT_THUMBWIDTH, TP_MSG_THUMBWIDTH_TITLE, TP_MSG_THUMBWIDTH_DESC, "120"),
	TP_OPT_THUMBHEIGHT => 	new tubepressOption(TP_OPT_THUMBHEIGHT, TP_MSG_THUMBHEIGHT_TITLE, TP_MSG_THUMBHEIGHT_DESC, "90"));
$advancedOptions = array(
	TP_OPT_KEYWORD => 	new tubepressOption(TP_OPT_KEYWORD, TP_MSG_KEYWORD_TITLE, TP_MSG_KEYWORD_DESC, "tubepress"),
	TP_OPT_TIMEOUT => new tubepressOption(TP_OPT_TIMEOUT, TP_MSG_TIMEOUT_TITLE, TP_MSG_TIMEOUT_DESC, "6"));
$searchVariables = array(
	TP_OPT_SEARCHBY => 		new tubepressOption(TP_OPT_SEARCHBY, ' ', '', "favorites"),
	TP_SRCH_TAGVAL => 	new tubepressOption(TP_SRCH_TAGVAL, ' ', '', "colbert"),
	TP_SRCH_USERVAL => 	new tubepressOption(TP_SRCH_USERVAL, ' ', '', "3hough"));


add_option(TP_OPTS_META,		$metaOptions);
add_option(TP_OPTS_ACCT,		$accountInfo);
add_option(TP_OPTS_SEARCH,		$videoSearchOptions);
add_option(TP_OPTS_DISP,		$videoDisplayOptions);
add_option(TP_OPTS_ADV, 		$advancedOptions);
add_option(TP_OPTS_SRCHV,		$searchVariables);

/* Adds our options page to the main WP options panel */
function tubepress_add_options_page() {
	if (function_exists('add_options_page'))
		add_options_page(TP_MSG_OPTPANELTITLE, TP_MSG_OPTPANELMENU, 9, 'tubepress.php', 'tubepress_options_subpanel');
}

/* Main function for our options page */
function tubepress_options_subpanel() {

	/* are we updating options? */
	if (isset($_POST['tubepress_save'])) tubepress_update_options();

	print <<<EOT
	<div class="wrap">
  		<form method="post">
		<h2>TubePress Options</h2>
EOT;

	tubepress_printHTML_genericOptionsArray(	get_option(TP_OPTS_ACCT), TP_MSG_ACCT, 30);
	tubepress_printHTML_searchArray(		get_option(TP_OPTS_SEARCH), TP_MSG_WHICHVIDS);
	tubepress_printHTML_genericOptionsArray(	get_option(TP_OPTS_DISP), TP_MSG_VIDDISP, 20);
	tubepress_printHTML_metaArray(		get_option(TP_OPTS_META), TP_MSG_META, $metas);
	tubepress_printHTML_genericOptionsArray(	get_option(TP_OPTS_ADV), TP_MSG_ADV, 20);

	$saveValue = TP_MSG_SAVE;
	print <<<EOT
		<input type="submit" name="tubepress_save" value="$saveValue" />
  		</form>
 	</div>
EOT;

}

/* Go through all the post variables and update the corresponding
 * database entries.
*/
function tubepress_update_options() {
	$css = new tubepressCSS();
	
	//TODO fix search tag stuff

	$mostOptions = array(TP_OPTS_ACCT, TP_OPTS_SEARCH,
		TP_OPTS_DISP, TP_OPTS_ADV, TP_OPTS_SRCHV);
	
	foreach ($mostOptions as $arrayName) {
		$optionArray = get_option($arrayName);
		foreach (array_keys($optionArray) as $index) {
			$option =& $optionArray[$index];
			$optionValue = $_POST[$option->name];
			
			/* This is where I should do error checking */
			switch ($option->name) {
				case TP_OPT_KEYWORD:
					if (substr($optionValue, 0, 1) == '[')
						$optionValue = substr($optionValue, 1);
					if (substr($optionValue, -1, 1) == ']')
						$optionValue = substr($optionValue, 0, strlen($optionValue) - 1);
			}
			$option->value = $optionValue;
		}
		update_option($arrayName, $optionArray);
	}
	
	/* We treat meta values differently since they rely on true/false */
	$metaOptions = get_option(TP_OPTS_META);
	foreach (array_keys($metaOptions) as $index) {
		$metaOption =& $metaOptions[$index];
		if (in_array($metaOption->name, $_POST['meta'])) $metaOptions[$metaOption->name]->value = true;
		else $metaOptions[$metaOption->name]->value = false;
	}
	update_option(TP_OPTS_META, $metaOptions);
	$successMSG = TP_MSG_OPTSUCCESS;
	print <<<EOT
			<div id="message" class="$css->success_class">
				<p><strong>
	    			$successMSG
				</strong></p>
			</div>
EOT;
}

function tubepress_printHTML_metaArray($theArray, $arrayName) {
	tubepress_printHTML_optionHeader($arrayName);

	foreach ($theArray as $metaOption) {
		
		$selected = "";
		if ($metaOption->value == true) $selected = "CHECKED";

print <<<EOT
		<tr>
			<th>$metaOption->title</th>
			<td>
				<input type="checkbox" name="meta[]" value="$metaOption->name" $selected />
			</td>
		</tr>
EOT;
	}
	tubepress_printHTML_optionFooter();
}

function tubepress_printHTML_searchArray($theArray, $arrayName, $inputSize=20) {
	tubepress_printHTML_optionHeader($arrayName);

	$radioName = TP_OPT_SEARCHBY;

	$searchVars = get_option(TP_OPTS_SRCHV);

	foreach ($theArray as $option) {
		$selected = "";
		if ($option->name == $searchVars[TP_OPT_SEARCHBY]->value)
			$selected = "CHECKED";
		$inputBox = "";
		if ($option->name == TP_SRCH_TAG)
			$inputBox = '<input type="text" name="' . $searchVars[TP_SRCH_TAGVAL]->name . '" size="' . $inputSize . '" value="' . $searchVars[TP_SRCH_TAGVAL]->value . '" />';
		if ($option->name == TP_SRCH_USER)
			$inputBox = '<input type="text" name="' . $searchVars[TP_SRCH_USERVAL]->name . '" size="' . $inputSize . '" value="' . $searchVars[TP_SRCH_USERVAL]->value . '" />';
print <<<EOT
		<tr>
			<th>$option->title</th>
			<td>
				<input type="radio" name="$radioName" value="$option->name" $selected /> $inputBox
			</td>
		</tr>
EOT;
	}
	tubepress_printHTML_optionFooter();
}

function tubepress_printHTML_genericOptionsArray($theArray, $arrayName, $inputSize=20, $radioName='') {
	tubepress_printHTML_optionHeader($arrayName);
	
	$openBracket = "";
	$closeBracket = "";
	foreach ($theArray as $option) {
		
		if ($option->name == TP_OPT_KEYWORD) {
			$openBracket = '[';
			$closeBracket = ']';
		} else {
			$openBracket = "";
			$closeBracket = "";
		}
		print <<<EOT
					<tr valign="top">
						<th scope="row">$option->title:</th>
						<td>
							$openBracket<input name="$option->name" type="text" id="$option->name" class="code" value="$option->value" size="$inputSize" />$closeBracket
							<br />$option->description
						</td>

					</tr>
EOT;
	}
	tubepress_printHTML_optionFooter();
}

function tubepress_printHTML_optionHeader($arrayName) {
	print <<<EOT
			<fieldset>
				<legend>$arrayName</legend>
				<table class="editform optiontable">
EOT;
}

function tubepress_printHTML_optionFooter() {
print <<<EOT
	</table>
     			</fieldset>
EOT;
}

?>
