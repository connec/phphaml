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
	 * A regular expression for matching an XML prolog.
	 */
	const RE_XML = '/^!!! xml(?: (.+))?$/i';
	
	/**
	 * A regular expression for matching a DOCTYPE.
	 */
	const RE_DOCTYPE = '/^!!!(?: (.+))?$/';
	
	/**
	 * A regular expression for matching the start of a tag line.
	 */
	const RE_TAG_START = '/^(?:(%[a-z_])|(\.[a-z0-9_-])|(#[a-z]))/i';
	
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
	 * A temporary string for building multiline nodes.
	 */
	protected $buffer;
	
	/**
	 * An array mapping regular expressions to callbacks for handling different
	 * source lines.
	 * 
	 * Handlers are matched in the order defined.
	 */
	protected static $handlers = array(
		self::RE_XML       => 'xml_prolog',
		self::RE_DOCTYPE   => 'doctype',
		self::RE_TAG_START => 'tag',
		'/./'              => 'text'
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
		
		if($this->indent_level > 0)
			$this->exception('Parse error: XML prolog can not be indented');
		
		$this->document->xml_prolog = true;
		if(isset($match[1]))
			$this->document->xml_encoding = $match[1];
		
		$this->expect_indent = self::EXPECT_SAME;
		
	}
	
	/**
	 * Handles a DOCTYPE decleration.
	 */
	protected function handle_doctype($match) {
		
		if($this->indent_level > 0)
			$this->exception('Parse error: XML prolog can not be indented');
		
		if(isset($match[1]))
			$this->document->doctype = $match[1];
		else
			$this->document->doctype = 'transitional';
		
		$this->expect_indent = self::EXPECT_SAME;
		
	}
	
	/**
	 * Handles a tag line.
	 */
	protected function handle_tag($match) {
		
		$node = new TagNode($this->document, $this->context, $this->indent_level);
		
		if($match[0][0] == '%') {
			preg_match(self::RE_TAG, $this->line, $match);
			$this->line = substr($this->line, strlen($match[0]));
			$node->tag = substr($match[0], 1);
			
			if(in_array($node->tag, $this->options['autoclose']))
				$node->self_closing = true;
		}
		
		while(preg_match(self::RE_CLASS, $this->line, $match)
			 or preg_match(self::RE_ID,    $this->line, $match)) {
		 	
			$this->line = substr($this->line, strlen($match[0]));
			
			$name = substr($match[0], 1);
			if($match[0][0] == '.') {
				if(!isset($node->attributes['class']))
					$node->attributes['class'] = array();
				$node->attributes['class'][] = $name;
			} else {
				$node->attributes['id'] = array($name);
			}
			
		}
		
		if($this->line[0] == '/') {
			if($this->line != '/')
				$this->exception('Parse error: self-closing tags cannot have inline content');
			
			$this->line = '';
			$node->self_closing = true;
		}
		
		if($this->line) {
			$node->inline_content = new TextNode($this->document, null, 0);
			$node->inline_content->content = trim($this->line);
			$this->line = '';
		}
		
		$this->context->children[] = $node;
		
	}
	
	/**
	 * Handles a text node.
	 */
	protected function handle_text($match) {
		
		$node = new TextNode($this->document, $this->context, $this->indent_level);
		$node->content = trim($this->line);
		$this->line = '';
		$this->context->children[] = $node;
		
	}
	
}

?>