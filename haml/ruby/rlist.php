<?php

/**
 * list.php
 */

namespace phphaml\haml\ruby;

use
	\phphaml\Exception,
	\phphaml\Node,
	\phphaml\haml\ruby;

/**
 * The List class provides the base for RubyHash and RubyArray classes
 * to extend from.
 */

abstract class RList {
	
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
	 * The node that created this RubyList.
	 */
	protected $node;
	
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
	public function __construct($str, Node $node) {
		
		$this->node = $node;
		
		$str = trim($str);
		
		if($str[0] != static::$open) {
			$this->node->exception(
				'Syntax error: unexpected ":char", :type must start with :open',
				array('char' => $str[0], 'type' => static::$type, 'open' => static::$open)
			);
		}
		if($str[strlen($str) - 1] != static::$close) {
			$this->node->exception(
				'Syntax error: unexpected ":char", :type must close with :close',
				array('char'  => $str[strlen($str) - 1], 'type'  => static::$type, 'close' => static::$close)
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
		
		// In general, we want to walk up to "," and parse that section as an entry in the list.
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
			array('[', ']'),
			array('(', ')')
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
		
		$this->node->exception('Syntax error: :type nesting error', array('type' => static::$type));
		
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
				$value = new ruby\RArray($value, $this->node);
				$value = $value->to_a();
			break;
			case '{':
				$value = new ruby\Hash($value, $this->node);
				$value = $value->to_a();
			break;
			case '"':
			  $value = ruby\InterpolatedString::compile($value);
		  break;
		  case ':':
		    $value = var_export(substr($value, 1), true);
	    break;
		}
		
		return $value;
		
	}
	
}

?>