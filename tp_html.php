<?php
/*
tp_html.php

Handles the majority of spitting out HTML

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
 * Prints out an embedded video at the top of the gallery.
 * Used in "normal" video playing mode.
 * 
 * @param vid A TubePressVideo object of the video we're going to play
 * @param css A CSS holder object
 * @param options A TubePressTag object holding all of our options
 */
function tp_printHTML_bigvid($vid, $css, $options)
{    
    /* we only do this stuff if we're operating in "normal" play mode */
    if ($options->get_option(TP_OPT_PLAYIN) != TP_PLAYIN_NORMAL) {
        return "";
    }
    $returnVal = <<<EOT
        <div id="$css->mainVid_id" class="$css->mainVid_class">
            <span class="$css->title_class">
                {$vid->metaValues[TP_VID_TITLE]}</span>
            <span class="$css->runtime_class">
                ({$vid->metaValues[TP_VID_LENGTH]})</span><br />
EOT;
    $returnVal .= 
        tp_printHTML_embeddedVid($vid->metaValues[TP_VID_ID], $options);
    $returnVal .= '</div> <!--' . $css->mainVid_class . '-->';
    
    return $returnVal;
}

/**
 * Handles the dirty work of printing out the embedded flash
 * 
 * @param id The YouTube video ID
 * @param options A TubePressTag object holding all of our options
 */
function tp_printHTML_embeddedVid($id, $options)
{
    $height = $options->get_option(TP_OPT_VIDHEIGHT);
    $width = $options->get_option(TP_OPT_VIDWIDTH);
    
    return <<<EOT
        <object type="application/x-shockwave-flash" 
            style="width:{$width}px; height:{$height}px;" 
            data="http://www.youtube.com/v/$id" >
                <param name="movie" value="http://www.youtube.com/v/$id" />
        </object>
EOT;
}

/**
 * Prints out video meta information below a video thumbnail.
 * The title and runtime are handled slightly differently than 
 * the rest since they output so differently.
 * 
 * @param vid A TubePressVideo object of the video in question
 * @param options A TubePressTag object holding all of our options
 * @param css A CSS holder object
 * @param link The attributes of the anchor for the title text. 
 * This is generated from tp_printHTML_smallVidLinkAttributes()
 */
function tp_printHTML_metaInfo($vid, $options, $css, $link)
{

    /* first do the title */
    $content = '<div class="' . $css->title_class . '">';
    if ($options->get_option(TP_VID_TITLE) == true) {
        $content .= '<a ' . $link . '>' . 
            $vid->metaValues[TP_VID_TITLE] . '</a><br/>';
        $content .= '</div><!-- ' . $css->title_class . ' -->';
    }
    /* now do the runtime */
    if ($options->get_option(TP_VID_LENGTH) == true) {
        $content .= '<span class="' . $css->runtime_class . '">'
            . $vid->metaValues[TP_VID_LENGTH] . '</span><br/>';
    }
    /* this is a little ugly, but it's much much faster than a 
     * db call which we had before */
    $metaOptions = array(TP_VID_AUTHOR, TP_VID_ID, TP_VID_TITLE, TP_VID_LENGTH,
        TP_VID_RATING_CNT, TP_VID_RATING_AVG, TP_VID_DESC, TP_VID_VIEW, 
        TP_VID_UPLOAD_TIME, TP_VID_COMMENT_CNT, TP_VID_TAGS, TP_VID_URL, 
        TP_VID_THUMBURL);

    /* now do the rest, since they all look alike */
    foreach ($metaOptions as $metaName) {
        
        /* ignore the title and runtime */
        if (($metaName == TP_VID_LENGTH) || ($metaName == TP_VID_TITLE)) {
            continue;
        }
        /* only bother with the ones the user wants to see */
        if ($options->get_option($metaName)) {
            $content .=  '<span class="' . $css->meta_class . '">';
            switch ($metaName) {
                
                case TP_VID_DESC:
                    $content .= '</span>' . $vid->metaValues[$metaName];
                    break;
                    
                case TP_VID_THUMBURL:
                    $content .= tp_printHTML_metaLink($option->title, 
                        $vid->metaValues[$metaName]);
                    break;
                    
                case TP_VID_URL:
                    $content .= tp_printHTML_metaLink($option->title, 
                        $vid->metaValues[$metaName]);
                    break;
                    
                case TP_VID_AUTHOR:
                    $content .= $metaName . ': ';
                    $content .= 
                        tp_printHTML_metaLink($vid->metaValues[$metaName],
                        'http://www.youtube.com/profile?user='
                        . $vid->metaValues[$metaName]);
                    break;
                    
                case TP_VID_COMMENT_CNT:
                    $content .= $metaName . ': ';
                    $content .= 
                        tp_printHTML_metaLink($vid->metaValues[$metaName],
                       'http://youtube.com/comment_servlet?all_comments&amp;v='
                        . $vid->metaValues[$metaName]);
                    break;
                    
                case TP_VID_TAGS:
                    $content .= $metaName . ': ';
                    $tags = explode(" ", $vid->metaValues[$metaName]);
                    $tags = implode("%20", $tags);
                    $content .= 
                        tp_printHTML_metaLink($vid->metaValues[$metaName],
                        'http://youtube.com/results?search_query='
                        . $tags . '&amp;search=Search');
                    break;
                    
                default:
                    $content .=  $metaName . ': </span>' 
                        . $vid->metaValues[$metaName];
            }
            $content .= '<br/>';
        }
    }
    $content .= '</div><!--' . $css->meta_group . ' -->';
    
    return $content;
}

