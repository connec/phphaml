<?php

/**
 * array.php
 */

namespace phphaml\haml\ruby;

use \phphaml\haml\ruby;

/**
 * The List class parses a ruby list (as a string).
 */

class RArray extends ruby\RList {
	
	/**
	 * The type of this list.
	 */
	protected static $type = 'array';
	
	/**
	 * The opening delimeter of this list type.
	 */
	protected static $open = '[';
	
	/**
	 * The closing delimeter of this list type.
	 */
	protected static $close = ']';
	
	/**
	 * Handles the captured entry.
	 */
	protected function handle_entry($entry) {
		
		$this->parsed[] = $this->handle_value($entry);
		
	}
	
}

?>