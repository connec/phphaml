<?php

/**
 * haml_comment.php
 */

namespace phphaml\haml\handlers;

use
	\phphaml\Handler,
	\phphaml\haml\nodes;

/**
 * The HamlComment handler handles HAML comments.
 */

class HamlComment extends Handler {
	
	/**
	 * The start-of-line trigger for this handler.
	 * 
	 * Note: line handling is ordered by the length of the trigger.
	 * Note: the catch-all trigger '*' is treated specially, and only one should be defined per
	 * parser (where more than one is defined, which one is chosen is undefined).
	 */
	protected static $trigger = array('-#');
	
	/**
	 * Handles the current line in the given parser.
	 */
	public static function handle() {
		
		static::$parser->lock_context();
		static::$parser->force_handler(get_called_class());
		
	}
	
}

?>