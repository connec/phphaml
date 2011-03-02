<?php

/**
 * text.php
 */

namespace phphaml\haml\handlers;

use
	\phphaml\Handler,
	\phphaml\haml\nodes,
	\phphaml\haml\Parser,
	\phphaml\haml\ruby;

/**
 * The Text handler handles text lines in a HAML source.
 */

class Text extends Handler {
	
	/**
	 * The start-of-line trigger for this handler.
	 * 
	 * Note: line handling is ordered by the length of the trigger.
	 * Note: the catch-all trigger '*' is treated specially, and only one should be defined per
	 * parser (where more than one is defined, which one is chosen is undefined).
	 */
	protected static $trigger = '*';
	
	/**
	 * Handles the current line in the parser.
	 */
	public static function handle() {
		
		$node = nodes\Text::new_from_parser(static::$parser);
		
		static::parse($node);
		
		static::$parser->expect_indent(Parser::EXPECT_LESS | Parser::EXPECT_SAME);
		
	}
	
	/**
	 * Parses the content of the node.
	 */
	public static function parse(nodes\Text $node) {
		
		if(preg_match('/^(?:(?:!|&) |(?:!|&|)(?:=|~))/', $node->content)) {
			if($node->content[0] == '&' or $node->content[0] == '!') {
				$node->escape = $node->content[0] == '&';
				$node->content = substr($node->content, 1);
			}
			
			if($node->content[0] == '~')
				$node->preserve = true;
			
			if($node->content[0] == '=' or $node->content[0] == '~')
				$node->content = '#{' . trim(substr($node->content, 1)) . '}';
		}
		
		if($node->escape === null)
			$node->escape = static::$parser->option('escape_html');
		
	}
	
}

?>