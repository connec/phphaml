<?php

/**
 * tag_handler.php
 */

namespace phphaml\haml;

use \phphaml\ruby\RubyInterpolatedString;

/**
 * The TagHandler class handles tag nodes in a HAML source.
 */

class TagHandler extends LineHandler {
	
	/**
	 * A regular expression for capturing a valid tag name.
	 */
	const RE_TAG = '/^[a-z_][a-z0-9_:-]*/i';
	
	/**
	 * A regular expression for capturing a valid class name.
	 */
	const RE_CLASS = '/^[a-z0-9_-]+/i';
	
	/**
	 * A regular expression for capturing a valid ID.
	 */
	const RE_ID = '/^[a-z][a-z0-9_:-]*/i';
	
	/**
	 * The start-of-line trigger for this handler.
	 * 
	 * Note: line handling is ordered by the length of the trigger.
	 * Note: the catch-all trigger '*' is treated specially, and only one should be defined per
	 * parser (where more than one is defined, which one is chosen is undefined).
	 */
	protected static $trigger = array('%', '.', '#');
	
	/**
	 * The tag name of this tag.
	 */
	protected $tag = 'div';
	
	/**
	 * An array of attributes for this tag.
	 */
	protected $attributes = array();
	
	/**
	 * A flag indicating whether or not this tag is self-closing.
	 */
	protected $self_closing = false;
	
	/**
	 * Parses the content of this node.
	 */
	public function parse() {
		
		if($this->content[0] == '%') {
			if(!preg_match(self::RE_TAG, substr($this->content, 1), $match))
				$this->exception('Parse error: invalid tag name');
			
			$this->content = substr($this->content, strlen($match[0]) + 1);
			$this->tag = $match[0];
		}
		
		while($this->content[0] == '.' or $this->content[0] == '#') {
			if($this->content[0] == '.') {
				if(!preg_match(self::RE_CLASS, substr($this->content, 1), $match))
					$this->exception('Parse error: invalid class name');
				
				if(!isset($this->attributes['class']))
					$this->attributes['class'] = array();
				
				$this->content = substr($this->content, strlen($match[0]) + 1);
				$this->attributes['class'][] = $match[0];
			} else {
				if(!preg_match(self::RE_ID, substr($this->content, 1), $match))
					$this->exception('Parse error: invalid id');
				
				$this->content = substr($this->content, strlen($match[0]) + 1);
				$this->attributes['id'] = array($match[0]);
			}
		}
		
		if($this->content[0] == '/') {
			if($this->content != '/')
				$this->exception('Parse error: self-closing tags cannot have content');
			$this->content = substr($this->content, 1);
			$this->self_closing = true;
		} elseif($this->content == '' and in_array($this->tag, $this->parser->option('autoclose')))
			$this->self_closing = true;
		
		$this->content = trim($this->content);
		
		if($this->self_closing or $this->content)
			$this->parser->expect_indent(Parser::EXPECT_LESS | Parser::EXPECT_SAME);
		
	}
	
	/**
	 * Renders the parsed tree.
	 */
	public function render() {

		$indent = str_repeat($this->parser->indent_string(), $this->indent_level);
		$open_tag = $indent . '<' . $this->tag . (empty($this->attributes) ? '' : ' ' . $this->render_attributes()) . '>';
		$close_tag = '</' . $this->tag . '>';

		if($this->self_closing) {
			if($this->parser->option('format') == 'xhtml')
				return substr($open_tag, 0, -1) . ' />';
			else
				return $open_tag;
		}

		if(empty($this->children)) {
			return $open_tag
				. $this->content
				. $close_tag;
		}

		return $open_tag . "\n"
			. $this->render_children() . "\n"
			. $indent . $close_tag;

	}
	
	/**
	 * Renders the attribute string for this tag.
	 */
	protected function render_attributes() {
		
		$attributes = array();
		foreach($this->attributes as $attribute => $value) {
			if(empty($value))
				return;
			
			if(is_array($value)) {
				foreach($value as &$_value) {
					if($_value instanceof RubyInterpolatedString)
						$_value = $_value->to_text($this->parser->variables());
				}
				if($attribute == 'class')
					$value = implode(' ', $value);
				if($attribute == 'id')
					$value = implode('_', $value);
			}
			
			$attributes[] = $attribute . '=' . $this->attr($value);
		}
		sort($attributes);
		return implode(' ', $attributes);
		
	}
	
}

?>