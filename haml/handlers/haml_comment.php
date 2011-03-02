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
	 * 
	 * "Haml comments are ignored, so this does nothing." - this would be the ideal scenario, but
	 * context switching in the parser needs a parent node, instead a placebo nodes\HamlComment is
	 * used.
	 */
	public static function handle() {
		
		static::$parser->lock_context();
		static::$parser->force_handler(get_called_class());
		
	}
	
}

?>