<?php

namespace hamlparser\lib\haml;

class Parser extends \hamlparser\lib\Parser {
	
	protected static $node_class = '\hamlparser\lib\haml\Node';
	protected static $doctypes = array(
		'html4' => array(
			'frameset'     => '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">',
			'strict'       => '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">',
			'transitional' => '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">'
		),
		'html5' => array(
			'transitional' => '<!DOCTYPE html>'
		),
		'xhtml' => array(
			'1.1'          => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">',
			'5'            => '<!DOCTYPE html>',
			'basic'        => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.1//EN" "http://www.w3.org/TR/xhtml-basic/xhtml-basic11.dtd">',
			'frameset'     => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">',
			'mobile'       => '<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.2//EN" "http://www.openmobilealliance.org/tech/DTD/xhtml-mobile12.dtd">',
			'rdfa'         => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN" "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd">',
			'strict'       => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">',
			'transitional' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'
		)
	);
	
	protected $variables;
	protected $options = array(
		'format' => 'xhtml',
		'escape_html' => false,
		'attr_wrapper' => '\'',
		'autoclose' => array(
			'meta', 'img',   'link',
			'br',   'hr',    'input',
			'area', 'param', 'col',
			'base'
		),
		'preserve' => array('textarea', 'pre')
	);
	
	public static function doctype($type = 'transitional') {
		
		if(!$type or !isset(static::$doctypes[static::options('format')][strtolower($type)]))
			$type = 'transitional';
		return static::$doctypes[static::options('format')][strtolower($type)];
		
	}
	
	public function __construct($source, $variables = array(), $options = array()) {
		
		parent::__construct($source, $options);
		$this->variables = $variables;
		
	}
	
	public function render() {
		
		extract($this->variables);
		ob_start();
		eval('?>' . $this->parse());
		return ob_get_clean();
		
	}
	
}

?>