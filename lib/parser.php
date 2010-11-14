<?php

namespace hamlparser\lib;

class Parser {
	
	const RE_INDENT = '/^\s+/';
	const EXPECT_LESS = 1;
	const EXPECT_SAME = 2;
	const EXPECT_MORE = 4;
	const EXPECT_ANY = 7;
	
	protected static $node_class = '\hamlparser\lib\Node';
	protected static $doctypes = array(
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
	protected static $current;
	
	protected $options = array(
		'format' => 'html5'
	);
	protected $expect_indent = self::EXPECT_SAME;
	protected $indent_string = '';
	protected $indent_level = 0;
	protected $line_number = 0;
	protected $line;
	protected $tree;
	protected $node;
	
	public static function doctype($type = 'transitional') {
		
		if(!$type or !isset(static::$doctypes[static::options('format')][strtolower($type)]))
			$type = 'transitional';
		return static::$doctypes[static::options('format')][strtolower($type)];
		
	}
	
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
	
	public function __construct($options = array()) {
		
		$this->options = array_merge($this->options, $options);
		
	}
	
	public function parse($file) {
		
		static::$current = $this;
		
		$node_class = static::$node_class;
		$this->tree = $this->node = new $node_class;
		
		$file = $this->open_file($file);
		while($this->line = rtrim(fgets($file), "\r\n")) {
			$this->line_number ++;
			
			$this->handle_indent();
			$this->expect_indent = self::EXPECT_ANY;
			
			$this->node->add_child();
		}
		
		return rtrim((string)$this->tree);
		
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