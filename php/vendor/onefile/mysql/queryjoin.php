<?php namespace OneFile\MySql;

include 'queryconditions.php';

/**
 *	@author C. Moller - 26 May 2014 <xavier.tnd@gmail.com> 
 */
class QueryJoin
{
	/**
	 *
	 * @var array|string
	 */
	protected $tables;
	
	/**
	 *
	 * @var array|string 
	 */
	protected $conditions;
	
	/**
	 *
	 * @var array|string
	 */
	protected $aliases;
	
	/**
	 *
	 * @var string
	 */
	protected $type;
	
	/**
	 *
	 * @var boolean
	 */
	protected $as_prepared;
	
	
	public function __construct($tables, $on, $aliases = null, $type = 'LEFT', $in_prep_statement = true)
	{
		$this->tables = $tables;
		$this->conditions = $on;
		$this->aliases = $aliases;
		$this->type = $type;
		$this->as_prepared = $in_prep_statement;
	}
		
	public static function create($tables, $on, $aliases = null, $type = 'LEFT', $in_prep_statement = true)
	{
		return new self($tables, $on, $aliases, $type, $in_prep_statement);
	}
	
	public function getParams()
	{
		if($this->conditions)
			return $this->conditions->getParams();
		else
			return array();
	}
	
	public function build()
	{
		$join_statement = $this->type.' JOIN ';
		
		if(is_array($this->tables))
		{
			foreach($this->tables as $i => $table)
			{
				$this->tables[$i] = $table . ($this->aliases ? " as $this->aliases[$i]" : '');
			}
			
			$join_statement .= implode(',', $this->tables);
		}
		else
			$join_statement .= $this->tables . ($this->aliases ? " as $this->aliases" : '');
		
		if(is_array($this->conditions))
		{
			$this->conditions = new QueryConditions(
				$this->conditions[0],
				$this->conditions[1],
				$this->conditions[2], 
				(isset($this->conditions[3]) ? $this->conditions[3] : null), 
				$this->as_prepared
			);
		}
			
		//Note: If $this->conditions is a QueryBuilder object, it will convert to string via its _toString() method
		$join_statement .= ' ON ' . $this->conditions;
		
		return $join_statement;
	}
	
	public function __toString()
	{
		return $this->build();
	}
}