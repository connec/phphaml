<?php

/**
 * haml_exception.php
 */

namespace phphaml;

/**
 * The base exception class for the haml library.
 */

class Exception extends \Exception {
	
	/**
	 * An array of predefined messages.
	 * 
	 * @var array
	 */
	protected static $messages = array();
	
	/**
	 * The original, non-substituted message.
	 */
	protected $original_message;
	
	/**
	 * An array of variables to substitute into the message on string conversion.
	 */
	protected $variables = array();
	
	/**
	 * Sets the message.
	 * 
	 * @param string $message
	 * @param array  $vars
	 * @return void
	 */
	public function __construct($message = '', $vars = array()) {
		
		if(isset(static::$messages[$message]))
			$message = static::$messages[$message];
		
		$this->original_message = $message;
		$this->variables = $vars;
		
		$this->substitute();
		
	}
	
	/**
	 * Substitutes set variables into the original message.
	 * 
	 * @return void
	 */
	protected function substitute() {
		
		$this->message = str_replace(
			array_map(function($v) { return ':' . $v; }, array_keys($this->variables)),
			array_values($this->variables),
			$this->original_message
		);
		
	}
	
	/**
	 * Sets a variable and resubstitutes into the original message.
	 * 
	 * @param string $var
	 * @param mixed  $val
	 * @return void
	 */
	public function set($var, $val) {
		
		$this->variables[$var] = $val;
		$this->substitute();
		
	}
	
}

?>