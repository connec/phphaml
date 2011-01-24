<?php

/**
 * value.php
 */

namespace phphaml\haml;

use
	\phphaml\Node,
	\phphaml\StringStream;

/**
 * The Value class represents an arbitrary value in a HAML parse tree.
 */

class Value {
	
	/**
	 * The variables to use when rendering this value.
	 */
	protected static $variables = array();
	
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
	 * Instantiates the Value and determines its type and content.
	 */
	public function __construct($value, Node $node) {
		
		if($value[0] == '\'') {
			if($value[strlen($value) - 1] != '\'')
				$node->exception('Parse error: missing closing quote (\')');
			
			if(preg_match('/(^|[^\\\\]|[\\\\][\\\\])\'/', $this->content = substr($value, 1, -1)))
				$node->exception('Parse error: unescaped quote (\')');
		} elseif($value[0] == '"') {
			if($value[strlen($value) - 1] != '"')
				$node->exception('Parse error: missing closing quote (")');
			
			if(preg_match('/(^|[^\\\\]|[\\\\][\\\\])"/', $this->content = substr($value, 1, -1)))
				$node->exception('Parse error: unescaped quote (")');
			
			$this->parse_interpolated_string(substr($value, 1, -1));
		} else
			$this->content = '<?php echo(' . $value . '); ?>';
		
	}
	
	/**
	 * Generates a string with interpolations switched for "<?php echo" statements.
	 */
	protected function parse_interpolated_string($content) {
		
		$re_find = '/(^|[^\\\\]|[\\\\][\\\\])#\{(.*?)\}/';
		$replace = '$1<?php echo($2); ?>';
		$this->content = preg_replace($re_find, $replace, $content);
		
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
		
		StringStream::set('haml/value', $this->content);
		extract(static::$variables);
		
		ob_start();
		include 'string://haml/value';
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