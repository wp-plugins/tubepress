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

/* This function will be called if the user has no or invalid TubePress options */
function tubepress_addOptions() {

	add_option(TP_OPTION_NAME, tubepress_getNewOptionsArray());
	
	tubepress_deleteLegacyOptions();
	
	return get_option(TP_OPTION_NAME);
}

function tubepress_deepArrayCheck($correctArray, $suspectArray) {
	foreach (array_keys($correctArray) as $thisKey) {
		if (!isset($suspectArray[$thisKey])) return false;
		if (!is_array($correctArray[$thisKey]) 	&& !is_array($suspectArray[$thisKey])) continue;
		if (is_array($correctArray[$thisKey]) 	&& !is_array($suspectArray[$thisKey])) return false;
		if (!is_array($correctArray[$thisKey]) 	&& is_array($suspectArray[$thisKey])) return false;
		/* if we're here, it means we're dealing with two arrays */
		if (count($suspectArray[$thisKey]) != count($correctArray[$thisKey])) return false;
		if (!tubepress_deepArrayCheck($correctArray[$thisKey], $suspectArray[$thisKey])) return false;
	}
	return true;
}

/* Gets rid of legacy options if they still exist. Please email me if you think I missed one! */
function tubepress_deleteLegacyOptions() {
	delete_option(TP_OPTS_ADV);
	delete_option(TP_OPTS_DISP);
	delete_option(TP_OPTS_META);
	delete_option(TP_OPTS_PLAYERLOCATION);
	delete_option(TP_OPTS_PLAYERMENU);
	delete_option(TP_OPTS_SEARCH);
	delete_option(TP_OPTS_SRCHV);
	delete_option("tubepress_accountInfo");
	delete_option("[tubepress]");
	delete_option("TP_OPT_SEARCHBY_TAGVAL");
	delete_option("TP_OPT_SEARCHBY_USERVAL");
	delete_option("TP_OPT_SEARCHKEY");
	delete_option("TP_OPT_THUMBHEIGHT");
	delete_option("tp_display_author");
	delete_option("tp_display_comment_count");
	delete_option("tp_display_description");
	delete_option("tp_display_id");
	delete_option("tp_display_length");
	delete_option("tp_display_rating_avg");
	delete_option("tp_display_rating_count");
	delete_option("tp_display_tags");
	delete_option("tp_display_title");
	delete_option("tp_display_upload_time");
	delete_option("tp_display_url");
	delete_option("tp_display_view_count");
	delete_option("mainVidHeight");
	delete_option("mainVidWidth");
	delete_option("searchBy");
	delete_option("searchByTagValue");
	delete_option("searchByUserValue");
	delete_option("thumbHeight");
	delete_option("thumbWidth");
	delete_option("timeout");
	delete_option("TP_OPT_THUMBEIGHT");
	delete_option("TP_VID_METAS");
	delete_option("username");
	delete_option("devID");
	delete_option("devIDlink");
	delete_option("searchByValue");
}

