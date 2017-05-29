<?php

Class DB{
	private static $_instance = null;
	private $_pdo,
			$_query,  
			$_error = false,
			$_errorStat = array(), 
			$_results, 
			$_count = 0;

	private function __construct(){
		try{
			// $this->_pdo = new PDO('mysql:host=' . Config::get('mysql/host') . ';dbname=' .Config::get('mysql/db'), Config::get('mysql/username'), Config::get('mysql/password'));
			$this->_pdo = new PDO('mysql:host=localhost;dbname=theCar', 'root', '');
			// echo "Connected";
		} catch(PDOException $e){
			die($e->getMessage());
		}
	}
	public static function getInstance(){
		if (!isset(self::$_instance)) {
			self::$_instance = new DB();
		}
		return self::$_instance;
	}

	public function query($sql, $params = array()){
		$this->_error = false;
		if ($this->_query = $this->_pdo->prepare($sql)) {
			$x = 1;
			if (count($params)) {
				foreach ($params as $param) {
					$this->_query->bindValue($x, $param);
					$x++;
				}
			}
			// $this->_pdo->$sql;

			if ($this->_query->execute()) {
				$this->_results = $this->_query->fetchAll(PDO::FETCH_OBJ);
				$this->_count = $this->_query->rowCount();

			} else{
				// echo "ErrorCode: 001";	// msg: Error in $sql... see insert() || update(),, whatever you are using
				// $this->_errorStat = $this->_query->errorInfo();	// PDO::errorInfo()
				print_r($this->_query->errorInfo());
				$this->_error = TRUE;
			}
		}
		
		return $this;
	}

	private function action($action, $table, $where = array(), $order = null){
		if (count($where) === 3) {
			$operators = array('=', '<', '>', '>=', '<=');

			$field 		= $where[0];
			$operator 	= $where[1];
			$value 		= $where[2];

			if (in_array($operator, $operators)) {
				$sql = "{$action} FROM {$table} WHERE {$field} {$operator} ? {$order}";
				if (!$this->query( $sql, array($value))->error()) {
					return $this;
				}
			}
		}										
		return false;
	}	
	public function get($table, $where, $order = null){
		return $this->action('SELECT *', $table, $where, $order);
	}

	public function result(){
		return $this->_results;
	}

	public function first(){
		return $this->result()[0];
	}

	public function insert($table, $fields = array() ){
		if (count($fields)) {
			$keys = array_keys($fields);
			$values = null;
			$x = 1;

			foreach ($fields as $field) { 
				$values .= "?";
				if ($x < count($fields)) {
					$values .= ", ";
				}
				$x++;
			}
			$sql = "INSERT INTO {$table} (`" . implode('`, `', $keys) . "`) VALUES ({$values})";
			if (!$this->query($sql, $fields)->error()) { // if ERROR
				return true;
			}
		}
		return false;
	}

	public function update($table, $fields, $id){
		$set = '';
		$x = 1;

		foreach ($fields as $name => $value) {
			$set .= "{$name} = ? ";
			if ($x < count($fields)) {
				$set .= ", ";
			}
			$x++;
		}

		$sql = "UPDATE {$table} SET {$set} WHERE user_id = {$id}";

		if (!$this->query($sql, $fields)->error()) {
				return true;
		}
		return false;
	}

	public function error(){
		return $this->_error;
	}

	public function count(){
		return $this->_count;
	}

	public function errorStat(){
		return $this->_errorStat;
	}
}


?>