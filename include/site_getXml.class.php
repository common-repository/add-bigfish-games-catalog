<?php
/**
 *  site_getXml
 * 
 *  This code is part of the e107 website system.
 *  Released under the terms and conditions of the
 *  GNU General Public License (http://gnu.org).
 * 
 * 	@author Sweetas 
 *  
 * Hacked Apart by William Moffett <william.moffett@bigfishgames.com>
 * Thank you for the class bro..... Cheers Que~
 * 	 
 */

class site_getXml{
	var $error;
	var $xmlFileContents;

	function site_getXml($address="",$debug = FALSE)
	{
		$this->_showDebug = ($debug ? TRUE : FALSE);
		if($this->_showDebug){
			$this->_debug('site_getXml::construct', "Debug is activated!.");
		}		
	}
	
	function getRemoteXmlFile($address)
	{
		if(function_exists("curl_init"))
		{
			$cu = curl_init (); 
			curl_setopt($cu, CURLOPT_URL, $address);
			curl_setopt($cu, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($cu, CURLOPT_HEADER, 0);
			curl_setopt ($cu, CURLOPT_TIMEOUT, 600);
			$xmlFileContents = curl_exec($cu);
			if (curl_error($cu))
			{
				$this->_debug('curl_init', "Error: ".curl_errno($cu).", ".curl_error($cu));
				return FALSE;
			}
			curl_close ($cu);
			return $xmlFileContents;
		}



		if(ini_get("allow_url_fopen"))
		{
			if(!$remote = @fopen ($address, "r"))
			{
				$this->_debug('fsockopen', "Unable to open remote XML file.");
				
				return FALSE;
			}
		}else{
		
			$tmp = parse_url($address);
			
			if(!$remote = fsockopen ($tmp['host'], 80 ,$errno, $errstr, 10))
			{
				$this->_debug('fsockopen', "Unable to open remote XML file.");

				return FALSE;
			}
			else
			{
				socket_set_timeout($remote, 10);
				fputs($remote, "GET ".$headline_url." HTTP/1.0\r\n\r\n");
			}
		}

		$xmlFileContents = "";
		while (!feof($remote))
		{
			$xmlFileContents .= fgets ($remote, 4096);
		}
		fclose ($remote);

		return $xmlFileContents;
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