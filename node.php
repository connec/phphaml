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
	protected $index = 0;
	
	/**
	 * The children of this node.
	 */
	protected $children = array();
	
	/**
	 * The line number of this node.
	 */
	protected $line_number = 0;
	
	/**
	 * The indentation level of this node.
	 */
	protected $indent_level = 0;
	
	/**
	 * The content of this node.
	 */
	protected $content = '';
	
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
	 * Returns the first child of this node, or false if there are no children.
	 */
	public function first_child() {
		
		return empty($this->children) ? false : reset($this->children);
		
	}
	
	/**
	 * Returns the last child of this node, or false if there are no children.
	 */
	public function last_child() {
		
		return empty($this->children) ? false : end($this->children);
		
	}
	
	/**
	 * Returns the previous child relative to a context child.
	 */
	public function previous_child($context) {
		
		if($context->index == $this->first_child()->index)
			return false;
		
		return $this->children[$context->index - 1];
		
	}
	
	/**
	 * Returns the next child relative to a context child.
	 */
	public function next_child($context) {
		
		if($context->index == $this->last_child()->index)
			return false;
		
		return $this->children[$context->index + 1];
		
	}
	
	/**
	 * Returns the previous node in this node's parent's children, or false if this is the first 
	 * sibling.
	 */
	public function previous_sibling() {
		
		return $this->parent->previous_child($this);
		
	}
	
	/**
	 * Returns the previous node in this node's parent's children, or false if this is the first 
	 * sibling.
	 */
	public function next_sibling() {
		
		return $this->parent->next_child($this);
		
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