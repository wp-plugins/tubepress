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

/* OPTIONS MESSAGES */
define("TP_MSG_ADV",				"Advanced");
define("TP_MSG_BACK2GALLERY",		"Back to gallery");
define("TP_MSG_DEVID_DESC",			"Default is \"qh7CQ9xJIIc\". I can't think of a reason why you'd want/need to change the default developer id to you own, but the option is here for completeness. Available from");
define("TP_MSG_DEVID_TITLE",		"Alternate YouTube developer ID");
define("TP_MSG_KEYWORD_DESC",		"The word you insert (in plaintext, between square brackets) into your posts to display your YouTube gallery.");
define("TP_MSG_KEYWORD_TITLE",		"Trigger keyword");
define("TP_MSG_META",				"Video meta display");
define("TP_MSG_OPTPANELTITLE",		"TubePress Options");
define("TP_MSG_OPTPANELMENU",		"TubePress");
define("TP_MSG_OPTSUCCESS",			"Options updated");
define("TP_MSG_SAVE",				"Save");
//define("TP_MSG_SRCH_CATEGORY_TITLE","this category");
define("TP_MSG_SRCH_FAV_TITLE",		"this user's \"favorites\"");
define("TP_MSG_SRCH_FEATURED_TITLE","The latest 25 \"featured\" videos on YouTube's homepage.");
define("TP_MSG_SRCH_PLST_TITLE",	"this playlist");
define("TP_MSG_SRCH_PLST_DESC",		"Will usually look something like this: D2B04665B213AE35. Copy the playlist id from the end of the URL in your browser's address bar (while looking at a YouTube playlist). It comes right after the 'p='. For instance: http://youtube.com/my_playlists?p=D2B04665B213AE35.");
define("TP_MSG_SRCH_POPULAR_TITLE",	"Top 25 most-viewed videos from the past...");
define("TP_MSG_SRCH_REL_TITLE",		"<i>any</i> of these tags");
define("TP_MSG_SRCH_TAG_TITLE",		"<i>all</i> of these tags");
define("TP_MSG_SRCH_USER_TITLE",	"this user's videos");
define("TP_MSG_SRCH_TAGREL_DESC",	"Space-separated tags with no special characters or punctuation.");
define("TP_MSG_THUMBHEIGHT_DESC",  	"Default is 90");
define("TP_MSG_THUMBHEIGHT_TITLE",	"Height (px) of thumbs");
define("TP_MSG_THUMBWIDTH_DESC",	"Default is 120");
define("TP_MSG_THUMBWIDTH_TITLE",	"Width (px) of thumbs");
define("TP_MSG_TIMEOUT_DESC",		"Default is 6 seconds");
define("TP_MSG_TIMEOUT_TITLE",		"How long to wait (in seconds) for YouTube to respond");
define("TP_MSG_USERNAME_TITLE",		"Alternate YouTube username");
define("TP_MSG_USERNAME_DESC",		"Default is \"3hough\". Again, no reason to change this value unless you know something I don't :p");
define("TP_MSG_VIDDISP",			"Video display");
define("TP_MSG_VIDHEIGHT_DESC", 	"Default is 350");
define("TP_MSG_VIDHEIGHT_TITLE",	"Max height (px) of main video");
define("TP_MSG_VIDSPERPAGE_TITLE",	"Videos per page");
define("TP_MSG_VIDSPERPAGE_DESC",	"Default is 20, maximum is 100. The only modes that support pagination are the tag modes, and videos from some user. Playlists are supposed to page but it appears to be broken on YouTube's side :(");
define("TP_MSG_VIDWIDTH_DESC",  	"Default is 425");
define("TP_MSG_VIDWIDTH_TITLE",		"Max width (px) of main video");
define("TP_MSG_WHICHVIDS",			"Which videos?");

/* ERROR MESSAGES */
define("TP_MSG_TIMEOUT",			"Timed out while contacting YouTube. Please try again later.");
define("TP_MSG_XMLERR",				"No videos from YouTube! (SOAP error)");
define("TP_MSG_YTERR",				"YouTube contacted successfully, but no videos were returned. If you think this is a mistake, please check your configuration.");

