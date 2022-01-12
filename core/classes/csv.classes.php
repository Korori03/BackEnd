<?php

/*
	* Csv Class Set
	* @Version 1.0.0
	* Developed by: Ami (亜美) Denault
*/

/*
	* CSV Template Class
	* @since 1.0.0
*/	
declare(strict_types=1);
class Csv {

/*
	* Variables
	* @Since 1.0.0
*/	
	private $_db = NULL;
/*
	* Construct if Class is called
	* @since 1.0.0
*/
	public function __construct() {
		self::$_db = Database::getInstance();
	}

/*
	* Import to DB from CSV File
	* @since 4.0.0
	* @Param (String file path, Array Options)
*/	
	public static function toMysql(string $csv_path,array $options = array()):string|bool
	{
		extract($options);
		
		if (($csv_handle = fopen($csv_path, "r")) === FALSE)
			throw new Exception('Cannot open CSV file');
			
		if(!$delimiter)
			$delimiter = ',';
			
		if(!$table)
			$table = preg_replace("/[^A-Z0-9]/i", '', basename($csv_path));
		
		if(!$fields){
			$fields = array_map(function ($field){
				return str::_tolower(preg_replace("/[^A-Z0-9]/i", '', $field));
			}, fgetcsv($csv_handle, 0, $delimiter));
		}
		
		$create_fields_str = join(', ', array_map(function ($field){
			return "$field TEXT NULL";
		}, $fields));
		

		$create_table_sql = "CREATE TABLE IF NOT EXISTS $table ($create_fields_str)";
		self::$_db::query($create_table_sql);
		
		$insert_fields_str = join(', ', $fields);
		$insert_values_str = join(', ', array_fill(0, count($fields),  '?'));
		$insert_sql = "INSERT INTO $table ($insert_fields_str) VALUES ($insert_values_str)";

		$insert_sth = self::$_db::prepare($insert_sql);

		while (($data = fgetcsv($csv_handle, 0, $delimiter)) !== FALSE) {
			self::$_db::execute($data);
		}

		fclose($csv_handle);
		return true;
	}
}
?>