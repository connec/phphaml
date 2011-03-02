<?php

/**
 * hash.php
 */

namespace phphaml\haml\ruby;

use \phphaml\haml\ruby;

/**
 * The Hash class parses a ruby hash (as a string).
 */

class Hash extends ruby\RList {
	
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
		if(count($entry) < 2)
			$this->node->exception('Invalid syntax: expected "=>" in hash entry');
		list($key, $value) = $entry;
		
		$this->parsed[] = array($this->handle_value($key), $this->handle_value($value));
		
	}
	
}

?>