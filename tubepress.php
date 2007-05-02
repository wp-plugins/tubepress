<?php
/*
Plugin Name: TubePress
Plugin URI: http://ehough.com/youtube/tubepress
Description: Display configurable YouTube galleries in your posts and/or pages
Author: Eric Hough
Version: 1.2.5
Author URI: http://ehough.com

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

/*
 * Change to "true" to DISABLE the ability to activate debug mode. This
 * will minimally increase your privacy, but will make it nearly impossible
 * for me to help you out if something goes awry. Use at your own risk :p
 */
$tubepress_disable_debug = false;

/* Imports */
defined('TP_OPT_DEVID') ||
    require("tubepress_strings.php");
class_exists('tubepressVideo') ||
    require("tubepress_classes.php");
function_exists('tp_add_options_page') ||
    require("tubepress_options.php");
function_exists('tp_get_youtube_xml') ||
    require("tubepress_utility.php");
function_exists('tp_printSingleVideo') ||
    require("tubepress_html.php");
function_exists('tp_debug') ||
    require("tubepress_debug.php");
class_exists('IsterXmlSimpleXMLImpl') ||
    require("lib/simpleXML/IsterXmlSimpleXMLImpl.php");
class_exists('Snoopy') ||
    require(ABSPATH . "wp-includes/class-snoopy.php");
class_exists('Net_URL') ||
    require("lib/PEAR/Net_URL/URL.php");

/**
 * Main filter hook. Looks for a tubepress tag
 * and replaces it with a gallery (or single video) if it's found
*/
function tp_showgallery ($content = '')
{
    /* Get out fast if we're not needed */
    $quickOpts = get_option(TP_OPTION_NAME);
    if ($quickOpts == NULL) {
        return $content;
    }
    $adv = $quickOpts[TP_OPTS_ADV];
    $keyword = $adv[TP_OPT_KEYWORD]->value;
    if (!strpos($content, '[' . $keyword->value)) {
        return $content;
    }
 
    /* Parse the tag  */
    $options = tp_parse_tag($content, $keyword);

    /* get css */
    $css = new tubepressCSS();

    /* Set up the header no matter what */
    $newcontent = tp_printHTML_videoheader($css);

    global $tubepress_disable_debug;

    /* Are we debugging? */
    if (!$tubepress_disable_debug && tp_areWeDebugging()) {
        tp_debug($options);
    }

    /* Are we printing a single video? */
    if (tp_printingSingleVideo($options)) {
        $newcontent .= tp_printHTML_singleVideo($css, $options);
        return tp_finish($newcontent, $content, $options, $css);
    }
    
    /* are we paging? */
    $paging = tp_areWePaging($options);

    /* Grab the XML from YouTube's API */
    $youtube_xml = tp_get_youtube_xml($options);

    /* count how many we got back */
    $videosReturnedCount = tp_count_videos($youtube_xml);

    $error = false;
    /* Check for a YouTube timeout */
    if ($youtube_xml == TP_XMLERR) {
        $error = true;
        $newcontent .= "<div>" . TP_MSG_TIMEOUT;
    }
    /* Did we get any videos? */
    if ($videosReturnedCount == 0) {
        $error = true;
        $newcontent .= "<div>" . TP_MSG_YTERR;
    }

    /* Loop through each video */
    if ($error == false) {
        
        /* keeps track of how many videos we've actually printed */
        $videoCount = 0;
        
        /* Next two lines figure out how many videos we're going to show */
        $vidLimit = ($paging?
            $options->get_option(TP_OPT_VIDSPERPAGE) : 
            $videosReturnedCount);
            
        if ($videosReturnedCount < $vidLimit) {
            $vidLimit = $videosReturnedCount;
        }
        
        for ($x = 0; $x < $vidLimit; $x++) {
   
            /* Create a tubepressVideo object from the XML (if we can) */
            $video = new tubepressVideo($youtube_xml->video[$x]);
            if ($video->metaValues[TP_VID_ID] == '') {
                continue;
            }
            
            /* If we're on the first video, see if we need to print a big one */
            if ($videoCount++ == 0) {
                $newcontent .= tp_printHTML_bigvid($video, $css, $options);
                if ($paging) {
                    $newcontent .= 
                        tp_printHTML_pagination($videosReturnedCount, 
                            $options, $css);
                }
                $newcontent .= '<div class="' . 
                    $css->thumb_container_class . '">';
            }
            $newcontent .= tp_printHTML_smallvid($video, $css, $options);
        }
        $newcontent .= '</div>';
        if ($paging) {
            $newcontent .= tp_printHTML_pagination($videosReturnedCount, 
                $options, $css);
        }
    }
    
    return tp_finish($newcontent, $content, $options, $css);
}

/**
 * Prints out HTML tail and does the work of replacing the tag string with
 * the TubePress result.
 * 
 * @param newcontent The new TubePress-generated HTML
 * @param content The old WordPress-generated HTML
 * @param options A tubepressTag object holding all of our options
 * @param css A CSS holder object
 */
function tp_finish($newcontent, $content, $options, $css)
{
    /* push out the footer */
    $newcontent .= tp_printHTML_videofooter($css);

    /* We're done, so let's insert the gallery (or single video) 
     * where the keyword is */
    return str_replace($options->tagString, $newcontent, $content);
}

/**
 * Spits out the CSS and JS files that we always need for TubePress
 */
function tp_insert_cssjs()
{
    $url = get_settings('siteurl') . "/wp-content/plugins/tubepress";
    echo '<script type="text/javascript" src="' . $url . 
        '/tubepress.js"></script>';
    echo '<link rel="stylesheet" href="' . $url . 
        '/tubepress.css" type="text/css" />';
}

function tp_insert_thickbox()
{
    $url = get_settings('siteurl') . "/wp-content/plugins/tubepress";
    echo '<script type="text/javascript" src="' . $url . 
        '/lib/thickbox/jquery.js"></script>';
    echo '<script type="text/javascript" src="' . $url . 
        '/lib/thickbox/thickbox.js"></script>';
    echo '<link rel="stylesheet" href="' . $url . 
        '/lib/thickbox/thickbox.css" media="screen" type="text/css" />';
}

/* ACTIONS */
add_action('admin_menu',  'tp_add_options_page');
add_action('wp_head',     'tp_insert_cssjs');
add_action('wp_head', 'tp_insert_thickbox');

/* FILTERS */
add_filter('the_content', 'tp_showgallery');

?>
