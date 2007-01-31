<?php
function tubepress_printSingleVideo($css, $options) {
	$width = $options->get_option(TP_OPT_VIDWIDTH) . "px";
	$height = $options->get_option(TP_OPT_VIDHEIGHT) . "px";
	$url = new Net_URL(tubepress_fullURL());
	$url->removeQueryString('tubepress_id');
	$backUrl = $url->getURL();
	$returnVal = <<<EOT
		<div id="$css->mainVid_id" class="$css->mainVid_class">
EOT;
	$returnVal .= tubepress_printHTML_embeddedVid($_GET['tubepress_id']);
	$returnVal .= <<<EOT
		</div>
		<a href="$backUrl">Back to gallery</a>
EOT;
	return $returnVal;
}


function tubepress_printHTML_videoheader($css) {
	return <<<EOT
		</p><!-- for XHTML validation -->
		<div class="$css->container">
EOT;
}
function tubepress_printHTML_pagination($vidCount, $options) {
	
	$currentPage = 1;
	$url = tubepress_fullURL();
	
	/* if we're already on a page, save that value, otherwise assume we're on the first page */
	if (isset($_GET[TP_PAGE_PARAM]))
		$currentPage = $_GET[TP_PAGE_PARAM];
	$nextText = tubepress_printHTML_link2NewPage($url, $currentPage + 1, "next >");
	
	/* print a previous button if we're not on the first page */
	$prevText = "";
	if ($currentPage > 1) $prevText = tubepress_printHTML_link2NewPage($url, $currentPage - 1, "< prev");
	
	/* vidcount will always be one more than what the user wanted, unless we're on the last page */
	if ($vidCount < $options->get_option(TP_OPT_VIDSPERPAGE)) $nextText = "";
	
	return '<div><div style="float: left">' . $prevText . '</div><div style="float: right">' . $nextText . '</div></div>';
}

function tubepress_printHTML_link2NewPage($queryString, $pageNum, $text) {
	$url = new Net_URL($queryString);
	$url->removeQueryString(TP_PAGE_PARAM);
	$url->addQueryString(TP_PAGE_PARAM, $pageNum);
	return "<a href=\"" . $url->getURL() . "\">" . $text . "</a>";
}

function tubepress_printHTML_videofooter($css) {
	return <<<EOT
			</div>
		</div>
		<p><!-- for XHTML validation -->
EOT;
}

function tubepress_printHTML_embeddedVid($id) {
	$height = $options->get_option(TP_OPT_VIDHEIGHT);
	$width = $options->get_option(TP_OPT_VIDWIDTH);
	return <<<EOT
	<object type="application/x-shockwave-flash" style="width:{$width}px; height:{$height}px;" data="http://www.youtube.com/v/$id" >
		<param name="movie" value="http://www.youtube.com/v/$id" />
	</object>
EOT;
}

function tubepress_printHTML_bigvid($vid, $css, $options) {
	if ($options->get_option(TP_OPT_PLAYIN) != TP_PLAYIN_NORMAL) return;
	$header = TP_MSG_MAINVID_HEADER;
	$returnVal = <<<EOT
		<div id="$css->mainVid_id" class="$css->mainVid_class">
        	<span class="$css->meta_class">$header</span> 
			<span class="$css->title_class">{$vid->metaValues[TP_VID_TITLE]}</span>
			<span class="$css->runtime_class">({$vid->metaValues[TP_VID_LENGTH]})</span>
EOT;
	$returnVal .= tubepress_printHTML_embeddedVid($vid->metaValues[TP_VID_ID]);
	$returnVal .= <<<EOT
		</div> <!-- $css->mainVid_class -->
		<div class="$css->thumb_container_class">
EOT;
	return $returnVal;
}

function tubepress_printHTML_ImgLink($vid, $options) {
	switch ($options->get_option(TP_OPT_PLAYIN)) {
		
		case TP_PLAYIN_LB:
			$returnVal = '<div id="' . $vid->metaValues[TP_VID_ID] . '" style="display:none">';
			$returnVal .= tubepress_printHTML_embedded($vid->metaValues[TP_VID_ID]);
			$returnVal .= <<<EOX
				</div>
				<a href='#TB_inline?height=350&width=425&inlineId={$vid->metaValues[TP_VID_ID]}' class='thickbox' title='{$vid->metaValues[TP_VID_TITLE]}'>
EOX;
			return $returnVal;
			
		case TP_PLAYIN_NW):
			$url = new Net_URL(tubepress_getFullURL());
			$url->addQueryString('tubepress_id', $vid->metaValues[TP_VID_ID]);
			$newURL = $url->getURL();
			return '<a href="' . $newURL . '">';
		case TP_PLAYIN_YT:
			return '<a href="http://youtube.com/watch?v=' . $vid->metaValues[TP_VID_ID];
		default:
			return "<a href=\"#\" onclick=\"javascript: playVideo('{$vid->metaValues[TP_VID_ID]}', '$vidHeight', '$vidWidth','$title', '{$vid->metaValues[TP_VID_LENGTH]}', '$location', '$url'); return true;\">";
	}
}

