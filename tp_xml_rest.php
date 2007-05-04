<?php
/**

tp_xml_rest.php

Does all our XML and REST dirty work

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

/**
 * Connects to YouTube and returns raw XML
*/
function tp_fetchRawXML($options)
{
    $request = tp_generateRequest($options);
    
    if (PEAR::isError($request)) {
        return $request;
    }
    
    $snoopy = new snoopy;
    $snoopy->read_timeout = $options->get_option(TP_OPT_TIMEOUT);
    
    if (!$snoopy->fetch($request)) {
        return PEAR::raiseError("Unable to connect to YouTube (" .
            $snoopy->error . ")"); 
    }
    
    if ($snoopy->timed_out) {
        return PEAR::raiseError("Timed out while trying to contact YouTube"); 
    }
    
    if (strpos($snoopy->response_code, "200 OK") === false) {
        return PEAR::raiseError("YouTube did not respond with an HTTP OK (" .
            $snoopy->response_code . ")");
    }
    
    return $snoopy->results;
}

/**
 * Takes YouTube's raw xml and tries to return an array of the videos
 */
function tp_parseRawXML($youtube_xml)
{
	class_exists('XML_Unserializer') || require("lib/PEAR/XML/XML_Serializer/Unserializer.php");
	
    $unserializer_options = array ('parseAttributes' => TRUE);

    $Unserializer = &new XML_Unserializer($unserializer_options);

    $status = $Unserializer->unserialize($youtube_xml);

    if (PEAR::isError($status)) {
        return $status;
    }

    $result = $Unserializer->getUnserializedData();
    
    /* make sure we could read the xml */
    if (!is_array($result) || PEAR::isError($result)) {
        return PEAR::raiseError("XML unserialization error");
    }
    
    /* make sure we have a status from YouTube */
    if (!array_key_exists('status', $result)) {
        return PEAR::raiseError("Valid XML from YouTube, but status is missing");
    }
    
    /* see if YouTube liked us */
    if ($result['status'] != "ok") {
    	$msg = "Unknown error";
    	if (is_array($result['error']) && array_key_exists('description', $result['error']) 
    	    && array_key_exists('code', $result['error'])) {
    		    $msg = $result['error']['description'] . " Code " . $result['error']['code'];
    	}
    	return PEAR::raiseError("YouTube responded with an error message: " . $msg);
    }
    
    if (!array_key_exists('total', $result)) {
        return PEAR::raiseError("YouTube didn't return a total video count");
    }
    
    /* if we have a video_list, just return it */
    if (is_array($result['video_list'])) {
    	return $result['video_list'];
    }
    
    return PEAR::raiseError("YouTube responded with OK, but no video_list returned");
}

function tp_generateRequest($options)
{
    $request = TP_YOUTUBE_RESTURL . "method=youtube.";

    switch ($options->get_option(TP_OPT_SEARCHBY)) {
       
        case TP_SRCH_USER:
            $request .= "videos.list_by_user" .
                "&user=" . $options->get_option(TP_SRCH_USERVAL);
            break;
            
        case TP_SRCH_FAV:
            $request .= "users.list_favorite_videos" .
                "&user=" . $options->get_option(TP_SRCH_FAVVAL);
            break;
            
        case TP_SRCH_TAG:
            $request .= "videos.list_by_tag" .
                "&tag=" . urlencode($options->get_option(TP_SRCH_TAGVAL));
            break;
            
        case TP_SRCH_REL:
            $request .= "videos.list_by_related" .
                "&tag=" . urlencode($options->get_option(TP_SRCH_RELVAL));
            break;
            
        case TP_SRCH_PLST:
            $request .= "videos.list_by_playlist" .
                "&id=" . $options->get_option(TP_SRCH_PLSTVAL);
            break;
            
        case TP_SRCH_POPULAR:
            $request .= "videos.list_popular" .
                "&time_range=" . $options->get_option(TP_SRCH_POPVAL);
            break;
            
        case TP_SRCH_CATEGORY:
            $request .= "videos.list_by_category" .
                "&page=1" .
                "&per_page=" . $options->get_option(TP_OPT_VIDSPERPAGE) .
                "&category_id=" . $options->get_option(TP_SRCH_CATVAL);
            $paging = true;
            break;
        
        case TP_SRCH_FEATURED:
            $request .= "videos.list_featured";
            break;
        default:
            return PEAR::raiseError("Invalid mode specified (" .
                $options->get_option(TP_OPT_SEARCHBY) . ")");
    }

    if (tp_areWePaging($options)) {
        $pageNum = ((isset($_GET[TP_PAGE_PARAM]))? $_GET[TP_PAGE_PARAM] : 1);
        $request .= "&page=" . $pageNum . "&per_page=" . $options->get_option(TP_OPT_VIDSPERPAGE);
    }

    $request .= "&dev_id=" . $options->get_option(TP_OPT_DEVID);
    return $request;
}
?>