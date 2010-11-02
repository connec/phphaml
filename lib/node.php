<?php

/**
 * node.php
 */

namespace hamlparser\lib;

/**
 * The Node class represents a node in the parse tree.
 */

abstract class Node {
	
	/**
	 * The parser that created this node.
	 */
	protected $parser;
	
	/**
	 * The line number that generated this node.
	 */
	protected $line_number;
	
	/**
	 * The line that generated this node.
	 */
	protected $line;
	
	/**
	 * The indent level of this node.
	 */
	protected $indent_level;
	
	/**
	 * The parent of this node.
	 */
	protected $parent;
	
	/**
	 * The children of this node.
	 */
	protected $children = array();
	
	/**
	 * Returns the line number that generated this node.
	 */
	public function line_number() {
		
		return $this->line_number;
		
	}
	
	/**
	 * Returns the line that generated this node.
	 */
	public function line() {
		
		return $this->line;
		
	}
	
	/**
	 * Returns the parent of this node.
	 */
	public function parent() {
		
		return $this->parent;
		
	}
	
	/**
	 * Returns the last child of this node.
	 */
	public function last_child() {
		
		return end($this->children);
		
	}
	
	/**
	 * Creates a new node and appends it to this node's children.
	 */
	public function add_child($parser) {
		
		$node = get_class($this);
		$this->children[] = new $node($parser, $this);
		
	}
	
	/**
	 * Initialises a node with the given parser.
	 */
	public function __construct($parser, $parent = null) {
		
		$this->parser = $parser;
		$this->parent = $parent;
		
		$this->line_number = $parser->line_number();
		$this->line = $parser->line();
		$this->indent_level = $parser->indent_level();
		
	}
	
	/**
	 * Parses the tree from this node.
	 */
	abstract public function parse();
	
	/**
	 * Generates the string representation for the tree from this node.
	 */
	abstract public function __toString();
	
}

?>