<?php

/**
 * test_handler.php
 */

namespace phphaml\haml\handlers;

use
	\phphaml\haml\InterpolatedString,
	\phphaml\haml\Parser;

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
	 * Indicates whether this line's contents should be escaped.
	 */
	protected $escape;
	
	/**
	 * Indicates whether this line's contents should be whitespace preserved.
	 */
	protected $preserve;
	
	/**
	 * Initialises the node.
	 */
	public function __construct(Parser $parser, TagHandler $parent = null) {
		
		if($parent) {
			$this->parser = $parser;
			$this->parent = $parent;
			$this->render_newline = false;
			$this->content = $parent->content;
			$this->parse();
		} else
			parent::__construct($parser);
		
	}
	
	/**
	 * Parses the content of this node.
	 */
	public function parse() {
		
		if(preg_match('/^(?:(?:!|&) |(?:!|&|)(?:=|~))/', $this->content)) {
			if($this->content[0] == '&' or $this->content[0] == '!') {
				$this->escape = $this->content[0] == '&';
				$this->content = substr($this->content, 1);
			}
			
			if($this->content[0] == '~')
				$this->preserve = true;
			
			if($this->content[0] == '=' or $this->content[0] == '~')
				$this->content = '#{' . trim(substr($this->content, 1)) . '}';
		}
		
		if($this->escape === null)
			$this->escape = $this->parser->option('escape_html');
		
		$this->content = new InterpolatedString($this->content, $this);
		
		$this->parser->expect_indent(Parser::EXPECT_LESS | Parser::EXPECT_SAME);
		
	}
	
	/**
	 * Renders the parsed tree.
	 */
	public function _render() {
		
		$indent = str_repeat($this->parser->indent_string(), $this->indent_level);
		$this->content = $indent . (string)$this->content;
		
		if($this->escape)
			$this->content = htmlentities($this->content);
		
		if($this->preserve)
			$this->preserve();
		
		$this->content = str_replace("\n", "\n$indent", $this->content);
		
		return $this->content;
		
	}
	
	/**
	 * Returns the string representation (render) of this text node.
	 */
	public function __toString() {
		
		return $this->render();
		
	}
	
	/**
	 * Replaces linebreaks in preserved tags with "&#x000A;".
	 */
	protected function preserve() {
		
		$re = '/<(' . implode('|', $this->parser->option('preserve')) . ")>.*?\n.*?<\/\\1>/i";
		
		while(preg_match($re, $this->content, $match, PREG_OFFSET_CAPTURE)) {
			$this->content = substr_replace(
				$this->content,
				str_replace("\n", '&#x000A;', $match[0][0]),
				$match[0][1],
				strlen($match[0][0])
			);
		}
		
	}
	
}

?>