/**
 * Simple helper method for tp_printHTML_metaInfo(). Prints
 * out a link for a line of meta information.
 * 
 * @param linkText The text of the link
 * @param linkvalue The anchor attributes
 */
function tp_printHTML_metaLink($linkText, $linkValue)
{
    return '</span><a href="' . $linkValue . '">' . $linkText . '</a>';
}
    
/**
 * Handles the logic and printing of pagination links ("next" and "prev")
 * 
 * @param vidCount How many videos we're supposed to print out per page 
 * (+ 1, unless we're on the last page)
 * @param options A TubePressTag object holding all of our options
 * @param css A CSS holder object
 */
function tp_printHTML_pagination($vidCount, $options, $css)
{

    /* if we're already on a page, save that value, otherwise assume 
     * we're on the first page */
    $currentPage = (isset($_GET[TP_PAGE_PARAM])? $_GET[TP_PAGE_PARAM] : 1);

    /* save our current full address */
    $url = TubePressTag::fullURL();

    /* print a previous button if we're not on the first page */
    $prevText = (($currentPage > 1)? 
        tp_printHTML_paginationLink($url, $currentPage - 1, "< prev")
         : "&nbsp;");

    /* vidcount will always be one more than what the user wanted, 
     * unless we're on the last page */
    $nextText = (($vidCount < $options->get_option(TP_OPT_VIDSPERPAGE))? 
        "&nbsp;"
         : tp_printHTML_paginationLink($url, $currentPage + 1, "next >"));

    return '<div class="' . $css->pagination . '"><div class="' 
        . $css->prevlink . '">' . $prevText . '</div><div class="' 
        . $css->nextlink . '">' . $nextText . '</div></div>';
}

/**
 * Simple helper for tp_printHTML_pagination(). Prints out the actual
 * anchor tag
 * 
 * @param queryString The full URL of the page we're currently on
 * @param pageNum The page for which this method will print out a link
 * @param text The text of the link (always either "next" or "prev")
 */
function tp_printHTML_paginationLink($queryString, $pageNum, $text)
{
    $url = new Net_URL($queryString);
    $url->removeQueryString(TP_PAGE_PARAM);
    $url->addQueryString(TP_PAGE_PARAM, $pageNum);
    
    return '<a href="' . str_replace("&", "&amp;", $url->getURL()) . '">' 
        . $text . '</a>';
}

/**
 * Used in "single video" mode to print out a single video and a 
 * "back to gallery" link
 *
 * @param options A TubePressTag object holding all of our options
 * @param css A CSS holder object 
 */
function tp_printHTML_singleVideo($css, $options)
{
    $url = new Net_URL(TubePressTag::fullURL());
    $url->removeQueryString(TP_VID_PARAM);
    
    $returnVal = '<div id="' . $css->mainVid_id . '" class="' 
        . $css->mainVid_class . '">';
    $returnVal .= tp_printHTML_embeddedVid($_GET[TP_VID_PARAM], $options);
    $returnVal .= '</div><a href="' . $url->getURL() . '">' 
        . TP_MSG_BACK2GALLERY . '</a>';
    
    return $returnVal;
}

/**
 * The main wrapper method for printing out a single video 
 * thumbnail and the meta information for it.
 * 
 * @param vid A TubePressVideo object for which this method 
 * will print the thumb
 * @param options A TubePressTag object holding all of our options
 * @param css A CSS holder object
 */
