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

/* Messages */
define("TP_MSG_OPTPANELTITLE",	"TubePress Options");
define("TP_MSG_OPTPANELMENU",	"TubePress");
define("TP_MSG_OPTSUCCESS",	"Options updated");
define("TP_MSG_TIMEOUT",	"Timed out while contacting YouTube. Please try again later.");
define("TP_MSG_YTERR",		"Could not retrieve any videos from YouTube. Please check your configuration.");
define("TP_MSG_WHICHVIDS",	"Which videos?");
define("TP_MSG_VIDDISP",	"Video display");
define("TP_MSG_META",		"Video meta display");
define("TP_MSG_ADV",		"Advanced");
define("TP_MSG_ACCT",		"Your YouTube Account");
define("TP_MSG_SAVE",		"Save");
define("TP_MSG_XMLERR",		"No videos from YouTube!");
define("TP_MSG_MAINVID_HEADER",	"Latest post:");
define("TP_SRCH_FAV_TITLE",	"your favorites");
define("TP_SRCH_TAG_TITLE",	"this tag");
define("TP_SRCH_USER_TITLE",	"this user");
define("TP_SRCH_YV_TITLE",	"your videos");
define("TP_MSG_DEVID_TITLE",		"YouTube developer ID");
define("TP_MSG_DEVID_DESC",		"Available from");
define("TP_MSG_KEYWORD_TITLE",		"Display keyword");
define("TP_MSG_KEYWORD_DESC",		"The trigger you insert (in plaintext, between square brackets) into your posts to display your YouTube gallery.");
define("TP_MSG_THUMBHEIGHT_TITLE",	"Height (px) of thumbs");
define("TP_MSG_THUMBHEIGHT_DESC",  	"Default is 90");
define("TP_MSG_THUMBWIDTH_TITLE",	"Width (px) of thumbs");
define("TP_MSG_THUMBWIDTH_DESC",	"Default is 120");
define("TP_MSG_USERNAME_TITLE",		"YouTube username");
define("TP_MSG_USERNAME_DESC",  	"");
define("TP_MSG_VIDHEIGHT_TITLE",	"Max height (px) of main video");
define("TP_MSG_VIDHEIGHT_DESC", 	"Default is 350");
define("TP_MSG_VIDWIDTH_TITLE",		"Max width (px) of main video");
define("TP_MSG_VIDWIDTH_DESC",  	"Default is 425");
define("TP_MSG_TIMEOUT_TITLE",		"How long to wait (in seconds) for YouTube to respond");
define("TP_MSG_TIMEOUT_DESC",		"Default is 6 seconds");
define("TP_MSG_VIDAUTHOR",	"Author");
define("TP_MSG_VIDID",		"Video ID");
define("TP_MSG_VIDTITLE",	"Title");
define("TP_MSG_VIDLEN",		"Length");
define("TP_MSG_VIDRATINGS",	"Ratings");
define("TP_MSG_VIDRATING",	"Rating");
define("TP_MSG_VIDDESC",	"Description");
define("TP_MSG_VIDVIEWS",	"Views");
define("TP_MSG_VIDUPLOAD",	"Uploaded");
define("TP_MSG_VIDCOMMENTS",	"Comments");
define("TP_MSG_VIDTAGS",	"Tags");
define("TP_MSG_VIDURL",		"YouTube URL");
define("TP_MSG_VIDTHUMBURL",	"Thumbnail URL");

/* General */
define("TP_MASTERNODE", 	"video_list");
define("TP_YOUTUBEDEVLINK", 	"http://www.youtube.com/my_profile_dev"	);
define("TP_YOUTUBE_RESTURL",	"http://www.youtube.com/api2_rest?");

/* Searching */
define("TP_SRCH_FAV",		"favorites");
define("TP_SRCH_TAG",		"tag");
define("TP_SRCH_USER",		"user");
define("TP_SRCH_YV",		"yourvideos");
define("TP_SRCH_TAGVAL",	"searchByTagValue");
define("TP_SRCH_USERVAL",	"searchByUserValue");

/* OPTIONS */
define("TP_OPT_DEVID",			"devID");
define("TP_OPT_KEYWORD", 		"tubepress");
define("TP_OPT_SEARCHBY", 		"searchBy");
define("TP_OPT_THUMBHEIGHT",		"thumbHeight");
define("TP_OPT_THUMBWIDTH",		"thumbWidth");
define("TP_OPT_USERNAME",		"username");
define("TP_OPT_VIDHEIGHT",		"mainVidHeight");
define("TP_OPT_VIDWIDTH",		"mainVidWidth");
define("TP_OPT_TIMEOUT",		"timeout");
define("TP_OPTS_META",		"tubepress_metaOptions");
define("TP_OPTS_ACCT",		"tubepress_accountInfo");
define("TP_OPTS_SEARCH",	"tubepress_videoSearchOptions");
define("TP_OPTS_DISP",		"tubepress_videoDisplayOptions");
define("TP_OPTS_ADV", 		"tubepress_advancedOptions");
define("TP_OPTS_SRCHV",		"tubepress_searchVariables");

/* VIDEO META INFO */
define("TP_VID_AUTHOR",		"author");
define("TP_VID_ID",		"id");
define("TP_VID_TITLE",		"title");
define("TP_VID_LENGTH",		"length");
define("TP_VID_RATING_CNT",	"ratings");
define("TP_VID_RATING_AVG",	"rating");
define("TP_VID_DESC",		"description");
define("TP_VID_VIEW",		"views");
define("TP_VID_UPLOAD_TIME",	"uploaded");
define("TP_VID_COMMENT_CNT",	"comments");
define("TP_VID_TAGS",		"tags");
define("TP_VID_URL",		"url");
define("TP_VID_THUMBURL",	"thumburl");

?>
