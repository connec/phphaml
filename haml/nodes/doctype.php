<?php

/**
 * doctype.php
 */

namespace phphaml\haml\nodes;

/**
 * The Doctype node represents a doctype or XML declaration in a HAML document.
 */

class Doctype extends Node {
	
	/**
	 * The available doctypes.
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
	 * The formatting string for an XML declaration.
	 */
	protected static $xml_declaration = '<?php echo \'<\'; ?>?xml version=%s encoding=%s ?>';
	
	/**
	 * The encoding to use in the xml declaration or false if this is a doctype.
	 */
	public $encoding = false;
	
	/**
	 * The doctype to use or false if this is an xml declaration.
	 */
	public $doctype = false;
	
	/**
	 * Retrives the specified doctype or false if it does not exist.
	 */
	public function doctype($format, $doctype = 'transitional') {
		
		if(!$format)
			$format = $this->option('format');
		
		if(isset(static::$doctypes[$format][$doctype]))
			return static::$doctypes[$format][$doctype];
		return false;
		
	}
	
	/**
	 * Renders the nodes content.
	 */
	public function render() {
		
		if($this->encoding) {
			$q = $this->option('attr_wrapper');
			return sprintf(static::$xml_declaration, $q . '1.0' . $q, $q . $this->encoding . $q);
		}
		
		return static::doctype($this->option('format'), $this->doctype);
		
	}
	
}

?>