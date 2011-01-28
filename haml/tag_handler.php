<?php

/**
 * tag_handler.php
 */

namespace phphaml\haml;

use
	\phphaml\haml\ruby\RubyHash,
	\phphaml\haml\ruby\RubyValue;

/**
 * The TagHandler class handles tag nodes in a HAML source.
 */

class TagHandler extends LineHandler {
	
	/**
	 * A flag indicating we are processing multiline HTML attributes.
	 */
	const MULTILINE_HTML_ATTRIBUTES = 1;
	
	/**
	 * A flag indicating we are processing multiline ruby attributes.
	 */
	const MULTILINE_RUBY_ATTRIBUTES = 2;
	
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
	 * A flag indicating whether we are processing a multiline tag.
	 */
	protected static $multiline = false;
	
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
	 * A flag indicating whether or not this tag should have surrounding whitespace.
	 */
	protected $outer_whitespace = true;
	
	/**
	 * A flag indicating whether or not this tag should have inner whitespace.
	 */
	protected $inner_whitespace = true;
	
	/**
	 * Handles the current line in the given parser.
	 * 
	 * This is used instead of the parser appending to the tree itself in order to deal with
	 * potential multiline statements.
	 */
	public static function handle(\phphaml\Parser $parser) {
		
		if(!static::$multiline) {
			parent::handle($parser);
			
			if(static::$multiline) {
				$parser->force_handler(get_called_class());
				$parser->expect_indent(Parser::EXPECT_MORE);
			}
		} else {
			$parser->context()->content .= ' ' . $parser->content();
			
			switch(static::$multiline) {
				case self::MULTILINE_HTML_ATTRIBUTES:
					static::$multiline = false;
					$parser->context()->parse_html_attributes();
				break;
				case self::MULTILINE_RUBY_ATTRIBUTES:
					static::$multiline = false;
					$parser->context()->parse_ruby_attributes();
				break;
				default:
					$parser->context()->exception('Sanity error: unknown multiline modifier');
			}
		}
		
	}
	
	/**
	 * Parses the content of this node.
	 */
	public function parse() {
		
		$this->parse_start();
		
	}
	
	/**
	 * Parses the beginning of a tag line (tag name, classes and ids).
	 */
	protected function parse_start() {
		
		if($this->content[0] == '%') {
			if(!preg_match(self::RE_TAG, substr($this->content, 1), $match))
				$this->exception('Parse error: invalid tag name');
			
			$this->content = substr($this->content, strlen($match[0]) + 1);
			$this->tag = $match[0];
		}
		
		while($this->content[0] == '.' or $this->content[0] == '#') {
			$type = $this->content[0] == '.' ? 'class' : 'id';
			
			if(!preg_match($type == 'id' ? self::RE_ID : self::RE_CLASS, substr($this->content, 1), $match)) {
				$this->exception(
					'Parse error: invalid :type',
					array('type' => $type == 'id' ? 'id' : 'class name')
				);
			}
			
			$this->content = substr($this->content, strlen($match[0]) + 1);
			
			if($type == 'class')
				$this->attributes[] = array($type, $match[0]);
			else
				$id = $match[0];
		}
		
		if(isset($id))
			$this->attributes[] = array('id', $id);
		
		$this->parse_html_attributes();
		
	}
	
	/**
	 * Parses HTML attributes from the content.
	 */
	protected function parse_html_attributes() {
		
		if($this->content[0] == '(') {
			$html_attributes = $this->extract_balanced('(', ')');
			
			if($html_attributes === false) {
				static::$multiline = self::MULTILINE_HTML_ATTRIBUTES;
				return;
			}
			
			foreach($this->quote_safe_explode(' ', $html_attributes) as $entry) {
				$parts = $this->quote_safe_explode('=', $entry);
				
				if(count($parts) != 2)
					$this->exception('Parse error: bad html attribute syntax');
				
				$this->attributes[] = array($parts[0], new Value($parts[1], $this));
			}
			
			$this->content = substr($this->content, strlen($html_attributes) + 2);
		}
		
		$this->parse_ruby_attributes();
		
	}
	
	/**
	 * Parses Ruby style attributes from the content.
	 */
	protected function parse_ruby_attributes() {
		
		if($this->content[0] == '{') {
			$ruby_attributes = $this->extract_balanced('{', '}');
			
			if($ruby_attributes === false) {
				if($this->content[strlen($this->content) - 1] != ',')
					$this->exception('Parse error: lines in a multiline hash attributes must end with a comma (,)');
				
				static::$multiline = self::MULTILINE_RUBY_ATTRIBUTES;
				return;
			}
			
			$this->content = substr($this->content, strlen($ruby_attributes) + 2);
			$ruby_attributes = new RubyHash('{' . $ruby_attributes . '}', $this);
			$this->attributes = array_merge($this->attributes, $ruby_attributes->to_a());
		}
		
		$this->parse_end();
		
	}
	
