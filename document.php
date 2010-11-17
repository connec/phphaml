<?php

/**
 * document.php
 */

namespace haml;

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
	 * The parser generating the document.
	 */
	protected $parser;
	
	/**
	 * An array of options affecting output generation.
	 */
	protected $options = array();
	
	/**
	 * An array of child nodes.
	 */
	protected $children = array();
	
	/**
	 * The output generated during rendering.
	 */
	protected $output = '';
	
	/**
	 * Constructs the Document, given a parser and options.
	 */
	public function __construct($parser, $options) {
		
		$this->parser = $parser;
		$this->options = array_merge($this->options, $options);
		
	}
	
	/**
	 * Generates and returns the output for the source.
	 */
	abstract public function render();
	
}

?>