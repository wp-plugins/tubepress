<?php
/*
tubepress_html.php
Handles the majority of spitting out HTML

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

function tubepress_printHTML_bigvid($vid, $css, $options) {
	if ($options->get_option(TP_OPT_PLAYIN) != TP_PLAYIN_NORMAL)
		return "";
	$returnVal = <<<EOT
		<div id="$css->mainVid_id" class="$css->mainVid_class">
			<span class="$css->title_class">{$vid->metaValues[TP_VID_TITLE]}</span>
			<span class="$css->runtime_class">({$vid->metaValues[TP_VID_LENGTH]})</span><br />
EOT;
	$returnVal .= tubepress_printHTML_embeddedVid($vid->metaValues[TP_VID_ID], $options);
	$returnVal .= '</div> <!--' . $css->mainVid_class . '-->';
	return $returnVal;
}

function tubepress_printHTML_embeddedVid($id, $options) {
	$height = $options->get_option(TP_OPT_VIDHEIGHT);
	$width = $options->get_option(TP_OPT_VIDWIDTH);
	return <<<EOT
		<object type="application/x-shockwave-flash" style="width:{$width}px; height:{$height}px;" data="http://www.youtube.com/v/$id" >
			<param name="movie" value="http://www.youtube.com/v/$id" />
		</object>
EOT;
}

function tubepress_printHTML_metaInfo($vid, $options, $css, $link) {

	/* first do the title */
	$content = '<div class="' . $css->title_class . '">';
	if ($options->get_option(TP_VID_TITLE) == true)
		$content .= '<a ' . $link . '>' . $vid->metaValues[TP_VID_TITLE] . '</a><br/>';
	$content .= '</div><!-- ' . $css->title_class . ' -->';

	/* now do the runtime */
	if ($options->get_option(TP_VID_LENGTH) == true)
		$content .= '<span class="' . $css->runtime_class . '">' . $vid->metaValues[TP_VID_LENGTH] . '</span><br/>';

	/* this is a little ugly, but it's much much faster than a db call which we had before */
	$metaOptions = array(TP_VID_AUTHOR, TP_VID_ID, TP_VID_TITLE, TP_VID_LENGTH,
		TP_VID_RATING_CNT, TP_VID_RATING_AVG, TP_VID_DESC, TP_VID_VIEW, TP_VID_UPLOAD_TIME,
		TP_VID_COMMENT_CNT, TP_VID_TAGS, TP_VID_URL, TP_VID_THUMBURL);

	/* now do the rest, since they all look alike */
	foreach ($metaOptions as $metaName) {
		if (($metaName == TP_VID_LENGTH) || ($metaName == TP_VID_TITLE)) continue;
		if ($options->get_option($metaName)) {
			$content .=  '<span class="' . $css->meta_class . '">';
			switch($metaName) {
				case TP_VID_DESC:
					$content .= '</span>' . $vid->metaValues[$metaName];
					break;
				case TP_VID_THUMBURL:
					$content .= tubepress_printHTML_metaLink($option->title, $vid->metaValues[$metaName]);
					break;
				case TP_VID_URL:
					$content .= tubepress_printHTML_metaLink($option->title, $vid->metaValues[$metaName]);
					break;
				case TP_VID_AUTHOR:
					$content .= $metaName . ': ';
					$content .= tubepress_printHTML_metaLink($vid->metaValues[$metaName], 'http://www.youtube.com/profile?user=' . $vid->metaValues[$metaName]);
					break;
				case TP_VID_COMMENT_CNT:
					$content .= $metaName . ': ';
					$content .= tubepress_printHTML_metaLink($vid->metaValues[$metaName], 'http://youtube.com/comment_servlet?all_comments&amp;v=' . $vid->metaValues[$metaName]);
					break;
				case TP_VID_TAGS:
					$content .= $metaName . ': ';
					$tags = explode(" ", $vid->metaValues[$metaName]);
					$tags = implode("%20", $tags);
					$content .= tubepress_printHTML_metaLink($vid->metaValues[$metaName], 'http://youtube.com/results?search_query=' . $tags . '&amp;search=Search');
					break;
				default:
					$content .=  $metaName . ': </span>' . $vid->metaValues[$metaName];
			}
			$content .= '<br/>';
		}
	}
	$content .= '</div><!--' . $css->meta_group . ' -->';
	return $content;
}

function tubepress_printHTML_metaLink($linkText, $linkValue) {
	return '</span><a href="' . $linkValue . '">' . $linkText . '</a>';
}

function tubepress_printHTML_pagination($vidCount, $options, $css) {

	/* if we're already on a page, save that value, otherwise assume we're on the first page */
	$currentPage = (isset($_GET[TP_PAGE_PARAM])? $_GET[TP_PAGE_PARAM] : 1);

	/* save our current full address */
	$url = tubepress_fullURL();

	/* print a previous button if we're not on the first page */
	$prevText = (($currentPage > 1)? tubepress_printHTML_paginationLink($url, $currentPage - 1, "< prev") : "&nbsp;");

	/* vidcount will always be one more than what the user wanted, unless we're on the last page */
	$nextText = (($vidCount < $options->get_option(TP_OPT_VIDSPERPAGE))? "&nbsp;" : tubepress_printHTML_paginationLink($url, $currentPage + 1, "next >"));

	return '<div class="' . $css->pagination . '"><div class="' . $css->prevlink . '">' . $prevText . '</div><div class="' . $css->nextlink . '">' . $nextText . '</div></div>';
}

