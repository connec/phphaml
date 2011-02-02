<?php

/**
 * handler.php
 */

namespace phphaml;

/**
 * The Handler class provides base functionality to child handlers.
 */

class Handler {
	
	/**
	 * The Parser that delegated this handler.
	 */
	protected static $parser;
	
	/**
	 * The start-of-line trigger for this handler.
	 * 
	 * Note: line handling is ordered by the length of the trigger.
	 * Note: the catch-all trigger '*' is treated specially, and only one should be defined per
	 * parser (where more than one is defined, which one is chosen is undefined).
	 */
	protected static $trigger = false;
	
	/**
	 * Accessor for {$trigger}.
	 */
	public static function trigger() {
		
		return static::$trigger;
		
	}
	
	/**
	 * Sets the Parser that's operating.
	 */
	public static function set_parser(Parser $parser) {
		
		static::$parser = $parser;
		
	}
	
	/**
	 * Handles the current line in the given parser.
	 */
	public static function handle() {
		
		throw new Exception('Sanity error: handlers must implement their own handle() methods.');
		
	}
	
	/**
	 * Resets any static properties once parsing is complete.
	 * 
	 * This resolves an edge case where static properties are left non-default when parsing terminates
	 * at EOF.
	 */
	public static function reset() {
		
		static::$parser = null;
		
	}
	
}

?>