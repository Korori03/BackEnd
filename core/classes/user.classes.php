<?php

/*
	* User Class Set
	* @Version 4.0.0
	* Developed by: Ami (亜美) Denault
*/
/*
	* Setup User Class
	* @since 4.0.0
*/

declare(strict_types=1);
class User
{

	/*
	* Private Variables
	* @since 4.0.0
*/
	private $_db,
		$_data,
		$_sessionName,
		$_isLoggedIn,
		$_permissions,
		$_useritem,
		$_usersettings;

	/*
	* Construct User
	* @since 4.0.0
	* @Param (String/Integer User)
*/
	public function __construct(mixed $user = null)
	{

		$this->_db = Database::getInstance();
		$this->_sessionName = Config::get('session/session_name');
		if (!$user) {
			if (Session::exists($this->_sessionName)) {
				$user = Session::get($this->_sessionName);
				if ($this->find($user))
					$this->_isLoggedIn = true;
			}
		} else
			$this->find($user);
	}

	/*
	* Create User
	* @since 4.0.0
	* @Param (Array Fields)
*/
	public function create(array $fields = array()): void
	{
		if (!$this->_db->insert(Config::get('table/users'), $fields))
			throw new Exception('There was a problem creating an account.');
	}

	/*
	* Find User
	* @since 4.0.0
	* @Param (String/Integer User)
*/
	public function find(mixed $user = null): bool
	{
		if ($user) {
			$field = (is_numeric($user)) ? 'id' : 'username';
			$data = $this->_db->get(Config::get('table/users'), array($field, '=', $user));
			if ($data->count()) {
				$this->_data = $data->first();
				$this->_permissions = $data->first()->permission;
				return true;
			}
		}
		return false;
	}

	/*
	* User Login
	* @since 4.0.0
	* @Param (String Username, String Password, Boolean Remember)
*/
	public function login(mixed $username = null,string $password = null,bool $remember = false): bool
	{
		if (!$username && !$password && $this->exists())
			Session::put($this->_sessionName, $this->data()->id);
		else {
			$user = $this->find($username);

			if ($user) {

				if ($this->data()->password === Hash::make($password)) {
					Session::put($this->_sessionName, $this->data()->id);

					if ($remember) {
						$hash = Hash::generateRandomString(35);
						$hashCheck = $this->_db->get('user_session', array('user_id', '=', $this->data()->salt));

						if (!$hashCheck->count())
							$this->_db->insert('users_session', array('user_id' => $this->data()->id, 'hash' => $hash));
						else
							$hash = $hashCheck->first()->hash;

						Cookie::put($this->_cookieName, $hash, Config::get('remember/cookie_expiry'));
					}

					return true;
				}
			}
		}
		return false;
	}

	/*
	* User Has Permission
	* @since 4.0.0
	* @Param (String Key)
*/
	public function hasPermission(string $key): bool
	{
		if (!is_null($this->_permissions)) {
			if (strlen(cast::_string($this->_permissions)) > 0) {
				$permission = json_decode($this->_permissions, true);
				if (filter::bool(cast::_string($permission[$key])))
					return true;
			}
		}
		return false;
	}

	/*
	* User Has Item
	* @since 4.0.1
	* @Param (String Key)
*/
	public function hasUserItem(string $key): string
	{
		if (strlen($this->_useritem) > 0) {
			$permission = json::decode($this->_useritem, true);
			return $permission[$key];
		}
		return '';
	}

	/*
	* User Settings
	* @since 4.0.1
	* @Param (String Key)
*/
	public function hasUserSettings(string $key): string
	{
		if (strlen($this->_usersettings) > 0) {
			$permission = json::decode($this->_usersettings, true);
			return $permission[$key];
		}
		return '';
	}
	/*
	* User Exists
	* @since 4.0.0
	* @Param ()
*/
	public function exists(): bool
	{
		return (!empty($this->_data)) ? true : false;
	}

	/*
	* User Logout
	* @since 4.0.0
	* @Param ()
*/
	public function logout(): void
	{
		$this->_db->delete('users_session', array('user_id', '=', $this->data()->id));
		$this->_isLoggedIn = false;
		$this->_data = '';
		$this->_permissions = '';
		if(SESSION::exists($this->_sessionName))
			SESSION::delete($this->_sessionName);

		if(Cookie::exists($this->_sessionName))
			Cookie::delete($this->_cookieName);
	}


	/*
	* Password Check
	* @since 4.0.0	
	* @param (String Password,Int Length)
*/
	public function password_check(string $password,int $min_length = 4): int
	{
		$password = preg_replace('/\s+/', ' ', $password);
		$strength = 0;
		if (strlen($password) >= $min_length) {
			if (preg_match_all('/[~!@#\$%\^&\*\(\)\-_=+\|\/;:,\.\?\[\]\{\}]/', $password, $match)) {
				$strength = 4;
				if (count($match[0]) > 1) {
					++$strength;
				}
			} else {
				if (preg_match('/[A-Z]+/', $password)) {
					++$strength;
				}
				if (preg_match('/[a-z]+/', $password)) {
					++$strength;
				}
				if (preg_match('/[0-9]+/', $password)) {
					++$strength;
				}
			}
			if (preg_match_all('/[^0-9a-z~!@#\$%\^&\*\(\)\-_=+\|\/;:,\.\?\[\]\{\}]/i', $password, $match)) {
				++$strength;
				if (count($match[0]) > 1) {
					++$strength;
				}
			}
		}
		return $strength;
	}

	/*
	* Generate Password
	* @since 4.0.0	
	* @param (Int Length,Int Strength)
*/
	public function password_generate(int $length = 10,int $strength = 5): string
	{
		static $special = [
			'~', '!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '-', '_',
			'=', '+', '|', '\\', '/', ';', ':', ',', '.', '?', '[', ']', '{', '}'
		];
		static $small, $capital;
		if ($length < 4) {
			$length = 4;
		}
		if ($strength < 1) {
			$strength = 1;
		} elseif ($strength > $length) {
			$strength = $length;
		}
		if ($strength > 5) {
			$strength = 5;
		}
		if (!isset($small)) {
			$small = range('a', 'z');
		}
		if (!isset($capital)) {
			$capital = range('A', 'Z');
		}
		$password = [];
		$symbols  = range(0, 9);
		if ($strength > 5) {
			$strength = 5;
		}
		if ($strength > $length) {
			$strength = $length;
		}
		if ($strength > 3) {
			$symbols = array_merge($symbols, $special);
		}
		if ($strength > 2) {
			$symbols = array_merge($symbols, $capital);
		}
		if ($strength > 1) {
			$symbols = array_merge($symbols, $small);
		}
		$size = count($symbols) - 1;
		while (true) {
			for ($i = 0; $i < $length; ++$i) {
				$password[] = $symbols[random_int(0, $size)];
			}
			shuffle($password);
			if ($this->password_check(implode('', $password)) == $strength) {
				return implode('', $password);
			}
			$password = [];
		}
		return '';
	}

	/*
	* User Data
	* @since 4.0.0
	* @Param ()
*/
	public function data(): array|object|null
	{
		return $this->_data;
	}

	/*
	* User Logged In
	* @since 4.0.0
	* @Param ()
*/
	public function isLoggedIn(): bool
	{
		return cast::_bool($this->_isLoggedIn);
	}
}
