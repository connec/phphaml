<?php

/**
 * php.php
 */

namespace phphaml\haml\handlers;

use
  \phphaml\Handler,
  \phphaml\Parser,
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
	      $node->control_structure = $control_structure;
	  }
	  
	  if(!$node->control_structure)
	    return static::$parser->expect_indent(Parser::EXPECT_LESS | Parser::EXPECT_SAME);
    
    if($node->control_structure == 'else if') {
      $node->content = substr_replace($node->content, 'elseif', 0, 7);
      $node->control_structure = 'elseif';
    }
    
    if($node->control_structure == 'elseif' or $node->control_structure == 'else') {
      $previous = $node->previous_sibling();
      if(
        $previous and get_class($previous) == get_class($node) and
        ($previous->control_structure == 'if' or 
         $previous->control_structure == 'elseif' or
         ($previous->control_structure == 'foreach' and
          $node->control_structure == 'else'
         )
        )
      );
      else {
        $node->exception('Invalid location for ' . $node->control_structure
          . ' statement, must come after if/elseif');
      }
    }
    
    switch($node->control_structure) {
      case 'if':
      case 'elseif':
      case 'else if':
      case 'while':
      case 'for':
      case 'foreach':
      case 'switch':
        $re = '/^' . $node->control_structure . '(?:\s*\(|\s+)\s*/';
        if(!preg_match($re, $node->content, $match))
          $node->exception('Invalid syntax for ' . $node->control_structure . ' statement');
        $node->content = substr($node->content, strlen($match[0]));
        
        if(strpos($match[0], '(') !== false) {
          if(substr($node->content, -1) != ')')
            $node->exception('Invalid syntax for ' . $node->control_structure . ' statement');
          $node->content = substr($node->content, 0, -1);
        }
      break;
      
      case 'do':
      case 'else':
        $re = '/^' . $node->control_structure . '$/';
        if(!preg_match($re, $node->content, $match))
          $node->exception('Invalid syntax for ' . $node->control_structure . ' statement');
        $node->content = '';
      break;
      
      case 'case':
        $re = '/^' . $node->control_structure . '\s*:?\s*/';
        if(!preg_match($re, $node->content, $match))
          $node->exception('Invalid syntax for ' . $node->control_structure . ' statement');
        $node->content = substr($node->content, strlen($match[0]));
      break;
    }
	  
	}
  
}

?>