function tubepress_printHTML_paginationLink($queryString, $pageNum, $text) {
	$url = new Net_URL($queryString);
	$url->removeQueryString(TP_PAGE_PARAM);
	$url->addQueryString(TP_PAGE_PARAM, $pageNum);
	return '<a href="' . str_replace("&", "&amp;", $url->getURL()) . '">' . $text . '</a>';
}

function tubepress_printHTML_singleVideo($css, $options) {
	$url = new Net_URL(tubepress_fullURL());
	$url->removeQueryString(TP_VID_PARAM);
	$backUrl = $url->getURL();
	$returnVal = '<div id="' . $css->mainVid_id . '" class="' . $css->mainVid_class . '">';
	$returnVal .= tubepress_printHTML_embeddedVid($_GET[TP_VID_PARAM], $options);
	$returnVal .= '</div><a href="' . $backUrl . '">' . TP_MSG_BACK2GALLERY . '</a>';
	return $returnVal;
}

function tubepress_printHTML_smallvid($vid, $css, $options) {
	$caption = 	$vid->metaValues[TP_VID_TITLE] . "(" . $vid->metaValues[TP_VID_LENGTH] . ")";
	$thumbWidth = 	$options->get_option(TP_OPT_THUMBWIDTH);
	$thumbHeight = 	$options->get_option(TP_OPT_THUMBHEIGHT);
	$title = htmlspecialchars($vid->metaValues[TP_VID_TITLE]);
	$link = tubepress_printHTML_smallVidLinkAttributes($vid, $options);

	$content = '<div class="' . $css->thumb_class . '"><div class="' . $css->thumbImg_class . '">';
	$thumbSrc = $vid->metaValues[TP_VID_THUMBURL];
	$content .= <<<EOT
			<a $link><img alt="{$vid->metaValues[TP_VID_TITLE]}"  src="$thumbSrc" width="$thumbWidth"  height="$thumbHeight"  />
			</a>
		</div><!-- $css->thumbImg_class -->
		<div class="$css->meta_group">
EOT;
	$content .= tubepress_printHTML_metaInfo($vid, $options, $css, $link);
	$content .= '</div><!--' . $css->thumb_class . '-->';
	if ($options->get_option(TP_OPT_PLAYIN) == TP_PLAYIN_THICKBOX) {
		$content .= '<div id="tp' . $vid->metaValues[TP_VID_ID] . '" style="display:none">';
		$content .= tubepress_printHTML_embeddedVid($vid->metaValues[TP_VID_ID], $options) . '</div>';
	}
	return $content;
}

function tubepress_printHTML_smallVidLinkAttributes($vid, $options) {
	$id = $vid->metaValues[TP_VID_ID];
	switch ($options->get_option(TP_OPT_PLAYIN)) {
		case TP_PLAYIN_THICKBOX:
			return 'href="#TB_inline?height=350&amp;width=425&amp;inlineId=tp' . $id . '" class="thickbox" title="' . $vid->metaValues[TP_VID_TITLE] . '"';
		case TP_PLAYIN_NW:
			$url = new Net_URL(tubepress_fullURL());
			$url->addQueryString(TP_VID_PARAM, $id);
			$newURL = $url->getURL();
			return 'href="' . $newURL . '"';
		case TP_PLAYIN_YT:
			return 'href="http://youtube.com/watch?v=' . $id . '"';
		case TP_PLAYIN_NORMAL:
			return "href=\"#\" onclick=\"javascript:playVideo('" . $id . "', '" . $options->get_option(TP_OPT_VIDHEIGHT) . "', '" . $options->get_option(TP_OPT_VIDWIDTH)  . "', '" . htmlspecialchars($vid->metaValues[TP_VID_TITLE])  . "', '" .$vid->metaValues[TP_VID_LENGTH]. "', 'normal', '" . $options->get_option('site_url') . "')\"";
		default:
			return "href=\"#\" onclick=\"javascript:playVideo('" . $id . "', '" . $options->get_option(TP_OPT_VIDHEIGHT) . "', '" . $options->get_option(TP_OPT_VIDWIDTH)  . "', '" . htmlspecialchars($vid->metaValues[TP_VID_TITLE])  . "', '" .$vid->metaValues[TP_VID_LENGTH]. "', 'popup', '" . $options->get_option('site_url') . "')\"";
	}
}

function tubepress_printHTML_videofooter($css) {
	return '</div><p>';
}

function tubepress_printHTML_videoheader($css) {
	return '</p><div class="' . $css->container . '">';
}

?>