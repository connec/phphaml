<?php

/**
 * node.php
 */

namespace hamlparser\lib;

/**
 * The Node class represents a node of parsed document data.
 */

class Node {
	
	/**
	 * The line this node is on.
	 */
	protected $line;
	
	/**
	 * The indent level this node is at.
	 */
	protected $indent_level;
	
	/**
	 * The content of this node.
	 */
	protected $content;
	
	/**
	 * A reference to this node's parent.
	 */
	protected $parent;
	
	/**
	 * The children of this node.
	 */
	protected $children = array();
	
	/**
	 * The metadata for this node.
	 */
	protected $metadata = array();
	
	/**
	 * Returns a node type depending on given content.
	 */
	public static function factory($line, $indent_level, $content = '', $parent = null, $children = array()) {
		
		return new Node($line, $indent_level, $content, $parent, $children);
		
	}
	
	/**
	 * Initialises the node.
	 */
	public function __construct($line, $indent_level, $content = '', $parent = null, $children = array()) {
		
		$this->line         = $line;
		$this->indent_level = $indent_level;
		$this->content      = $content;
		$this->parent       = $parent;
		$this->children     = $children;
		
	}
	
	/**
	 * Parses the content into appropriate content/metadata.
	 */
	protected function parse() {}
	
	/**
	 * Adds a child to the node.
	 */
	public function add_child($child) {
		
		$this->children[] = $child;
		
	}
	
	/**
	 * Gets the parent of the node.
	 */
	public function parent() {
		
		return $this->parent;
		
	}
	
	/**
	 * Gets the last child in the child list.
	 */
	public function last_child() {
		
		return end($this->children);
		
	}
	
	/**
	 * Checks the tree from this node (inclusive) is valid.
	 */
	public function validate() {
		
		return true;
		
	}
	
	/**
	 * Generates the string output for this node and it's subtree.
	 */
	public function __toString() {
		
		if($this->content)
			$return = str_repeat("\t", $this->indent_level) . $this->content . "\n";
		else
			$return = '';
		
		foreach($this->children as $child)
			$return .= (string)$child;
		
		return $return;
		
	}
	
}

?>