function tubepress_getNewOptionsArray() {
	$metaOptions = array(
		TP_VID_TITLE =>			new tubepressOption(TP_VID_TITLE,		TP_MSG_VIDTITLE,	'', true),
		TP_VID_LENGTH => 		new tubepressOption(TP_VID_LENGTH, 		TP_MSG_VIDLEN, 		'', true),
		TP_VID_VIEW =>	 		new tubepressOption(TP_VID_VIEW, 		TP_MSG_VIDVIEWS, 	'', true),
		TP_VID_AUTHOR => 		new tubepressOption(TP_VID_AUTHOR ,		TP_MSG_VIDAUTHOR, 	'', false),
		TP_VID_ID => 			new tubepressOption(TP_VID_ID, 			TP_MSG_VIDID, 		'', false),
		TP_VID_RATING_AVG => 	new tubepressOption(TP_VID_RATING_AVG, 	TP_MSG_VIDRATING, 	'', false),
		TP_VID_RATING_CNT => 	new tubepressOption(TP_VID_RATING_CNT, 	TP_MSG_VIDRATINGS, 	'', false),
		TP_VID_UPLOAD_TIME => 	new tubepressOption(TP_VID_UPLOAD_TIME, TP_MSG_VIDUPLOAD, 	'', false),
		TP_VID_COMMENT_CNT => 	new tubepressOption(TP_VID_COMMENT_CNT, TP_MSG_VIDCOMMENTS, '', false),
		TP_VID_TAGS => 			new tubepressOption(TP_VID_TAGS, 		TP_MSG_VIDTAGS, 	'', false),
		TP_VID_URL => 			new tubepressOption(TP_VID_URL, 		TP_MSG_VIDURL, 		'', false),
		TP_VID_THUMBURL =>		new tubePressOption(TP_VID_THUMBURL,	TP_MSG_VIDTHUMBURL, '', false),
		TP_VID_DESC => 			new tubepressOption(TP_VID_DESC, 		TP_MSG_VIDDESC, 	'', false));
	$videoSearchOptions = array(
		TP_SRCH_PLST => 		new tubepressOption(TP_SRCH_PLST, 		TP_MSG_SRCH_PLST_TITLE, 	TP_MSG_SRCH_PLST_DESC, ''),
		TP_SRCH_TAG => 			new tubepressOption(TP_SRCH_TAG, 		TP_MSG_SRCH_TAG_TITLE, 		TP_MSG_SRCH_TAGREL_DESC, ''),
		TP_SRCH_REL => 			new tubepressOption(TP_SRCH_REL, 		TP_MSG_SRCH_REL_TITLE, 		TP_MSG_SRCH_TAGREL_DESC, ''),
		TP_SRCH_USER => 		new tubepressOption(TP_SRCH_USER, 		TP_MSG_SRCH_USER_TITLE, 	'', ''),
		TP_SRCH_FAV => 			new tubepressOption(TP_SRCH_FAV, 		TP_MSG_SRCH_FAV_TITLE, 		'', ''),
		TP_SRCH_FEATURED => 	new tubepressOption(TP_SRCH_FEATURED, 	TP_MSG_SRCH_FEATURED_TITLE, '', ''),
		TP_SRCH_POPULAR => 		new tubepressOption(TP_SRCH_POPULAR, 	TP_MSG_SRCH_POPULAR_TITLE, 	'', ''));
	//  TODO: YouTube's "category" api call doesn't seem to work for now...
	//	TP_SRCH_CATEGORY => 	new tubepressOption(TP_SRCH_CATEGORY, 	TP_MSG_SRCH_CATEGORY_TITLE, 'See <a href="http://youtube.com/categories">http://youtube.com/categories</a>', ''));
	$searchVariables = array(
		TP_OPT_SEARCHBY => 		new tubepressOption(TP_OPT_SEARCHBY,	' ', '', TP_SRCH_FAV),
		TP_SRCH_TAGVAL => 		new tubepressOption(TP_SRCH_TAGVAL, 	' ', '', "stewart daily show"),
		TP_SRCH_RELVAL => 		new tubepressOption(TP_SRCH_RELVAL, 	' ', '', "mooninite aqua teen hunger force"),
		TP_SRCH_USERVAL => 		new tubepressOption(TP_SRCH_USERVAL, 	' ', '', "3hough"),
		TP_SRCH_PLSTVAL =>		new tubepressOption(TP_SRCH_PLSTVAL,	' ', '', "D2B04665B213AE35"),
		TP_SRCH_FAVVAL =>		new tubepressOption(TP_SRCH_FAVVAL,		' ', '', "mrdeathgod"),
		TP_SRCH_POPVAL =>		new tubepressOption(TP_SRCH_POPVAL,		' ', '', "day"));
	//	TP_SRCH_CATVAL =>		new tubepressOption(TP_SRCH_CATVAL,		' ', '', "19"));
	$videoDisplayOptions = array(
		TP_OPT_VIDSPERPAGE=> 	new tubepressOption(TP_OPT_VIDSPERPAGE, 	TP_MSG_VIDSPERPAGE_TITLE, 	TP_MSG_VIDSPERPAGE_DESC, "20"),
		TP_OPT_VIDWIDTH => 		new tubepressOption(TP_OPT_VIDWIDTH, 	TP_MSG_VIDWIDTH_TITLE, 		TP_MSG_VIDWIDTH_DESC,		"425"),
		TP_OPT_VIDHEIGHT => 	new tubepressOption(TP_OPT_VIDHEIGHT, 	TP_MSG_VIDHEIGHT_TITLE, 	TP_MSG_VIDHEIGHT_DESC, 		"350"),
		TP_OPT_THUMBWIDTH => 	new tubepressOption(TP_OPT_THUMBWIDTH, 	TP_MSG_THUMBWIDTH_TITLE, 	TP_MSG_THUMBWIDTH_DESC, 	"120"),
		TP_OPT_THUMBHEIGHT => 	new tubepressOption(TP_OPT_THUMBHEIGHT, TP_MSG_THUMBHEIGHT_TITLE, 	TP_MSG_THUMBHEIGHT_DESC,	"90"));
	$advancedOptions = array(
		TP_OPT_KEYWORD => 		new tubepressOption(TP_OPT_KEYWORD, 	TP_MSG_KEYWORD_TITLE, TP_MSG_KEYWORD_DESC, TP_OPTION_NAME),
		TP_OPT_TIMEOUT => 		new tubepressOption(TP_OPT_TIMEOUT, 	TP_MSG_TIMEOUT_TITLE, TP_MSG_TIMEOUT_DESC, "6"),
		TP_OPT_DEVID => 		new tubepressOption(TP_OPT_DEVID, 		TP_MSG_DEVID_TITLE, TP_MSG_DEVID_DESC . ' <a href="' . TP_YOUTUBEDEVLINK . '">' . TP_YOUTUBEDEVLINK . '</a>', "qh7CQ9xJIIc"),
		TP_OPT_USERNAME => 		new tubepressOption(TP_OPT_USERNAME, 	TP_MSG_USERNAME_TITLE, TP_MSG_USERNAME_DESC, "3hough")
		);
	$videoPlayerLocationOptions = array(
		TP_PLAYIN_NORMAL =>		new tubepressOption(TP_PLAYIN_NORMAL, 	TP_MSG_PLAYIN_NORMAL_TITLE,	'', ''),
		TP_PLAYIN_NW =>			new tubepressOption(TP_PLAYIN_NW, 		TP_MSG_PLAYIN_NW_TITLE, 	'', ''),
		TP_PLAYIN_YT =>			new tubepressOption(TP_PLAYIN_YT, 		TP_MSG_PLAYIN_YT_TITLE, 	'', ''),
		TP_PLAYIN_POPUP =>		new tubepressOption(TP_PLAYIN_POPUP, 	TP_MSG_PLAYIN_POPUP_TITLE, 	'', ''),
		TP_PLAYIN_THICKBOX =>	new tubePressOption(TP_PLAYIN_THICKBOX, TP_MSG_PLAYIN_LB_TITLE, 	'', ''));
	$videoPlayerMenu = array(
		TP_OPT_PLAYIN =>		new tubepressOption(TP_OPT_PLAYIN, 		TP_MSG_PLAYIN_TITLE, '', TP_PLAYIN_NORMAL));
	
	$allOptions = array(
		TP_OPTS_ADV => 				$advancedOptions,
		TP_OPTS_DISP =>				$videoDisplayOptions,
		TP_OPTS_META =>				$metaOptions,
		TP_OPTS_PLAYERLOCATION => 	$videoPlayerLocationOptions,
		TP_OPTS_PLAYERMENU =>		$videoPlayerMenu,
		TP_OPTS_SEARCH =>			$videoSearchOptions,
		TP_OPTS_SRCHV =>			$searchVariables
	);
	return $allOptions;
}

function tubepress_validOptions($options) {
	if ($options == NULL) return false;
	if (!is_array($options)) return false;
	
	return tubepress_deepArrayCheck(tubepress_getNewOptionsArray(), $options);
}

?>