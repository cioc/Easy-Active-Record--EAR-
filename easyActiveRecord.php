<?php
require_once('sqlQueryManager.php');

//Easy Active Record(EAR) class 
//AUTHOR		CHARLES CARY
//DESCRIPTION	MY IMPLEMENTATION OF ACTIVERECORD

//ALL ELEMENTS THAT IMPLEMENT ACTIVE RECORD MUST HAVE AN ID COLUMN IN THE DATABASE WITH AUTO_INCREMENT
class activeRecord
{
	//this will store all of the fields and their values
	protected $fields = array();
	protected $overrideTableName;	
	//override the get and set properties in order to facilitate the active record pattern 
	public function __get($field)
	{
		if (array_key_exists($field,$this->fields))
		{
			return $this->fields[$field];
		}
	}
	public function __set($field, $value)
	{
		$this->fields[$field] = $value;
	}
	public function __isset($field)
	{
		return isset($this->fields[$field]);
	}
	public function set_field($field, $value)
	{
		$this->fields[$field] = $value;
	}
	public function getTableName()
	{
		if (isset($this->overrideTableName))
		{
			return $this->overrideTableName;
		}
		else
		{
			return get_class($this);
		}
	}
	
	public function overrideTableName($name)
	{
		$this->overrideTableName = $name;
	}
	//TODO
	//CREATE DOCUMENTATION FOR:
	//conditions DONE
	//order DONE
	//limit DONE
	//select DONE
	//from (which table) DONE
	//finds records in the database
	public function find()
	{
		if (func_num_args() == 2)
		{
			$id = func_get_arg(0);
			$params = func_get_arg(1);
		}
		else
		{
			$id = func_get_arg(0);
		}
		if (is_array($id) || $id > -1)
		{
			$query = "SELECT ";
			if (isset($params) && is_array($params))
			{
				if (isset($params['select']))
				{
					$query = $query.$params['select']." ";
				}
				else
				{
					$query = $query."* ";
				}
				if (isset($params['from']))
				{
					$query = $query."FROM ".$params['from']." ";
				}
				else
				{
					$query = $query."FROM ".$this->getTableName()." ";
				}
				if (is_array($id))
				{
					$query = $query."WHERE ";
					if (isset($params['conditions']))
					{
						$first = true;
						foreach ($id as $ident)
						{
							if ($first)
							{
								$query = $query." id = '".$ident."'";
								$first = false;
							}
							else
							{
								$query = $query." OR id = '".$ident."'";
							}
						}
						$query = $query." OR ".$params['conditions'];
					}
					else
					{
						$first = true;
						foreach ($id as $ident)
						{
							if ($first)
							{
								$query = $query." id = '".$ident."'";
								$first = false;
							}
							else
							{
								$query = $query." OR id = '".$ident."'";
							}
						}
					}
				}
				else
				{
					if (isset($params['conditions']))
					{
						$query = $query."WHERE id = '".$id."' OR ".$params['conditions']." ";
					}
					else
					{
						$query = $query."WHERE id = '".$id."' ";
					}
				}
				if (isset($params['limit']))
				{
					$query = $query." LIMIT ".$params['limit'];
				}
				if (isset($params['order']))
				{
					$query = $query." ORDER BY ".$params['order'];
				}
			}
			else
			{
				if (is_array($id))
				{
					$query = $query." * FROM ".$this->getTableName()." WHERE";
					$first = true;
					foreach ($id as $ident)
					{
						if ($first)
						{
							$query = $query." id = '".$ident."'";
							$first = false;
						}
						else
						{
							$query = $query." OR id = '".$ident."'";
						}
					}
				}
				else
				{
					$query = $query." * FROM ".$this->getTableName()." WHERE id = '".$id."'";
				}
			}
		}
		else
		{
			$query = "SELECT ";
			if (isset($params['select']))
			{
				$query = $query.$params['select']." ";
			}
			else
			{
				$query = $query."* ";
			}
			if (isset($params['from']))
			{
				$query = $query."FROM ".$params['from']." ";
			}
			else
			{
				$query = $query."FROM ".$this->getTableName()." ";
			}
			if (isset($params['conditions']))
			{
				$query = $query."WHERE ".$params['conditions'];
			}
			if (isset($params['limit']))
			{
				$query = $query." LIMIT ".$params['limit'];
			}
			if (isset($params['order']))
			{
				$query = $query." ORDER BY ".$params['order'];
			}
		}
		$sqlman = new sqlQueryManager();
		$sqlman->connect();
		$recordResult = $sqlman->query($query);
		$sqlman->close();
		
		if ((is_array($id) && isset($params) && is_array($params)) || $id < 0 || is_array($id) || (isset($params) && is_array($params)))
		{
			//return array
			if (mysqli_num_rows($recordResult) > 0)
			{
				$output;
				while ($varArray = mysqli_fetch_array($recordResult, MYSQLI_ASSOC))
				{
					$tempObj = new Activerecord();
					foreach ($varArray as $key => $val)
					{
						$tempObj->fields[$key] = $val;
					}
					$output[] = $tempObj;
				}
				return $output;
			}
		}
		else
		{
			//load into this object
			if (mysqli_num_rows($recordResult) > 0)
			{
				$varArray = mysqli_fetch_array($recordResult, MYSQLI_ASSOC);
				foreach ($varArray as $key => $val)
				{
					$this->fields[$key] = $val;
				}
			} 
		}
	}
	
	//deletes the active record from the database
	public function delete($id)
	{
		if (is_array($id))
		{
			$table = $this->getTableName();
			$query = "DELETE FROM $table WHERE";
			$first = true;
			foreach ($id as $ident)
			{
				if ($first)
				{
					$query = $query." id = '".addslashes($ident)."'";
					$first = false;
				}
				else
				{
					$query = $query." OR id = '".addslashes($ident)."'";
				}
			}
		}
		else
		{
			$table = $this->getTableName();
			$query = "DELETE FROM $table WHERE id='".addslashes($id)."'";
		}
		$sqlman = new sqlQueryManager();
		$sqlman->connect();
		$sqlman->query($query);
		$sqlman->close();
	}
	
	//call this method to save a new instance to the database
	//setting the field overrideTableName changes the table that the new record will be inserted into 
	public function save()
	{
		//IMPORTANT
		//check if id is set, update instead of inserting
		//IMPORTANT
		$table = $this->getTableName();
		if (isset($this->fields['id']))
		{
			//update instead
			$query = "UPDATE $table SET";
			foreach ($this->fields as $key => $value)
			{
				if ($key == 'id'){}
				else
				{
					$query = $query." $key = '".addslashes($value)."',";
				}
			}
			$query = rtrim($query, ",");
			$query = $query." WHERE id = '".$this->fields['id']."'";
			//echo $query;
		}
		else
		{
			$newRecord = true;
			$query = "INSERT INTO $table (";
			foreach ($this->fields as $key => $value)
			{
				if ($key == 'id'){}
				else
				{
					$query = $query."$key, ";
				}
			}
			$query = rtrim($query, " ,");
			$query = $query.")";
			$query = $query." VALUES (";
			foreach ($this->fields as $key => $value)
			{
				if ($key == 'id'){}
				else
				{
					$query = $query."'".addslashes($value)."',";
				}
			}
			$query = rtrim($query, " ,");
			$query = $query.")";
		}
		$sqlman = new sqlQueryManager();
		$sqlman->connect();
		$sqlman->query($query);
		if ($newRecord)
		{
			$this->fields['id'] = $sqlman->get_last_insert_id();
		}
		$sqlman->close();
	}
	
	public function get_json()
	{
		return json_encode($this->fields);
	}
}
?>