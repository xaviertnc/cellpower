<?php namespace OneFile;

/**
 * @author C. Moller <xavier.tnc@gmail.com> -2012
 * @update C. Moller 7 June 2014 - Complete Rewrite 
 * 				- Changed from fully static to regular class with magic methods.
 * 				- Significantly simplified
 */
class Log
{

	/**
	 *
	 * @var boolean
	 */
	protected $enabled = false;
	
	/**
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * Can be string, function or closure. We use default generator if not defined
	 * 
	 * @var array()
	 */
	protected $filenames = array();
	
	/**
	 * Stores the compiled filenames if we use closures or sprintf() strings as filenames
	 *  
	 * @var array
	 */
	protected $compiled;

	/**
	 * Indicates which log types are enabled.
	 * If a type is not in the list, Log requests of that type will not execute,
	 * 
	 * @var array
	 */
	protected $allowedTypes = array('error', 'warning', 'info');

	/**
	 *
	 * @var string
	 */
	protected $shortDateFormat = 'Y-m-d';

	/**
	 *
	 * @var string
	 */
	protected $longDateFormat = 'd M Y H:i:s';

	/**
	 * Can be sprintf() string, function or closure
	 * 
	 * @var mixed
	 */
	protected $formatter;

	/**
	 * The log file permissions to apply
	 * 
	 * @var octal
	 */
	protected $mode = 0775;

