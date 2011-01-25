<?php

/**
 * test_handler.php
 */

namespace phphaml\haml;

/**
 * The TextHandler class handles text lines in a HAML source.
 */

class TextHandler extends LineHandler {
	
	/**
	 * The start-of-line trigger for this handler.
	 * 
	 * Note: line handling is ordered by the length of the trigger.
	 * Note: the catch-all trigger '*' is treated specially, and only one should be defined per
	 * parser (where more than one is defined, which one is chosen is undefined).
	 */
	protected static $trigger = '*';
	
	/**
	 * Parses the content of this node.
	 */
	public function parse() {
		
		$this->parser->expect_indent(Parser::EXPECT_LESS | Parser::EXPECT_SAME);
		
	}
	
	/**
	 * Renders the parsed tree.
	 */
	public function render() {
		
		$indent = str_repeat($this->parser->indent_string(), $this->indent_level);
		
		$this->content = new InterpolatedString($this->content, $this);
		
		return $indent . $this->content;
		
	}
	
}

?>