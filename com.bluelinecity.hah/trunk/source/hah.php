<?php
/**
 * Package: HAH Parser
 * 
 * Description:
 * HAH Ain't Haml (but it's close) is a parser and renderer for a domain specific language
 * similar to HAML.
 * 
 * License:
 * Copyright (c) 2011, Bryan English - bryan@bluelinecity.com
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */


if ( !defined('HAH_NL') )
   define( 'HAH_NL', "\r\n" );

if ( !defined('HAH_INDENT') )   
   define( 'HAH_INDENT', "  " );

if ( !defined('HAH_NONE_SINGLE_TAGS') )
   define('HAH_NONE_SINGLE_TAGS',"/script|iframe|textarea/i");

if ( !defined('HAH_VERSION') )
   define('HAH_VERSION',"0.9");

if ( !defined('HAH_CACHE') ){}
   //then don't do anything
   
   
/*
 * Class: HahNode
 * Composite object all hah nodes are based off of.
 */
class HahNode
{
	/*
	 * Property: name
	 * mixed - name of the node instance
	 */
   public $name;

   /*
    * Property: value
    * mixed - value of the node instance
    */
   public $value = '';
   
   /*
    * Property: level
    * int - node distance from the top parent node 
    */
   public $level = 0;
   
   /*
    * Property: attributes
    * array - hash array of node attributes
    */
   public $attributes = array();

   /*
    * Property: children
    * array - Array of child objects, usually HahNode based objects  
    */
   public $children = array();
   
   /*
    * Property: parent
    * Public Object reference to this node's parent. Null if top parent.
    */
   public $parent = null;
   
   /*
    * Property: sibling
    * int - index of where this node exists among its siblings. 
    */
   public $sibling = null;
   
   /*
    * Constructor
    * 
    * Parameters:
    * 	$name - optional name of the node
    * 	$value - optional, value of the node
    */
   public function __construct( $name = null, $value = '' )
   {
      $this->name = $name;
      $this->value = $value;
   }
   
   /*
    * Method: __toString
    * Returns a string representation of the node when used in string context
    */
   public function __toString()
   {
      return $this->value;
   }   
   
   /*
    * Method: setParent
    * Sets this node's parent
    * 
    * Parameters:
    * 	$parent - object to assign as the parent
    */
   public function setParent( $parent )
   {
      $this->parent = $parent;
   }

   /*
    * Method: addChild
    * Adds a child object to this node, taking care of setting its parent and sibling order.
    * 
    * Parameters:
    * 	$child - object to add
    */
   public function addChild( $child )
   {
      $child->setParent( $this );
      $child->sibling = count($this->children); 
      $this->children[] = $child;
   }

   /*
    * Method: setLevel
    * Set's the heirarchy level of the node
    * 
    * Parameters:
    * 	$level - int of the level to set it too
    */
   public function setLevel( $level )
   {
      $this->level = $level;
   }
   
   /*
    * Method: findClosestLevel
    * Given a level will return the node closest to it with out being higher.
    * 
    * Parameters:
    * 	$level - int, level to look for
    * 
    * Return:
    * 	HahNode - parent hahnode which is closest to but not higher than $level
    */
   public function findClosestLevel( $level )
   {
      if ( $this->parent == null || $this->level < $level ) return $this;
      return $this->parent->findClosestLevel( $level );
   }
   
   /*
    * Method: hasChildren
    * Simple test to check if this node has children
    * 
    * Return:
    * 	boolean - true if has children
    */
   public function hasChildren()
   {
      return count($this->children);
   }
   
   /*
    * Method: isSingular
    * Returns if this node has no children or just one which in turn has no children. Mainly for formatting.
    * 
    * Return:
    * 	boolean - true if singular
    */
   public function isSingular()
   {
      return (count($this->children) == 0 || ( count($this->children) == 1 && $this->children[0]->isSingular()));
   }
   
   /*
    * Method: getChildren
    * Returns a string representation of this node's children imploded by an optional seperator
    * 
    * Parameters:
    * 	$sep = optional string to glue the children together
    * 
    * Return:
    * 	string representation of child nodes.
    */
   public function getChildren( $sep = '' )
   {      
      return implode( $sep, $this->children );
   }

