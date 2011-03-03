<?php

/**
 * php.php
 */

namespace phphaml\haml\nodes;

use \phphaml\haml\handlers;

/**
 * The Php class represents a PHP node in a Haml document.
 */

class Php extends Node {
  
  /**
   * Indicates that a newline should be printed after this node's content.
   */
  public $render_newline = false;
  
  /**
   * Indicates that an indentation string should be printed before this node's
   * content.
   */
  public $render_indent = false;
  
  /**
   * Indicates whether this PHP node represents a control structure.
   */
  public $control_structure = false;
  
  /**
	 * Generates PHP/HTML code for this node and its children.
	 */
	public function render() {
	  
	  if($this->control_structure) {
	    foreach($this->children as $child)
	      $child->indent_level --;
      
	    if($this->control_structure == 'elseif' or $this->control_structure == 'else')
	      $return = $this->control_structure;
      elseif($this->control_structure == 'foreach') {
        list($array, $other) = handlers\Tag::quote_safe_explode(' as ', $this->content);
        $array = trim($array);
        $other = trim($other);
        $return = '<?php $__array = ' . $array . '; if(!empty($__array)) { '
          . 'foreach($__array as ' . $other . ') { ?>';
      } else
        $return = '<?php ' . $this->control_structure;
      
	    switch($this->control_structure) {
	      case 'if':
	      case 'elseif':
	      case 'while':
	      case 'for':
	      case 'switch':
	        $return .= '(' . $this->content . ') { ?>';
        break;
        case 'do':
        case 'else':
          $return .= ' { ?>';
        break;
        case 'case':
          foreach($this->children as $child)
	          $child->indent_level --;
          $return .= ' ' . $this->content . '; ?>';
        break;
	    }
	    
	    $return .= $this->render_children() . '<?php echo "\n"; ?>';
	    
	    if($this->control_structure == 'case');
      elseif($this->control_structure == 'if' or $this->control_structure == 'elseif') {
        $next = $this->next_sibling();
        if($next and get_class($next) == get_class() and
          ($next->control_structure == 'elseif' or $next->control_structure == 'else'))
          $return .= '<?php } ';
        else
          $return .= '<?php } ?>';
      } elseif($this->control_structure == 'foreach') {
        $next = $this->next_sibling();
        if($next and get_class($next) == get_class() and $next->control_structure == 'else')
          $return .= '<?php }} ';
        else
          $return .= '<?php }} ?>';
      } else
        $return .= '<?php } ?>';
      
      return $return;
	  }
	  
	  return '<?php ' . $this->content . '; ?>';
	  
	}
  
}

?>