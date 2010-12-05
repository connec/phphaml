<?php

namespace phphaml\test;

/**
 * Marks up a diff between $str1 and $str2, with $str1 considered the 'right'
 * version.
 */
function diff($str1, $str2) {
	
	$output = '';
	$lines1 = array_map(function($v) { return htmlentities(rtrim($v)); }, explode("\n", $str1));
	$lines2 = array_map(function($v) { return htmlentities(rtrim($v)); }, explode("\n", $str2));
	
	for($i = 0; $i < count($lines1); $i ++) {
		if(!isset($lines2[$i])) {
			$color = 'red';
		} else {
			$color = $lines1[$i] == $lines2[$i] ? 'green' : 'red';
			$output .= "<div class=\"bg-$color\">$i. {$lines2[$i]}</div>";
		}
		if($color == 'red')
			$output .= "<div class=\"bg-yellow\">$i. {$lines1[$i]}</div>";
	}
	
	for($i; $i < count($lines2); $i ++) {
		$output .= "<div class=\"bg-red\">$i. {$lines2[$i]}</div>";
	}
	return $output;
	
}

?>