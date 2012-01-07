<?php

/**
 * parser.php
 */

namespace phphaml\haml;

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
		'format' => 'html',
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
		
		if(!$this->line_number)
			$this->parse();
		
		$result = $this->root->render();
		
		ob_start();
		
		StringStream::add_string('result', $result);
		extract($this->variables);
		$__options = $this->options;
		$__render_attributes = function($attributes) use ($__options) {
		  nodes\Tag::render_attributes_html($__options['format'], $__options['attr_wrapper'], $attributes);
		};
		include 'StringStream://result';
		StringStream::clear('result');
		
		return rtrim(ob_get_clean());
		
	}
	
}

?>