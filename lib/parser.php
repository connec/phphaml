<?php

namespace hamlparser\lib;

abstract class Parser {
	
	const RE_INDENT = '/^\s+/';
	const EXPECT_LESS = 1;
	const EXPECT_SAME = 2;
	const EXPECT_MORE = 4;
	const EXPECT_ANY = 7;
	
	protected static $current;
	
	protected $source;
	protected $options;
	protected $output;
	protected $expect_indent = self::EXPECT_SAME;
	protected $indent_string = '';
	protected $indent_level = 0;
	protected $line_number = 0;
	protected $line;
	protected $tree;
	protected $node;
	
	public static function options($key = null) {
		
		if(!$key)
			return static::$current->options;
		else
			return static::$current->options[$key];
		
	}
	
	public static function expect_indent($expect_indent) {
		
		static::$current->expect_indent = $expect_indent;
		
	}
	
	public static function line() {
		
		return static::$current->line;
		
	}
	
	public static function line_number() {
		
		return static::$current->line_number;
		
	}
	
	public static function indent_string() {
		
		return static::$current->indent_string;
		
	}
	
	public static function indent_level() {
		
		return static::$current->indent_level;
		
	}
	
	public static function node() {
		
		return static::$current->node;
		
	}
	
	public function __construct($source, $options = array()) {
		
		$this->source = $source;
		$this->options = array_merge($this->options, $options);
		
		if(is_file($this->source))
			$this->source = open_file($this->source);
		else
			$this->source = explode("\n", str_replace(array("\r\n", "\r"), "\n", $this->source));
		
	}
	
	public function parse() {
		
		static::$current = $this;
		
		$node_class = static::$node_class;
		$this->tree = $this->node = new $node_class;
		
		while($this->line = rtrim($this->get_line(), "\r\n")) {
			$this->line_number ++;
			
			$this->handle_indent();
			$this->expect_indent = self::EXPECT_ANY;
			
			if(trim($this->line) != '')
				$this->node->add_child();
		}
		
		$this->output = rtrim((string)$this->tree);
		return $this->output;
		
	}
	
	protected function get_line() {
		
		if(is_array($this->source)) {
			$line = current($this->source);
			next($this->source);
			return $line;
		}
		
		return fgets($this->source);
		
	}
	
	protected function open_file($file) {
		
		$sub = array('file' => $file);
		if(!file_exists($file))
			throw new Exception('File error: file does not exist - :file', $sub);
		if(!is_readable($file))
			throw new Exception('File error: file is not readable - :file', $sub);
		if(!($fh = fopen($file, 'r')))
			throw new Exception('File error: file could not be opened - :file', $sub);
		return $fh;
		
	}
	
	protected function handle_indent() {
		
		$sub = array('line' => $this->line_number);
		
		if(!preg_match(self::RE_INDENT, $this->line, $match)) {
			$indent_level = 0;
		} else {
			if(!$this->indent_string) {
				$this->indent_string = $match[0];
				$indent_level = 1;
			} elseif(str_replace($this->indent_string, '', $match[0]) == '') {
				$indent_level = substr_count($match[0], $this->indent_string);
			} else {
				throw new Exception('Parse error: mixed indentation - line :line', $sub);
			}
			$this->line = substr($this->line, strlen($match[0]));
		}
		
		$diff = $indent_level - $this->indent_level;
		
		if($diff < 0) {
			if(!($this->expect_indent & self::EXPECT_LESS))
				throw new Exception('Parse error: unexpected indent decrease - line :line', $sub);
			for($i = 0; $i > $diff; $i --)
				$this->node = $this->node->parent();
		}
		
		if($diff > 0) {
			if(!($this->expect_indent & self::EXPECT_MORE))
				throw new Exception('Parse error: unexpected indent increase - line :line', $sub);
			if($diff > 1)
				throw new Exception('Parse error: indent increased by more than 1 - line :line', $sub);
			$this->node = $this->node->last_child();
		}
		
		if($diff == 0 and !($this->expect_indent & self::EXPECT_SAME))
				throw new Exception('Parse error: expected indent to change - line :line', $sub);
		
		$this->indent_level = $indent_level;
		
	}
	
}

?>