   /*
    * Method: getSibling
    * Returns a node's sibling using $offset as a linear locator
    * 
    * Parameters:
    * 	$offset - integer indicating what sibling to return relative to this one
    * 
    * Return:
    * 	mixed - null if no sibling, HahNode if one found.
    */
   public function getSibling( $offset )
   {
      if ( $this->parent == null || $this->sibling + offset < 0 )
         return null;
      else
         return $this->parent->children[ $this->sibling + $offset ];
   }
   
   /*
    * Method: set 
    * Add/update an attribute to this node.
    * 
    * Parameters:
    * 	key/value - if only parameter is considered the value and added as an index. Otherwise is considered the attribute key name.
    * 	value - optional, the value of the attribute. 
    */
   public function set()
   {
      $args = func_get_args();
      
      if ( count($args) == 1 )
         $this->attributes[] = $args[0];
         
      elseif ( count($args) == 2 )
         $this->attributes[$args[0]] = $args[1];
   }

   /*
    * Method: get
    * Returns the value of a named attribute for this node
    * 
    * Parameters:
    * 	$name - string, name of the attribute to look for
    * 	$default - optional, mixed value to return if attribute doesn't exist.
    * 
    * Return:
    * 	Value of the attribute or null if none.
    */   
   public function get( $name, $default = null )
   {
      return isset($this->attributes[$name]) ? $this->attributes[$name] : $default;
   }
   
   /*
    * Method: getIndent
    * Returns a string of spaces or tabs based on this nodes nest level. For code formatting.
    * 
    * Return:
    * 	string - indent for formatting
    */
   public function getIndent()
   {
      return str_repeat(HAH_INDENT, $this->level );
   }
   
   /*
    * Method: toArray
    * Returns a structured array representation of the node tree
    * 
    * Return:
    *	array - set of nested arrays
    */
   public function toArray()
   {
      if ( $this->hasChildren() )
      {
         $ar = array();
         foreach( $this->children as $child )
            $ar[ $child->name ] = $child->toArray();
         
         return $ar;
      }
      else
         return $this->value;
   }
   
   /*
    * Method: pick
    * Accepts a list of parameters and returns the first one that evaluates to true or the last parameter if none found.
    * 
    * Parameters:
    * 	polymorphic, variable number of mixed variables
    * 
    * Return:
    * 	First none empty argument. Or an empty string. 
    */
   static public function pick()
   {
      $args = func_get_args();
      
      foreach ( $args as $arg )
      {
         if ( !empty($arg) ) return $arg;
      }
      
      return '';
   }

   
   /*
    * Method: _onion
    * Takes a string and peels off outer layers based on a left / right delimiter
    * 
    * Parameters:
    * 	$str - source string to peel
    * 	$l - left character delimiter to start the peel
    * 	$r - right character dlimiter to stop the peel
    * 
    * Return:
    * 	array - array of the next level of "onions"
    */
   protected function _onion( $str, $l = '(', $r = ')' )
   {
      $s = 0;
      $tally = -1;
      $found = array();
      
      for ($cnt = 0; $cnt < strlen($str); $cnt++)
      {
         if ( $str{$cnt} == $l && $str{$cnt-1} != '\\' ) 
            if ( $tally == -1 ) {$s = $cnt+1; $tally = 1;}
            else $tally++; 
         
         if ($tally != -1 && $str{$cnt} == $r && $str{$cnt-1} != '\\' ) $tally--;
   
         if ( $tally == 0 )
         {
            $found[] =  substr($str,$s,$cnt - $s);
            $tally = -1;
         }      
      }
      
      return $found;
   }
   
   
   /*
    * Method: _preg_eat
    * preg_eat finds matches and modifies the string by removeing the whole matched pattern. It basically
    * uses preg_match and removes the found string from the source string
    * 
    * Parameters:
    * 	$pattern - string, regexp pattern to look for 
    * 	$string - source string to match against
    * 	$matches - array of matches found, passed by reference
    * 
    * Return:
    * 	boolean - if pattern found true, false otherwise
    * 
    */
   protected function _preg_eat( $pattern, &$string, &$matches )
   {
      if ( preg_match( $pattern, $string, $matches ) )
      {
         $string = preg_replace( '/^' . preg_quote($matches[0],'/') . '/', '', $string );
         return true;        
      }
      
      return false;
   }
   
}


