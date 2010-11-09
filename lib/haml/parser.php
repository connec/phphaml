<?php

namespace hamlparser\lib\haml;

class Parser extends \hamlparser\lib\Parser {
	
	protected static $node_class = '\hamlparser\lib\haml\Node';
	
	protected $options = array(
		'format' => 'html5',
		'escape_html' => false,
		'attr_wrapper' => '"',
		'autoclose' => array(
			'meta', 'img',   'link',
			'br',   'hr',    'input',
			'area', 'param', 'col',
			'base'
		),
		'preserve' => array('textarea', 'pre')
	);
	
}

?>