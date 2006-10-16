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
define("TP_MSG_OPTPANELTITLE",	"TubePress Options"			);
define("TP_MSG_OPTPANELMENU",	"TubePress"				);
define("TP_MSG_OPTSUCCESS",	"Options updated"			);
define("TP_MSG_TIMEOUT",	"Timed out while contacting YouTube. Please try again later.");
define("TP_MSG_YTERR",		"Could not retrieve any videos from YouTube. Please check your configuration.");

/* General */
define("TP_MASTERNODE", 	"video_list"				);
define("TP_YOUTUBEDEVLINK", 	"http://www.youtube.com/my_profile_dev"	);
define("TP_YOUTUBE_RESTURL",	"http://www.youtube.com/api2_rest?");
define("TP_MAINVID_HEADER",	"Latest post:"				);
define("TP_XMLERR",		"No videos"				);

/* Searching */
define("TP_SRCH_FAV",		"favorites"				);
define("TP_SRCH_FAV_DESC",	"your favorites"			);
define("TP_SRCH_TAG",		"tag"					);
define("TP_SRCH_TAG_DESC",	"this tag"				);
define("TP_SRCH_USER",		"user"					);
define("TP_SRCH_USER_DESC",	"this user"				);
define("TP_SRCH_YV",		"yourvideos"				);
define("TP_SRCH_YV_DESC",	"your videos"				);

/* OPTIONS */
define("TP_OPT_DEVID",			"devID"					);
define("TP_OPT_DEVID_DESC",		"YouTube developer ID"			);
define("TP_OPT_DEVID_DEF",    		'Available from <a href="' . TP_YOUTUBEDEVLINK . '">' . TP_YOUTUBEDEVLINK . '</a>');
define("TP_OPT_KEYWORD", 		"tubepress"				);
define("TP_OPT_KEYWORD_DESC",		"Display keyword"			);
define("TP_OPT_KEYWORD_DEF",		"The trigger you insert (in plaintext, between square brackets) into your posts to display your YouTube gallery.");
define("TP_OPT_SEARCHBY", 		"searchBy"				);
define("TP_OPT_SEARCHBY_TAGVAL",	"searchByTagValue"			);
define("TP_OPT_SEARCHBY_USERVAL",	"searchByUserValue"			);
define("TP_OPT_THUMBHEIGHT",		"thumbHeight"				);
define("TP_OPT_THUMBHEIGHT_DESC",	"Max height (px) of thumbs"		);
define("TP_OPT_THUMBHEIGHT_DEF",       	"Default is 90"     			);
define("TP_OPT_THUMBWIDTH",		"thumbWidth"				);
define("TP_OPT_THUMBWIDTH_DESC",	"Max width (px) of thumbs"		);
define("TP_OPT_THUMBWIDTH_DEF",     	"Default is 120"      			);
define("TP_OPT_USERNAME",		"username"				);
define("TP_OPT_USERNAME_DESC",		"YouTube username"			);
define("TP_OPT_USERNAME_DEF",  		""                      		);
define("TP_OPT_VIDHEIGHT",		"mainVidHeight"				);
define("TP_OPT_VIDHEIGHT_DESC",		"Max height (px) of main video"		);
define("TP_OPT_VIDHEIGHT_DEF", 		"Default is 350"         		);
define("TP_OPT_VIDWIDTH",		"mainVidWidth"				);
define("TP_OPT_VIDWIDTH_DESC",		"Max width (px) of main video"		);
define("TP_OPT_VIDWIDTH_DEF",  		"Default is 425"          		);
define("TP_OPT_TIMEOUT",		"timeout");
define("TP_OPT_TIMEOUT_DESC",		"How long to wait (in seconds) for YouTube to respond");
define("TP_OPT_TIMEOUT_DEF",		"Default is 6 seconds");

/* CSS */
define("TP_CSS_CONTAINER", 	"tubepress_container"			);
define("TP_CSS_MAINVIDID", 	"tubepress_mainvideo"			);
define("TP_CSS_MAINVID",	"tubepress_mainvideo"			);
define("TP_CSS_META",		"tubepress_meta"			);
define("TP_CSS_THUMBS",		"tubepress_video_thumbs"		);
define("TP_CSS_THUMB",		"tubepress_thumb"			);
define("TP_CSS_THUMBIMG",	"tubepress_video_thumb_img"		);
define("TP_CSS_SUCCESS",	"updated fade"				);
define("TP_CSS_RUNTIME",	"runtime"				);
define("TP_CSS_TITLE",		"tubepress_title"			);
?>
