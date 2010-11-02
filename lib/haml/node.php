<?php

/**
 * node.php
 */

namespace hamlparser\lib\haml;

/**
 * The Node class handles the tree representation and parsing of HAML elements.
 */

class Node extends \hamlparser\lib\Node {
	
	/**
	 * Creates a new node and appends it to this node's children.
	 */
	public function add_child($parser) {
		
		// Ignore empty lines.
		if(!$this->parser->line())
			return;
		
		// Decide which node to generate based on the first character.
		switch(substr($parser->line(), 0, 1)) {
			case '%':
			case '.':
			case '#':
				$this->children[] = new TagNode($parser, $this);
			break;
			default:
				$this->children[] = new TextNode($parser, $this);
				$parser->expect(Parser::INDENT_LESS | Parser::INDENT_SAME);
		}
		
	}
	
	/**
	 * Parses the tree from this node.
	 */
	public function parse() {
		
		foreach($this->children as $child)
			$child->parse();
		
	}
	
	/**
	 * Generates the result of the tree from this node.
	 */
	public function __toString() {
		
		$return = '';
		foreach($this->children as $child)
			$return .= (string)$child;
		return $return;
		
	}
	
}

?>