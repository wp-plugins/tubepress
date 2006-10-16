function playVideo(id, height, width, title, time) {
	document.getElementById('tubepress_the_video').innerHTML = ' \
		<a name="tubepress_video"></a> \
			<span class="tubepress_title">' + title + '</span> \
			<span class="runtime">(' + time + ') \
			<object width="' + width +'" height="' + height + '"> \
				<param name="movie" value="http://www.youtube.com/v/' + id + '" /> \
				<embed src="http://www.youtube.com/v/' + id + '" type="application/x-shockwave-flash" width="' + width + '" height="' + height + '" /> \
			</object> \
	';
	document.location.hash = '#tubepress_video';
}
