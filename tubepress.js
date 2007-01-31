function playVideo(id, height, width, title, time, location, url) {
	switch (location) {
		case "popup":
			var newurl = url + "/wp-content/plugins/tubepress/tubepress_popup.php?name=" + escape(title) + "&id=" + id + "&w=" + width + "&h=" + height;
			window.open(newurl, "newwin", "width=" + width + ",height=" + height + ",toolbar=false,locationbar=false,directories=false,status=false,menubar=false,scrollbars=false,resizable=true,copyhistory=false");
			break;
		default:
			document.getElementById('tubepress_mainvideo').innerHTML = ' \
				<a name="tubepress_video"></a> \
					<span class="tubepress_title">' + title + '</span> \
					<span class="runtime">(' + time + ') \
					<object width="' + width +'" height="' + height + '"> \
						<param name="movie" value="http://www.youtube.com/v/' + id + '" /> \
						<embed src="http://www.youtube.com/v/' + id + '" type="application/x-shockwave-flash" width="' + width + 'px" height="' + height + 'px" /> \
					</object> \
			';
			document.location.hash = '#tubepress_video';
			break;
	}
}