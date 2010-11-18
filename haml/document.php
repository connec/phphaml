<?php

/**
 * document.php
 */

namespace haml\haml;

/**
 * The Document class is the Document implementation for HAML.
 */

class Document extends \haml\Document {
	
	/**
	 * An array of available DOCTYPES.
	 */
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
				'strict'       => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">',
				'transitional' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
				'rdfa'         => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN" "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd">'
		)
	);
	
	/**
	 * A boolean value indicating whether or not an XML prolog should be output at
	 * the beginning of the document.
	 */
	public $xml_prolog = false;
	
	/**
	 * The encoding to use for the XML prolog, if one is generated.
	 */
	public $xml_encoding;
	
	/**
	 * The DOCTYPE to be output at the beginning of the document, after the XML
	 * prolog (if generated).
	 * 
	 * A value evaluating to boolean false will cause no DOCTYPE to be generated.
	 */
	public $doctype;
	
	/**
	 * Wraps the given text with the an enclosure.
	 */
	protected static function wrap($text, $enclosure) {
		
		return $enclosure . $text . $enclosure;
		
	}
	
	/**
	 * Generates and returns the output for the source.
	 */
	public function render() {
		
		$this->output = array();
		
		if($this->xml_prolog and $this->options['format'] == 'xhtml') {
			if(!$this->xml_encoding)
				$this->xml_encoding = $this->options['encoding'];
			
			$attributes = $this->attributes(array(
				'version' => '1.0',
				'encoding' => $this->xml_encoding
			));
			
			$this->output[] = '<?xml ' . $attributes . ' ?>';
		}
		
		if($this->doctype) {
			$this->doctype = strtolower($this->doctype);
			if(isset(static::$doctypes[$this->options['format']][$this->doctype]))
				$this->output[] = static::$doctypes[$this->options['format']][$this->doctype];
		}
		
		foreach($this->children as $child)
			$this->output[] = $child->render();
		
		return implode("\n", $this->output);
		
	}
	
	/**
	 * Generates an XML attribute string given an array of attribute => value 
	 * pairs.
	 */
	protected function attributes($attributes) {
		
		$_attributes = array();
		foreach($attributes as $attribute => $value)
			$_attributes[] = $attribute . '=' . static::wrap($value, $this->options['attr_wrapper']);
		return implode(' ', $_attributes);
		
	}
	
}

?>