class HahDocument extends HahNode 
{
   
   const ENGINE_ON = 0;
   const ENGINE_OFF = 1;
         
   private $engine_mode = 0;   
   private $engine_trigger = '';  
   private $cursor;         
   private $current_level = 0;
   private $cached = null;
   
   public function __construct( $file )
   {
      if ( !file_exists( $file) ) die( 'HAH Compiler Error: No such file ' . $file );
      
      $this->name = $file;
      $this->value = file( $this->name );
      
      if ( defined('HAH_CACHE') )
         $this->cached = HAH_CACHE . md5(implode($this->value)) . '.php';         
   }

   
   public function compile()
   {
      //parse hah file into a valid node tree//
      $this->cursor = $this;
                  
      //foreach line
      foreach( $this->value as $line )
      {         
         if ( $this->isEngineOff( $line ) ) continue;

         //first stage - establish indent/level and node type to create//
         if ( $this->_preg_eat('/^([\s\t]*)(\:|\?|\!|\/\/|\-|@|\.|\#|<|[a-z0-9_]+)/', $line, $matches ) )
         {            
            if ( $matches[2] == '//' ) continue;

            $this->current_level = $this->level + strlen($matches[1]);                                                
               
            switch ( $matches[2] )
            {                              
               case '!': //include a sub hah file//
                  $this->addImportNode( $line );                                   
               break;
               
               case '-': //create & add php block node//
                  $this->addCodeBlockNode( $line );                  
               break;

               case '?': //create an if block
                  $this->addCodeBlockNode( 'if (' . trim($line) . ')' );                  
               break;

               case ':': //create an elseif or else block
                  if ( trim($line) == '' )
                     $this->addCodeBlockNode( 'else' );
                  else
                     $this->addCodeBlockNode( 'elseif (' . trim($line) . ')' );                  
               break;
               
               case '<': //create & add raw block node//
                  $this->addRawNode( $line );
               break;
               
               case '@': //set an attribute for the current node//
                  $this->addAttribute( $line );
               break;
                              
               case '.': //create tag nodes
               case '#':
                  $this->addTagNode( 'div', $matches[2] . $line );
               break;

               default:
                  //create regular tag node//
                  $this->addTagNode( $matches[2], $line );
               break;                  
            }
         }      
      }       
   }
 
   
   public function __toString()
   {
      //look for cached php//
      if ( defined('HAH_CACHE') )
      {      	
      	if ( !file_exists( $this->cached ) )
      	{
      		$this->compile();
      		$fp = fopen($this->cached,'w');
      		fwrite($fp, implode($this->children));
      		fclose($fp);
      	}
      	
			$__php = "require('". $this->cached ."');";      	      
      }
		else
		{
	      $this->compile();
			$__php = '?>' . implode($this->children) . '<?php ';
		}  
		       
      extract( $this->attributes, EXTR_REFS );
      
      ob_start();
      eval($__php);
      //echo highlight_string(implode($this->children));
      return ob_get_clean();      
   }
           
   
   public function findClosestLevel( $level )
   {
      return $this;
   }    
   
   
   private function addImportNode( $data )
   {
      preg_match('/^([^\(]*)(.*)$/', trim($data), $matches );
         
      if ( preg_match('/\.js$/', $matches[1] ))
      {
         $node = new HahTag('script');
         $node->set('src', $matches[1] );
         $node->set('type','text/javascript');         
      }  
      elseif ( preg_match('/\.css$/', $matches[1] ))
      {
         $node = new HahTag('link');
         $node->set('href', $matches[1] );
         $node->set('type','text/css');
         $node->set('rel','stylesheet');         
      }  
      elseif ( preg_match('/\.(jpg|png|jpeg|gif)$/', $matches[1] ))
      {
         $node = new HahTag('img');
         $node->set('src', $matches[1] );
      }
        
      elseif ( preg_match('/\.csv$/', $matches[1] ))
      {       	
      	//$fp = fopen($matches[1],'r');
      	//$header = fgetcsv($fp);
      	$node = new HahTable('colors,code',array(
      		array('red','#0000ff'),
      		array('green','#ff0000'),
      		array('blue','#00ff00'),
      	));         
      }  
      else 
      {          
         $node = new HahSubDocument( dirname( $this->name ) . DIRECTORY_SEPARATOR . trim($matches[1]) );
      }

      $this->_parseAddAttributes( $matches[2], $node );      
      $this->addNode( $node );
   }
   
