<?php

/**
 * ruby_hash.php
 */

namespace hamlparser\lib;

/**
 * The RubyHash class parses a ruby hash (as a string).
 */

class RubyHash extends RubyList {
	
	/**
	 * The type of this list.
	 */
	protected static $type = 'hash';
	
	/**
	 * The opening delimeter of this list type.
	 */
	protected static $open = '{';
	
	/**
	 * The closing delimeter of this list type.
	 */
	protected static $close = '}';
	
	/**
	 * Handles the captured entry,
	 */
	protected function handle_entry($entry) {
		
		// Split the entry into key/value.
		$entry = array_map('trim', explode('=>', $entry, 2));
		if(count($entry) < 2) {
			throw new Exception(
				'Invalid syntax: expected "=>" in hash entry'
			);
		}
		list($key, $value) = $entry;
		
		$this->parsed[RubyValue::value_to_string($key)] = $this->handle_value($value);
		
	}
	
}

?>