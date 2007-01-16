function playVideo(id, height, width, title, time, location, url) {
	switch (location) {
		case "popup":
			var newurl = url + "/wp-content/plugins/tubepress/tubepress_popup.php?name=" + title + "&id=" + id + "&w=" + width + "&h=" + height;
			window.open(newurl, "newwin", "width=" + width + ",height=" + height + ",toolbar=false,locationbar=false,directories=false,status=false,menubar=false,scrollbars=false,resizable=true,copyhistory=false");
			break;
		case "youtube":
			document.location.href = "http://youtube.com/watch?v=" + id;
			break;
		case "new_window":
			var url = document.location.href;
			var endLocation = url.indexOf("tubepress_id=", 0);
			if (endLocation != -1) {
				url = url.substr(0, endLocation -1);
			}
			endLocation = url.indexOf("#", 0);
			if (endLocation != -1) {
				url = url.substr(0, endLocation);
			}
			var firstChar = '?';
			if (url.indexOf(".php?") != -1)
				firstChar = '&';
			url += firstChar + "tubepress_id=" + id;
			document.location.replace(url);
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
function backToGallery() {
	var url = document.location.href;
	var endLocation = url.indexOf("tubepress_id=", 0);
	if (endLocation != -1) {
		url = url.substr(0, endLocation -1);
	}
	document.location.replace(url);
}