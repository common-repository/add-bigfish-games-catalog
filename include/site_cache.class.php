<?php
/**
 *
 * PNP TOOLS site_cache
 *
 * @author William Moffett <william.moffett@bigfishgames.com>
 * @version 0.5
 * @package pnp_tools
 *
 */
class site_cache {

		var $storePath;
		var $lifetime;
		var $file;
		var $cache;
		var $source;
		var $_showDebug;

	    function site_cache($storePath="", $lifetime="", $file="", $debug = FALSE)
		{
			$this->_showDebug = ($debug ? TRUE : FALSE);
			if($this->_showDebug){
				$this->_debug("site_cache::construct", "Debug is activated!");
			}
	    	if(!isset($storePath) || !isset($lifetime) || !isset($file)){
	    		$this->_debug("site_cache::construct",
					(!isset($storePath) ? "Cache Storage Path is not defined" : "").
					(!isset($lifetime) ? "Cache Lifetime is not defined" : "").
					(!isset($file) ? "Cache File is not defined." : ""));
	    	}
	    	if(isset($storePath)){ $this->storePath = $storePath; }
	 		if(isset($lifetime)){
		 		$this->set_lifetime($lifetime);
	    	}else{
	    		$this->set_lifetime('1');
	    	}
	    	if(isset($file)){ $this->file = $file; }
	    }

		function set_path($storePath)
		{
			$this->storePath=$storePath;
			$this->_debug("site_cache::set_path", "Path set to: ".$storePath);
		}

		function set_lifetime($lifetime)
		{
			$this->lifetime=$lifetime*60*60;
			$this->_debug("site_cache::set_lifetime", "Time set to: ".$lifetime." ".(($lifetime > 1) ? "hrs" : "hr"));
		}

		function set_file($file,$ext='html')
		{	/**
			 * Set the file to be written
			 */
			if(isset($file)){
				$this->file = $this->storePath.$file."_".md5($file)."cache.".$ext;
				$this->_debug("site_cache::set_file", "File set to: ".$this->file);
			}else{
				$this->_debug("site_cache::set_file", "No target file was passed to save cache to.");
			}
		}

		function set_source($source)
		{	/**
			 * Set the content to be written
			 */
			if(isset($source)){
				$this->source = $source;
			}else{
				$this->_debug("site_cache::set_source", "No source was passed for processing.");
			}
		}

	    function write_file()
	    {	/**
	    	 *	Write cache file
	    	 */

	    	if(!empty($this->source)){
		    	$this->cache = @fopen($this->file,"w");
		     	if(!$this->cache){
		    		$this->_debug("site_cache::write_file", "Unable to open ".$this->file." for writing.");
		    		return FALSE;
		    	}else{
		    		$this->write = fwrite($this->cache,$this->source);
		    		if($this->write){
						$this->_debug("site_cache::write_file", $this->cache_size($this->write)."  Written to ".$this->file);
		    		}
		    		@fclose($this->cache);
		    		return TRUE;
		     	}
	    	}else{
	    		$this->_debug("site_cache::write_file", "There is no source to write.");
	    	}
	    }

		function read_file($readfile='FALSE')
		{	/*
			 * Read in cached file and return contents
			 */
			 if($readfile=='TRUE'){
				return readfile($this->file);
			 }else{
				return file_get_contents($this->file,TRUE);
			 }

		}

		function del_file()
		{	/**
			 * Delete file
			 */
			if(is_file($this->file)) {
				if(@unlink($this->file)){
					$this->_debug("site_cache::del_file", "Deleted: ".$this->file);
					return TRUE;
				}else{
					$this->_debug("site_cache::del_file", "Permission Denied: ".$this->file);
					return FALSE;
				}
			}else{
				$this->_debug("site_cache::del_file", "The file ".$this->file." Does not exist.");
				return TRUE;
			}
		}

		function require_newfile()
		{	/**
			 * Check to see if we need a new cache file.
			 * Returns true if we one.
			 */

			if(!is_file($this->file)){
				$this->_debug("site_cache::require_newfile", " File does not exist.");
				return TRUE; // if we need a new file return true
			}else if(time() > (@filemtime($this->file) + $this->lifetime)){
				// $this->del_file();
				$this->_debug("site_cache::require_newfile", " File lifetime expired. We need a new one.");
				return TRUE;
			}else{
				$this->_debug("site_cache::require_newfile", " File is valid untill: ".date("F d Y H:i:s.", filemtime($this->file) + $this->lifetime));
				return FALSE;
			}
		}

		function file_time(){
			if(is_file($this->file)) {
				if($time =@filemtime($this->file)){
					return date("F d Y H:i:s.", $time);
				}else{
					$this->_debug("site_cache::file_time", "Unable to extract information on file: ".$this->file);
					return FALSE;
				}
			}else{
				$this->_debug("site_cache::file_time", "Unable to open: ".$this->file);
				return FALSE;
			}
		}

		function cache_size($size){
		/**
		 *Returns a human readable size
		 */
		  $i=0;
		  $iec = array(" B", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
		  while (($size/1024)>1) {
		   $size=$size/1024;
		   $i++;
		  }
		  return substr($size,0,strpos($size,'.')+4).$iec[$i];
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