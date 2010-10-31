<?php

use \hamlparser\lib\haml\HamlParser;

require '../lib.php';
require 'diff.php';

\hamlparser\Lib::autoload();

$tpl_container = <<< 'END'
<h4 class="test_heading %1$s" onclick="toggle(test_%2$d);">%3$s (%4$s)</h4>
<div class="container %1$s" id="test_%2$d">
%5$s
%6$s
</div>
END;

$tpl_test = <<< 'END'
<div class="left"><pre>
%1$s
</pre></div>
<div class="right"><pre>
%2$s
</pre></div>
<div style="clear: both"></div>
END;

$tests = array_unique(array_map(
	function($v) { return substr($v, 0, -5); },
	array_filter(
		scandir('references'),
		function($v) { return $v[0] != '.'; }
	)
));

natsort($tests);

foreach($tests as $i => $test) {
	
	$parser = new HamlParser;
	$error = '';
	try {
		$parser->parse("references/$test.haml");
	} catch(Exception $e) {
		$error = '<pre class="error">'.$e.'</pre>';
	}
	
	$input = htmlentities(str_replace("\r", '', file_get_contents("references/$test.haml")));
	$output = trim($parser->result());
	$expected = trim(str_replace("\r", '', file_get_contents("references/$test.html")));
	
	if($output == $expected)
		$success = true;
	else
		$success = false;
	
	$test_result = sprintf($tpl_test, $input, diff($expected, $output));
	printf($tpl_container, $success ? 'success' : 'failure', $i, $test, $success ? 'Success' : 'Failure', $error, $test_result);
	
}

?>