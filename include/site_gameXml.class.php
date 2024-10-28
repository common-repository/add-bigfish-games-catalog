<?php
/**
 * site_gameXML
 * 
 * Site Game XML is a helper class used 
 * to convert the bfggamexml into an array. 
 * 
 * @author William Moffett <william.moffett@bigfishgames.com>
 * @version 0.1
 * @package bfglab_library 
 * 
 */
class site_gameXML {

		var $parser;					// our xml parser object
		var $current_game;		// game currently being parsed
		var $games;						// collection of parsed games
		var $initgame  = false;
		var $source;
		var $_showDebug;
		
		function site_gameXML ($source = FALSE ,$debug = FALSE) 
	    {  
			$this->source = ($source ? $source : FALSE);
			$this->_showDebug = ($debug ? TRUE : FALSE);
			if($this->_showDebug){
				$this->_debug('site_gameXML::construct', "Debug is activated!.");
			}
			if($this->source){
				$this->xml_parser_init ($this->source);
			}
	    }
	    
		function xml_parser_init ($source = FALSE ,$debug = FALSE) 
		{  
	        if (!function_exists('xml_parser_create')){
	            $this->error( "Failed to load PHP's XML Extension. " . 
	                          "http://www.php.net/manual/en/ref.xml.php",
	                           E_USER_ERROR );
	        }
	        # we'll do a quick scrub of the content here.
	        $source = $this->toRss($source);
	        
	        list($parser, $source) = $this->create_xml_parser($source);
	        
	        if(!is_resource($parser)){
	            $this->_debug('site_gameXML', "Failed to create an instance of PHP's XML parser. " .
	                          "http://www.php.net/manual/en/ref.xml.php");              
	        }
	        
	        $this->parser = $parser;
	        
	        # pass in parser, and a reference to this object
	        # setup handlers
	        #
	        xml_set_object($this->parser, $this);
			xml_set_element_handler($this->parser, 'xml_start_element', 'xml_end_element');
			xml_set_character_data_handler( $this->parser, 'xml_cdata'); 
	        $status = xml_parse( $this->parser, $source );
	        
	        if(! $status )
	        {
	            $errorcode = xml_get_error_code( $this->parser );
	            if( $errorcode != XML_ERROR_NONE ){
	                $xml_error = xml_error_string( $errorcode );
	                $error_line = xml_get_current_line_number($this->parser);
	                $error_col = xml_get_current_column_number($this->parser);
	                $errormsg = "{$xml_error} at line {$error_line}, column {$error_col}";
	                $this->_debug('site_gameXML::xml_parse', $errormsg);               
	            }
	        }
	        xml_parser_free($this->parser);
	        return $this->games;
	    }   
	
	    function xml_start_element($p, $element, $attrs="")
	    {
	        if ($element == 'GAME'){
	             $this->initgame = true;
	        }
	        $this->element = $element;
	    }
	    
	    function xml_cdata ($p, $data) 
	    {
	 		if($this->initgame == true && $this->element != 'GAME'){
				if(empty($this->current_game)){ 
					$this->current_game = $this->create_game_array(); 
				}
				if($this->element != 'IMAGES' && $this->element != 'SYSTEMREQ' && $this->element != 'PC' && $this->element != 'EM'){
					if(!empty($data) && array_key_exists($this->element,$this->current_game)){
						$this->current_game[''.$this->element.''] .= trim($data);
					}
				}
			}
		}    
	   
	    function xml_end_element($p, $element, $attrs="")
	    {
	    	if ($element == 'GAME'){
	            $this->games[] = $this->current_game;
	            $this->current_game = $this->create_game_array();
	            $this->initgame = false;
	        }
	    	
	    }
	    
	    function create_xml_parser($source, $out_enc="", $in_enc="", $detect="")
	    {
	     	return array(xml_parser_create(), $source);
	    }	
	    
	    function create_game_array()
	    {
			return array('GAMEID'=>NULL,
					'GAMENAME'=>NULL,
					'GENREID'=>NULL,
					'GENRENAME'=>NULL,
					'SHORTDESC'=>NULL,
					'MEDDESC'=>NULL,
					'LONGDESC'=>NULL,
					'BULLET1'=>NULL,
					'BULLET2'=>NULL,
					'BULLET3'=>NULL,
					'BULLET4'=>NULL,
					'BULLET5'=>NULL,
					'FOLDERNAME'=>NULL,
					'OFFERING'=>NULL,
					'PRICE'=>NULL,
					'INSTALLER'=>NULL,
 					'SMALL'=>NULL,
					'MED'=>NULL,
					'SUBFEATURE'=>NULL,
					'FEATURE'=>NULL,
					'THUMB1'=>NULL,
					'THUMB2'=>NULL,
					'SCREEN1'=>NULL,
					'SCREEN2'=>NULL,
					'GAMEURL'=>NULL,
					'DOWNLOADURL'=>NULL,
					'BUYURL'=>NULL,
					'DOWNLOADIFRAME'=>NULL,
					'BUYIFRAME'=>NULL,
					'GAMERANK'=>NULL,					
					'RELEASEDATE'=>NULL,
					'GAMESIZE'=>NULL,
					'SYSREQOS'=>NULL,
					'SYSREQDX'=>NULL,
					'SYSREQMHZ'=>NULL,
					'SYSREQVIDEO'=>NULL,
					'SYSREQMEM'=>NULL,
					'SYSREQHD'=>NULL,
					'SYSREQ3D'=>NULL,
					'SYSREQOTHER'=>NULL);   	
	    }
	    
	    function toRss($string,$option="")
		{ 
			$option = strtoupper($option);
			
			if($option=='CDATA'){
				return "<![CDATA[ $string ]]>";
			}else{
				$search = array("&", "'","", "<BR>","<br />","'");
				$replace = array("&#38;", "&#39;", "&#39;", "", "", "&#39;");
				$string = trim(str_replace($search,$replace,$this->convertUTF8($string)));
				return $string;
			}
		}
	
		function convertUTF8($string)
		{
			/**
			 * Detect if string is ASCII if so convert to utf-8
			 * 
			 * for more info see the following url
			 * http://us3.php.net/manual/en/function.mb-detect-encoding.php
			 */
			 if(function_exists("mb_convert_encoding")){
			 	return mb_convert_encoding($string, "UTF-8", "ASCII");
			 }else{
			 	return $string;
			 }
		}
	    
	    function _debug($function, $string)
		{
		   	global $sl;
		   	if ($this->_showDebug){
		 	    if(is_object($sl->class['site_debug'])){
			    	$sl->class['site_debug']->_debug($function, $string);
			    	return;
			    }
	            echo "<p><strong style=\"color:#FF0000\">$function:</strong> $string</p>\n";
	        }
	    }

}
?>