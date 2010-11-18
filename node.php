<?php

/**
 * node.php
 */

namespace haml;

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
	 * The indent level of this node.
	 */
	public $indent_level;
	
	/**
	 * Initialises the node.
	 */
	public function __construct($document, $parent, $indent_level) {
		
		$this->document = $document;
		$this->parent = $parent;
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