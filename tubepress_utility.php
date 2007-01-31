<?php
function tubepress_count_videos($youtube_xml) {
	if ($youtube_xml == "") return 0;
	if ($youtube_xml->children() == NULL) return 0;
	return count($youtube_xml->children());
}
function tubepress_printingSingleVideo($options) {
	return (($options->get_option(TP_OPT_PLAYIN) == TP_PLAYIN_NW) && isset($_GET['tubepress_id']));
}

function tubepress_fullURL() {
	return "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
}

function tubepress_parse_tag($content = '', $keyword) {  

	$optionsArray = array();  

	/* Use a regular expression to match everything in square brackets after the TubePress keyword */
	$regexp = '\[' . $keyword . ' ?([A-Za-z0-9=_ ]*)\]';  
	preg_match("/$regexp/", $content, $matches);  

	/* Execute if anything was matched by the parentheses */
	if(isset($matches[1])) {  
		/* Break up the options and store them in an ASSOCIATIVE array */
		$pairs = explode(" ", $matches[1]);  
		foreach($pairs as $pair) {  
			$pieces = explode("=", $pair);  
			$optionsArray[$pieces[0]] = $pieces[1];
			}
	}  

	/* Create and return new tubepressTag object */
	return new tubepressTag($matches[0], $optionsArray);  
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

/*
 * Connects to YouTube and grabs gallery info over
 * REST API
*/
function tubepress_get_youtube_xml($options) {
	$request = TP_YOUTUBE_RESTURL . "method=youtube.";

	$paging = false;
	switch ($options->get_option(TP_OPT_SEARCHBY)) {
		case TP_SRCH_USER:
			$request .= "videos.list_by_user&user=" . $options->get_option(TP_SRCH_USERVAL);
			$paging = true;
			break;
		case TP_SRCH_FAV:
			$request .= "users.list_favorite_videos&user=" . $options->get_option(TP_OPT_USERNAME);
			break;
		case TP_SRCH_TAG:
			$request .= "videos.list_by_tag&tag=" . urlencode($options->get_option(TP_SRCH_TAGVAL));
			$paging = true;
			break;
		case TP_SRCH_REL:
			$request .= "videos.list_by_related&tag=" . urlencode($options->get_option(TP_SRCH_RELVAL));
			$paging = true;
			break;
		case TP_SRCH_YV:
			$request .= "videos.list_by_user&user=" . $options->get_option(TP_OPT_USERNAME);
			break;
		case TP_SRCH_PLST:
			$request .= "videos.list_by_playlist&id=" . $options->get_option(TP_SRCH_PLSTVAL);
			$paging = true;
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

	if ($paging) {
		$pageNum = 1;
		if (isset($_GET[TP_PAGE_PARAM])) $pageNum = $_GET[TP_PAGE_PARAM];
		$request .= "&page=" . $pageNum . "&per_page=" . $options->get_option(TP_OPT_VIDSPERPAGE);
	}
		
	$request .= "&dev_id=" . $options->get_option(TP_OPT_DEVID);
	return tubepress_fetchXML($request);
}

function tubepress_fetchXML($request) {
	echo "REQUEST: " . $request;
	$snoopy = new snoopy;
	$snoopy->read_timeout = $options->get_option(TP_OPT_TIMEOUT);

	$snoopy->fetch($request);
	if ($snoopy->results == "") return TP_XMLERR;
	$impl = new IsterXmlSimpleXMLImpl;
	$results = $impl->load_string($snoopy->results);
	return $results->ut_response->video_list;
}

?>