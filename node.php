<?php

/**
 * node.php
 */

namespace phphaml;

/**
 * The Node class provides common functionality for generating and traversing trees.
 */

abstract class Node {
	
	/**
	 * The root of the tree this node is attached to.
	 */
	protected $parser;
	
	/**
	 * The parent of this node.
	 */
	protected $parent;
	
	/**
	 * The index of this node in its parent's children.
	 * 
	 * This is stored to enable easily removing 
	 */
	protected $index;
	
	/**
	 * The children of this node.
	 */
	protected $children = array();
	
	/**
	 * The line number of this node.
	 */
	protected $line_number;
	
	/**
	 * The indentation level of this node.
	 */
	protected $indent_level;
	
	/**
	 * The content of this node.
	 */
	protected $content;
	
	/**
	 * Returns the last child of this node, or false if there are no children.
	 */
	public function last_child() {
		
		if(empty($this->children))
			return false;
		
		return end($this->children);
		
	}
	
	/**
	 * Accessor for {$line_number}.
	 */
	public function line_number() {
		
		return $this->line_number;
		
	}
	
	/**
	 * Accessor for {$indent_level}.
	 */
	public function indent_level() {
		
		return $this->indent_level;
		
	}
	
	/**
	 * Accessor for {$content}.
	 */
	public function content() {
		
		return $this->content;
		
	}
	
	/**
	 * Adds a child node.
	 */
	public function add_child(Node $child) {
		
		$this->children[] = $child;
		end($this->children);
		$child->index = key($this->children);
		
	}
	
	/**
	 * Removes the last child node.
	 */
	public function remove_last_child() {
		
		end($this->children);
		unset($this->children[key($this->children)]);
		
	}
	
	/**
	 * Throws an exception, and appends the line number to the given message.
	 */
	public function exception($message, array $sub = array()) {
		
		$sub['line'] = $this->line_number;
		throw new Exception($message . ' - line :line', $sub);
		
	}
	
}

?>