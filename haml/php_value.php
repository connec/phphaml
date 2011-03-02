<?php

/**
 * php_value.php
 */

namespace phphaml\haml;

/**
 * The PhpValue class produces a PHP/HTML string to render a given input
 * expression or value.
 */
class PhpValue {
  
  /**
   * Compiles PHP/HTML for the given PHP expression.
   */
  public static function compile($value) {
    
    if($value[0] == '"')
      return ruby\InterpolatedString::compile(substr($value, 1, -1));
    else
      return '<?php echo (' . $value . '); ?>';
    
  }
  
}

?>