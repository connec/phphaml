<?php

/**
 * filter.php
 */

namespace phphaml\haml\handlers;

use
	\phphaml\Handler,
	\phphaml\Library,
	\phphaml\haml\InterpolatedString,
	\phphaml\haml\nodes;

/**
 * The Filter handler handles filtered blocks.
 */

class Filter extends Handler {
	
	/**
	 * The start-of-line trigger for this handler.
	 * 
	 * Note: line handling is ordered by the length of the trigger.
	 * Note: the catch-all trigger '*' is treated specially, and only one should be defined per
	 * parser (where more than one is defined, which one is chosen is undefined).
	 */
	protected static $trigger = array(':');
	
	/**
	 * The namespace filters reside in.
	 * 
	 * By default this will be the filter subnamespace of this namespace.
	 */
	protected static $filter_namespace;
	
	/**
	 * Represents the indent level this filter began on.
	 */
	protected static $indent_level = false;
	
	/**
	 * Handles the current line in the given parser.
	 * 
	 * This is used instead of the parser appending to the tree itself in order to deal with
	 * potential multiline statements.
	 */
	public static function handle() {
		
		if(!static::$filter_namespace) {
			static::$filter_namespace =
				  Library::namespace_from_class(get_class(static::$parser))
				. '\\filters';
		}
		
		if(static::$parser->context_locked() === false) {
			$node = nodes\Filter::new_from_parser(static::$parser);
			
			$node->filter = 
				  static::$filter_namespace . '\\'
				. Library::class_name_from_file_name(substr($node->content, 1) . '.php');
			
			if(!class_exists($node->filter))
				$node->exception("Load Error: could not load filter $node->content");
			
			$node->content = array();
			
			static::$indent_level = static::$parser->indent_level();
			static::$parser->lock_context();
		} else {
			$indent_level = static::$parser->indent_level() - static::$indent_level - 1;
			$indent = str_repeat(static::$parser->indent_string(), $indent_level < 0 ? 0 : $indent_level);
			
			static::$parser->context()->last_child()->content[] = $indent . static::$parser->content();
		}
		
		static::$parser->force_handler(get_called_class());
		
	}
	
	/**
	 * Resets any static properties once parsing is complete.
	 */
	public static function reset() {
	  
	  static::$indent_level = false;
	  parent::reset();
	  
	}
	
}

?>