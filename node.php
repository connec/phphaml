<?php

/**
 * node.php
 */

namespace phphaml;

/**
 * The Node class provides common functionality for generating and traversing trees.
 * 
 * Node trees form the high-level cacheable representation of output documents
 */

abstract class Node {
	
	/**
	 * The RootNode this node descends from.
	 */
	public $root;
	
	/**
	 * The parent of this node.
	 */
	public $parent;
	
	/**
	 * The index of this node in its parent's children.
	 * 
	 * This is stored to enable easily removing 
	 */
	public $index;
	
	/**
	 * The children of this node.
	 */
	public $children = array();
	
	/**
	 * The line number of this node.
	 */
	public $line_number = 0;
	
	/**
	 * The indentation level of this node.
	 */
	public $indent_level = 0;
	
	/**
	 * The content of this node.
	 */
	public $content = '';
	
	/**
	 * Instantiates and sets a node's attributes based on the given parser's current position.
	 */
	public static function new_from_parser(Parser $parser) {
		
		$node = get_called_class();
		$node = new $node;
		$node->set_from_parser($parser);
		
		$parser->context()->add_child($node);
		
		return $node;
		
	}
	
	/**
	 * Sets node attributes based on the parser's current position.
	 */
	public function set_from_parser(Parser $parser) {
		
		$this->line_number = $parser->line_number();
		$this->indent_level = $parser->indent_level();
		$this->content = $parser->content();
		
	}
	
	/**
	 * Generates PHP/HTML code for this node and its children.
	 */
	abstract public function render();
	
	/**
	 * Generates PHP/HTML code for this nodes children.
	 */
	public function render_children() {
		
		$result = '';
		foreach($this->children as $child)
			$result .= $child->render() . "\n";
		return $result;
		
	}
	
	/**
	 * Gets the value of an option.
	 */
	public function option($key) {
		
		return $this->root->option($key);
		
	}
	
	/**
	 * Gets the indent string to use when rendering.
	 */
	public function indent_string() {
		
		return $this->root->indent_string();
		
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
		
		end($this->children);
		if(!prev($this->children))
			return false;
		
		return current($this->children);
		
	}
	
	/**
	 * Returns the next child relative to a context child.
	 */
	public function next_child($context) {
		
		reset($this->children);
		if(!next($this->children))
			return false;
		
		return current($this->children);
		
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
		
		$child->root = $this->root;
		$child->parent = $this;
		
		end($this->children);
		$child->index = key($this->children);
		
	}
	
	/**
	 * Removes the last child node.
	 */
	public function remove_last_child() {
		
		end($this->children)->remove();
		
	}
	
	/**
	 * Removes this node from the tree.
	 */
	public function remove() {
		
		unset($this->parent->children[$this->index]);
		
		$this->root = $this;
		$this->parent = null;
		$this->index = 0;
		
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