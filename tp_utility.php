<?php
/**
tp_utility.php

Various miscellaneous functions that come in handy

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

function tp_bail($msg, $error)
{
	echo $msg . " (" . $error->message . ")";
}

function tp_areWeDebugging()
{
    return isset($_GET[TP_DEBUG_PARAM]) && ($_GET[TP_DEBUG_PARAM] == true);
}

function tp_areWePaging($options)
{
    $searchBy = $options->get_option(TP_OPT_SEARCHBY);
    //TODO: playlists currently aren't paging for some reason
    if (($searchBy == TP_SRCH_USER)
        || ($searchBy == TP_SRCH_TAG)
        || ($searchBy == TP_SRCH_REL)) {
            return true;
    }
    return false;
}

function tp_cleanupTagValue($nameOrValue)
{
    /*
     * WTF: this seems to work, though I have no idea why the quotes are getting
     * converted into these stupid entities.
     */
    return trim(
        str_replace(
            array("&#8220;", "&#8221;", "&#8217;", "&#8216;",
                  "&#8242;", "&#8243;", "&#34"),"", 
                  trim($nameOrValue))
               );
}

function tp_fullURL()
{
    return "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
}

/**
 * Converts seconds to minutes and seconds
 * 
 * @param length_seconds The runtime of a video, in seconds
 */
function tp_humanTime($length_seconds)
{
    $seconds = $length_seconds;
    $length = intval($seconds / 60);
    $leftOverSeconds = $seconds % 60;
    if ($leftOverSeconds < 10) $leftOverSeconds = "0" . $leftOverSeconds;
    $length .=     ":" . $leftOverSeconds;
    return $length;
}

function tp_parse_tag($content = '', $keyword)
{

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
            $optionsArray[tp_cleanupTagValue($pieces[0])] = tp_cleanupTagValue($pieces[1]);
        }
    }

    /* Create and return new TubePressTag object */
    return new TubePressTag($matches[0], $optionsArray);
}



?>