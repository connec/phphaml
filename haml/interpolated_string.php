<?php

/**
 * interpolated_string.php
 */

namespace phphaml\haml;

use
	\phphaml\Node,
	\phphaml\StringStream;

/**
 * The InterpolatedString class handles parsing of ruby-style interpolated strings.
 */

class InterpolatedString {
	
	/**
	 * The variables to use when rendering this value.
	 */
	protected static $variables = array();
	
	/**
	 * The Node that instantiated this object.
	 */
	protected $node;
	
	/**
	 * The content of the value.
	 */
	protected $content;
	
	/**
	 * Sets the variables to use when rendering.
	 */
	public static function variables(array $variables) {
		
		static::$variables = $variables;
		
	}
	
	/**
	 * Instantiates the interpolated string and parses it.
	 */
	public function __construct($string, Node $node) {
		
		$this->node = $node;
		
		$re_find = array(
			'/(^|[^\\\\]|[\\\\][\\\\])#\{(.*?)\}/',
			'/(^|[^\\\\]|[\\\\][\\\\])\\\#/',
			'/(^|[^\\\\]|[\\\\][\\\\])[\\\\][\\\\]/'
		);
		$replace = array('$1<?php echo($2); ?>', '$1#', '$1\\');
		$this->content = preg_replace($re_find, $replace, $string);
		
	}
	
	/**
	 * Returns the PHP string to generate the value's result.
	 */
	public function get_php() {
		
		return $this->content;
		
	}
	
	/**
	 * Returns the result of rendering the string with the assigned variables.
	 */
	public function get_text() {
		
		StringStream::set('interpolatedstring', $this->content);
		extract(static::$variables);
		
		ob_start();
		include 'string://interpolatedstring';
		return ob_get_clean();
		
	}
	
	/**
	 * Returns the result of rendering the string with the assigned variables.
	 */
	public function __toString() {
		
		return $this->get_text();
		
	}
	
}

?>