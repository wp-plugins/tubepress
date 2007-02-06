<?php
/*
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

/* Adds our options page to the main WP options panel */
function tubepress_add_options_page() {
	if (function_exists('add_options_page'))
		add_options_page(TP_MSG_OPTPANELTITLE, TP_MSG_OPTPANELMENU, 9, 'tubepress.php', 'tubepress_options_subpanel');
}

function tubepress_initOptions() {
	/* OPTIONS */

	require_once("tubepress_init.php");
	
	$options = get_option(TP_OPTION_NAME);
	if (tubepress_validOptions($options)) return $options;
	return tubepress_addOptions();
}

/* Main function for our options page */
function tubepress_options_subpanel() {

	$dbOptions = tubepress_initOptions();

	$pageTitle = TP_MSG_OPTPANELTITLE;

	/* are we updating options? */
	if (isset($_POST['tubepress_save'])) {
		tubepress_update_options($dbOptions);
		$dbOptions = tubepress_initOptions();
	}

	print <<<EOT
	<div class="wrap">
  		<form method="post">
		<h2>$pageTitle</h2>This page sets your global (though a better term might be "default") options. Each option here can be overridden on any page that has your TubePress trigger tag. Also note that Thickbox is <strong>disabled</strong> by default and must be enabled manually. See the <a href="http://ehough.com/wp-content/plugins/tubepress/doc/tubepress_docs.html">documentation</a> for more information.
		<br /><br />
EOT;

	tubepress_printHTML_searchArray(			$dbOptions[TP_OPTS_SEARCH], TP_MSG_WHICHVIDS, $dbOptions[TP_OPTS_SRCHV]);
	tubepress_printHTML_genericOptionsArray(	$dbOptions[TP_OPTS_DISP], 	TP_MSG_VIDDISP, 5);
	tubepress_printHTML_playerLocationMenu($dbOptions);
	tubepress_printHTML_metaArray(				$dbOptions[TP_OPTS_META], 	TP_MSG_META, 	$metas);
	tubepress_printHTML_genericOptionsArray(	$dbOptions[TP_OPTS_ADV], 	TP_MSG_ADV, 	20);

	$saveValue = TP_MSG_SAVE;
	print <<<EOT
		<input type="submit" name="tubepress_save" value="$saveValue" />
  		</form>
 	</div>
EOT;

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
						<th style="font-weight: bold; font-size: 1em" scope="row">$option->title:</th>
						<td>
							$openBracket<input name="$option->name" type="text" id="$option->name" class="code" value="$option->value" size="$inputSize" />$closeBracket
							<br />$option->description
						</td>

					</tr>
EOT;
	}
	tubepress_printHTML_optionFooter();
}

function tubepress_printHTML_metaArray($theArray, $arrayName) {
	tubepress_printHTML_optionHeader($arrayName);
	echo "<tr><td width='10%'></td><td><table cellspacing='0' cellpadding='0' width='100%'>";

	$logan = 0;
	foreach ($theArray as $metaOption) {

		$colCount = $logan % 5;

		$selected = "";
		if ($metaOption->value == true) $selected = "CHECKED";

		if ($colCount == 0) echo "<tr>";
print <<<EOT
			<td>
				<input type="checkbox" name="meta[]" value="$metaOption->name" $selected />
			</td>
			<td><b>$metaOption->title</b></td>
EOT;
		if ($colCount == 4) echo "</tr>";
		$logan++;
	}
	echo "</tr></table>";
	tubepress_printHTML_optionFooter();
}

function tubepress_printHTML_optionHeader($arrayName) {
	print <<<EOT
			<fieldset>
EOT;
	if ($arrayName != "")
		echo '<h3>' . $arrayName . '</h3>';
print <<<EOT
				<table class="editform optiontable">
EOT;
}

function tubepress_printHTML_optionFooter() {
print <<<EOT
	</table>
     			</fieldset>
EOT;
}

