<?php

/**
 * haml_exception.php
 */

namespace hamlparser\lib;

/**
 * The base exception class for the haml_parser library.
 */

class Exception extends \Exception {
	
	/**
	 * An array of predefined messages.
	 * 
	 * @var array
	 */
	protected static $messages = array();
	
	/**
	 * Sets the message.
	 * 
	 * @param string $message
	 * @param array  $vars
	 * @return void
	 */
	public function __construct($message, $vars = array()) {
		
		if(isset(static::$messages[$message]))
			$message = static::$messages[$message];
		
		$this->message = str_replace(
			array_map(function($k) { return ':' . $k; }, array_keys($vars)),
			array_values($vars),
			$message
		);
		
	}
	
}

?>