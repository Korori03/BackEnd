<?php

/*
	* Template Class Set
	* @Version 1.0.1
	* Developed by: Ami (亜美) Denault
*/

/*
	* Core Template Class
	* @since 1.0.0
*/

declare(strict_types=1);
class Template
{

	/*
	* Variables
	* @updates
	*	Moved Variables inside Class and had it Protected and Private to prevent un-needed access
	* @since 1.0.1
*/
	protected 	$tpl,
		$values = array();
	private $template_dir = 'content/themes/',
		$template_ext = 'tpl';


	/*
	* Construct if Class is called
	* @Update 1.0.1
	*	Added Extension Check
	* @since 1.0.0
*/
	public function __construct(string $tpl)
	{
		$file_parts = pathinfo($tpl);
		if (strtolower($file_parts['extension']) == $this->template_ext)
			$this->tpl = $this->template_dir . Options::get('template') . '/' . $tpl;
	}

	/*
	* Set Key file to Value
	* @since 1.0.0
	* @Param (String Key,String Value)
*/
	public function set(string $key, string $value): void
	{
		$this->values[$key] = trim($value);
	}

	/*
	* Set Key file to Value from an Array
	* @since 1.0.1
	* @Param (Array)
*/
	public function setArray(array $array): void
	{
		foreach ($array as $key => $value)
			$this->values[$key] = str::_trim(cast::_string($value));
	}

	/*
	* Return Template
	* @Update 1.0.3
	*	Changed Output to Show
	* 	Now can do basic Conidtional Statement
	* @since 1.0.0
	* @Param ()
*/
	public function show(): string
	{
		if (!file_exists($this->tpl))
			return "Error loading template file (" . $this->tpl . ")<br />";

		$message = file_get_contents($this->tpl);

		preg_match_all('/\[IF \{([^\}]*)\}\](.[^\]]+)(?:\[ELSE\](.+?))?\[ENDIF\]/s', $message, $matches);
		if (empty($matches))
			return $message;

		$math_tag = '';
		foreach ($matches[0] as $m_index => $match) {
			$math_tag =  trim($matches[1][$m_index]);
			if (!empty($tags[$math_tag]))
				$message = str_replace($match, $matches[2][$m_index], $message);
			elseif (empty($tags[$math_tag]) && $matches[3][$m_index])
				$message = str_replace($match, $matches[3][$m_index], $message);
			else
				$message = str_replace($match, '', $message);
		}

		//Replace Keys with Values
		foreach ($this->values as $key => $value) {
			$tagToReplace = "[@$key]";
			$message = str_replace($tagToReplace, $value, $message);
		}
		return $message;
	}

	/*
	* Read TPL return Content Only
	* @since 1.0.1
	* @Param (String Tpl)
*/
	public function readtpl(string $tpl_temp): string
	{
		$extension = explode('.', $tpl_temp);
		$extension = end($extension);
		if (strtolower($extension) == 'tpl') {
			if (!file_exists($this->template_dir . Options::get('template') . '/' . $tpl_temp))
				return "Error loading template file <br />";
			else
				return file_get_contents($this->template_dir . Options::get('template') . '/' . $tpl_temp);
		}
	}
}
