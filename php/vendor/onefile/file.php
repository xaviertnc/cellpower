<?php namespace OneFile;

use FilesystemIterator;

/*
 * $path_parts = pathinfo('/www/htdocs/inc/lib.inc.php');
 * 
 * echo $path_parts['dirname'];
 * echo $path_parts['basename'];
 * echo $path_parts['extension'];
 * echo $path_parts['filename']; // since PHP 5.2.0
 * 
 * The above example will output:
 * 
 * /www/htdocs/inc
 * lib.inc.php
 * php
 * lib.inc	
 */

class File
{
	/**
	 *
	 * @var string
	 */
	protected $filePath;

	/**
	 *
	 * @var string
	 */
	protected $path;

	/**
	 *
	 * @var string
	 */
	protected $filename;

	/**
	 *
	 * @var string
	 */
	protected $nameonly;

	/**
	 *
	 * @var string
	 */
	protected $ext;

	/**
	 *
	 * @var octal
	 */
	protected $mode = 0775;
	
	/**
	 *
	 * @var boolean
	 */
	protected $mapped = false;


	protected function mapInfo()
	{
		$fileInfo = pathinfo($this->getFilePath());
		
		$this->path = $fileInfo['dirname'];
		
		$this->filename = $fileInfo['basename'];
		
		$this->ext = $fileInfo['extension'];
		
		$this->nameonly = $fileInfo['filename'];
	}
	
	public function getFilePath()
	{
		if (is_array($this->filePath))
		{
			$this->filePath = implode(DIRECTORY_SEPARATOR, $this->filePath);
		}
		
		return $this->filePath;
	}
	
	public function getPath()
	{
		if( ! $this->mapped) { $this->mapInfo(); }
		return $this->path;
	}
	
	public function getFilename()
	{
		if( ! $this->mapped) { $this->mapInfo(); }
		return $this->filename;
	}
	
	public function getNameOnly()
	{
		if( ! $this->mapped) { $this->mapInfo(); }
		return $this->nameonly;
	}
	
	public function getExt()
	{
		if( ! $this->mapped) { $this->mapInfo(); }
		return $this->ext;
	}
	
	public function getMode()
	{
		return $this->mode;
	}
	
	public function setMode($mode)
	{
		$this->mode = $mode;
		return $this;
	}
	
	public function setFilePath($path, $filename = null)
	{
		$this->mapped = false;
		$this->filePath = $this->makeFilePath($path, $filename);
		return $this;
	}
	
	public function __construct($path = null, $filename = null, $mode = 0775)
	{
		$this->setFilePath($path, $filename);
		$this->mode = $mode;
	}
	
	public function write($data, $append = false)
	{
		$filePath = $this->getFilePath();
		
		if ( ! file_exists($filePath))
		{
			$oldumask = umask(0);
			
			$dirname = dirname($filePath);
			@mkdir($dirname, $this->mode, true);
			
			umask($oldumask);
		}
		
		if ($append)
		{
			$options = FILE_APPEND | LOCK_EX;
		}
		else
		{
			$options = LOCK_EX;
		}
		
		file_put_contents($filePath, $data, $options);

		chmod($filePath, $this->mode);		
	}
	
	public function append($data)
	{
		return $this->write($data, true);
	}
	
	/**
	 * 
	 * @param type $this->filePath
	 * @return boolean
	 */
	public function delete()
	{
		if (is_file($this->filePath) and file_exists($this->filePath))
		{
			unlink($this->filePath);
		}
		
		return $this;
	}
	
	public function makeFilePath($path, $filename = null, $extension = null)
	{
		if (is_array($path))
		{
			$path = implode(DIRECTORY_SEPARATOR, $path);
		}
		
		if ( ! $filename)
		{
			return $path;
		}
			
		if ($extension)
		{			
			return $path . DIRECTORY_SEPARATOR . $filename . ".$extension";
		}
		else
		{
			return $path . DIRECTORY_SEPARATOR . $filename;
		}
	}
	/**
	 * 
	 * @param type $filename
	 * @return type
	 */
	public function sanitize($filename)
	{
		return preg_replace('/[^A-Za-z0-9_\-\.]+/', '', $filename);
	}

	/**
	 * Get the file's last modification time.
	 *
	 * @return int
	 */
	public function lastModified()
	{
		return filemtime($this->filePath);
	}	
	
	/**
	 * 
	 * @param integer $size in Bytes
	 * @return type
	 */
	public function sizeToString($size = null)
	{
		if (is_null($size) and $this->filePath)
		{
			$size = filesize($this->filePath);
		}

		if ($size < 1024)
			return $size . ' B';
		elseif ($size < 1048576)
			return round($size / 1024, 2) . ' KB';
		elseif ($size < 1073741824)
			return round($size / 1048576, 2) . ' MB';
		elseif ($size < 1099511627776)
			return round($size / 1073741824, 2) . ' GB';
		else
			return round($size / 1099511627776, 2) . ' TB';
	}
	
	/**
	 * 
	 * @param integer $size
	 * @param string $units
	 * @return integer
	 */
	public function convertSize($size = null, $units = null)
	{
		switch ($units)
		{
			case 'B' : return $size;
			case 'KB': return $size * 1024;
			case 'MB': return $size * 1048576;
			case 'GB': return $size * 1073741824;
		}

		return $size;
	}
	
