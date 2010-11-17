<?php

/**
 * parser.php
 */

namespace haml\haml;

/**
 * The Parser class is the parser implementation for HAML.
 */

class Parser extends \haml\Parser {
	
	/**
	 * A regular expression for extracting a tag name.
	 */
	const RE_TAG = '/^%[a-z_][a-z0-9_:-]*/i';
	
	/**
	 * A regular expression for extracting a class.
	 */
	const RE_CLASS = '/^\.[a-z0-9_-]+/i';
	
	/**
	 * A regular expression for extracting an id.
	 */
	const RE_ID = '/^#[a-z][a-z0-9_:-]*/i';
	
	/**
	 * The class to use for the parsed document.
	 */
	protected static $document_class = '\haml\haml\Document';
	
	/**
	 * An array mapping regular expressions to callbacks for handling different
	 * source lines.
	 * 
	 * Handlers are matched in the order defined.
	 */
	protected static $handlers = array(
		'/^!!! xml(?: (.+))?/i' => 'xml_prolog',
		'/^!!!(?: (.+))?/i' => 'doctype',
		'/^(?:(%[a-z_])|(\.[a-z0-9_-])|(#[a-z]))/i' => 'tag'
	);
	
	/**
	 * An associative array of variable names to values to be evaluated during
	 * rendering.
	 */
	protected $variables = array();
	
	/**
	 * An array of options affecting parsing or output generation.
	 * 
	 * Options are given on construction, and are passed to the Document.
	 */
	protected $options = array(
		'format' => 'xhtml',
		'escape_html' => false,
		'ugly' => false,
		'suppress_eval' => false,
		'attr_wrapper' => '\'',
		'filename' => false,
		'line' => false,
		'autoclose' => array(
			'meta', 'img',   'link',
			'br',   'hr',    'input',
			'area', 'param', 'col',
			'base'
		),
		'preserve' => array('textarea', 'pre'),
		'encoding' => 'utf-8'
	);
	
	/**
	 * Constructs the parser, given a source and options.
	 */
	public function __construct($source, $variables = array(), $options = array()) {
		
		parent::__construct($source, $options);
		$this->variables = $variables;
		
	}
	
	/**
	 * Handles an XML prolog.
	 */
	protected function handle_xml_prolog($match) {
		
		$this->document->xml_prolog = true;
		if(isset($match[1]))
			$this->document->xml_encoding = $match[1];
		
	}
	
	/**
	 * Handles a DOCTYPE decleration.
	 */
	protected function handle_doctype($match) {
		
		if(isset($match[1]))
			$this->document->doctype = $match[1];
		else
			$this->document->doctype = 'transitional';
		
	}
	
}

?>