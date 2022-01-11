<?php

/*
	* Validate Class Set
	* @Version 4.0.3
	* Developed by: Ami (亜美) Denault
*/
/*
	* Setup Validate Class
	* @ Version 1.0.1
	* @ Since 4.0.0
*/

declare(strict_types=1);
class validate{

/*
	* Private Variables
	* @since 4.0.0
*/			
	private $_passed = false,
			$_errors = array(),
			$_db= null;

/*
	* Validate Construct
	* @since 4.0.0
*/
	public function __construct(){
		$this->_db = Database::getInstance();
	}

/*
	* Validate Check
	* @since 4.0.0
	* @param (String Source, String Items)
*/
	public function check(mixed $source,array $items =array()):object{

		foreach($items as $item=> $rules){

			$n = @(str::_strtolower($rules['name'])?$rules['name']:'Unknown');

			foreach($rules as $rule => $rule_value){

				$rule = trim($rule);
				$value = trim($source[$item]);
				$item  = Sanitize::escape($item);
				
				if(strtolower($rule) === 'required' && empty($value)){
					$this->addError("{$n} is required.");
				}
				else if(!empty($value)){
					switch($rule){
						case 'id':
							if(!is_numeric($value))
								$this->addError("Unable to validate item.");
						break;
						case 'url':
							if($this->url($value) === false)
								$this->addError("{$n} invalid url.");
						break;
						case 'min':
							if(strlen($value) < $rule_value)
								$this->addError("{$n} must be a minimum of {$rule_value}.");
						break;
						case 'max':
							if(strlen($value) > $rule_value)
								$this->addError("{$n} must be a maximum of {$rule_value}.");
						break;
						case 'matches':
							if($value != $source[$rule_value])
								$this->addError("{$rule_value} must match {$item}.");
						break;
						case 'regex':
							if(!preg_match($source[$rule_value],$value))
								$this->addError("{$rule_value} must match {$item}.");
						break;
						case 'changed':
							if(str::_strtolower($value) != str::_strtolower($rule_value)){
								$check =$this->_db->get(Config::get('table/users'),array('username', '=',$rule_value));

								if($check->count())
									$this->addError("{$n} already exist.");
							}
						break;
						case 'equalpw':
							if(Hash::make($value) != $rule_value)
								$this->addError("{$n} are not the same.");
						break;
						case 'hashcompare':
							if( hash('sha256',$value) != $rule_value)
								$this->addError("{$n} are not the same.");
						break;
						case 'equal':
							if($value != $rule_value)
								$this->addError("{$n} are not the same.");
						break;
						case 'unique':
							$check = $this->_db->get($rule_value,array($item, '=',$value));
							if($check->count())
								$this->addError("{$n} already exist.");
						break;
						case 'email':
							if($this->email($value) === false)
								$this->addError("{$n} invalid email.");
						break;	
						case 'recaptcha':
							if($value === 0)
								$this->addError("{$n} is invalid.");
						break;
						case 'checkaganistlower':
							if(!in_array(str::_strtolower($value),cast::_array(str::_strtolower($rule_value))))
								$this->addError("Please enter a valid option for {$n}.");
						break;
					}
				}
			}
		}
		if(empty($this->_errors))
			$this->_passed = true;

		return $this;
	}

/*
	* Validate Email
	* @since 4.0.0	
	* @param (String Email)
*/		
	public function email(string $email):bool{

		if(filter_var($email, FILTER_VALIDATE_EMAIL) !== false)
				return true;

		$this->addError("Please enter a valid email");
		return false;
	}

/*
	* Validate Url
	* @since 4.0.0	
	* @param (String Url)
*/		
	private function url(string $uri):bool{
		if (filter_var($uri, FILTER_VALIDATE_URL) === false) 
			return true;
		
		$this->addError("Please enter a valid url");
		return false;
	}

/*
	* Validate Sqli Input Check
	* @since 4.0.0	
	* @param (String String)
*/	
	public function sqliCheck(string $string):object{
	
		$SQLiKey = array('information_schema', 'information_schema.tables', 'concat','version()','--','0x3a','/*','*/','char(');

		for($x = 0;$x < count($SQLiKey);$x++){	
			if(strcmp($string, $SQLiKey[$x]) == 0)	
				$this->_passed = true;
		}
		$this->addError("Oh No");
		return $this;	
	}

/*
	* Validate Add Error
	* @since 4.0.0	
	* @param (String Error)
*/		
	private function addError(string $error):void{
		$this->_errors[] = $error;
	}

/*
	* Validate Return Errors
	* @since 4.0.0
	* @Param ()
*/	
	public function errors():array{
		return $this->_errors;
	}

/*
	* Validate Return Passed
	* @since 4.0.0
	* @Param ()
*/	
	public function passed():bool{
		return $this->_passed;
	}
}
?>