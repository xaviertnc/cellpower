<?php namespace OneFile\MySql\Ext;

include __DIR__ . '/../querybuilder.php';

use OneFile\MySql\QueryConditions as QC;
use OneFile\MySql\QueryBuilder as QB;

/**
 *	@author C. Moller - 26 May 2014 <xavier.tnd@gmail.com> 
 */
class QueryConditions extends QC
{
	public function isLike($leftside, $term, $table = null, $glue = 'AND')
	{
		return $this->where($leftside, 'LIKE', "%$term%", $table, $glue);
	}
	
	public function beginsLike($leftside, $term, $table = null, $glue = 'AND')
	{
		return $this->where($leftside, 'LIKE', "$term%", $table, $glue);
	}
	
	public function endsLike($leftside, $term, $table = null, $glue = 'AND')
	{
		return $this->where($leftside, 'LIKE', "%$term", $table, $glue);
	}
	
	//Note: Remember to double quote date values! e.g '"2014-05-26"'
	public function isBetween($leftside, $lower_limit, $upper_limit, $table = null, $glue = 'AND')
	{
		$leftside = $this->resolve($leftside, $glue);
		
		if($this->as_prepared)
		{
			$this->params[$glue][] = $lower_limit;
			$this->params[$glue][] = $upper_limit;
			$rightside = '? AND ?';
		}
		else
		{
			$rightside = $this->quote($lower_limit) . ' AND ' . $this->quote($upper_limit);
		}

		$operator = ' BETWEEN ';
		
		if($table)
			$this->statements[$glue][] = '(' . $table . '.' . $leftside->asString . $operator . $rightside . ')';
		else
			$this->statements[$glue][] = '(' . $leftside->asString . $operator . $rightside . ')';

		return $this;
	}

	public function pickFrom($leftside, array $options, $table = null, $glue = 'AND', $not = false)
	{
		$leftside = $this->resolve($leftside, $glue);
		
		$options_is_array = is_array($options);
		
		if(!$this->as_prepared and !$options_is_array)
			$options = explode(',', $options);
		
		if($options_is_array)
		{
			if($this->as_prepared)
				$this->params[$glue] = array_merge($this->params[$glue], $options);
			
			foreach($options as $i => $option)
			{				
				$options[$i] = $this->as_prepared ? '?' : $this->quote($option);
			}

			$options = implode(',', $options);
		}
		
		$rightside = '(' . $options . ')';
		
		if($not)
			$operator = ' NOT IN ';
		else
			$operator = ' IN ';
		
		if($table)
			$this->statements[$glue][] = '(' . $table . '.' . $leftside->asString . $operator . $rightside . ')';
		else
			$this->statements[$glue][] = '(' . $leftside->asString . $operator . $rightside . ')';
		
		return $this;
	}
	
	public function exclude($leftside, array $options, $table = null, $glue = 'AND')
	{
		return $this->pickFrom($leftside, $options, $table, $glue, true); //NOT IN
	}
}

/**
 *	@author C. Moller - 25 May 2014 <xavier.tnd@gmail.com> 
 */
class QueryBuilder extends QB
{
	protected function getConditions()
	{
		if(!$this->conditions)
			$this->conditions = QueryConditions::create($this->as_prepared);
			
		return $this->conditions;
	}
			
	public function isLike($leftside, $term, $table = null, $glue = 'AND')
	{
		$this->getConditions()->isLike($leftside, $term, $table, $glue);
		return $this;
	}
	
	public function beginsLike($leftside, $term, $table = null, $glue = 'AND')
	{
		$this->getConditions()->beginsLike($leftside, $term, $table, $glue);
		return $this;
	}
	
	public function endsLike($leftside, $term, $table = null, $glue = 'AND')
	{
		$this->getConditions()->endsLike($leftside, $term, $table, $glue);
		return $this;
	}
	
	public function isBetween($leftside, $lower_limit, $upper_limit, $table = null, $glue = 'AND')
	{
		$this->getConditions()->isBetween($leftside, $lower_limit, $upper_limit, $table, $glue);
		return $this;
	}

	public function pickFrom($leftside, array $options, $table = null, $glue = 'AND')
	{
		$this->getConditions()->pickFrom($leftside, $options, $table, $glue);
		return $this;
	}

	public function exclude($leftside, array $options, $table = null, $glue = 'AND')
	{
		$this->getConditions()->exclude($leftside, $options, $table, $glue);
		return $this;
	}	
}