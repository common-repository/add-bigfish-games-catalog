<?
/**
 * Site Parse
 * 
 * Site Parse extends Core Template for use in site level parse functions.
 * 
 * 
 * @author William Moffett <william.moffett@bigfishgames.com>
 * @version 0.1
 * @package bfglab_library
 */

/**
 * Site Parse Class
 * @package bfglab_library 
 */
class site_parse {
	
	var $_layout;
	var $_startTag = "{";	
	var $_stopTag = "}";	
	var $_showDebug;

	function site_parse($debug = FALSE)
	{
		$this->_showDebug = ($debug ? TRUE : FALSE);
		if($this->_showDebug){
			self::_debug('site_parse::construct', "Debug is activated!.");
		}
	}
	
	function set_tags($startTag=NULL,$stopTag=NULL)
	{
		if(!isset($startTag) && !isset($stopTag)){
			self::_debug('set_tags', "You must pass both start end end tags. Example: obj->set_tags(startTag,stopTag);");
		}else{
			$this->_startTag = $startTag;
			$this->_stopTag = $stopTag;
			self::_debug('set_tags', "StartTag set to '<strong style='color:#FF0000'> ".$this->_startTag." </strong>' StopTag set to '<strong style='color:#FF0000'> ".$this->_stopTag." </strong>.'");
		}
	}
		
	function parse_layout($source,$vars=array())
	{
/*		print("=========<br />");
		print($source);
		print_r($vars);
		print("=========<br />");
*/		if(count($vars)>0) { 
			while(list($key,$value) = each($vars)) {
					$param_name = $this->_startTag.$key.$this->_stopTag; 
					$source = str_replace($param_name,$value,$source); 
			}
			return($source);
		} else {
			self::_debug('parse_layout', "No strings passed for replacement!");
		}
	}
	
    function _debug($function, $string)
    {
        if ($this->_showDebug)
        {
            echo "<p><strong style=\"color:#FF0000\">Debug message from function $function:</strong> $string</p>\n";
        }
    }	
}

?>