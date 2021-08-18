<?php
/*
	* Database Class Set
	* @Version 1.1.3
	* Developed by: Ami (亜美) Denault
	* Coded on: 15th April 2021
*/

/*
	* Setup Database Class
	* @since 4.0.0
*/
declare(strict_types=1);
class Database{

/*
	* Private Static Variables
	* @since 4.0.0
*/		
	private static $_instance = null;
		
/*
	* Private Variables
	* @since 4.0.0
*/
	private $_pdo;
	public 	$_query,
			$_error = false,
			$_errormsg = "",
			$_results,
			$_count = 0,
			$_columnCount = 0,
			$_lastinsert;

/*
	* Construct Database
	* @since 4.0.0
*/
	private function __construct(){
		try{
			$this->_pdo = new PDO('mysql:host='.Config::get('mysql/host'). ';dbname='.Config::get('mysql/db'),Config::get('mysql/username'),Config::get('mysql/password'));
			$this->_pdo->exec("set names utf8");
			$this->_pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			$this->_pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
		}catch(PDOException $e){
			echo "Connection error: " . $e->getMessage();
			die(Redirect::to(404));
		}
	}

/*
	* Destruction
	* @Since 4.4.7
*/
	public function __destruct(){
		$this->_pdo = null;
		$this->_query = null;
	}

/*
	* Get PDO
	* @since 4.0.0
*/
	public function PDO():object{
		return $this->_pdo;
	}

/*
	* Get Instance of Database
	* @since 4.0.0
*/
	public static function getInstance():object{
		if(!isset(self::$_instance)){
			self::$_instance = new Database();
		}
		return self::$_instance;
	}

/*
	* Database Query for API
	* @Since 4.4.7
	* @Param (String SQL, Array Fields)
*/
	public function queryAPI($sql,$data = "" ,$column = ""):object{
		$this->_error = false;
	
		try{
			if($this->_query = $this->_pdo->prepare($sql)){
				if(!empty($data) && !empty($column))
					$this->bind($data,$column);

				if($this->_query->execute()){
					$this->_results = $this->_query->fetchAll(PDO::FETCH_ASSOC);
					$this->_columnCount = $this->_query->columnCount();
					$this->_count = $this->_query->rowCount();	
					$this->_lastinsert = $this->_pdo->lastInsertId();
					if(!$this->_query->rowCount()){ 
						$this->_error = true;
						$this->_errormsg = $this->_pdo->errorInfo();
						$this->_results = NULL;
					}
				}
				else{
					$this->_error = true;
					$this->_errormsg = $this->_pdo->errorInfo();
					$this->_results = NULL;
				}
			}
		} catch (Exception $e) {
			$this->_error = true;
			$this->_errormsg = $this->_pdo->errorInfo();
			$this->_results = NULL;
		}

		//Used to Debug Errors
		//$this->_query->debugDumpParams();
		return $this;
	}

/*
	* Database Query
	* @since 4.0.0
	* @Param (String SQL, Array Fields)
*/
	public function query($sql,$params = array()):object{
		$this->_error = false;
		
		if($this->_query = $this->_pdo->prepare($sql)){
			$x = 1;
			if(count($params)){
				foreach($params as $param){
					$this->bindParam($x,$param);
					$x++;
				}
			}	

			if($this->_query->execute()){
				$this->_results = $this->_query->fetchAll(PDO::FETCH_OBJ);
				$this->_columnCount = $this->_query->columnCount();
				$this->_count = $this->_query->rowCount();	
				$this->_lastinsert = $this->_pdo->lastInsertId();
			}
			else{
				$this->_error = true;
			}
		}
		//$this->_query->debugDumpParams();
		return $this;
	}

/*
	* Prepare SQL
	* @since 4.0.0
	* @Param (String)
*/
	public function prepare($sql):void{
		$this->_query = $this->_pdo->prepare($sql);
	}

/*
	* Bind
	* @Since 4.4.7
	* @Param (Object,Array)
*/
	public function bind($data,$columns):void{
		for($x= 0;$x <count($columns);$x++){
            if (isset($data->{$columns[$x]}->value)){
				$bind = ":" .str_replace('#','',$columns[$x]);
				$value = htmlspecialchars(strip_tags($data->{$columns[$x]}->value));
				$this->bindParam($bind,$value);
			}
        }
    }

/*
	* Column County
	* @since 4.0.0
*/
	public function columnCount():int{
		return $this->_columnCount;
	}

/*
	* Return Error Message
	* @Since 4.4.7
*/	
	public function errorMsg():object{
		$errorMsg = new StdClass();
		$errorMsg->message = (object) $this->_errormsg;
		return $errorMsg ;
	}

/*
	* Get Column Name
	* @since 4.0.0
*/	
	public function columnName():array{
		$meta = array();
		foreach(range(0, $this->_columnCount - 1) as $column_index)
		  $meta[] = $this->_query->getColumnMeta($column_index);

		return $meta;
	}

/*
	* Database Execute
	* @since 4.0.0
	* @Param (String SQL, Array Fields)
*/
	public function execute():void{
		$this->_error = false;
		if($this->_query->execute()){
			$this->_results = $this->_query->fetchAll(PDO::FETCH_OBJ);
			$this->_count = $this->_query->rowCount();	
			$this->_lastinsert = $this->_pdo->lastInsertId();
		}
		else
			$this->_error = true;
	}
	
/*
	* Database Query
	* @since 4.0.0
	* @Param (String SQL, Array Fields)
*/
	public function bindParam($x,$param):void{
		$var_type = PDO::PARAM_STR;
		switch (true) {
			case is_bool($param):
				$var_type = PDO::PARAM_BOOL;
			break;
			case is_int($param):
				$var_type = PDO::PARAM_INT;
			break;
			case is_null($param):
				$var_type = PDO::PARAM_NULL;
			break;
		}

		$this->_query->bindValue($x,$param,$var_type);
	}	

/*
	* Database Action
	* @since 4.0.0
	* @Param (String Action(Select/Delete,String Table,Array Where, Array Orders)
*/
	private function action($action,$table,$where = array(),$orders = array()):object|bool{
		
		if(count($where) == 3){
			$operators = array('=','>','<','>=','<=');
			$field 		= $where[0];
			$operator   = $where[1];
			$value 		= $where[2];

			$order = '';
			if(count($orders) == 2)
				$order = "ORDER BY `" . $orders[0]. "` " . $orders[1];
			
			if(in_array($operator,$operators)){
				$sql="{$action} FROM {$table} WHERE {$field} {$operator} ? {$order}";
				if(!$this->query($sql,array($value))->error())
					return $this;
			}
		}
		else if(count($orders) == 2){
			$order = "ORDER BY `" . $orders[0]. "` " . $orders[1];
			$sql="{$action} FROM {$table}  {$order}";
			if(!$this->query($sql)->error())
				return $this; 		
		}
		else{
			$sql="{$action} FROM {$table}";
			if(!$this->query($sql)->error())
				return $this; 		
		}
			
		return false;
	}

/*
	* Database Get
	* @since 4.0.0
	* @Param (String Table, String Where, Array Orders)
*/
	public function get($table,$where=array(),$order=array()):object{
		return $this->action('SELECT *',$table,$where,$order);
	}

/*
	* Database All
	* @since 4.0.0
	* @Param (String Table)
*/	
	public function all($table):object{
		$sql="SELECT * FROM {$table}";
		if(!$this->query($sql)->error())
			return $this;	
	}

/*
	* Database Delete
	* @since 4.0.0
	* @Param (String Table, String Where)
*/
	public function delete($table,$where):object{
		return $this->action('DELETE',$table,$where);
	}

/*
	* Database Insert
	* @since 4.0.0
	* @Param (String Table, Array Fields)
*/
	public function insert($table,$fields= array()):bool{
		if(count($fields)){
			$keys = array_keys($fields);
			$values = null;
			$x = 1;

			foreach($fields as $field){
				$values .='? ';
				if($x < count($fields)){
					$values.=', ';
				}
				$x++;
			}
			$sql = "INSERT INTO {$table} (`".implode('`, `', $keys) . "`) VALUES({$values})";
			if(!$this->query($sql,$fields)->error()){
				return true;
			}
		}
		return false;
	}

/*
	* Database Update
	* @since 4.0.0
	* @Param (String Table, String Where, Array Orders)
*/
	public function update($table,$fields,$id,$byid = 'id'):bool{
		$set ='';
		$x = 1;
		foreach($fields as $name=>$value){
			$set .= "`{$name}` = ?";
			if($x < count($fields)){
				$set .= ', ';
			}
			$x++;
		}
		$sql = "UPDATE {$table} SET {$set} WHERE {$byid} = '{$id}'";
		if(!$this->query($sql,$fields)->error()){
			return true;
		}
		return false;
	}	
/*
	* Database Get Results
	* @since 4.0.0
*/	
	public function results():mixed{
		return $this->_results;
	}

/*
	* Database First Result
	* @since 4.0.0
*/
	public function first():object{
		return $this->results()[0];
	}

/*
	* Database Error
	* @since 4.0.0
*/	
	public function error():bool{
		return $this->_error;
	}

/*
	* Database Get Count
	* @since 4.0.0
*/
	public function count():int{
		return $this->_count;
	}
	
/*
	* Database Get Last Inserted Record
	* @since 4.0.0
*/	
	public function last():int{
		return $this->_lastinsert;
	}
	
/*
	* Database Close
	* @since 4.0.2
*/	
	public function close():void{
		$this->_pdo = null;
		$this->_query = null;
	}
}
?>