/* META INFO MESSAGES */
define("TP_MSG_VIDAUTHOR",			"Author");
define("TP_MSG_VIDCOMMENTS",		"Comments");
define("TP_MSG_VIDDESC",			"Description");
define("TP_MSG_VIDID",				"Video ID");
define("TP_MSG_VIDLEN",				"Length");
define("TP_MSG_VIDTITLE",			"Title");
define("TP_MSG_VIDRATING",			"Rating");
define("TP_MSG_VIDRATINGS",			"Ratings");
define("TP_MSG_VIDTAGS",			"Tags");
define("TP_MSG_VIDTHUMBURL",		"Thumbnail URL");
define("TP_MSG_VIDUPLOAD",			"Uploaded date");
define("TP_MSG_VIDURL",				"YouTube URL");
define("TP_MSG_VIDVIEWS",			"Views");
define("TP_MSG_PLAYIN_NW_TITLE", 	"in a new window by itself");
define("TP_MSG_PLAYIN_YT_TITLE", 	"from the original YouTube page");
define("TP_MSG_PLAYIN_NORMAL_TITLE","normally (at the top of your gallery)");
define("TP_MSG_PLAYIN_POPUP_TITLE", "in a popup window");
define("TP_MSG_PLAYIN_LB_TITLE", 	"using Thickbox (experimental)");
define("TP_MSG_PLAYIN_TITLE", 		"Play each video...");

/* MISC */
define("TP_MASTERNODE", 			"video_list");
define("TP_YOUTUBEDEVLINK", 		"http://www.youtube.com/my_profile_dev"	);
define("TP_YOUTUBE_RESTURL",		"http://www.youtube.com/api2_rest?");
define("TP_PAGE_PARAM",				"tubepress_page");
define("TP_VID_PARAM",				"tubepress_id");
define("TP_OPTION_NAME",			"tubepress");

/* SEARCHING MODES */
define("TP_SRCH_FAV",				"favorites");
define("TP_SRCH_TAG",				"tag");
define("TP_SRCH_REL",				"related");
define("TP_SRCH_USER",				"user");
define("TP_SRCH_PLST",				"playlist");
define("TP_SRCH_FEATURED",			"featured");
define("TP_SRCH_POPULAR",			"popular");
//define("TP_SRCH_CATEGORY",			"category");

/* SEARCHING VALUES */
define("TP_SRCH_TAGVAL",			"tagValue");
define("TP_SRCH_RELVAL",			"relatedValue");
define("TP_SRCH_USERVAL",			"userValue");
define("TP_SRCH_PLSTVAL",			"playlistValue");
define("TP_SRCH_POPVAL",			"popularValue");
define("TP_SRCH_FAVVAL",			"favoritesValue");
//define("TP_SRCH_CATVAL",			"categoryValue");

/* VIDEO PLAYER LOCATIONS */
define("TP_PLAYIN_NW",				"new_window");
define("TP_PLAYIN_YT", 				"youtube");
define("TP_PLAYIN_NORMAL",			"normal");
define("TP_PLAYIN_POPUP",			"popup");
define("TP_PLAYIN_THICKBOX", 		"thickbox");

/* SINGLE OPTIONS */
define("TP_OPT_DEVID",				"devID");
define("TP_OPT_KEYWORD", 			"tubepress");
define("TP_OPT_SEARCHBY", 			"mode");
define("TP_OPT_THUMBHEIGHT",		"thumbHeight");
define("TP_OPT_THUMBWIDTH",			"thumbWidth");
define("TP_OPT_USERNAME",			"username");
define("TP_OPT_VIDHEIGHT",			"mainVidHeight");
define("TP_OPT_VIDWIDTH",			"mainVidWidth");
define("TP_OPT_TIMEOUT",			"timeout");
define("TP_OPT_PLAYIN", 			"playerLocation");
define("TP_OPT_VIDSPERPAGE",		"resultsPerPage");

/* OPTION ARRAYS */
define("TP_OPTS_META",				"tubepress_metaOptions");
define("TP_OPTS_SEARCH",			"tubepress_videoSearchOptions");
define("TP_OPTS_DISP",				"tubepress_videoDisplayOptions");
define("TP_OPTS_ADV", 				"tubepress_advancedOptions");
define("TP_OPTS_SRCHV",				"tubepress_searchVariables");
define("TP_OPTS_PLAYERLOCATION",	"tubepress_playerLocations");
define("TP_OPTS_PLAYERMENU",		"tubepress_playerLocationValue");

/* VIDEO META INFO */
define("TP_VID_AUTHOR",				"author");
define("TP_VID_ID",					"id");
define("TP_VID_TITLE",				"title");
define("TP_VID_LENGTH",				"length");
define("TP_VID_RATIN	G_CNT",		"ratings");
define("TP_VID_RATING_AVG",			"rating");
define("TP_VID_DESC",				"description");
define("TP_VID_VIEW",				"views");
define("TP_VID_UPLOAD_TIME",		"uploaded");
define("TP_VID_COMMENT_CNT",		"comments");
define("TP_VID_TAGS",				"tags");
define("TP_VID_URL",				"url");
define("TP_VID_THUMBURL",			"thumburl");

?>