function tubepress_printHTML_smallvid($vid, $css, $options) {
	$caption = 	$vid->metaValues[TP_VID_TITLE] . "(" . $vid->metaValues[TP_VID_LENGTH] . ")";
	$thumbWidth = 	$options->get_option(TP_OPT_THUMBWIDTH);
	$thumbHeight = 	$options->get_option(TP_OPT_THUMBHEIGHT);
	$vidWidth = 	$options->get_option(TP_OPT_VIDWIDTH);
	$vidHeight = 	$options->get_option(TP_OPT_VIDHEIGHT);
	$location = 	$options->get_option(TP_OPT_PLAYIN);
	$url = 			get_settings('siteurl');
	$title = htmlspecialchars($vid->metaValues[TP_VID_TITLE]);
	
$content = <<<EOT
	<div class="$css->thumb_class">
		<div class="$css->thumbImg_class">
EOT;
	$content .= tubepress_printHTML_ImgLink($vid, $options, $vidWidth, $vidHeight);
		
$content .= <<<EOT
			<img alt="{$vid->metaValues[TP_VID_TITLE]}"  src="{$vid->metaValues[TP_VID_THUMBURL]}" width="$thumbWidth"  height="$thumbHeight"  />
			</a>
		</div>

		<div class="$css->meta_group">
		<div class="$css->title_class">
EOT;
	if ($options->get_option(TP_VID_TITLE) == true) {
		if ($options->get_option(TP_OPT_PLAYIN) != TP_PLAYIN_LB)
			$content .= "<a href=\"#\" onclick=\"javascript: playVideo('{$vid->metaValues[TP_VID_ID]}', '$vidHeight', '$vidWidth', '$title', '{$vid->metaValues[TP_VID_LENGTH]}', '$location', '$url'); return true;\">{$vid->metaValues[TP_VID_TITLE]}</a><br/>";
		else
			$content .= "<a href='#TB_inline?height=350&width=425&inlineId={$vid->metaValues[TP_VID_ID]}' class='thickbox' title='{$vid->metaValues[TP_VID_TITLE]}'>{$vid->metaValues[TP_VID_TITLE]}</a><br/>";
	}
	$content .= <<<EOT
		</div>
EOT;
	if ($options->get_option(TP_VID_LENGTH) == true) {
		$content .= <<<EOP
			<span class="$css->runtime_class">{$vid->metaValues[TP_VID_LENGTH]}</span><br/>
EOP;
	}

	$content .= tubepress_printHTML_metaInfo($vid, $options);
	$content .= '</div><!--' . $css->thumb_class . '-->';
	return $content;
}

function tubepress_printHTML_metaInfo($vid, $options) {
	$metaOptions = get_option(TP_OPTS_META);
	foreach ($metaOptions as $option) {
		if (($option->name == TP_VID_LENGTH) || ($option->name == TP_VID_TITLE)) continue;
		if ($options->get_option($option->name) == "true") {
			$content .=  '<span class="' . $css->meta_class . '">';		
			switch($option->name) {
				case TP_VID_DESC:
					$content .= '</span>' . $vid->metaValues[$option->name];
					break;
				case TP_VID_THUMBURL:
					$content .= tubepress_makeMetaLink($option->title, $vid->metaValues[$option->name]);
					break;
				case TP_VID_URL:
					$content .= tubepress_makeMetaLink($option->title, $vid->metaValues[$option->name]);
					break;
				case TP_VID_AUTHOR:
					$content .= $option->title . ': ';
					$content .= tubepress_makeMetaLink($vid->metaValues[$option->name], 'http://www.youtube.com/profile?user=' . $vid->metaValues[$option->name]); 
					break;
				case TP_VID_COMMENT_CNT:
					$content .= $option->title . ': ';
					$content .= tubepress_makeMetaLink($vid->metaValues[$option->name], 'http://youtube.com/comment_servlet?all_comments&v=' . $vid->metaValues[$option->name]);
					break;
				case TP_VID_TAGS:
					$content .= $option->title . ': ';
					$tags = explode(" ", $vid->metaValues[$option->name]);
					$tags = implode("%20", $tags);
					$content .= tubepress_makeMetaLink($vid->metaValues[$option->name], 'http://youtube.com/results?search_query=' . $tags . '&search=Search'); 
					break;
				default:
					$content .=  $option->title . ': </span>' . $vid->metaValues[$option->name];
			}
			$content .= '<br/>';
		}
	}
	$content .= '</div><!--' . $css->meta_group . ' -->';
}

function tubepress_makeMetaLink($linkText, $linkValue) {
	return '</span><a href="' . $linkValue . '">' . $linkText . '</a>';
}
?>