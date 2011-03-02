<?php

/**
 * doctype.php
 */

namespace phphaml\haml\handlers;

use
	\phphaml\Handler,
	\phphaml\haml\nodes,
	\phphaml\haml\Parser;

/**
 * The Doctype handler handles DOCTYPE / XML declarations in a HAML source.
 */

class Doctype extends Handler {
	
	/**
	 * The start-of-line trigger for this handler.
	 * 
	 * Note: line handling is ordered by the length of the trigger.
	 * Note: the catch-all trigger '*' is treated specially, and only one should be defined per
	 * parser (where more than one is defined, which one is chosen is undefined).
	 */
	protected static $trigger = '!!!';
	
	/**
	 * Handles the current line in the parser.
	 */
	public static function handle() {
		
	  $node = nodes\Doctype::new_from_parser(static::$parser);
		
		$content = trim(substr($node->content, 3));
		
		if(strtolower(substr($content, 0, 3)) == 'xml') {
		  if(static::$parser->option('format') != 'xhtml')
		    $node->remove();
			elseif(!($encoding = trim(substr($content, 3))))
				$node->encoding = static::$parser->option('encoding');
		} else {
			$node->doctype = $content;
			if(!$node->doctype(null, $node->doctype))
				$node->doctype = 'transitional';
		}
		
		static::$parser->expect_indent(Parser::EXPECT_LESS | Parser::EXPECT_SAME);
		
	}
	
}

?>