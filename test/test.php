<?php

namespace phphaml\test;

use
    \Exception,
    \StdClass,
    \phphaml\haml\Parser;

require '../library.php';
require 'diff.php';

\phphaml\Library::autoload();

$options = array(
	'4' => array(
		'format' => 'xhtml'
	)
);

$tpl_header = <<< 'END'
<div class="heading">
<span class="success">%1$d</span>
/
<span class="failure">%2$d</span>
(
<span class="percent">%3$d&#37;</span>
)
</div>
END;

$tpl_container = <<< 'END'
<h4 class="test_heading %1$s" onclick="toggle(test_%2$d);">%2$d) %3$s (%4$s) %5$s</h4>
<div class="container %1$s" id="test_%2$d">
%6$s
%7$s
</div>
END;

$tpl_test = <<< 'END'
<div class="left">
<pre>%1$s</pre>
</div>
<div class="right">
<pre>%2$s</pre>
</div>
<div style="clear: both"></div>
END;

$contexts = json_decode(file_get_contents('references/tests.json'));
$total = 0;
$passed = 0;
$failed = 0;

ob_start();

foreach($contexts as $context) {
	foreach($context as $name => $test) {
		
		$total ++;
		
		if(!isset($test->locals))
			$test->locals = new StdClass;
		if(!isset($test->config))
			$test->config = new StdClass;
		
		if(!isset($test->config->format))
			$test->config->format = 'xhtml';
		
		$parser = new Parser($test->haml, (array)$test->locals, (array)$test->config);
		
		$error = '';
		try {
			$time = microtime(true);
			$output = $parser->render();
			$time = microtime(true) - $time;
		} catch(Exception $e) {
			$time = 0;
			$output = '';
			$error = '<pre class="error">'.$e.'</pre>';
		}
		
		$time = number_format($time, 3);
		$input = htmlentities(str_replace("\r", '', $test->haml));
		$expected = trim(str_replace("\r", '', $test->html));
		
		if(!$error and $output == $expected) {
			$success = true;
			$passed ++;
		} else {
			$success = false;
			$failed ++;
		}
		
		$test_result = sprintf($tpl_test, $input, diff($expected, $output));
		printf($tpl_container, $success ? 'success' : 'failure', $total, $name, $success ? 'Success' : 'Failure', $time, $error, $test_result);
		
	}
}

$percent = (int)round($passed*100 / $total, 0);

$output = ob_get_clean();
printf($tpl_header, $passed, $total, $percent);
echo $output;

?>