	/**
	 * Parses the end of a tag line (self-closing, content) and sets up the parser for the next line.
	 */
	protected function parse_end() {
		
		while(($token = $this->content[0]) == '<' or $this->content[0] == '>') {
			$this->content = substr($this->content, 1);
			
			if($token == '<')
				$this->inner_whitespace = false;
			else {
				$this->outer_whitespace = false;
				$this->previous_sibling()->render_newline = false;
				$this->render_newline = false;
			}
		}
		
		if(in_array($this->tag, $this->parser->option('preserve')))
			$this->inner_whitespace = false;
		
		if($this->content[0] == '/') {
			if($this->content != '/')
				$this->exception('Parse error: self-closing tags cannot have content');
			
			$this->content = substr($this->content, 1);
			$this->self_closing = true;
		} elseif($this->content == '' and in_array($this->tag, $this->parser->option('autoclose')))
			$this->self_closing = true;
		
		if(trim($this->content))
			$this->content = new InterpolatedString(trim($this->content), $this);
		
		if($this->self_closing or $this->content)
			$this->parser->expect_indent(Parser::EXPECT_LESS | Parser::EXPECT_SAME);
		
	}
	
	/**
	 * Renders the parsed tree.
	 */
	public function _render() {
		
		$indent = str_repeat($this->parser->indent_string(), $this->indent_level);
		$open_tag = '<' . $this->tag . (empty($this->attributes) ? '' : ' ' . $this->render_attributes()) . '>';
		$close_tag = '</' . $this->tag . '>';
		
		if($this->self_closing) {
			if($this->parser->option('format') == 'xhtml')
				$open_tag = substr($open_tag, 0, -1) . ' />';
			
			return $indent . $open_tag;
		}
		
		if(empty($this->children))
			return $indent . $open_tag . $this->content . $close_tag;
		
		if($this->inner_whitespace)
			return $indent . $open_tag . "\n" . $this->render_children() . "\n" . $indent . $close_tag;
		
		return
			  $indent . $open_tag
			. ltrim(str_replace("\n" . $this->parser->indent_string(), "\n", $this->render_children()))
			. $close_tag;
		
	}
	
	/**
	 * Extracts a balanced substring from the line.
	 */
	protected function extract_balanced($open, $close) {
		
		if($this->content[0] != $open)
			$this->exception('Sanity error: content does not begin with $open');
		
		$quote = false;
		$escape = false;
		$depth = 0;
		
		for($i = 0; $i < strlen($this->content); $i ++) {
			$c = $this->content[$i];
			
			if($escape) {
				$escape = false;
				continue;
			}
			
			switch($c) {
				case '\\':
					$escape = true;
				break;
				
				case '"':
				case '\'':
					if(!$quote)
						$quote = $c;
					elseif($quote == $c)
						$quote = false;
				break;
				
				case $open:
					if(!$quote)
						$depth ++;
				break;
				
				case $close:
					if(!$quote)
						$depth --;
				break;
			}
			
			if($depth == 0)
				return substr($this->content, 1, $i - 1);
		}
		
		if($depth != 0)
			return false;
		
	}
	
	/**
	 * Explodes a string by another string, given that the string isn't in a quote.
	 */
	protected function quote_safe_explode($delimeter, $string) {
		
		$result = array();
		
		$quote = false;
		$escape = false;
		
		for($i = 0; $i < strlen($string); $i ++) {
			if($string[$i] == '"' or $string[$i] == '\'') {
				if($escape)
					$escape == false;
				elseif(!$quote)
					$quote = $string[$i];
				elseif($quote == $string[$i])
					$quote = false;
				
				continue;
			}
			
			if(!$quote and strpos($string, $delimeter, $i) === $i) {
				$result[] = substr($string, 0, $i);
				$string = substr($string, $i + strlen($delimeter));
			}
		}
		
		$result[] = $string;
		return $result;
		
	}
	
	/**
	 * Renders the attribute string for this tag.
	 */
	protected function render_attributes() {
		
		$attributes = array();
		$class = array();
		$id = array();
		
		foreach($this->attributes as $attribute) {
			list($key, $value) = $attribute;
			$key = (string)$key;
			
			if(empty($value))
				continue;
			
			if($key == 'class') {
				if(is_array($value)) {
					foreach($value as $_value)
						$class[] = (string)$_value;
				} else
					$class[] = (string)$value;
			} elseif($key == 'id') {
				if(is_array($value)) {
					foreach($value as $_value)
						$id[] = (string)$_value;
				} else
					$id[] = (string)$value;
			} else {
				$value = (string)$value;
				if($value == "1") {
					if(substr($this->parser->option('format'), 0, 4) == 'html')
						$attributes[] = $key;
					else
						$attributes[] = $key . '=' . $this->attr($key);
				} else
					$attributes[] = $key . '=' . $this->attr($value);
			}
		}
		
		if(!empty($class)) {
			sort($class);
			$attributes[] = 'class=' . $this->attr(implode(' ', $class));
		}
		
		if(!empty($id))
			$attributes[] = 'id=' . $this->attr(implode('_', $id));
		
		sort($attributes);
		return implode(' ', $attributes);
		
	}
	
}

?>