function tp_printHTML_smallvid($vid, $css, $options)
{
    $caption =         $vid->metaValues[TP_VID_TITLE] . "(" 
        . $vid->metaValues[TP_VID_LENGTH] . ")";
    $thumbWidth =     $options->get_option(TP_OPT_THUMBWIDTH);
    $thumbHeight =     $options->get_option(TP_OPT_THUMBHEIGHT);
    $title =         htmlspecialchars($vid->metaValues[TP_VID_TITLE]);
    $link =         tp_printHTML_smallVidLinkAttributes($vid, $options);

    $content = '<div class="' . $css->thumb_class . '"><div class="' 
        . $css->thumbImg_class . '">';
    $thumbSrc = $vid->metaValues[TP_VID_THUMBURL];
    $content .= <<<EOT
            <a $link>
                <img alt="{$vid->metaValues[TP_VID_TITLE]}"  
                    src="$thumbSrc" width="$thumbWidth"  
                    height="$thumbHeight"  />
            </a>
        </div><!-- $css->thumbImg_class -->
        <div class="$css->meta_group">
EOT;
    $content .= tp_printHTML_metaInfo($vid, $options, $css, $link);
    $content .= '</div><!--' . $css->thumb_class . '-->';
    if ($options->get_option(TP_OPT_PLAYIN) == TP_PLAYIN_THICKBOX) {
        $content .= '<div id="tp' . $vid->metaValues[TP_VID_ID] 
            . '" style="display:none">';
        $content .= 
            tp_printHTML_embeddedVid($vid->metaValues[TP_VID_ID], $options) 
            . '</div>';
    }
    
    return $content;
}

/**
 * Prints out the "play" link attributes for a video thumbnail
 * 
 * @param vid A TubePressVideo object for the video in question
 * @param options A TubePressTag object holding all of our options
 */
function tp_printHTML_smallVidLinkAttributes($vid, $options)
{
    $id =         $vid->metaValues[TP_VID_ID];
    $height =     $options->get_option(TP_OPT_VIDHEIGHT);
    $width =     $options->get_option(TP_OPT_VIDWIDTH);
    
    switch ($options->get_option(TP_OPT_PLAYIN)) {
        case TP_PLAYIN_THICKBOX:
            return 'href="#TB_inline?height=350&amp;width=425&amp;inlineId=tp'
                . $id . '" class="thickbox" title="' 
                . $vid->metaValues[TP_VID_TITLE] . '"';
        
        case TP_PLAYIN_NW:
            $url = new Net_URL(TubePressTag::fullURL());
            $url->addQueryString(TP_VID_PARAM, $id);
            $newURL = $url->getURL();
            return 'href="' . $newURL . '"';
        
        case TP_PLAYIN_YT:
            return 'href="http://youtube.com/watch?v=' . $id . '"';
        
        case TP_PLAYIN_LWINDOW:
            return "href=\"" . $options->get_option('site_url') 
                . "/wp-content/plugins/tubepress/tp_popup.php?name=" 
                . htmlspecialchars($vid->metaValues[TP_VID_TITLE]) . "&id=" 
                . $id . "&w=" . $options->get_option(TP_OPT_VIDWIDTH) . "&h="
                . $options->get_option(TP_OPT_VIDHEIGHT) 
                . "\" class=\"lWOn\" title=\"" 
                . htmlspecialchars($vid->metaValues[TP_VID_TITLE]) 
                . "\" params=\"lWWidth=425,lWHeight=355\"";
    
        case TP_PLAYIN_NORMAL:
            return "href=\"#\" onclick=\"javascript:playVideo('" . $id . "', '"
                . $height . "', '" . $width  . "', '" 
                . htmlspecialchars($vid->metaValues[TP_VID_TITLE])  
                . "', '" . $vid->metaValues[TP_VID_LENGTH] . "', 'normal', '" 
                . $options->get_option('site_url') . "')\"";
        
        default:
            return "href=\"#\" onclick=\"javascript:playVideo('" . $id . "', '" 
                . $height . "', '" . $width  . "', '" 
                . htmlspecialchars($vid->metaValues[TP_VID_TITLE])  
                . "', '" . $vid->metaValues[TP_VID_LENGTH] . "', 'popup', '" 
                . $options->get_option('site_url') . "')\"";
    }
}

/**
 * Prints out the tail end of the gallery
 * 
 * @param css A CSS holder object
 */
function tp_printHTML_videofooter($css)
{
    return '</div><p>';
}

/**
 * Prints out the very beginning of the gallery
 * 
 * @param css A CSS holder object
 */
function tp_printHTML_videoheader($css)
{
    return '</p><div class="' . $css->container . '">';
}

?>