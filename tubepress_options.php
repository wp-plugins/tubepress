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
add_option("msgs",              $msg,           "Message strings");
add_option(TP_OPT_USERNAME,   	"3hough",       "YouTube username.");
add_option(TP_OPT_VIDWIDTH,      "425",          "Max width (px) of main video");
add_option(TP_OPT_VIDHEIGHT,     "350",          "Max height (px) of main video");
add_option(TP_OPT_THUMBWIDTH,        "130",          "Max width (px) of video thumbnails");
add_option("thumbHeight",       "97",           "Max height (px) of video thumbnails");
add_option("devID",             "qh7CQ9xJIIc",  'YouTube developer ID');

function tubepress_add_options_page() {
	if (function_exists('add_options_page')) {
		add_options_page(message('options_panel_title'), message('options_panel_menuitem'), 9, 'tubepress.php', 'tubepress_options_subpanel');
    	}
}

function tubepress_options_subpanel() {
	$youTubeAccountInfo = array(
		array('devID', 'YouTube developer ID', 'Available from somewhere'),
		array('username', 'YouTube username', '') 
	);
	$videoDisplayOptions = array(
		array('mainVidWidth', 'Max width (px) of main video', 'Default is 425'),
		array('mainVidHeight', 'Max height (px) of main video', 'Default is 350'),
		array('thumbWidth', 'Max width (px) of video thumbs', 'Default is 130'),
		array('thumbHeight', 'Max height (px) of video thumbs', 'Default is 97')
	);

	if (isset($_POST['tubepress_save'])) {
		$allOptions = array($youTubeAccountInfo, $$videoDisplayOptions);
		foreach ($allOptions as $k => $optionArray) {
			foreach ($optionArray as $t => $option) {
				if (isset($_POST[$option[0]])) update_option($option[0], $_POST[$option[0]]);
			}
		}
	
		print <<<EOT
		<div class="updated fade">
			<p><strong>
    			message('success');
			</strong></p>
		</div>
EOT;
	}

	print <<<EOT
	<div class=wrap>
  		<form method="post">
		<h2>TubePress Options</h2>
EOT;
	printHTML_optionsArray($youTubeAccountInfo, "YouTube account");
	printHTML_optionsArray($videoDisplayOptions, "Video display");

	print <<<EOT
		<input type="submit" name="tubepress_save" value="Save" />
  		</form>
 	</div>
EOT;

}

function printHTML_optionsArray($theArray, $arrayName) {
	print <<<EOT

			<feildset name="$arrayName">
				<table class="editform optiontable">
				<legend>$arrayName</legend>
EOT;
	foreach ($theArray as $k => $option) {
		$optionName = $option[0];
		$optionDesc = $option[1];
		$optionDefault = $option[2];
		$optionValue = get_option($optionName);
		print <<<EOT
					<tr valign="top">
						<th scope="row">$optionDesc:</th>
						<td>
							<input name="$optionName" type="text" id="$optionName" class="code" value="$optionValue" size="40" />
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
