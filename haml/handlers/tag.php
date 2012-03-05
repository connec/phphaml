<?php

/**
 * tag.php
 */

namespace phphaml\haml\handlers;

use
	\phphaml\Handler,
	\phphaml\NotHandledException,
	\phphaml\haml\nodes,
	\phphaml\haml\Parser,
	\phphaml\haml\PhpValue,
	\phphaml\haml\ruby;

/**
 * The Tag handler handles tag nodes in a HAML source.
 */

class Tag extends Handler {
	
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
	 * Handles the current line in the given parser.
	 * 
	 * This is used instead of the parser appending to the tree itself in order to deal with
	 * potential multiline statements.
	 */
	public static function handle() {
		
		if(!static::$multiline) {
			$node = nodes\Tag::new_from_parser(static::$parser);
			static::parse_start($node);
			
			if(static::$multiline) {
				static::$parser->force_handler(get_called_class());
				static::$parser->expect_indent(Parser::EXPECT_MORE);
			}
		} else {
			$node = static::$parser->context();
			$node->content .= ' ' . static::$parser->content();
			
			switch(static::$multiline) {
				case self::MULTILINE_HTML_ATTRIBUTES:
					static::$multiline = false;
					static::parse_html_attributes($node);
				break;
				case self::MULTILINE_RUBY_ATTRIBUTES:
					static::$multiline = false;
					static::parse_ruby_attributes($node);
				break;
				default:
					$node->exception('Sanity error: unknown multiline modifier');
			}
			
			if(static::$multiline)
			  static::$parser->force_handler(get_called_class());
		}
		
	}
	
	/**
	 * Parses the beginning of a tag line (tag name, classes and ids).
	 */
	protected static function parse_start(nodes\Tag $node) {
		
	  $text_handler = '\phphaml\haml\handlers\Text';
	  
		if($node->content[0] == '%') {
			if(!preg_match(self::RE_TAG, substr($node->content, 1), $match)) {
			  static::$parser->force_handler($text_handler);
			  return static::$parser->handle();
			}
			
			$node->content = substr($node->content, strlen($match[0]) + 1);
			$node->tag_name = $match[0];
		}
		
		$id = false;
		$classes = array();
		while($node->content[0] == '.' or $node->content[0] == '#') {
			$type = $node->content[0] == '.' ? 'class' : 'id';
			
			if(!preg_match($type == 'id' ? self::RE_ID : self::RE_CLASS, substr($node->content, 1), $match)) {
			  $node->remove();
			  throw new NotHandledException();
		  }
			
			$node->content = substr($node->content, strlen($match[0]) + 1);
			
			if($type == 'class')
			  $classes[] = '\'' . $match[0] . '\'';
			else
			  $id = $match[0];
		}
		
		if($classes)
		  $node->attributes[] = array('\'class\'', $classes);
		if($id)
		  $node->attributes[] = array('\'id\'', '\'' . $id . '\'');
		
		static::parse_html_attributes($node);
		
	}
	
	/**
	 * Parses HTML attributes from the content.
	 */
	protected static function parse_html_attributes(nodes\Tag $node) {
		
		if($node->content[0] == '(') {
			$html_attributes = static::extract_balanced($node, '(', ')');
			
			if($html_attributes === false) {
				static::$multiline = self::MULTILINE_HTML_ATTRIBUTES;
				return;
			}
			
			$attributes = array();
			foreach(static::quote_safe_explode(' ', $html_attributes) as $entry) {
				$parts = static::quote_safe_explode('=', $entry);
				
				if(count($parts) != 2)
					$node->exception('Parse error: bad html attribute syntax');
				
				if($parts[1][0] == '"')
				  $parts[1] = ruby\InterpolatedString::compile($parts[1]);
				
				$attributes[$parts[0]] = $parts[1];
			}
			
			foreach($attributes as $attribute => $value)
			  $node->attributes[] = array('\'' . $attribute . '\'', $value);
			
			$node->content = substr($node->content, strlen($html_attributes) + 2);
		}
		
		static::parse_ruby_attributes($node);
		
	}
	
	/**
	 * Parses Ruby style attributes from the content.
	 */
	protected static function parse_ruby_attributes(nodes\Tag $node) {
		
		if($node->content[0] == '{') {
			$ruby_attributes = static::extract_balanced($node, '{', '}');
			
			if($ruby_attributes === false) {
				if($node->content[strlen($node->content) - 1] != ',' and $node->content != '{')
					$node->exception('Parse error: lines in a multiline hash attributes must end with a comma (,)');
				
				static::$multiline = self::MULTILINE_RUBY_ATTRIBUTES;
				return;
			}
			
			$node->content = substr($node->content, strlen($ruby_attributes) + 2);
			$ruby_attributes = new ruby\Hash('{' . $ruby_attributes . '}', $node);
			$node->attributes = array_merge($node->attributes, $ruby_attributes->to_a());
		}
		
		static::parse_end($node);
		
	}
	
	/**
	 * Parses the end of a tag line (self-closing, content) and sets up the parser for the next line.
	 */
	protected static function parse_end(nodes\Tag $node) {
		
	  if($node->content[0] == '<') {
	    $node->content = substr($node->content, 1);
	    $node->trim_inner = true;
	  }
	  
	  if($node->content[0] == '>') {
	    $node->content = substr($node->content, 1);
	    $node->trim_outer = true;
	    $node->render_newline = false;
	    if($node->previous_sibling())
	      $node->previous_sibling()->render_newline = false;
	  }
		
		if(in_array($node->tag_name, static::$parser->option('preserve')))
			$node->trim_inner = true;
		
		if($node->content[0] == '/') {
			if($node->content != '/')
				$node->exception('Parse error: self-closing tags cannot have content');
			
			$node->content = substr($node->content, 1);
			$node->self_closing = true;
		}
		
		if($node->content = trim($node->content)) {
			$text_node = new nodes\Text();
			$text_node->set_from_parser(static::$parser);
			$text_node->root = $node->root;
			$text_node->parent = $node;
			$text_node->indent_level = 0;
			$text_node->content = $node->content;
			Text::parse($text_node);
			
			$node->content = $text_node;
		}
		
		if($node->self_closing or $node->content)
			static::$parser->expect_indent(Parser::EXPECT_LESS | Parser::EXPECT_SAME);
		
	}
	
	/**
	 * Extracts a balanced substring from the line.
	 */
	protected static function extract_balanced(nodes\Tag $node, $open, $close) {
		
		if($node->content[0] != $open)
			$node->exception('Sanity error: content does not begin with $open');
		
		$quote = false;
		$escape = false;
		$depth = 0;
		
		for($i = 0; $i < strlen($node->content); $i ++) {
			$c = $node->content[$i];
			
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
				return substr($node->content, 1, $i - 1);
		}
		
		if($depth != 0)
			return false;
		
	}
	
	/**
	 * Explodes a string by another string, given that the string isn't in a quote.
	 */
	public static function quote_safe_explode($delimeter, $string) {
		
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
				$i = -1;
			}
		}
		
		$result[] = $string;
		return $result;
		
	}
	
}

?>