	/**
	 * This method assumes you already found that the file path is
	 * not available and it should find the next possible name.
	 * 
	 * @param string $filePath
	 */
	public function getNextPossibleName($filePath, $duplicationLimit = 100)
	{	
		$file = new File($filePath);
		
		$namepart = $file->getNameOnly();
		
		$ext = $file->getExtension();
		
		$path = $file->getPath();
		
		$duplicateIndex = 0;
		
		$newfile = $path . $namepart . "_$duplicateIndex" . $ext;
		
		// $duplicationLimit = Endless loop precaution measure + Prevent excessive performance hit
		while (file_exists($newfile) and $duplicateIndex < $duplicationLimit)
		{
			$newfile = $path . $namepart . "_$duplicateIndex" . $ext;
			$duplicateIndex++;
		}

		return $newfile;
	}
	
	/**
	 * Return the quarter for a timestamp.
	 * @returns integer
	 */
	protected function quarter($ts) {
	   return ceil(date('n', $ts)/3);
	}	

	/**
	 * Adds a subfolder path in-front of the supplied filename
	 * to enable saving the file in a specific file group
	 * 
	 * @param string $groupName
	 * @param string $groupFormat
	 * @param time $groupDate
	 * @return string
	 */
	public function makeGroupPath($groupName = '', $groupFormat = 'Y', $groupDate = null)
	{	
		if( ! $groupDate) { $groupDate = time(); }
		
		switch ($groupFormat)
		{
			case 'YQ':
				$datePath = date('Y', $groupDate) . 'Q' . $this->quarter($groupDate);
				break;
			
			//The next option is covered by the default action, but it is shown here to 
			//remind that you can add subfolder slashes to the format string!
			case 'Y/m':
				$datePath = date('Y/m', $groupDate);
				break;

			default:
				if ($groupFormat)
				{
					$datePath = date($groupFormat, $groupDate);
				}
				else
				{
					$datePath = '';
				}
		}
		
		if ($groupName) { $prefix_parts[] =  $groupName; }
		
		if ($datePath) { $prefix_parts[] = $datePath; }

		if ($prefix_parts)
		{
			return implode(DIRECTORY_SEPARATOR, $prefix_parts);
		}
		else
		{
			return '';
		}
	}
	
	public function addGroupPath($groupName = '', $groupFormat = 'Y', $groupDate = null)
	{
		$path = $this->getPath();
		
		if ($path and $path !== '.') { $path_parts[] = $this->getPath(); }
		
		$path_parts[] = $this->makeGroupPath($groupName, $groupFormat, $groupDate);
		
		$this->setFilePath(implode(DIRECTORY_SEPARATOR, $path_parts), $this->getFilename());
		
		return $this;
	}
	
	/**
	 *
	 * Moves a file from one location to another.
	 * If OVERWRITE_DESTFILE = FALSE , a number will be added to the end of the
	 * destination filename to prevent overwriting the existing file!
	 *
	 * @param string $src_path  Path ONLY + Trailing Slash Optional
	 * @param string $dest_path Path ONLY + Trailing Slash Optional
	 * @param string $src_filename
	 * @param string $dest_filename
	 * @param boolean $overwrite_destfile Don't over-write an existing dest file TRUE / FALSE
	 * @param boolean $delete_sourcefile
	 * @param octal $mode
	 * @return string|boolean Move Succuessfull = Dest Filename / Else FALSE
	 */
	public function move($src_path, $dest_path, $src_filename = null, $dest_filename = null, 
			$overwrite_destfile = true, $delete_sourcefile = true, $mode = 0775)
	{
		if ( ! $src_filename)
		{
			if(is_file($src_path))
			{
				$src_filepath = $src_path;
				$src_path = dirname($src_filepath);
				$src_filename = basename($src_filepath);
			}
			else
			{
				return false;
			}
		}

		if ( ! $dest_filename)
		{
			if(is_file($dest_path))
			{
				$dest_filepath = $src_path;
				$dest_path = dirname($dest_filepath);
				$dest_filename = basename($dest_filepath);
			}
			else
			{
				$dest_filename = $src_filename;
			}
		}

		$src_path = rtrim($src_path, DIRECTORY_SEPARATOR);
		$dest_path = rtrim($dest_path, DIRECTORY_SEPARATOR);

		$src_file = $src_path . DIRECTORY_SEPARATOR . $src_filename;
		$dest_file = $dest_path . DIRECTORY_SEPARATOR . $dest_filename;

		if ( ! file_exists($src_file))
		{
			return false;
		}

		//The "dest_filename" can sometimes contain a path segment or two, so recalc the 
		//actual path after combining "dest_path"+"dest_filename"...NM 13 Nov 2012
		$full_dest_path = dirname($dest_file);

		if ( ! file_exists($full_dest_path))
		{
			$oldumask = umask(0);

			if ( ! mkdir($full_dest_path, $mode, true))
			{
				return false;
			}

			umask($oldumask);

			//chmod($full_dest_path, $mode);
		}

		if (!$overwrite_destfile and file_exists($dest_file))
		{
			$dest_filename = $this->getNextPossibleName($dest_file);
		}

		if ( ! copy($src_file, $dest_file))
		{
			return false;
		}

		chmod($dest_file, $mode);

		if ($delete_sourcefile)
		{
			$this->delete($src_file);
		}

		return $dest_filename;
	}

	/**
	 * 
	 * @param type $src_path
	 * @param type $dest_path
	 * @param type $src_filename
	 * @param type $dest_filename
	 * @param type $overwrite_destfile
	 * @param type $mode
	 * @return type
	 */
	public function copy($src_path, $dest_path, $src_filename = '', $dest_filename = '', $overwrite_destfile = true, $mode = 0775)
	{
		return $this->moveExt($src_path, $dest_path, $src_filename, $dest_filename, $overwrite_destfile, false, $mode);
	}	
	
	public function __toString()
	{
		return $this->filePath;
	}
}