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
	 * Generates the result of the tree from this node.
	 */
	public function __toString() {
		
		return str_repeat($this->parser->indent(), $this->indent_level) . trim($this->line) . "\n";
		
	}
	
}

?>