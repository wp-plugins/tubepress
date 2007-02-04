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

function tubepress_areWePaging($options) {
	$searchBy = $options->get_option(TP_OPT_SEARCHBY);
	//TODO: playlists currently aren't paging for some reason
	if (($searchBy == TP_SRCH_USER) || ($searchBy == TP_SRCH_TAG) || ($searchBy == TP_SRCH_REL))
		return true;
	return false;
}

function tubepress_cleanupTagValue($nameOrValue) {
	/*
	 * WTF: this seems to work, though I have no idea why the quotes are getting
	 * converted into these stupid entities.
	 */
	return trim(str_replace(array("&#8220;", "&#8221;", "&#8217;", "&#8216;", "&#8242;", "&#8243;", "&#34"), "", trim($nameOrValue)));
}

function tubepress_count_videos($youtube_xml) {
	if ($youtube_xml == "") return 0;
	if ($youtube_xml->children() == NULL) return 0;
	return count($youtube_xml->children());
}

function tubepress_fetchXML($request, $options) {
	$snoopy = new snoopy;
	$snoopy->read_timeout = $options->get_option(TP_OPT_TIMEOUT);
	$snoopy->fetch($request);
	if ($snoopy->results == "") return TP_XMLERR;
	$impl = new IsterXmlSimpleXMLImpl;
	$results = $impl->load_string($snoopy->results);
	return $results->ut_response->video_list;
}

function tubepress_fullURL() {
	return "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
}

/*
 * Connects to YouTube and grabs gallery info over
 * REST API
*/
function tubepress_get_youtube_xml($options) {
	$request = TP_YOUTUBE_RESTURL . "method=youtube.";

	switch ($options->get_option(TP_OPT_SEARCHBY)) {
		case TP_SRCH_USER:
			$request .= "videos.list_by_user&user=" . $options->get_option(TP_SRCH_USERVAL);
			break;
		case TP_SRCH_FAV:
			$request .= "users.list_favorite_videos&user=" . $options->get_option(TP_SRCH_FAVVAL);
			break;
		case TP_SRCH_TAG:
			$request .= "videos.list_by_tag&tag=" . urlencode($options->get_option(TP_SRCH_TAGVAL));
			break;
		case TP_SRCH_REL:
			$request .= "videos.list_by_related&tag=" . urlencode($options->get_option(TP_SRCH_RELVAL));
			break;
		case TP_SRCH_PLST:
			$request .= "videos.list_by_playlist&id=" . $options->get_option(TP_SRCH_PLSTVAL);
			break;
		case TP_SRCH_POPULAR:
			$request .= "videos.list_popular&time_range=" . $options->get_option(TP_SRCH_POPVAL);
			break;
		//case TP_SRCH_CATEGORY:
		//	$request .= "videos.list_by_category&page=1&per_page=" . $options->get_option(TP_OPT_VIDSPERPAGE) . "&category_id=" . $options->get_option(TP_SRCH_CATVAL);
		//	$paging = true;
		//	break;
		case TP_SRCH_FEATURED:
			$request .= "videos.list_featured";
			break;
	}

	if (tubepress_areWePaging($options)) {
		$pageNum = ((isset($_GET[TP_PAGE_PARAM]))? $_GET[TP_PAGE_PARAM] : 1);
		$request .= "&page=" . $pageNum . "&per_page=" . $options->get_option(TP_OPT_VIDSPERPAGE);
	}

	$request .= "&dev_id=" . $options->get_option(TP_OPT_DEVID);
	return tubepress_fetchXML($request, $options);
}

function tubepress_humanTime($length_seconds) {
	/* convert the time to human-friendly */
	$seconds = $length_seconds;
	$length = intval($seconds/60);
	$leftOverSeconds = $seconds%60;
	if ($leftOverSeconds < 10) $leftOverSeconds = "0" . $leftOverSeconds;
	$length .= 	":" . $leftOverSeconds;
	return $length;
}

function tubepress_parse_tag($content = '', $keyword) {

	$optionsArray = array();

	/* Use a regular expression to match everything in square brackets after the TubePress keyword */
	$regexp = '\[' . $keyword . "(.*)\]";
	preg_match("/$regexp/", $content, $matches);

	/* Execute if anything was matched by the parentheses */
	if(isset($matches[1])) {
		/* Break up the options by comma and store them in an associative array */
		$pairs = explode(",", $matches[1]);
		foreach($pairs as $pair) {
			$pieces = explode("=", $pair);
			$optionsArray[tubepress_cleanupTagValue($pieces[0])] = tubepress_cleanupTagValue($pieces[1]);
		}
	}

	/* Create and return new tubepressTag object */
	return new tubepressTag($matches[0], $optionsArray);
}

function tubepress_printingSingleVideo($options) {
	return (($options->get_option(TP_OPT_PLAYIN) == TP_PLAYIN_NW) && isset($_GET[TP_VID_PARAM]));
}

?>