<?php
//sqlQueryManager class
//AUTHOR  : CHARLES CARY

//CLASS STARTS
class sqlQueryManager
{
	//FIELDS
	private $host;
	private $user; 
	private $password;
	private $database;
	private $mysqli;
	
	//CONSTRUCTOR
	public function __construct ()
	{
		//PLACE YOUR DB INFO HERE
		$this->host = "HOST_HERE";
		$this->user = "USER_HERE";
		$this->password = "PASSWORD_HERE"; 
		$this->database = "DATABASE_HERE";
	}
	
	//METHODS
	
	//CONNECTS TO A SQL DB
	public function connect()
	{
		$this->mysqli = mysqli_connect($this->host, $this->user, $this->password, $this->database);
		if (mysqli_connect_errno())
		{
			printf("Connect failed: %s\n", mysqli_connect_error());
			exit(); 
		}
	}
	
	//PERFORMS A QUERY ON THE CONNECTED DB
	public function query($query)
	{
		if (isset($this->mysqli))
		{
			$result = mysqli_query($this->mysqli, $query);
			return $result;
		}
		else
		{
			print "ERROR: No Connection to Database." ;
			exit();
		}
	}
	public function get_last_insert_id()
	{
		return mysqli_insert_id($this->mysqli);
	}
	//CLOSES THE CONNECTION TO THE DB
	public function close()
	{
		if (isset($this->mysqli))
		{
			mysqli_close($this->mysqli);
		}
		else
		{
			print "ERROR: No Connection to Database." ;
			exit();
		}
	}
}

?>