<?php

/**
 * node.php
 */

namespace phphaml\document;

/**
 * The Node class is the base for all document nodes.
 */

abstract class Node {
	
	/**
	 * The Document this Node is attached to.
	 */
	public $document;
	
	/**
	 * The parent Node or Document for this Node.
	 */
	public $parent;
	
	/**
	 * Any children of this Node.
	 */
	public $children = array();
	
	/**
	 * The line number of this node.
	 */
	public $line_number;
	
	/**
	 * The indent level of this node.
	 */
	public $indent_level;
	
	/**
	 * Initialises the node.
	 */
	public function __construct($document, $parent, $line_number, $indent_level) {
		
		$this->document = $document;
		$this->parent = $parent;
		$this->line_number = $line_number;
		$this->indent_level = $indent_level;
		
	}
	
	/**
	 * Generates and returns the output for this Node and its subtree.
	 */
	abstract public function render();
	
	/**
	 * Generates and returns the output for this Node's subtree.
	 */
	protected function render_children() {
		
		$render = array();
		foreach($this->children as $child)
			$render[] = $child->render();
		return implode("\n", $render);
		
	}
	
}

?>