<?php
/**

tp_gallery.php

Handles fetching and printing out a YouTube gallery

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

function tp_generateGallery($options, $css)
{
	function_exists('tp_fetchRawXML') || require("tp_xml_rest.php");
	
    /* Print out the header */
    $newcontent = tp_printHTML_videoheader($css);
    
    /* are we paging? */
    $paging = tp_areWePaging($options);
    
    /* Grab the XML from YouTube's API */
    $youtube_xml = tp_fetchRawXML($options);

    /* Any HTTP errors? */
	if (PEAR::isError($youtube_xml)) {
		return $youtube_xml;
	}
	
	$videoArray = tp_parseRawXML($youtube_xml);

	/* Any parsing errors? Or errors from YouTube? */
	if (PEAR::isError($videoArray)) {
	    return $videoArray;
	}
        
    /* keeps track of how many videos we've actually printed */
    $videoCount = 0;
        
    /* Next two lines figure out how many videos we're going to show */
    $vidLimit = ($paging?
        $options->get_option(TP_OPT_VIDSPERPAGE) : 
        $videoArray['total']);
            
    if ($videoArray['total'] < $vidLimit) {
        $vidLimit = $videoArray['total'];
    }

    for ($x = 0; $x < $vidLimit; $x++) {
			
	    /* Create a TubePressVideo object from the XML (if we can) */
        $video = new TubePressVideo($videoArray['video'][$x]);
		
        /* If we're on the first video, see if we need to print a big one */
        if ($videoCount++ == 0) {
            $newcontent .= tp_printHTML_bigvid($video, $css, $options);
            if ($paging) {
                $newcontent .= 
                    tp_printHTML_pagination($videoArray['total'], 
                        $options, $css);
            }
            $newcontent .= '<div class="' . 
                $css->thumb_container_class . '">';
        }
        $newcontent .= tp_printHTML_smallvid($video, $css, $options);
    }
    
    $newcontent .= '</div>';
    if ($paging) {
        $newcontent .= tp_printHTML_pagination($videoArray['total'], 
            $options, $css);
    }
    
    return $newcontent;
}
?>