<?php namespace OneFile\MySql;

/**
 *	@author C. Moller - 26 May 2014 <xavier.tnd@gmail.com> 
 */
class QueryConditions
{
	/**
	 *
	 * @var array
	 */
	protected $statements;
	
	/**
	 *
	 * @var boolean
	 */
	protected $as_prepared;
	
	/**
	 *
	 * @var array
	 */
	protected $params;
	
	
	public function __construct($as_prep_statement = true)
	{
		$this->as_prepared = $as_prep_statement;
		$this->params = array('AND' => array(), 'OR' => array());
	}
	
	public static function create($as_prep_statement = true)
	{
		return new static($as_prep_statement);
	}
	
	 // Only for non-prepared queries
	protected function quote($value)
	{
		if($value === null)
			return 'NULL';
		else
		{
			if(is_string($value))
				return "'" . mysql_escape_string($value) . "'";
			else
				return mysql_escape_string($value);
		}
	}
	
	protected function resolve($side, $glue)
	{
		$resolved = new \stdClass();
		
		$resolved->isObj = is_object($side);
		
		if($resolved->isObj and is_a($side, get_class($this)))
		{
			if($this->as_prepared)
				$this->params[$glue] = array_merge($this->params[$glue], $side->getParams());
									
			$resolved->asObj = $side;
			$resolved->asString = '(' . $side .')';
		}
		else
			$resolved->asString = $side;
		
		return $resolved;
	}
	
	public function on($columns, $tables = null)
	{
		if(is_array($columns))
		{
			$leftside = $columns[0];
			$rightside = $columns[1];
		}
		else
		{
			$leftside = $rightside = $columns;
		}
		
		if($tables)
		{
			$leftside = $tables[0] . '.' . $leftside;
			$rightside = $tables[1] . '.' .$rightside;
		}
		
		$this->statements['AND'][] = $leftside . ' = ' . $rightside;
		
		return $this;
	}
	
	 // If $table is a string, $leftside MUST be a string
	 // If $table == array, $leftside AND rightside MUST be strings
	public function where($leftside, $operator, $rightside, $table = null, $glue = 'AND')
	{
		$leftside = $this->resolve($leftside, $glue);
		
		if($operator)
			$operator = ' ' . trim($operator) . ' ';
		else
		{
			$this->statements[$glue][] = $leftside->asString;
			return $this;
		}
		
		$rightside = $this->resolve($rightside, $glue);		
		
		//-- PREPARED TYPE VARIANTS --
		if($this->as_prepared)
		{
			if(is_array($table))
			{
				$this->statements[$glue][] = $table[0] . '.' . $leftside->asString . $operator . '?';
				$this->params[$glue][] = $table[1] . '.' . $rightside->asString;
				return $this;
			}
			
			if($table)
			{
				$this->statements[$glue][] = $table . '.' . $leftside->asString . $operator . '?';
				$this->params[$glue][] = $rightside->isObj ? $rightside->asString : $rightside->asString;
				return $this;
			}
			
			$this->statements[$glue][] = $leftside->asString . $operator . '?';
			$this->params[$glue][] = $rightside->isObj ? $rightside->asString : $rightside->asString;
			return $this;
		}

		//-- PLAIN TYPE VARIANTS --
		if(is_array($table))
		{
			$this->statements[$glue][] = $table[0] . '.' . $leftside->asString . $operator . $table[1] . '.' . $rightside->asString;
			return $this;
		}

		if($table)
		{
			$this->statements[$glue][] = $table . '.' . $leftside->asString . $operator . $this->quote($rightside->asString);
			return $this;
		}
		
		$this->statements[$glue][] = $leftside->asString . $operator . $this->quote($rightside->asString);
		
		return $this;
	}
		
	public function orWhere($leftside, $operator = null, $rightside = null, $table = null)
	{
		return $this->where($leftside, $operator, $rightside, $table, 'OR');
	}
	
	public function isNull($leftside, $table = null, $glue = 'AND')
	{
		$this->statements[$glue][] = '(' . ($table ? $table . '.' : '') . $leftside . 'IS NULL )';
		return $this;
	}

	public function isNotNull($leftside, $table = null, $glue = 'AND')
	{
		$this->statements[$glue][] = '(' . ($table ? $table . '.' : '') . $leftside . 'IS NOT NULL )';
		return $this;
	}
	
	public function raw($raw_statement, $glue = 'AND')
	{
		$this->statements[$glue][] = $raw_statement;
		return $this;
	}
		
	public function getParams()
	{
		return array_merge($this->params['AND'], $this->params['OR']);
	}
	
	public function build()
	{
		$conditions = '';

		if(isset($this->statements['AND']))
		{
			$conditions .= implode(' AND ', $this->statements['AND']);
		}

		if(isset($this->statements['OR']))
		{
			if($conditions) $conditions .= ' OR ';
			$conditions .= implode(' OR ', $this->statements['OR']);
		}
		
//		echo '<span style="color:white">', $conditions, '</span><br>';
		
		return (string) $conditions;
	}
	
	public function __toString()
	{
		return $this->build();
	}
}