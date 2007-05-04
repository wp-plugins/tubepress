<?php
/*
tp_classes.php

The classes used in TubePress

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
 * Serves as a constant object to hold CSS info
*/
class TubePressCSS
{
    /**
     * Constructor
     */
    function TubePressCSS()
    {
        $this->container =             "tubepress_container";
        $this->mainVid_id =            "tubepress_mainvideo";
        $this->mainVid_class =         "tubepress_mainvideo";
        $this->meta_class =            "tubepress_meta";
        $this->thumb_container_class = "tubepress_video_thumbs";
        $this->thumb_class =           "tubepress_thumb";
        $this->thumbImg_class =        "tubepress_video_thumb_img";
        $this->runtime_class =         "tubepress_runtime";
        $this->title_class =           "tubepress_title";
        $this->success_class =         "updated fade";
        $this->meta_group =            "tubepress_meta_group";
        $this->pagination =            "tubepress_pagination";
        $this->nextlink =              "tubepress_next";
        $this->prevlink =              "tubepress_prev";
    }
}

/**
 * This class holds all of the options for the plugin,
 * both pulled from the db and those the user defined
 * in the tag string.
 */
class TubePressTag
{
    var $tagString, $customOptions, $dbOptions;

    /**
     * Constructor
     */
    function TubePressTag($content = '', $keyword)
    {
    	$optionsArray = array();

        /* Use a regular expression to match everything in square brackets after the TubePress keyword */
        $regexp = '\[' . $keyword . "(.*)\]";
        preg_match("/$regexp/", $content, $matches);

        /* Execute if anything was matched by the parentheses */
        if (isset($matches[1])) {
            /* Break up the options by comma and store them in an associative array */
            $pairs = explode(",", $matches[1]);
        
            foreach($pairs as $pair) {
                $pieces = explode("=", $pair);
                $optionsArray[TubePressTag::cleanupTagValue($pieces[0])] = 
                    TubePressTag::cleanupTagValue($pieces[1]);
            }
        }

        $this->tagString = $matches[0];
        $this->customOptions = $optionsArray;
        foreach (get_option(TP_OPTION_NAME) as $dbOptionArray) {
            foreach ($dbOptionArray as $dbOption) {
                $this->dbOptions[$dbOption->name] = $dbOption->value;
            }
        }
        $this->customOptions['site_url'] = get_settings('siteurl');
    }

    /**
     * First checks the tag string for the option, otherwise gets what
     * was in the db
     */
    function get_option($option = '')
    {
        if (!empty($this->customOptions)
            && isset($this->customOptions[$option])) {
                return $this->customOptions[$option];
        }
        if (!empty($this->dbOptions)
            && isset($this->dbOptions[$option])) {
                return $this->dbOptions[$option];
        }
    }
    
    private static function cleanupTagValue($nameOrValue)
    {
        /*
         * WTF: this seems to work, though I have no idea why the quotes are getting
         * converted into these stupid entities.
         */
        return trim(
            str_replace(
                array("&#8220;", "&#8221;", "&#8217;", "&#8216;",
                      "&#8242;", "&#8243;", "&#34"),"", 
                      trim($nameOrValue)));
    }
}

/**
 * This class represents a video pulled from YouTube. It's really
 * just a glorified wrapper for an associated array.
 */
class TubePressVideo
{
    var $metaValues;

    /**
     * Constructor
     */
    function TubePressVideo($videoXML)
    {
            $this->metaValues =
                array(TP_VID_AUTHOR =>
                          $videoXML['author'],
                        
                      TP_VID_ID =>          
                          $videoXML['id'],
                          
                      TP_VID_TITLE =>       
                          str_replace("'","&#145;", $videoXML['title']),
                          
                      TP_VID_LENGTH =>      
                          TubePressVideo::humanTime($videoXML['length_seconds']),
                          
                      TP_VID_RATING_AVG =>  
                          $videoXML['rating_avg'],
                          
                      TP_VID_RATING_CNT =>  
                          number_format($videoXML['rating_count']),
                          
                      TP_VID_DESC =>        
                          $videoXML['description'],
                          
                      TP_VID_VIEW =>        
                          number_format($videoXML['view_count']),
                          
                      TP_VID_UPLOAD_TIME => 
                          date("M j, Y", $videoXML['upload_time']),
                          
                      TP_VID_COMMENT_CNT => 
                          number_format($videoXML['comment_count']),
                          
                      TP_VID_TAGS =>        
                          $videoXML['tags'],
                          
                      TP_VID_URL =>         
                          $videoXML['url'],
                          
                      TP_VID_THUMBURL =>    
                          $videoXML['thumbnail_url']);
    }
    
    /**
     * Converts seconds to minutes and seconds
     * 
     * @param length_seconds The runtime of a video, in seconds
     */
    private static function humanTime($length_seconds)
    {
        $seconds = $length_seconds;
        $length = intval($seconds / 60);
        $leftOverSeconds = $seconds % 60;
        if ($leftOverSeconds < 10) $leftOverSeconds = "0" . $leftOverSeconds;
        $length .=     ":" . $leftOverSeconds;
        return $length;
    }
}

/**
 * A single TubePress option
 */
class TubePressOption
{
    var $name, $title, $description, $value;

    /**
     * Constructor
     */
    function TubePressOption($theName, $theTitle, $theDesc, $theValue)
    {
        $this->name = $theName;
        $this->description = $theDesc;
        $this->value = $theValue;
        $this->title = $theTitle;
    }
}

class TubePress
{
    public static function bail($msg, $error)
    {
	    return $msg . " (" . $error->message . ")";
    }

    public static function areWePaging($options)
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
    
    public static function fullURL()
    {
        return "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
    }
    
    public static function determineNextAction($options)
    {
        if ($options->get_option(TP_OPT_PLAYIN) == TP_PLAYIN_NW
            && isset($_GET[TP_VID_PARAM]))
                return "SINGLEVIDEO";
    }
}
?>