   /**
    * @method addAttribute
    * Sets the attribute of the current node using the data passed. ( @att= value )
    * @param string $data - the hah data line for the attribute.
    */
   private function addAttribute( $data )
   {
      if ( !preg_match('/^([^\s\=]+)(\=)?(\S*)\s+(.+)/', $data, $matches) ) 
         return;

      //set as a plain text attribute//
      if ( $matches[2] != '=' )
      {
         $this->cursor->set( $matches[1], trim($matches[4]) );
         return;
      }
      
      //else attribute is a php value//
      $prop = new HahVarTag( trim($matches[4]) );
      
      if ( !empty($matches[3]) )
      {
         //look for no_empty_attribute flag and fix before handing to addAssignmentFilter
         if ( strpos($matches[3], '?') !== false )
         {
            $prop->set('no_empty_attribute', $matches[1]);
            $matches[3] = str_replace('?','',$matches[3]);
         }
                        
         $this->addAssignmentFilter( $prop, $matches[3] );
      }
      
      if ( $prop->get('no_empty_attribute') )
         $this->cursor->set( $prop );
      else
         $this->cursor->set( $matches[1], $prop );                     
   }
   
   
   private function addRawNode( $data )
   {
      preg_match('/^([a-z0-9_\-\?\!]+)/i', $data, $matches);
      
      switch ( $matches[1] )
      {
         case '?php':
            $this->turnOffEngine( '?>' );
         break;      
         case '!html':
         case '!html5':
            $data = "!DOCTYPE HTML>\n";
         break;
         case '!html4strict':
            $data = '!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">' . "\n";
         break;
         case '!html4':
         case '!html4transitional':
            $data = '!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">' . "\n";
         break;
         case '!xhtmlstrict':
            $data = '!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n";
         break;
         case '!xhtml':
         case '!xhtmltransitional':
            $data = '!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' . "\n";
         break;
         default:
            $this->turnOffEngine( '</' . $matches[1] . '>' );
         break;
      }
            
      $node = new HahRaw();
      $node->value = str_repeat( ' ', $this->current_level ) . '<' . $data;
      
      $this->addNode( $node );
   }
  
   
   private function turnOffEngine( $trigger )
   {
      $this->engine_mode = ENGINE_OFF;
      $this->engine_trigger = $trigger;
      $this->engine_trigger_level = $this->current_level - $this->level;
   }
   
   
   private function isEngineOff( $line ) 
   {
      if ( $this->engine_mode == $this->ENGINE_ON ) return false;
      
      if ( preg_match( '/^(\t|\s){'. $this->engine_trigger_level .'}' . preg_quote($this->engine_trigger,'/') . '/', $line ) )
         $this->engine_mode = $this->ENGINE_ON;
        
      $this->cursor->value .= $line;
      
      return true;
   }

   
   private function addCodeBlockNode( $data )
   {
      $node = new HahCodeBlock();
      $node->value = trim($data);      
      $this->addNode( $node );
      
      //special case for if/else blocks//
      if ( preg_match('/^\s*else/',$data) )
      {                                    
         $sibling = $this->cursor->getSibling(-1);
         $sibling->set('leave_block_open', true);
         $this->cursor->value = '} ' . $this->cursor->value; 
      }
   }
   
   
   private function addNode( $node )
   {      
      //navigate the tree to find the correct parent//
      $this->cursor = $this->cursor->findClosestLevel( $this->current_level );      
      
      //add the new node//
      $this->cursor->addChild( $node );      
      
      //move to the new node//
      $this->cursor = $node;

      //update the level//
      $this->cursor->setLevel( $this->current_level );
   }


