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
	 * Indicates whether this line's content should be escaped.
	 */
	protected $escape;
	
	/**
	 * Parses the content of this node.
	 */
	public function parse() {
		
		if(preg_match('/^(?:(?:!|&) |(?:!|&|)=)/', $this->content)) {
			if($this->content[0] == '&' or $this->content[0] == '!') {
				$this->escape = $this->content[0] == '&';
				$this->content = substr($this->content, 1);
			}
			
			if($this->content[0] == '=')
				$this->content = '#{' . trim(substr($this->content, 1)) . '}';
		}
		
		if($this->escape === null)
			$this->escape = $this->parser->option('escape_html');
		
		$this->parser->expect_indent(Parser::EXPECT_LESS | Parser::EXPECT_SAME);
		
	}
	
	/**
	 * Renders the parsed tree.
	 */
	public function _render() {
		
		$indent = str_repeat($this->parser->indent_string(), $this->indent_level);
		
		if($this->escape)
			$this->content = htmlentities($this->content);
		
		$this->content = new InterpolatedString($this->content, $this);
		
		return $indent . $this->content;
		
	}
	
}

?>