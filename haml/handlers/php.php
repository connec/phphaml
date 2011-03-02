<?php

/**
 * php.php
 */

namespace phphaml\haml\handlers;

use
  \phphaml\Handler,
  \phphaml\haml\nodes;

/**
 * The Php handler handles arbitrary PHP code.
 */
class Php extends Handler {
  
  /**
	 * The start-of-line trigger for this handler.
	 * 
	 * Note: line handling is ordered by the length of the trigger.
	 * Note: the catch-all trigger '*' is treated specially, and only one should be defined per
	 * parser (where more than one is defined, which one is chosen is undefined).
	 */
	protected static $trigger = '-';
	
	/**
	 * An array of PHP's control structures.
	 */
	protected static $control_structures = array(
	  'if', 'else', 'elseif', 'else if', 'while', 'do', 'for', 'foreach',
	  'switch', 'case'
	);
	
	/**
	 * Handles the current line in the given parser.
	 */
	public static function handle() {
	  
	  $node = nodes\Php::new_from_parser(static::$parser);
	  
	  static::parse($node);
	  
	}
	
	/**
	 * Parses the line's contents.
	 */
	public static function parse(nodes\Php $node) {
	  
	  $node->content = trim(substr($node->content, 1));
	  
	  foreach(static::$control_structures as $control_structure) {
	    if(substr($node->content, 0, strlen($control_structure)) == $control_structure)
	      throw new \Exception('not implemented');
	  }
	  
	}
  
}

?>