   private function addTagNode( $tag, $data )
   {
      $node = new HahTag( $tag );

      while ( $this->_preg_eat( '/^(\#|\.)([a-z0-9_\-]+)/i', $data, $matches ) )
      {
         if ( $matches[1] == '#' )
         {
            $node->set('id', $matches[2]);            
         }
         else
         {
            $classes = $node->get('class', '');
            $node->set('class', ( empty($classes) ? '' : $classes . ' ' ) . $matches[2] );            
         }
      }

      //look for params add tag attributes
      if ( $data{0} == '(' )
      {
         $this->_parseAddAttributes( $data, $node );
      }
            
      //look for inline nesting with a comma//
      if ( $this->_preg_eat('/^\,([a-z0-9_\-]+)/i', $data, $matches) )
      {
         $this->addNode( $node );
         $this->current_level++;
         $this->addTagNode( $matches[1], $data );
         return; //bailout, we're done with this node//         
      }
      
      //look for php assignment and modifier/filter
      if ( preg_match( '/^(\=)?(\S*)\s+(.*)$/i', $data, $matches) )
      {         
         //treat assignment as a vartag
         if ( $matches[1] == '=' )
         {
            $vartag = new HahVarTag( trim($matches[3]) );
            
            if ( !empty($matches[2]) )
               $this->addAssignmentFilter( $vartag, $matches[2] );         
            
            $node->addChild( $vartag ); 
         }
         else //treat as normal text
         {
            $node->value = trim($matches[3]);
         }
      }      
      $this->addNode( $node );
   }


   /**
    * 
    * Parses a string off attribute=value pairs and applies them to the node given. It modifies
    * the data string by reference and has special handling for passing values by refrence for 
    * child hahdocuments.
    * @param $data
    * @param $node
    * @param $passthru
    */
   private function _parseAddAttributes( &$data, $node )
   {
      $atts = $this->_onion( $data );
      $attributes = array();
      if ( !empty($atts) )
      {
         $props = $atts[0];
         while ( $this->_preg_eat('/^.*?\s*(\@)?([a-z0-9_\-]+)\=("([^"]*)"|\'([^\']*)\')/i', $props, $matches ) )
         {
            $attval = trim( $matches[3], '\'"' );
            
            if ( $matches[1] == '@' )
               $node->set( $matches[2], new HahVarTag($attval) );            
            else
               $node->set( $matches[2], $attval );
         }
      }
      
      //chomp off attributes from data line//      
      $data = substr( $data, strlen($atts[0]) + 2 );
   }
   
   
   /**
    * addAssignmentFilter - add a filter to a HahVarTag node such as $,#,date format, etc
    * @param $node - object reference to the HahVarTag node filters are to be assigned to
    * @param $filters - string of the filters found in the hah file.
    */
   public function addAssignmentFilter( $node, $filters )
   {
      if ( strpos( $filters, '?' ) !== false )
      {         
         //wrap the node in a conditional block//
         $node->name = 'HahNode::pick('. $node->name .')';
         $this->addCodeBlockNode('if ('. $node->name .' != \'\')');
         $this->current_level++;
         $filters = str_replace('?','',$filters);
      } 
      
      if ( preg_match('/\"([^"]+)\"/', $filters, $matches ) )
         $node->set('date', str_replace("_", " ", $matches[1]));

      elseif ( strpos( $filters, '$' ) !== false )
         $node->set('money','');

      elseif ( strpos( $filters, '#' ) !== false )
         $node->set('number_format','');
         
      elseif ( !empty($filters) )
         foreach ( explode( ',', $filters ) as $filter )
            $node->set($filter,'');
   }         
}



/**
 * A class node representing a single php variable/string being echoed
 * div= $varname, div= "This is my var $name"
 */