function tubepress_printHTML_playerLocationMenu($dbOptions) {
	$locationVars = 	$dbOptions[TP_OPTS_PLAYERLOCATION];
	$theArray = 		$dbOptions[TP_OPTS_PLAYERMENU];
	$theOption = 		$theArray[TP_OPT_PLAYIN];
	tubepress_printHTML_optionHeader("");

print <<<EOT
			<tr>
			<th style="font-weight: bold; font-size: 1em">$theOption->title</th>
			<td><select name="$theOption->name">
EOT;
	foreach ($locationVars as $location) {
		$selected = "";
		if ($location->name == $theOption->value)
			$selected = "selected";
		$inputBox = "";
print <<<EOT
		<option value="$location->name" $selected>$location->title</option>
EOT;
	}
print <<<EOT
		</select>
	</td>
		</tr>
EOT;
	tubepress_printHTML_optionFooter();
}

function tubepress_printHTML_quickSrchVal($value, $searchVars, $inputSize) {
	$whichValue = "";
	switch ($value) {
		case TP_SRCH_TAG:
			$whichValue = TP_SRCH_TAGVAL;
			$inputSize = 40;
			break;
		case TP_SRCH_REL:
			$whichValue = TP_SRCH_RELVAL;
			$inputSize = 40;
			break;
		case TP_SRCH_USER: $whichValue = TP_SRCH_USERVAL;break;
		case TP_SRCH_PLST: $whichValue = TP_SRCH_PLSTVAL;break;
		case TP_SRCH_POPULAR: $whichValue = TP_SRCH_POPVAL;break;
		//case TP_SRCH_CATEGORY: $whichValue = TP_SRCH_CATVAL;break;
		case TP_SRCH_FAV: $whichValue = TP_SRCH_FAVVAL;break;
	}
	return '<input type="text" name="' . $searchVars[$whichValue]->name . '" size="' . $inputSize . '" value="' . $searchVars[$whichValue]->value . '" />';
}

function tubepress_printHTML_searchArray($theArray, $arrayName, $searchVars, $inputSize=20) {
	tubepress_printHTML_optionHeader($arrayName);

	$radioName = TP_OPT_SEARCHBY;

	foreach ($theArray as $option) {
		$selected = "";
		if ($option->name == $searchVars[TP_OPT_SEARCHBY]->value)
			$selected = "CHECKED";
		$inputBox = "";
		
		/* The idea here is only one mode that doesn't need any kind of input */
		if ($option->name != TP_SRCH_FEATURED)
				$inputBox = tubepress_printHTML_quickSrchVal($option->name, $searchVars, $inputSize);
		if ($option->name == TP_SRCH_POPULAR) {
			$name = TP_SRCH_POPVAL;
			$inputBox = <<<EOX
				<select name="$name">
					<option value="day">day</option>
					<option value="week">week</option>
					<option value="month">month</option>
				</select>
EOX;
		}
print <<<EOT
		<tr>
			<th style="font-weight: bold; font-size: 1em" valign="top">$option->title</th>
			<td>
				<input type="radio" name="$radioName" id="$option->name" value="$option->name" $selected /> $inputBox
				<br />$option->description
			</td>
		</tr>
EOT;
	}
	tubepress_printHTML_optionFooter();
}

/* Go through all the post variables and update the corresponding
 * database entries.
*/
function tubepress_update_options($dbOptions) {
	$css = new tubepressCSS();

	$mostOptions = array(TP_OPTS_SEARCH,
		TP_OPTS_DISP, TP_OPTS_ADV, TP_OPTS_SRCHV, TP_OPTS_PLAYERMENU);

	foreach ($mostOptions as $arrayName) {
		$optionArray = $dbOptions[$arrayName];
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
		$dbOptions[$arrayName] = $optionArray;
	}

	/* We treat meta values differently since they rely on true/false */
	$metaOptions = $dbOptions[TP_OPTS_META];
	foreach (array_keys($metaOptions) as $index) {
		$metaOption =& $metaOptions[$index];
		if (in_array($metaOption->name, $_POST['meta'])) $metaOptions[$metaOption->name]->value = true;
		else $metaOptions[$metaOption->name]->value = false;
	}
	$dbOptions[TP_OPTS_META] = $metaOptions;

	update_option(TP_OPTION_NAME, $dbOptions);
	
	$successMSG = TP_MSG_OPTSUCCESS;
	print <<<EOT
			<div id="message" class="$css->success_class">
				<p><strong>
	    			$successMSG
				</strong></p>
			</div>
EOT;
}
?>
