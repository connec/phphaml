<?php

/**
 * doctype_handler.php
 */

namespace phphaml\haml;

/**
 * The DoctypeHandler class handles DOCTYPE / XML declarations in a HAML source.
 */

class DoctypeHandler extends LineHandler {
	
	/**
	 * The start-of-line trigger for this handler.
	 * 
	 * Note: line handling is ordered by the length of the trigger.
	 * Note: the catch-all trigger '*' is treated specially, and only one should be defined per
	 * parser (where more than one is defined, which one is chosen is undefined).
	 */
	protected static $trigger = '!!!';
	
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
	protected static $xml_declaration = '<?xml version=%s encoding=%s ?>';
	
	/**
	 * Parses the content of this node.
	 */
	public function parse() {
		
		$format = $this->parser->option('format');
		if(!isset(static::$doctypes[$format]))
			$format = 'xhtml';
		
		$remainder = trim(substr($this->content, 3));
		
		if(strtolower(substr($remainder, 0, 3)) == 'xml') {
			if($format != 'xhtml')
				$this->parent->remove_last_child();
			else {
				if(!($encoding = trim(substr($remainder, 3))))
					$encoding = $this->parser->option('encoding');
				
				$this->content = sprintf(
					static::$xml_declaration,
					$this->attr('1.0'),
					$this->attr($encoding)
				);
			}
		} else {
			if(!isset(static::$doctypes[$format][$remainder]))
				$remainder = 'transitional';
			$this->content = static::$doctypes[$format][$remainder];
		}
		
		$this->parser->expect_indent(Parser::EXPECT_LESS | Parser::EXPECT_SAME);
		
	}
	
}

?>