class HahVarTag extends HahNode 
{
   public function __toString()
   {
      $code = $this->name;
      $origin = $code; 
      
      //attributes of a vartag are functions to apply//
      foreach( $this->attributes as $key => $value )
      {
         switch ($key)
         {        
            case 'no_empty_attribute': break;
                
            case 'date':
               $code = 'date("'. $value .'",strtotime(' . $code . '))';
            break;

            case 'money':
               $code = '\'$\' . number_format('. $code .',2)';
            break;
            
            default:
               $code = $key . '(' . $code . $value . ')';
            break;
         }
      }
      
      //add inline conditional code treating this as an attribute value, used in html attributes//
      if ( !empty($this->attributes['no_empty_attribute']) )
      {
         $code = 'HahNode::pick(' . $code . ')';
         $code = 'htmlentities(' . $code . ', ENT_QUOTES)';
         $code = '(empty(' . $origin . ')?\'\':\' ' . $this->attributes['no_empty_attribute'] . '="\'.' . $code . '.\'"\')';
      }
      
      return '<?php echo ' . $code .'; ?>';
   }  
   
}


/**
 * A class node representing a php block such as if, while or foreach or just a line
 */
class HahCodeBlock extends HahNode {

   public function __toString()
   {
      $indent = $this->getIndent();
      
      if ( !$this->hasChildren() )
         return $indent . '<?php ' . $this->value .'; ?>';
      
      $output = $indent . '<?php ' . $this->value .' { ?>';

      if ( $this->isSingular() )
         $output .= $this->getChildren();
      else
         $output .= HAH_NL . $this->getChildren(HAH_NL) . HAH_NL;

      if ( !$this->get('leave_block_open',false))
         $output .= ( $this->isSingular() ? '' : $indent ) . '<?php } ?>';
 	     
      return $output;
   }
   
}


/**
 * Represents a node of raw code. Just returns its value.
 */
class HahRaw extends HahNode {}


/**
 * 
 * Represents an included hah document node
 *
 */
class HahSubDocument extends HahNode
{
   public function __toString()
   {                 
      $output = '<?php $__subhahdoc = new HahDocument(\''. $this->name .'\'); ';
      foreach ( $this->attributes as $name => $value )
      {
         $val = ( is_a($value,'HahVarTag') ? $value->name : '"' . $value . '"' );
         $output .= '$__subhahdoc->set(\''. $name .'\','. $val .'); ';
      }
      $output .= 'echo $__subhahdoc; ';
      $output .= 'unset($__subhahdoc); ';
      $output .= ' ?>';
      
      return $output;
   }
}


/**
 * The most common type of node, representing an HTML/XML tag.
 */
class HahTag extends HahNode 
{   
   public function __toString()
   {
      $indent = $this->getIndent();
      $closed = ( !$this->hasChildren() && $this->value == '' && !preg_match(HAH_NONE_SINGLE_TAGS,$this->name) );

      $output = ($this->parent && $this->parent->isSingular() ? '' : $indent ) . '<' . $this->name;
      
      foreach ( $this->attributes as $key => $value )
      {  
         if ( is_numeric($key) )
         {
            $output .= $value;
         }
         else
         {
            $output .= ' ' . $key . '="';
            $output .= ( (is_object($value) && get_class($value) == 'HahVarTag') ? $value : htmlspecialchars($value) );
            $output .= '"';
         }
      }
      
      if ( $closed ) return $output .= ' />';
      
      $output .= '>' .  $this->value;
      
      if ( $this->isSingular() )  
         $output .= $this->getChildren();         

      elseif ( $this->hasChildren() )
         $output .= HAH_NL . $this->getChildren(HAH_NL) . HAH_NL .  $indent;
         
      return $output . '</'. $this->name .'>';         
   }         
}


class HahTable extends HahNode 
{   
   public function __toString()
   {
   	//create a table
   	$table = new HahTag('table');
   	
   	$thead = new HahTag('thead');
   	$tr = new HahTag('tr');
   	foreach ( explode(',',$this->name) as $header )
   		$tr->addChild( new HahTag('th', $header) );
   	$thead->addChild($tr);
   	
   	$table->addChild($thead);
   	
   	foreach ( $this->value as $row )
   	{
   		$tr = new HahTag('tr');
   		$tr->set('class',ff('even','odd'));
   		foreach ( $row as $index => $cell )
   		{
   			$td = new HahTag('td',htmlspecialchars($cell));
   			$tr->addChild($td);
   		}
   		$table->addChild($tr);
   	}
   	
   	$this->addChild($table);
   	
   	return (string) $table;
   }         
}

