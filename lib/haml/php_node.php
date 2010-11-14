<?php

namespace hamlparser\lib\haml;

class PhpNode extends Node {
	
	protected static $types = array(
		'do',
		'else',
		'elseif',
		'for',
		'foreach',
		'if',
		'while'
	);
	
	protected $type;
	protected $extra = array();
	
	public function type() {
		
		return $this->type;
		
	}
	
	public function parse() {
		
		$parser = static::$parser_class;
		
		if(substr($this->content, -1) == ':') {
			$this->content = '<?php ' . $this->content . ' ?>';
			return;
		}
		
		$this->content = trim(substr($this->content, 1));
		foreach(static::$types as $type) {
			$re = '/^' . $type . '[^a-zA-Z]/';
			if($type != $this->content and !preg_match($re, $this->content))
				continue;
			
			$this->type = $type;
			break;
		}
		
		if($this->type == 'foreach') {
			if(preg_match('/\((.*?)as/', $this->content, $match))
				$this->extra['iterable'] = trim($match[1]);
			else {
				throw new Exception(
					'Parse error: bad foreach definition - line :line',
					array('line' => $this->line_numer)
				);
			}
		}
		
		if($this->type == 'while') {
			if($previous_sibling = $this->previous_sibling() and $previous_sibling->type() == 'do')
				$parser::expect_indent($parser::EXPECT_SAME | $parser::EXPECT_MORE);
			else {
				throw new Exception(
					'Parse error: while of do-while cannot have children - line :line',
					array('line' => $this->line_number)
				);
			}
		}
		
	}
	
	public function __toString() {
		
		$indent = str_repeat($this->indent_string, $this->indent_level);
		$return = '<?php ';
		$close = $indent . "<?php } ?>\n";
		
		$class = get_called_class();
		$previous_sibling = $this->previous_sibling();
		if(!($previous_sibling instanceof $class))
			$previous_sibling = false;
		
		$next_sibling = $this->next_sibling();
		if(!($next_sibling instanceof $class))
			$next_sibling = false;
		
		switch($this->type) {
			case 'do':
				if($next_sibling and $next_sibling->type() == 'while')
					$close = '';
			break;
			case 'else':
				if($previous_sibling and $previous_sibling->type() == 'foreach')
					$return .= '}} ';
				else
					$return .= '} ';
			break;
			case 'elseif':
				$return .= '} ';
				if($next_sibling and substr($next_sibling->type(), 0, 4) == 'else')
					$close = '';
			break;
			case 'if':
				if($next_sibling and substr($next_sibling->type(), 0, 4) == 'else')
					$close = '';
			break;
			case 'foreach':
				if($next_sibling and $next_sibling->type() == 'else') {
					$return = '<?php if(' . $this->extra['iterable'] . ') { ';
					$close = '';
				}
			break;
			case 'while':
				if($previous_sibling and $previous_sibling->type() == 'do')
					return $indent . $return . ' } ' . $this->content . "; ?>\n" . $this->child_content();
			break;
		}
		
		$return = $indent . $return . $this->content . " { ?>\n" . $this->child_content();
		
		return $return . $close;
		
	}
	
}

?>