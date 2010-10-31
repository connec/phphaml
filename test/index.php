<html>
	<head>
		<style type="text/css">
			h4 {
				margin: 0;
				margin-top: 0.5em;
				padding: 0.5em;
				cursor: pointer;
			}
			
			h4:first-child {
				margin: 0;
			}
			
			pre.error {
				margin: 0;
				padding: 1em;
			}
			
			.container, pre.error {
				padding-bottom: 1em;
				font-size: 0.9em;
			}
			
			div.success, div.failure {
				display: none;
			}
			
			.left, .right {
				float: left;
				width: 500px;
				margin: 0 1em;
				padding: 1em;
				background-color: #eee;
				border: 1px solid #666;
			}
			
			.bg-red, .failure {
				background-color: #f99;
			}
			
			.bg-green, .success {
				background-color: #9f9;
			}
			
			.bg-yellow {
				background-color: #ff9;
			}
		</style>
		<script type="text/javascript">
			function $(e) {
				if(typeof(e) == 'string')
					return document.getElementById(e);
				else
					return e;
			}
			
			function show(e) {
				$(e).style.display = 'block';
			}
			
			function hide(e) {
				$(e).style.display = 'none';
			}
			
			function toggle(e) {
				e = $(e);
				if(e.style.display == 'block')
					hide(e);
				else
					show(e);
			}
		</script>
	</head>
	<body>
<? include 'test.php' ?>
	</body>
</html>