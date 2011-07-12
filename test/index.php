<?php

namespace phphaml\test;

?><html>
	<head>
		<style type="text/css">
		    body {
		        font-family: 'Lucida Sans Unicode';
		        font-size: 10pt;
		    }
		    
		    pre {
		        margin: 0;
		    }
		    
			.heading {
				background-color: #999;
				text-align: center;
				font-size: 2em;
			}
			
			.heading .success, .heading .failure {
				background-color: transparent;
			}
			
			.heading .success {
				color: #9f9;
			}
			
			.heading .failure {
				color: #f99;
			}
			
			h4 {
				margin: 0;
				margin-top: 5px;
				padding: 5px;
				cursor: pointer;
				font-weight: normal;
			}
			
			h4:first-child {
				margin: 0;
			}
			
			pre.error {
				margin: 0;
				padding: 5px;
			}
			
			.container, pre.error {
				padding-bottom: 5px;
				font-size: 0.9em;
			}
			
			div.success {
				display: none;
			}
			
			div.failure {
				display: block;
			}
			
			.left, .right {
				float: left;
				width: 40%;
				margin: 0 5px;
				padding: 5px;
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
<?php include 'test.php' ?>
	</body>
</html>