<?php

/**
 * parser.php
 */

namespace phphaml\parser\haml;

use
	\phphaml\ruby\RubyInterpolatedString,
	\phphaml\ruby\RubyHash;

/**
 * The Parser class is the parser implementation for HAML.
 */

class Parser extends \phphaml\parser\Parser {
	
	/**
	 * A flag indicating the next line is expected to continue multiline HTML
	 * attributes.
	 */
	const MULTILINE_HTML_ATTRIBUTES = 32;
	
	/**
	 * A flag indicating the next line is expected to continue multiline hash
	 * attributes.
	 */
	const MULTILINE_HASH_ATTRIBUTES = 64;
	
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
	 * A regular expression for matching HAML comments.
	 */
	const RE_HAML_COMMENT = '/^-#/';
	
	/**
	 * A regular expression for matching the start of a PHP node.
	 */
	const RE_PHP_NODE = '/^-/';
	
	/**
	 * A regular expression matching any character.
	 */
	const RE_ANY = '/./';
	
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
	 * A regular expression for extracting an attribute hash.
	 */
	const RE_ATTRIBUTE_HASH = '/{.*}/';
	
	/**
	 * A regular expression for matching a valid HTML attribute.
	 */
	const RE_ATTRIBUTE_NAME = '/^[a-z:_][a-z0-9:._-]*$/i';
	
	/**
	 * A regular expression for matching the beginning of an interpolation.
	 */
	const RE_INTERPOLATION_START = RubyInterpolatedString::RE_INTERPOLATION_START;
	
