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
	 * Initialises the node.
	 */
	public function __construct($document, $parent) {
		
		$this->document = $document;
		$this->parent = $parent;
		
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