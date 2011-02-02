<?php

/**
 * parser.php
 */

namespace phphaml\haml;

use \phphaml\haml\filters\Filter;

/**
 * The Parser class forms the root of a source document, and handles traversing and delegating the
 * source lines.
 * 
 * The phphaml\Parser class extends the base Parser with HAML specific functionality.
 */

class Parser extends \phphaml\Parser {
	
	/**
	 * An array of options affecting parsing or output generation.
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
	 * An array of variable substitutions for rendering.
	 */
	protected $variables = array();
	
	/**
	 * Initialises the parser with given source and options.
	 */
	public function __construct($source, array $variables = array(), array $options = array()) {
		
		parent::__construct($source, $options);
		$this->variables = $variables;
		
	}
	
	/**
	 * Renders the parsed tree.
	 */
	public function render() {
		
		EvalString::variables($this->variables);
		
		if(!$this->line_number)
			$this->parse();
		
		$result = '';
		foreach($this->root->children as $child)
			$result .= $child->render() . ($child->append_newline ? "\n" : '');
		return rtrim($result);
		
	}
	
}

?>