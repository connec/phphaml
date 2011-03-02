<?php

/**
 * interpolated_string.php
 */

namespace phphaml\haml\ruby;

/**
 * The InterpolatedString class handles parsing of ruby-style interpolated strings.
 */

class InterpolatedString {
	
	/**
	 * Fixes backslashes.
	 */
	protected static function fix_slashes($string) {
		
		return str_replace(array('\#', '\\\\'), array('#', '\\'), $string);
		
	}
	
	/**
	 * Compiles an evaluateable representation of the content.
	 */
	public static function compile($content) {
		
	  if($content[0] == '"')
	    $content = substr($content, 1, -1);
	  
	  $compiled = '';
		while(preg_match('/(?:^|[^\\\\]|[\\\\][\\\\])(#\{)(.*?)\}/', $content, $matches, PREG_OFFSET_CAPTURE)) {
			if($s = substr($content, 0, $matches[1][1]))
				$compiled .= var_export(static::fix_slashes($s), true) . '.';
			
			$compiled .= $matches[2][0] . '.';
			
			$content = substr($content, $matches[2][1] + strlen($matches[2][0]) + 1);
		}
		return $compiled . var_export(static::fix_slashes($content), true);
		
	}
	
}

?>