	/**
	 * An array mapping regular expressions to callbacks for handling different
	 * source lines.
	 * 
	 * Handlers are matched in the order defined.
	 */
	protected static $handlers = array(
		self::RE_XML          => 'xml_prolog',
		self::RE_DOCTYPE      => 'doctype',
		self::RE_TAG_START    => 'tag_start',
		self::RE_HAML_COMMENT => 'haml_comment',
		self::RE_ANY          => 'any'
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
	 * An unfinished node being processed.
	 */
	protected $node;
	
	/**
	 * A flag indicating what multiline content is expected.
	 */
	protected $multiline = false;
	
	/**
	 * Constructs the parser, given a source and options.
	 */
	public function __construct($source, $variables = array(), $options = array()) {
		
		parent::__construct($source, $options);
		$this->variables = $variables;
		
	}
	
	/**
	 * Returns the result of rendering the Document.
	 */
	public function render() {
		
		if(!$this->document)
			$this->parse();
		$this->document->variables = $this->variables;
		return $this->document->render();
		
	}
	
	/**
	 * Handles an XML prolog.
	 */
	protected function handle_xml_prolog($match) {
		
		if($this->document->doctype or !empty($this->document->children))
			$this->exception('Parse error: XML prolog must be the first content in the document');
		
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
		
		if(!empty($this->document->children))
			$this->exception('Parse error: DOCTYPE must come before any content');
		
		if($this->indent_level > 0)
			$this->exception('Parse error: XML prolog can not be indented');
		
		if(isset($match[1]))
			$this->document->doctype = $match[1];
		else
			$this->document->doctype = 'transitional';
		
		$this->expect_indent = self::EXPECT_SAME;
		
	}
	
	/**
	 * Handles the start of a tag line (before any attributes).
	 */
	protected function handle_tag_start($match) {
		
		$this->context->children[] = $this->node = $node = $this->create_node('tag');
		
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
		
		$this->handle_attributes();
		
	}
	
	/**
	 * Handles tag attributes, including dealing with multiline tags.
	 */
	protected function handle_attributes() {
		
		if($this->line[0] == '(' or $this->multiline == self::MULTILINE_HTML_ATTRIBUTES) {
			if(!$this->handle_html_attributes()) {
				if($this->multiline)
					$this->expect_indent = self::EXPECT_SAME;
				else {
					$this->multiline = self::MULTILINE_HTML_ATTRIBUTES;
					$this->expect_indent = self::EXPECT_MORE;
				}
				return;
			}
		}
		
		if($this->line[0] == '{' or $this->multiline == self::MULTILINE_HASH_ATTRIBUTES) {
			if(!$this->handle_hash_attributes()) {
				if($this->multiline)
					$this->expect_indent = self::EXPECT_SAME;
				else {
					$this->multiline = self::MULTILINE_HASH_ATTRIBUTES;
					$this->expect_indent = self::EXPECT_MORE;
				}
				return;
			}
		}
		
		$this->handle_tag_end();
		
	}
	
	/**
	 * Handles HTML-style attributes.
	 */
	protected function handle_html_attributes() {
		
		$escape = false;
		$in_apos = false;
		$in_quot = false;
		$finished = false;
		$last_offset = ($this->line[0] == '(' ? 1 : 0);
		
		$attribute = '';
		$value = '';
		
		preg_match_all('/[\b\'")=\s]|$/', $this->line, $matches, PREG_OFFSET_CAPTURE);
		foreach($matches[0] as $match) {
			switch($match[0]) {
				case '\'':
					if(!$in_quot and !$escape)
						$in_apos = !$in_apos;
				continue 2;
				case '"':
					if(!$in_apos and !$escape)
						$in_quot = !$in_quot;
				continue 2;
				case '\\':
					if(!$escape)
						$escape = true;
				continue 2;
			}
			
			if($match[0] == '=') {
				$attribute = substr($this->line, $last_offset, $match[1] - $last_offset);
				if(!preg_match(self::RE_ATTRIBUTE_NAME, $attribute))
					$this->exception('Syntax error: invalid attribute name');
			}
			
			if(preg_match('/[\s)]|^$/', $match[0])) {
				$value = trim(substr($this->line, $last_offset, $match[1] - $last_offset));
				
				if($value[0] == '"')
					$value = new RubyInterpolatedString(substr($value, 1, -1));
				elseif($value[0] == "'")
				    $value = substr($value, 1, -1);
				else
					$value = new RubyInterpolatedString('#{' . $value . '}');
				
				if($match[0] == ')')
					$finished = true;
			}
			
			if($attribute and $value) {
				if($attribute == 'class')
					$this->node->attributes['class'][] = $value;
				elseif($attribute == 'id')
					$this->node->attributes['id'][] = $value;
				else
					$this->node->attributes[$attribute] = $value;
				$attribute = '';
				$value = '';
			}
			
			$last_offset = $match[1] + 1;
			
			if($finished)
				break;
		}
		
		$this->line = substr($this->line, $last_offset);
		
		if($finished)
			return true;
		else
			return false;
		
	}
	
	/**
	 * Handles ruby-style hash attributes.
	 */
	protected function handle_hash_attributes() {
		
		static $line = '';
		
		if(substr($this->line, -1) == ',') {
			$line .= $this->line;
			return false;
		}
		
		$this->line = $line . $this->line;
		$line = '';
		
		if(preg_match(self::RE_ATTRIBUTE_HASH, $this->line, $match)) {
			try {
				$hash = new RubyHash($match[0]);
				$hash = $hash->to_a();
			} catch(Exception $e) {
				$this->exception($e->getMessage());
			}
			
			foreach(array('class', 'id') as $field) {
				if(isset($hash[$field])) {
					if(!is_array($hash[$field])) {
						if(isset($this->node->attributes[$field]))
							$this->node->attributes[$field][] = $hash[$field];
						else
							$this->node->attributes[$field] = array($hash[$field]);
					} else {
						if(isset($this->node->attributes[$field]))
							$this->node->attributes[$field] = array_merge($this->node->attributes[$field], $hash[$field]);
						else
							$this->node->attributes[$field] = $hash[$field];
					}
					unset($hash[$field]);
				}
			}
			$this->node->attributes = array_merge($this->node->attributes, $hash);
			
			$this->line = substr($this->line, strlen($match[0]));
			return true;
		}
		
		$this->exception('Syntax error: invalid hash');
		
	}
	
	/**
	 * Handles the end of a tag line (after any attributes).
	 */
	protected function handle_tag_end() {
		
		$node = $this->node;
		
		if($this->line[0] == '/') {
			if($this->line != '/')
				$this->exception('Parse error: self-closing tags cannot have inline content');
			
			$this->line = '';
			$node->self_closing = true;
			
			$this->expect_indent = self::EXPECT_LESS | self::EXPECT_SAME;
		}
		
		if($this->line) {
		    $node->inline_content = $this->create_node('text');
		    $node->inline_content->indent_level = 0;
			$node->inline_content->content = new RubyInterpolatedString(trim($this->line));
			$this->line = '';
			
			$this->expect_indent = self::EXPECT_LESS | self::EXPECT_SAME;
		}
		
		$this->multiline = false;
		$this->node = null;
		
	}
	
	/**
	 * Handles a HAML comment.
	 */
	protected function handle_haml_comment() {
		
		$this->context->children[] = $this->create_node('haml_comment');
		
	}
	
	/**
	 * Handles any unhandled nodes, defaulting to a text node.
	 */
	protected function handle_any($match) {
		
		switch($this->multiline) {
			case self::MULTILINE_HTML_ATTRIBUTES:
			case self::MULTILINE_HASH_ATTRIBUTES:
				return $this->handle_attributes();
			break;
		}
		
		$node = $this->create_node('text');
		$node->content = new RubyInterpolatedString(trim($this->line));
		$this->line = '';
		$this->context->children[] = $node;
		
		$this->expect_indent = self::EXPECT_LESS | self::EXPECT_SAME;
		
	}
	
}

?>