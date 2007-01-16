<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
	<head>
		<title><?php echo $_GET['name']; ?></title>	
	</head>
	<body style="margin: 0pt 0pt">
		<object type="application/x-shockwave-flash" style="width:<?php echo $_GET['w']; ?>px; height:<?php echo $_GET['h'];?>px;" data="http://www.youtube.com/v/<?php echo $_GET['id']; ?>" >
				<param name="movie" value="http://www.youtube.com/v/<?php echo $_GET['id']; ?>" />
			</object>
	</body>
</html>