	/**
	 * 
	 * @param mixed $logPath
	 * @param boolean $enable
	 * @param array|string $typesFilter
	 * @param boolean $replaceDefaultFilter
	 */
	public function __construct($logPath = null, $enable = true, $typesFilter = null, $replaceDefaultFilter = false)
	{
		$this->setLogPath($logPath);
		
		$this->addAllowedTypes($typesFilter, $replaceDefaultFilter);
		
		$this->setFilename($this->getDate() . '.log' , 'info');

		$this->enabled = $enable;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function getLogPath()
	{
		return $this->path;
	}
	
	/**
	 * 
	 * @return array
	 */
	public function getFilenames()
	{
		return $this->filenames;
	}
	
	/**
	 * Get filename based on the content type of $this->filenames
	 *  - Content can be an array or single: closure, callable, sprintf() string
	 *  - If the content is an array, the Log $type value is used as key.
	 * 
	 * Closures and sprintf() strings get compiled with: logType, shortDate and longDate as possible parameters
	 * 
	 * Compiled filenames get cached in the $this->compiled array to avoid re-compiling on every request
	 * 
	 * @return string
	 */
	public function getFilename($type = null)
	{
		
		if ($type)
		{
			$key = $type;
		}
		else
		{
			$key = 'info'; 
		}

		if($this->compiled and isset($this->compiled[$key]))
		{
			return $this->compiled[$key];
		}

		$filename_uncompiled = isset($this->filenames[$key]) ? $this->filenames[$key] : $this->filenames['info'];

		if (is_callable($filename_uncompiled))
		{
			$filename = $filename_uncompiled($type, $this);

			$this->compiled[$key] = $filename;

			return $filename;
		}

		//Use indexed references inside your filename template if the parameters aren't in
		//the correct sequence.   %1$s = type, %2$s = short, %3$s = long.
		$filename = sprintf($filename_uncompiled, $type, $this->getDate(), $this->getDate(true));

		$this->compiled[$key] = $filename;

		return $filename;
		
	}

	/**
	 * Log message formatter.
	 * Override if you don't like the default implementation
	 * 
	 * @param string $message
	 * @param string $type
	 * @return string
	 */
	public function formatMessage($message, $type = 'info')
	{
		if ($this->formatter)
		{
			if (is_callable($this->formatter))
			{
				return $this->formatter($message, $type, $this);
			}
			else
			{
				//Use indexed references inside your message template if the parameters aren't in
				//the correct sequence.   %1$s = message, %2$s = type, %3$s = short, %4$s = long.				
				return sprintf($this->formatter, $message, $type, $this->getDate(), $this->getDate(true));
			}
		}
		else
		{
			return '[' . str_pad(ucfirst($type), 5) . "]:\t" . $this->getDate(true) . ' - ' . $message . PHP_EOL;
		}
	}

	/**
	 * No error checking or supressing errors. Keep it fast.
	 * Make sure your folders exist and permissions are set correctly or expect fatal errors!
	 * 
	 * @param string $message
	 * @param string $type
	 * @param string $filename
	 */
	public function write($message = '', $type = null, $filename = null)
	{
		if ($type)
		{
			$type = strtolower($type);
		}
		
		if ( ! $filename)
		{
			$filename = $this->getFilename($type);
		}
			
		if ($type and ! in_array($type, $this->allowedTypes))
		{
			return;
		}

		if ($type)
		{
			$message = $this->formatMessage($message, $type);
		}
		
		$logFilePath = $this->path ? $this->path . '/' . $filename : $filename;
		
		if ( ! file_exists($logFilePath))
		{
			$oldumask = umask(0);
			
			$dirname = dirname($logFilePath);
			@mkdir($dirname, $this->mode, true);
			
			umask($oldumask);
		}
		
		file_put_contents($logFilePath, $message, FILE_APPEND | LOCK_EX);

		chmod($logFilePath, $this->mode);
	}

	/**
	 * 
	 * @param string $shortDateFormat
	 * @return \OneFile\Log
	 */
	public function setShortDateFormat($shortDateFormat)
	{
		$this->shortDateFormat = $shortDateFormat;

		return $this;
	}

	/**
	 * 
	 * @param string $longDateFormat
	 * @return \OneFile\Log
	 */
	public function setLongDateFormat($longDateFormat)
	{
		$this->longDateFormat = $longDateFormat;

		return $this;
	}

	/**
	 * 
	 * @param string $logPath
	 * @return \OneFile\Log
	 */
	public function setLogPath($logPath)
	{
		$this->path = $logPath;
		
		return $this;
	}
	
	/**
	 * 
	 * @param array|string $filename
	 * @return \OneFile\Log
	 */
	public function setFilename($filename, $logType = 'info')
	{
		if (is_array($logType))
		{
			foreach($logType as $type)
			{
				$this->filenames[strtolower($type)] = $filename;
			}
		}
		else
		{
			$this->filenames[strtolower($logType)] = $filename;			
		}

		return $this;
	}

	/**
	 * 
	 * @param array|string $filter
	 * @param boolean $replaceExistingFilter
	 * @return \OneFile\Log
	 */
	public function addAllowedTypes($filter, $replaceExistingFilter = true)
	{
		if ( ! $filter)
		{
			return $this;
		}
		
		if ($filter and ! is_array($filter))
		{
			$filter = explode('|', $filter);
		}

		if ($replaceExistingFilter)
		{
			$this->allowedTypes = $filter;
		}
		else
		{
			$this->allowedTypes = array_merge($this->allowedTypes, $filter);
		}
		
		// Always save types in lower case.  i.e. Types aren't case sensitive
		foreach ($this->allowedTypes as $key => $type)
		{
			$this->allowedTypes[$key] = strtolower($type);
		}

		return $this;
	}

	/**
	 * Could be a closure, callable or sprintf() format string.
	 * 
	 * @param mixed $formatter
	 * @return \OneFile\Log
	 */
	public function setLineFormatter($formatter)
	{
		$this->formatter = $formatter;

		return $this;
	}

	/**
	 * 
	 * @param octal $mode
	 * @return \OneFile\Log
	 */
	public function setFileMode($mode)
	{
		$this->mode = $mode;

		return $this;
	}

	/**
	 * 
	 * @return \OneFile\Log
	 */
	public function enable()
	{
		$this->enabled = true;

		return $this;
	}

	/**
	 * 
	 * @return \OneFile\Log
	 */
	public function disable()
	{
		$this->enabled = false;

		return $this;
	}
	
	/**
	 * 
	 * @return boolean
	 */
	public function enabled()
	{
		return $this->enabled;
	}
	
	/**
	 * 
	 * @param boolean $long
	 * @return string
	 */
	public function getDate($long = false)
	{
		return $long ? date($this->longDateFormat) : date($this->shortDateFormat);
	}
	
	/**
	 * 
	 * @param type $name
	 * @param type $arguments
	 */
	public function __call($name, $arguments)
	{
		switch(count($arguments))
		{
			case 1:
				$this->write($arguments[0], $name);
				break;

			case 2:
				$this->write($arguments[1], $name);
				break;

			default:
				$this->write('', $name);
		}
	}
	
}
