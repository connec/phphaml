<?php

/**
 * parser.php
 */

namespace phphaml\haml;

/**
 * The Parser class forms the root of a source document, and handles traversing and delegating the
 * source lines.
 * 
 * The phphaml\Parser class extends the base Parser with HAML specific functionality.
 */

class Parser extends \phphaml\Parser {
	
  /**
   * The header to prepend to the compiled PHP/HTML.
   */
  protected static $header = <<<HEADER
<?php
\$__generate_attributes = function(\$format, \$quote, \$attributes) {
  \$result = array();
  \$classes = array();
  \$ids = array();
  foreach(\$attributes as \$attribute => \$value) {
    if(is_int(\$attribute)) {
      if(\$value[0] == 'class') {
        if(is_array(\$value[1]))
          \$classes = array_merge(\$classes, \$value[1]);
        else
          \$classes[] = \$value[1];
        continue;
      }
      if(\$value[0] == 'id') {
        if(is_array(\$value[1]))
          \$ids = array_merge(\$ids, \$value[1]);
        else
          \$ids[] = \$value[1];
        continue;
      }
      \$attribute = \$value[0];
      \$value = \$value[1];
    }
    
    if(!\$value)
      continue;
    
    if(\$value === true and \$format != 'xhtml') {
      \$result[] = \$attribute;
      continue;
    } elseif(\$value === true) {
      \$result[] = \$attribute . '=' . \$quote . \$attribute . \$quote;
      continue;
    }
    
    if(\$attribute == 'class') {
      \$classes = array_merge(\$classes, \$value);
      continue;
    } elseif(\$attribute == 'id') {
      \$ids = array_merge(\$ids, \$value);
      continue;
    }
    
    \$result[] = \$attribute . '=' . \$quote . \$value . \$quote;
  }
  
  if(!empty(\$classes)) {
    natcasesort(\$classes);
    \$result[] = 'class=' . \$quote . implode(' ', \$classes) . \$quote;
  }
  if(!empty(\$ids))
    \$result[] = 'id=' . \$quote . implode('_', \$ids) . \$quote;
  
  natcasesort(\$result);
  echo implode(' ', \$result);
}
?>
HEADER;
  
	/**
	 * An array of options affecting parsing or output generation.
	 */
	protected $options = array(
		'format' => 'xhtml',
		'escape_html' => false,
		'ugly' => false,
		'suppress_eval' => false,
		'attr_wrapper' => '\'',
		'filename' => false,
		'line' => false,
		'autoclose' => array(
			'meta', 'img',   'link',
			'br',   'hr',    'input',
			'area', 'param', 'col',
			'base'
		),
		'preserve' => array('textarea', 'pre'),
		'encoding' => 'utf-8'
	);
	
	/**
	 * An array of variable substitutions for rendering.
	 */
	protected $variables = array();
	
	/**
	 * Initialises the parser with given source and options.
	 */
	public function __construct($source, array $variables = array(), array $options = array()) {
		
		parent::__construct($source, $options);
		$this->variables = $variables;
		
	}
	
	/**
	 * Renders the parsed tree.
	 */
	public function render() {
		
		if(!$this->line_number)
			$this->parse();
		
		$result = $this->root->render_children();
		var_dump($result);
		$result = static::$header . $result;
		
		ob_start();
		
		StringStream::add_string('result', $result);
		extract($this->variables);
		include 'StringStream://result';
		StringStream::clear('result');
		
		return rtrim(ob_get_clean());
		
	}
	
}

?>