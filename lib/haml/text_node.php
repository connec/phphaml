<?php

/**
 * text_node.php
 */

namespace hamlparser\lib\haml;

/**
 * The TextNode class handles tree representation and parsing of HAML text
 * nodes.
 */

class TextNode extends Node {
	
	/**
	 * Initialises a node with the given parser.
	 */
	public function __construct($parser, $parent = null) {
		
		parent::__construct($parser, $parent);
		
		// Prevent text nodes from having children.
		$this->parser->expect(Parser::INDENT_LESS | Parser::INDENT_SAME);
		
	}
	
	/**
	 * Generates the result of the tree from this node.
	 */
	public function __toString() {
		
		return str_repeat($this->parser->indent(), $this->indent_level) . trim($this->line) . "\n";
		
	}
	
}

?>