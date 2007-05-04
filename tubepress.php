<?php
/**
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
defined('TP_OPT_DEVID') || require("tp_strings.php");
class_exists('TubePressVideo') || require("tp_classes.php");
function_exists('tp_add_options_page') || require("tp_options_page.php");    
function_exists('tp_get_youtube_xml') || require("tp_utility.php");
function_exists('tp_printSingleVideo') || require("tp_html.php");
function_exists('tp_debug') || require("tp_debug.php");
class_exists('Translation2') || require("lib/PEAR/Internationalization/Translation2/Translation2.php");
class_exists('Snoopy') || require(ABSPATH . "wp-includes/class-snoopy.php");
class_exists('Net_URL') || require("lib/PEAR/Networking/Net_URL/URL.php");
function_exists('tp_generateGallery') || require("tp_gallery.php");

/**
 * Main filter hook. Looks for a tubepress tag
 * and replaces it with a gallery (or single video) if it's found
*/
function tp_main ($content = '')
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
 
 	/* We'll store everything we generate in the following string */
 	$newcontent = "";
 
 	//$m = loadTranslations();
 
    /* Parse the tag  */
    $options = new TubePressTag($content, $keyword);
	if (PEAR::isError($options)) {
	    return TubePress::bail($content, "There was a problem parsing the TubePress tag in this page", $options);
	}

    /* Get CSS constants */
    $css = new TubePressCSS();

	/* Are we debugging? */
    global $tubepress_disable_debug;
    if (!$tubepress_disable_debug
        && isset($_GET[TP_DEBUG_PARAM]) 
        && ($_GET[TP_DEBUG_PARAM] == true)) {
            $newcontent .= tp_debug($options);
    }

	switch (TubePress::determineNextAction($options)) {
	    case "SINGLEVIDEO":
	        $newcontent .= tp_printHTML_singleVideo($css, $options);
	        break;
        default:
            $result = tp_generateGallery($options, $css);
            $newcontent .= PEAR::isError($result)?
                TubePress::bail("There was a problem generating the gallery", $result) :
                $result;
            break;
	}

    /* We're done! Replace the tag with our new content */
    return str_replace($options->tagString, $newcontent, $content);
}

/**
 * Spits out the CSS and JS files that we always need for TubePress
 */
function tp_insert_cssjs()
{
	$url = get_settings('siteurl') . "/wp-content/plugins/tubepress";
	print<<<GBS
	    <script type="text/javascript" src="{$url}/tubepress.js"></script>
	    <link rel="stylesheet" href="{$url}/tubepress.css" type="text/css" />
GBS;
}

/**
 * Spits out the CSS and JS files that we need for LightWindow
 */
function tp_insert_lightwindow() {
	$url = get_settings('siteurl') . "/wp-content/plugins/tubepress/lib/lightWindow";
    print<<<GBS
        <script type="text/javascript" src="{$url}/javascript/prototype.js"></script>
	    <script type="text/javascript" src="{$url}/javascript/effects.js"></script>
	    <script type="text/javascript" src="{$url}/javascript/lightWindow.js"></script>
	    <link rel="stylesheet" href="{$url}/css/lightWindow.css" media="screen" type="text/css" />
GBS;
}

/**
 * Spits out the CSS and JS files that we need for ThickBox
 */
function tp_insert_thickbox()
{
	$url = get_settings('siteurl') . "/wp-content/plugins/tubepress/lib/thickbox";
	print<<<GBS
	    <script type="text/javascript" src="{$url}/jquery.js"></script>
	    <script type="text/javascript" src="{$url}/thickbox.js"></script>
	    <link rel="stylesheet" href="{$url}/thickbox.css" media="screen" type="text/css" />
GBS;
}

/* ACTIONS */
add_action('admin_menu',  'tp_add_options_page');
add_action('wp_head',     'tp_insert_cssjs');

/* add thickbox or lightwindow, if we need them */
$quickOpts = get_option(TP_OPTION_NAME);
if ($quickOpts != NULL) {
	$disp = $quickOpts[TP_OPTS_PLAYERMENU];
	$playWith = $disp[TP_OPT_PLAYIN]->value;
	
	switch ($playWith) {
		case TP_PLAYIN_THICKBOX:
			add_action('wp_head', 'tp_insert_thickbox');
			break;
		case TP_PLAYIN_LWINDOW:
			add_action('wp_head', 'tp_insert_lightwindow');
			break;
		default:
	}
}

/* FILTERS */
add_filter('the_content', 'tp_main');

?>
