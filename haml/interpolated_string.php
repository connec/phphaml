<?php

/**
 * interpolated_string.php
 */

namespace phphaml\haml;

/**
 * The InterpolatedString class handles parsing of ruby-style interpolated strings.
 */

class InterpolatedString {
	
	/**
	 * The content of the value.
	 */
	protected $content = array();
	
	/**
	 * Fixes backslashes.
	 */
	protected static function fix_slashes($string) {
		
		return str_replace(array('\#', '\\\\'), array('#', '\\'), $string);
		
	}
	
	/**
	 * Instantiates the interpolated string and parses it.
	 */
	public function __construct($content) {
		
		while(preg_match('/(?:^|[^\\\\]|[\\\\][\\\\])(#\{)(.*?)\}/', $content, $matches, PREG_OFFSET_CAPTURE)) {
			if($s = substr($content, 0, $matches[1][1]))
				$this->content[] = static::fix_slashes($s);
			
			$this->content[] = new EvalString($matches[2][0]);
			
			$content = substr($content, $matches[2][1] + strlen($matches[2][0]) + 1);
		}
		$this->content[] = static::fix_slashes($content);
		
	}
	
	/**
	 * Returns the result of rendering the string with the assigned variables.
	 */
	public function result() {
		
		$result = '';
		
		foreach($this->content as $content)
			$result .= (string) $content;
		
		return $result;
		
	}
	
	/**
	 * Returns the result of rendering the string with the assigned variables.
	 */
	public function __toString() {
		
		return $this->result();
		
	}
	
}

?>