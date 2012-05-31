<?php
/*
 * Package: HAH Parser
 * 
 * Description:
 * HAH Ain't Haml (but it's close) is a parser and renderer for a domain specific language
 * similar to HAML.
 * 
 * License:
 * Copyright (c) 2012, Bryan English - bryan@bluelinecity.com
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
   define('HAH_NONE_SINGLE_TAGS',"/script|iframe|textarea|div/i");

if ( !defined('HAH_VERSION') )
   define('HAH_VERSION',"1.3");

if ( !defined('HAH_ASSETS') )
   define('HAH_ASSETS', dirname($_SERVER['SCRIPT_FILENAME']) . DIRECTORY_SEPARATOR . 'haha' . DIRECTORY_SEPARATOR);
   
if ( !defined('HAH_CACHE') ){}
   //then don't do anything
   
if ( !defined('HAH_DEBUG') )
   define('HAH_DEBUG',false);
   
   
   
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
    * Method: getTop
    * This crawls up the node tree and returns the top node
    * 
    * Return:
    * 	$parent - the top parent, HAHDocument Node.
    */
   public function getTop()
   {
      if ( $this->parent )
         return $this->parent->getTop();
      else
         return $this;
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
    * Method: merge
    * Merges an array passed in with its own attributes array
    *
    * Parameters:
    *    $a - associative array to merge with this node's attributes
    */
   public function merge( $a )
   {
      $this->attributes = array_merge_recursive( $this->attributes, $a );
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
    * Method: date
    * Formats a date ( if given one ) according the the first arguments format string
    * 
    * Parameters:
    * 	polymorphic, variable number of mixed variables. 1st is the date format and rest are one
    *   or more variables to try ( uses <HahNode::pick()> )
    * 
    * Return:
    * 	Formatted date. Or an empty string. 
    */
   static public function date()
   {
      $args = func_get_args();
      $format = array_shift($args);
      
      $date = call_user_func_array(array('HahNode','pick'),$args);
      
      if ( empty($date) )
         return '';
      else 
         return date($format, strtotime($date)); 
   }
   

   /*
    * Method: money
    * Formats a number ( if given one ) as money.
    * 
    * Parameters:
    * 	polymorphic, variable number of mixed variables. 
    * 
    * Return:
    * 	Formatted number. Or an empty string. 
    */   
   static public function money()
   {
      $args = func_get_args();
      
      $money = call_user_func_array(array('HahNode','pick'),$args);
      
      if ( empty($money) )
         return '';
      else
      {
         $parts = explode('.',number_format($money,2));
         return '<span class="dollars">$'. $parts[0] .'</span><span class="cents">.'. $parts[1] .'</span>';   
      }
   }

   
   /*
    * Method: htmllist
    * Takes an array and formats it as an html list (ul,ol,dl)
    * 
    * Parameters:
    *    $data - array of values to make into a list
    *    $type - type of list to create
    * 
    * Return:
    * 	html list
    */   
   static public function htmllist( $data, $type = 'ul')
   {      
      if ( empty($data) || !is_array($data) )
         return '';
      else
      {
         switch ( $type )
         {
            case 'ul':
            case 'unordered':
               return '<ul><li>' . implode('</li><li>',$data) . '</li></ul>';
            break;
            case 'ol':
            case 'ordered':
               return '<ol><li>' . implode('</li><li>',$data) . '</li></ol>';
            break;
            case 'dl':
            case 'definition':
               $content = '<dl>';
               foreach ( $data as $dt => $dd )
                  $content .= '<dt>' . $dt . '</dt><dd>' . $dd . '</dd>';
               return $content . '</dl>';
            break;
         }
      }
   }
   
   
   /*
    * Method: table
    * Creates a simple html table from a 2-dimentional array.
    * 
    * Parameters:
    *    $data - array, of data to populate the table with.
    *    $id - string, id of the table
    *
    * Return:
    *    string, html code of the table.
    */   
   static public function table( $data, $id )
   {      
      if ( empty($data) || !is_array($data) ) return '';           
   
      $table = '<table id="'. htmlspecialchars($id) .'"><thead><tr><th>';
      $table .= implode('</th><th>', array_shift($data) );
      $table .= '</th></thead><tbody>';
      foreach ( $data as $key => $row )
         $table .= '<tr><td>' . implode('</td><td>',$row) . '</td></tr>';
      $table .= '</tbody></table>';
      
      return $table;
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
         if ( $str{$cnt} == $l && ($cnt == 0 || $str{$cnt-1} != '\\') ) 
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


/*
 * Class: HahDocument
 * The frontend class for using HAH to render files. This represents a single HAH document
 * which is loaded then queried to return a rendered file.
 */
class HahDocument extends HahNode 
{

   /*
    * Constant: ENGINE_ON
    * int - used to indicate if the parsing engine is on.
    */
   const ENGINE_ON = 0;

   /*
    * Constant: ENGINE_OFF
    * int - used to indicate if the parsing engine is off.
    */   
   const ENGINE_OFF = 1;

   /*
    * Property: engine_mode
    * int - state variable of the current parser <ENGINE_ON>, <ENGINE_OFF>
    */
   private $engine_mode = 0;

   /*
    * Property: engine_trigger
    * string - pattern to look for before turning the engine back on
    */
   private $engine_trigger = '';  
   
   /*
    * Property: cursor
    * HahNode object - reference to the current node being worked on.
    */
   private $cursor;

   /*
    * Property: current_level
    * int - numerical depth of the current node we're processing
    */
   private $current_level = 0;
   
   /*
    * Property: cached
    * string - filename of the cache file if one.
    */
   private $cached = null;
   
   
   /*
    * Constructor
    * Creates a HahDocument checking for a cached document based on an MD5 hash of the file's contents.
    * 
    * Parameters:
    * 	$file - string, path to the hah document to load and render.
    */
   public function __construct( $file )
   {
      if ( !file_exists( $file) ) die( 'HAH Compiler Error: No such file ' . $file );
      
      $this->name = $file;
      $this->value = file( $this->name );
      
      //if cache is set then look for a cached file//
      if ( defined('HAH_CACHE') && !HAH_DEBUG )
         $this->cached = HAH_CACHE . md5(implode($this->value)) . '.php';         
   }

   
   /*
    * Method: compile
    * Does a line-by-line file parse of the currently loaded hah document and creates the composite
    * HahNode tree.
    * 
    * 
    */
   public function compile()
   {
      //parse hah file into a valid node tree//
      $this->cursor = $this;
                  
      //foreach line
      foreach( $this->value as $line )
      {         
         if ( $this->isEngineOff( $line ) ) continue;

         //first stage - establish indent/level and node type to create//
         if ( $this->_preg_eat('/^([\s\t]*)(\:|\?|\!|\/\/|\-|@|\.|\#|\$|<|[a-z0-9_][a-z0-9_\-]*)/', $line, $matches ) )
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

               case '$': //create a variable node//
                  $this->addVarNode( $line );
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
 
   
   /*
    * Method: __toString
    * Compiles and executes the hah document returning the results.
    * 
    * Return:
    * 	String of post-parsed php code.
    */
   public function __toString()
   {
      //look for cached php//
      if ( defined('HAH_CACHE') && !HAH_DEBUG )
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
      $__result = eval($__php);
		 
      if ( $__result === false && HAH_DEBUG )
         echo $this->formatSource( $__php );
      
      return ob_get_clean();      
   }
   
   
   /*
    * Method: formatSource
    * Takes a string and formats and adds line-numbers for use in viewing the source of compiled HAH code
    * 
    * Parameters:
    * 	$code - the php to format
    * 
    * Return:
    * 	string - html formatted code ready for echoing
    */
   public function formatSource( $code )
   {
      $lines = explode('<br />', highlight_string($code, true));
      foreach ( $lines as $i => $line )
         $lines[$i] = '<span>' . str_pad($i,5,'0',STR_PAD_LEFT) . '&nbsp;</span>' . $line;
      echo '<br />' . implode('<br />', $lines);       
   }
   
   
   /*
    * Method: findClosestLevel
    * Returns this document since it is the top of the top.
    * 
    * Parameters:
    * 	$level - int, but really doesn't do anything in this class
    * 
    * Return:
    * 	HahDocument - this
    */
   public function findClosestLevel( $level )
   {
      return $this;
   }    
   
   
   /*
    * Method: addImportNode
    * Handles the haha import command (!). Is able to create nodes to handle the following file types:
    *
    * Import Types:
    * 	js - creates html script tags
    * 	css - creates css link tags
    * 	jpg,png,jpeg,gif - creates image tags
    * 	* - treats as another HAH document
    */
   private function addImportNode( $data )
   {
      preg_match('/^([^\(]*)(.*)$/', trim($data), $matches );
      
      //javascript file//
      if ( preg_match('/\.js$/i', $matches[1] ))
      {
         $node = new HahTag('script');
         $node->set('src', $matches[1] );
         $node->set('type','text/javascript');         
      }  
      //css file//
      elseif ( preg_match('/\.css$/i', $matches[1] ))
      {
         $node = new HahTag('link');
         $node->set('href', $matches[1] );
         $node->set('type','text/css');
         $node->set('rel','stylesheet');         
      }  
      //image file//
      elseif ( preg_match('/\.(jpg|png|jpeg|gif)$/i', $matches[1] ))
      {
         $node = new HahTag('img');
         $node->set('src', $matches[1] );
      }
      //raw php include//
      elseif ( preg_match('/\.(php|html)$/i', $matches[1] ))
      {
         $node = new HahCodeBlock( null, "include('". $matches[1] ."');" );
      }
      //a variable include//
      elseif ( preg_match('/^\$/i', $matches[1] ) )
      {
         $node = new HahSubDocument( $matches[1] );         
      }
      //hah sub document or hah asset//
      else 
      {
         $fp = dirname( $this->name ) . DIRECTORY_SEPARATOR . trim($matches[1]);
         if ( file_exists($fp) )
            $node = new HahSubDocument( $fp );
         else 
            $node = new HahSubDocument( HAH_ASSETS . trim($matches[1]) );
      }

      $this->_parseAddAttributes( $matches[2], $node );      
      $this->addNode( $node );
   }
   
   
   /*
    * Method: addAttribute    
    * Handles the HAH Attribute command (@) for parent tags
    * 
    * Parameters:
    * 	$data - string, the hah line in question
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
   
   
   /*
    * Method: addRawNode
    * Handles raw node blocks in HAH files like html tags, php code and special case HTML doctypes,
    * Also turns the parsing engine off and sets the trigger for when it needs to turn back on.
    * 
    * Parameters:
    * 	$data - string, line from the hah document 
    */
   private function addRawNode( $data )
   {
      preg_match('/^([a-z0-9_\-\?\!]+)/i', $data, $matches);
      
      switch ( $matches[1] )
      {
         case '?php':
            $this->turnOffEngine( '?>' );
         break;
         case '?xml':
            $data = "?php echo '<?xml version=\"1.0\" encoding=\"UTF-8\" ?>'; ?>\n";
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
  
   
   /*
    * Method: addVarNode
    * Adds a php variable node for straight out echoing indicated by the ($) command.
    * 
    * Parameters:
    * 	$data - string, line from the hah document
    */
   private function addVarNode( $data )
   {
      preg_match('/^([^\(]*)(.*)$/', trim($data), $matches );
      
      $node = new HahVarTag( trim('$' . $matches[1]) );
      
      $this->_parseAddAttributes( $matches[2], $node );            
      
      $this->addNode( $node );
   }

   
   /*
    * Method: turnOffEngine
    * Handles turning off the parse engine and setting the trigger for when it comes back on.
    * 
    * Parameters:
    * 	$trigger - string, pattern to look for to turn the engine back on.
    */
   private function turnOffEngine( $trigger )
   {
      $this->engine_mode = HahDocument::ENGINE_OFF;
      $this->engine_trigger = $trigger;
      $this->engine_trigger_level = $this->current_level - $this->level;
   }
   
   
   /*
    * Method: isEngineOff
    * Determines the state of the engine and returns true if it's off, false otherwise. Also
    * handles checking the trigger to see if it should turn back on and does so if needed.
    * 
    * Parameters:
    * 	$line - string, the currently processing HAH line. 
    * 
    * Return:
    * 	Boolean
    */
   private function isEngineOff( $line ) 
   {
      if ( $this->engine_mode == HahDocument::ENGINE_ON ) return false;
      
      if ( preg_match( '/^(\t|\s){'. $this->engine_trigger_level .'}' . preg_quote($this->engine_trigger,'/') . '/', $line ) )
         $this->engine_mode = HahDocument::ENGINE_ON;
        
      $this->cursor->value .= $line;
      
      return true;
   }

   
   /*
    * Method: addCodeBlockNode
    * Adds a <HahCodeBlock> node to the tree with the data passed to it.
    * 
    * Parameters:
    * 	$data - string, data for the raw code node
    */
   private function addCodeBlockNode( $data )
   {
      $node = new HahCodeBlock();
      $node->value = trim($data);      
      $this->addNode( $node );
      
      //special case for if/else blocks//
      if ( preg_match('/^\s*else/i',$data) )
      {                                    
         $sibling = $this->cursor->getSibling(-1);
         $sibling->set('leave_block_open', true);
         $this->cursor->value = '} ' . $this->cursor->value; 
      }
      
      //TODO: special case for do while
   }
   

   /*
    * Method: addNode
    * General purpose method to add a node to the tree taking into account
    * the indent level and updating the cursor appropriatly
    * 
    *  Parameters:
    *  	$node - HahNode to add
    */
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


   /*
    * Method: addTagNode
    * Adds a TagNode object to the document tree at the current cursor.
    * 
    * Parameters:
    *    $tag - the tag name to create
    *    $data - the rest of the tag options as specified in the hah line
    */
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


   /*
    * Method: _parseAddAttributes
    * Parses a string off attribute=value pairs and applies them to the node given. It modifies
    * the data string by reference and has special handling for passing values by refrence for 
    * child hahdocuments.
    *
    * Parameters:
    *    $data - reference string, attributes to parse out
    *    $node - node to apply the attributes too
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
               $node->set( $matches[2], new HahVarTag('"' . $attval . '"') );            
            else
               $node->set( $matches[2], $attval );
         }
      }
      
      //chomp off attributes from data line//
	  $len = 2 + ( isset($atts[0]) ? strlen($atts[0]) : 0 );
      $data = substr( $data, $len );
   }
   
   
   /*
    * Method: addAssignmentFilter
    * Add an output filter to a HahVarTag node such as ?,$,#,date format.
    *
    * Parameters:
    *    $node - object reference to the HahVarTag node filters are to be assigned to
    *    $filters - string of the filters found in the hah file.
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



/*
 * Class: HahVarTag
 * A class node representing a single php variable/string being echoed. Attributes can 
 * control how the variable is output in the tree.
 *
 * Example:
 *    div= $varname, div= "This is my var $name"
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
            case 'no_empty_attribute':
            break;
            
            case 'date':  
               $code = 'HahNode::date("'. $value .'",' . $code . ')';
            break;

            case 'money':
               $code = 'HahNode::money('. $code .')';
            break;
            
            case 'list':
               $code = 'HahNode::htmllist('. $code .',"'. $value .'")';
            break;

            case 'table':
               $code = 'HahNode::table('. $code .',"'. $value .'")';
            break;
            
            default:
               $code = $key . '(' . $code . ')';
            break;
         }
      }
      
      //add inline conditional code treating this as an attribute value, used in html attributes//
      if ( !empty($this->attributes['no_empty_attribute']) )
      {
         $code = 'HahNode::pick(' . $code . ')';
         $code = '((' . $code . ' == \'\')?\'\':\' ' . $this->attributes['no_empty_attribute'] . '="\'.htmlentities(' . $code . ', ENT_QUOTES).\'"\')';
      }
      
      return '<?php echo ' . $code .'; ?>';
   }  
}


/*
 * Class: HahCodeBlock
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


/*
 * Class: HahRaw
 * Represents a node of raw code. Just returns its value.
 */
class HahRaw extends HahNode {}


/*
 * Class: HahSubDocument 
 * Represents an included hah document node. Outputs all the code required to create a new hahdocument.
 *
 */
class HahSubDocument extends HahNode
{
   public function __toString()
   {  
      
      $output = '<?php $__subhahdoc = new HahDocument("'. $this->name .'"); ';
      
      $doc = $this->getTop();

		//pass all top level vars down to this subdoc//
      foreach ( $doc->attributes as $name => $value )
         if ( !isset($this->attributes[$name]) && !is_numeric($name) )
            $this->attributes[$name] = new HahVarTag('$' . $name);
      
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


/*
 * Class: HahTag
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
