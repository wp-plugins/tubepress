function playVideo(id, height, width, caption) {
	document.getElementById('tubepress_the_video').innerHTML = ' \
		<a name="tubepress_video"> \
			<div class="tubepress_meta_large"> \
                        	' + caption + ' \
                	</div> \
			<object width="' + width +'" height="' + height + '"> \
				<param name="movie" value="http://www.youtube.com/v/' + id + '" /> \
				<embed src="http://www.youtube.com/v/' + id + '" type="application/x-shockwave-flash" width="' + width + '" height="' + height + '" /> \
			</object> \
		</a> \
	';
	document.location.hash = '#tubepress_video';
}
