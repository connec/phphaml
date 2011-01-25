<?php

/**
 * filter_handler.php
 */

namespace phphaml\haml;

/**
 * The FilterHandler handles filtered content.
 */

class FilterHandler extends LineHandler {
	
	/**
	 * The start-of-line trigger for this handler.
	 * 
	 * Note: line handling is ordered by the length of the trigger.
	 * Note: the catch-all trigger '*' is treated specially, and only one should be defined per
	 * parser (where more than one is defined, which one is chosen is undefined).
	 */
	protected static $trigger = array(':');
	
	/**
	 * Parses the content of this node.
	 * 
	 * Naturally, for HAML comments this does nothing.
	 */
	public function parse() {
		
		
		
	}
	
	/**
	 * Renders the parsed tree.
	 * 
	 * Naturally, for HAML comments this returns an empty string.
	 */
	public function render() {
		
		
		
	}
	
}

?>