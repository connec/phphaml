<?php

/**
 * document.php
 */

namespace phphaml\document;

/**
 * The Document provides the base for Document implementations.
 * 
 * Documents are responsible for representing the parsed data of a source, and
 * generating output from the parsed data. All parsing concerns are handled by
 * the relevant Parser. Documents only deal with generating representations of
 * the parsed data.
 * 
 * A Document is effectively the root of the tree of parsed data.  Child
 * elements must extend a base Node class, providing methods for traversing
 * nodes and generating output for their subtrees.
 */

abstract class Document {
	
	/**
	 * An array of options affecting output generation.
	 */
	public $options = array();
	
	/**
	 * An array of child nodes.
	 */
	public $children = array();
	
	/**
	 * The string used for indentation in the source.
	 */
	public $indent_string;
	
	/**
	 * Constructs the Document, given a parser and options.
	 */
	public function __construct($parser, $options) {
		
		$this->options = array_merge($this->options, $options);
		$this->context = $this;
		
	}
	
	/**
	 * Generates and returns the output for the source.
	 */
	abstract public function render();
	
}

?>