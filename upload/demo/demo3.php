<?php require dirname(dirname(__FILE__)) . '/callback/inc/Security.php'; ?>

<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Call Back Demo 3 - Plain</title>
</head>
<body>

	<ul>
		<li><a href="index.php">PHP Demo</a></li>
		<li><a href="demo2.html">HTML Demo</a></li>
		<li><a href="demo3.php">Plain Form Demo</a></li>

	</ul>
	<h3>Call Back example page 3 - Skeleton</h3>

	<!-- Hidden message boxes -->
	<div id="success" style="display: none;" class="alert-success"></div>

	<div id="error" style="display: none;" class="alert-danger">
		<p>The following errors occured</p>
		<ul class="error_list"></ul>
	</div>

	<form name="" id="callback_form" class="form-horizontal" role="form" action="../callback/" method="post">

		<label for="inputName">Name</label>
		<input type="text" name="name" id="name" placeholder="Your Name">
		<br />

		<label for="inputNumber">Number</label>
		<input type="text" name="number" id="number" placeholder="Phone Number">
		<br />

		<label for="inputTime">Time</label>
		<input type="text" name="time" id="time" placeholder="Call me back at">
		<br />

		<label for="inputCaptcha"></label>
		<!-- Display human readable captcha -->
		<span><?php print Callback_Security::generateCaptchaQuestion(); ?></span>
		<input type="text" id="inputCaptcha" placeholder="Answer" name="captcha" value="" />
		<br />

		<!-- Hidden bot protection field -->
		<input type="text" name="bot" id="bot" style="display: none;"  />

		<!-- Hidden CSRF field to prevent external usage -->
		<input type="hidden" name="csrf" value="<?php print Callback_Security::generateCsrf(); ?>" />

		<button type="submit" id="submit">Submit</button>

	</form>

<script src="js/jquery-1.10.2.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/callback.ajax.js"></script>
</body>
</html>