<!DOCTYPE HTML>
<html manifest="">
<style>
	body {
		background: #FFF url("https://i.imgur.com/KheAuef.png") top left repeat-x;
		font-family: 'Alex Brush', cursive !important;
	}

	.page    { display: none; padding: 0 0.5em; }
	.page h1 { font-size: 2em; line-height: 1em; margin-top: 1.1em; font-weight: bold; }
	.page p  { font-size: 1.5em; line-height: 1.275em; margin-top: 0.15em; }

	#loading {
		display: block;
		position: absolute;
		top: 0;
		left: 0;
		z-index: 100;
		width: 100vw;
		height: 100vh;
		background-color: rgba(192, 192, 192, 0.5);
		background-image: url("https://i.stack.imgur.com/MnyxU.gif");
		background-repeat: no-repeat;
		background-position: center;
	}
</style>
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta charset="UTF-8">

    <title>LabMed</title>
    <script type="text/javascript" src="application/frontend/resources/locale/locale.js"></script>
    <!-- The line below must be kept intact for Sencha Cmd to build your application -->
    <script id="microloader" type="text/javascript" src="application/frontend/bootstrap.js"></script>
	<script type="text/javascript">
		function onReady(callback) {
			var intervalId = window.setInterval(function() {
				if (document.getElementsByTagName('body')[0] !== undefined) {
					window.clearInterval(intervalId);
					callback.call(this);
				}
			}, 1000);
		}

		function setVisible(selector, visible) {
			document.querySelector(selector).style.display = visible ? 'block' : 'none';
		}

		onReady(function() {
			console.log('onReady');
			setVisible('.page', true);
			setVisible('#loading', false);
		});
	</script>
</head>
<body>
<link href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css?family=Alex+Brush" rel="stylesheet">
<div class="page">
	<h1>The standard Lorem Ipsum passage</h1>
	<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure
		dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
</div>
<div id="loading"></div>
</body>
</html>
