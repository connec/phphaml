<?php

/**
 * ruby_list.php
 */

namespace hamlparser\lib;

/**
 * The RubyList class provides the base for RubyHash and RubyArray classes
 * to extend from.
 */

abstract class RubyList {
	
	/**
	 * The type of this list.
	 */
	protected static $type;
	
	/**
	 * The opening delimeter of this list type.
	 */
	protected static $open;
	
	/**
	 * The closing delimeter of this list type.
	 */
	protected static $close;
	
	/**
	 * The parsed content of the list as a PHP array.
	 */
	protected $parsed = array();
	
	/**
	 * Returns the parsed data.
	 */
	public function to_a() {
		
		return $this->parsed;
		
	}
	
	/**
	 * Parses the given string as a ruby list.
	 */
	public function __construct($str) {
		
		$str = trim($str);
		
		if($str[0] != static::$open) {
			throw new Exception(
				'Syntax error: unexpected ":char", :type must start with :open',
				array(
					'char' => $str[0],
					'type' => static::$type,
					'open' => static::$open
				)
			);
		}
		if($str[strlen($str) - 1] != static::$close) {
			throw new Exception(
				'Syntax error: unexpected ":char", :type must close with :close',
				array(
					'char'  => $str[strlen($str) - 1],
					'type'  => static::$type,
					'close' => static::$close
				)
			);
		}
		
		$str = substr($str, 1, -1);
		
		while($str)
			$this->walk($str);
		
	}
	
	/**
	 * Walks the string, parsing it as it goes.
	 */
	protected function walk(&$str) {
		
		// Keep the string trimmed.
		$str = trim($str);
		
		// In general, we want to walk up to "," and parse that section as an entry
		// in the list.
		$entry = $this->capture_balanced($str);
		
		// Handle and store the entry.
		$this->handle_entry($entry);
		
	}
	
	/**
	 * Captures an entry with balanced delimeters.
	 */
	protected function capture_balanced(&$str) {
		
		static $delimeters = array(
			array('{', '}'),
			array('[', ']')
		);
		
		$depth = array();
		for($i = 0; $i < strlen($str); $i ++) {
			if($str[$i] == ',') {
				if(empty($depth)) {
					$capture = trim(substr($str, 0, $i));
					$str = trim(substr($str, $i + 1));
					return $capture;
				}
			}
				
			foreach($delimeters as $j => $delimeter) {
				if($str[$i] == $delimeter[0]) {
					if(!isset($depth[$j]))
						$depth[$j] = 0;
					$depth[$j] ++;
				}
				if($str[$i] == $delimeter[1]) {
					$depth[$j] --;
					if($depth[$j] == 0)
						unset($depth[$j]);
				}
			}
		}
		
		if(empty($depth)) {
			$capture = trim($str);
			$str = '';
			return $capture;
		}
		
		throw new Exception(
			'Syntax error: :type nesting error',
			array('type' => static::$type)
		);
		
	}
	
	/**
	 * Handles the captured entry.
	 */
	abstract protected function handle_entry($entry);
	
	/**
	 * Handles a value.  Assumes $value is trimmed.
	 */
	protected function handle_value($value) {
		
		switch($value[0]) {
			case '[':
				$value = new RubyArray($value);
				$value = $value->to_a();
			break;
			case '{':
				$value = new RubyHash($value);
				$value = $value->to_a();
			break;
			case '$':
				$re = '/^\$[a-zA-Z_][a-zA-Z0-9_]+$/';
				if(!preg_match($re, $value)) {
					throw new Exception(
						'Syntax error: invalid syntax for variable'
					);
				}
				$value = '#{' . $value . '}';
			break;
			default:
				$value = RubyValue::value_to_string($value);
		}
		
		return $value;
		
	}
	
}

?>