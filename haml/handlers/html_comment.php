<?php

/**
 * html_comment.php
 */

namespace phphaml\haml\handlers;

use
	\phphaml\Handler,
	\phphaml\haml\nodes,
	\phphaml\haml\Parser;

/**
 * The HtmlComment handler handles HTML comments in a HAML document.
 */

class HtmlComment extends Handler {
	
	/**
	 * The start-of-line trigger for this handler.
	 * 
	 * Note: line handling is ordered by the length of the trigger.
	 * Note: the catch-all trigger '*' is treated specially, and only one should be defined per
	 * parser (where more than one is defined, which one is chosen is undefined).
	 */
	protected static $trigger = array('/');
	
	/**
	 * Handles the parser's current line.
	 */
	public static function handle() {
		
		$node = nodes\HtmlComment::new_from_parser(static::$parser);
		
		$node->content = trim(substr($node->content, 1));
		
		if($node->content and $node->content[0] == '[')
			$node->conditional = true;
		
		if($node->content and !$node->conditional)
			static::$parser->expect_indent(Parser::EXPECT_SAME | Parser::EXPECT_MORE);
		
	}
	
}

?>