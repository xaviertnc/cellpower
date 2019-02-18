<?php namespace OneFile\MySql;

use PDO;
use PDOException;

/**
 *	@author C. Moller - 24 May 2014 <xavier.tnd@gmail.com> 
 */
class Field
{
	/**
	 *
	 * @var type 
	 */
	public $name;
	
	/**
	 *
	 * @var type 
	 */
	public $type;
	
	/**
	 *
	 * @var type 
	 */
	public $allow_null;
	
	/**
	 *
	 * @var type 
	 */
	public $is_index;
	
	/**
	 *
	 * @var type 
	 */
	public $def_value;
	
	/**
	 *
	 * @var type 
	 */
	public $extras;

	/**
	 * 
	 * @param array $definition
	 */
	function __construct(array $definition)
	{
		$this->name		  = $definition[0];
		$this->type		  = $definition[1];
		$this->allow_null = $definition[2];
		$this->is_index	  = $definition[3];
		$this->def_value  = $definition[4];
		$this->extras	  = $definition[5];
	}

	function __toString()
	{
		return 'FIELD: name='.$this->name.', type='.$this->type.', allow_NULL='.$this->allow_null.
			', is_index='.$this->is_index.', def_value ='.$this->def_value.', extras = '.$this->extras;
	}
}

/**
 *	@author C. Moller - 24 May 2014 <xavier.tnd@gmail.com> 
 */
class Table
{
	/**
	 *
	 * @var string
	 */
	public $name;
	
	/**
	 *
	 * @var array
	 */
	public $fields;
	
	/**
	 *
	 * @var array
	 */
	public $fieldnames;
	
	/**
	 *
	 * @var boolean
	 */
	public $autoincrement;
	
	/**
	 *
	 * @var DbField
	 */
	public $pk;

	/**
	 * 
	 * @param \OneFile\MySql\MySql $db
	 * @param string $tablename
	 */
	function __construct(MySql $db, $tablename)
	{
		$this->name = $tablename;
		
		$column_definitions = $db->query('SHOW COLUMNS FROM ' . $db->quote($tablename));
		
		while($column_definition = $column_definitions->fetch(PDO::FETCH_NUM))
		{
			$field = new Field($column_definition);
			
			if($field->is_index == 'PRI')
				$this->pk = $field;
			
			$this->fields[$field->name] = $field;
			$this->fieldnames[] = $field->name;
		}
		
		if($this->pk && $this->pk->extras == 'auto_increment')
		{
			$this->autoincrement = true;
		}
		else
		{
			$this->autoincrement = false;
		}
	}

	function __toString()
	{
		$result = 'TABLE '.$this->name.NL;
		$i = 0;
		foreach ($this->fields as $name=>$field) {
			if ($i) $result .= NL;
			$result .= $name.' - '.$field->type.'';
			$i++;
		}
		if ($this->pk) $result .= NL.NL.'Primary Key = '.$this->pk->name;
		return $result;
	}	
}

/**
 * @author C. Moller 24 May 2014 <xavier.tnc@gmail.com>
 */
class Database extends PDO
{	     
	/**
	 *
	 * @var array
	 */
	protected $config;

	/**
	 * 
	 * @param string|array $config
	 */
	function __construct($config = null)
    {
		if(!$config)
			$this->handle_error('Database Config Required');
		
		if(is_array($config))
		{
			$this->config = $config;
		}
		elseif(file_exists($config))
		{
			/**
			 * Config File Content
			 * -------------------
			 * return array(
			 *	'DBHOST'=>'...',
			 *	'DBNAME'=>'...',
			 * 	'DBUSER'=>'...',
			 * 	'DBPASS'=>'...'
			 * );
			 */
			$this->config = include($config);
			
			if(!$this->config)
				$this->handle_error('Config File Invalid');
		}
		else
			$this->handle_error('Config Invalid');

		try
		{
			parent::__construct(
				'mysql:host=' . $this->config['DBHOST'] . ';dbname=' . $this->config['DBNAME'],
				$this->config['DBUSER'],
				$this->config['DBPASS'], 
				array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'')
			);
			
            $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
		catch(PDOException $e)
		{
			$this->handle_error($e->getMessage(), $e->getCode());
        }
    }	

	/**
	 * 
	 * @param string $error
	 * @param integer $code
	 */
	protected function handle_error($error = null, $code = null)
	{
		die('<br><span style="color:red">MySql Database Error! Code: ' . $code . ', Message: ' . $error . '</span>');
	}
	
	/**
	 * 
	 * @param string $query
	 * @param array $params
	 * @return \PDOStatement
	 */
	public function exec_prepared($query, $params = null)
	{
		$prepared = $this->prepare($query);
		
		if($prepared->execute($params))
			return $prepared;
	}
}

//->fetch(PDO::FETCH_ASSOC);
//->fetch(PDO::FETCH_OBJ);