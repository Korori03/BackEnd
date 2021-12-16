<?php

/*
	* Calendar Class Set
	* @Version 4.0.0
	* Developed by: Ami (亜美) Denault
*/
declare(strict_types=1);
class Calendar{

/*
	* Variables
	* @Since 4.1.0
*/		
	private $_url = NULL,
			$_query = NULL,
			$_month = NULL,
			$_year = NULL;

/*
	* Construct Function
	* @Since 4.0.0
	* @Param (String URL,String Query,Int Int,Int Year)
*/				
	public function __construct(string $url = NULL, string $query = NULL,int $month = NULL,int $year = NULL) {
		$this->_month = (is_null($month)?date::_custom('n'):$month);
		$this->_year = (is_null($year)?date::_custom('Y'):$year);
		$this->_url = $url;
		$this->_query = $query;
		$this->show();
	}	

/*
	* Show Calendar
	* @Since 4.0.0
	* @Param (None)
*/		
	public function show():string{
		
		$month = $this->_month;
		$year = $this->_year;

		$info = grabber::fromURL($this->_url);
		$api_array = json::decode($info, true);
		$calendar = array();

		foreach($api_array as $key => $item)
			$calendar[] = array("name"=>str::_format($item['name']),"date"=> date::_custom($item['timestamp'],"m-d-Y"),"type"=>"holiday");
		
		$cal_Row = Database::getInstance()->query($this->_query);
		foreach($cal_Row->results() as $cal_info)
			$calendar[] = array("name"=>str::_format($cal_info->name),"date"=> date::_custom($cal_info->cal_date,"m-d-Y"),"type"=>"event");

		$cal_Row->close();
		
		arr::_sksort($calendar,"date",true);
		$layout = new Template("calendar.tpl");		
		
		list($cal_out,$events) = self::view($calendar,$year,$month);
	
		$event_items ='';
		foreach($events as $item)
			$event_items .= '<li style="padding-top:30px;">'.$item .'</li><li><div style="margin-top:7px;width:100%;webkit-border-radius: 20px;-moz-border-radius: 20px;border-radius: 20px;margin-left:auto;margin-right:auto;background-color:#daebfc;height:4px;">&#32;&#32;&#32;&#32;&#32;&#32;</div></li>';

		$layout->setArray(array(
			'events'=>$event_items,
			'calendar'=>$cal_out,
			'rowspan'=>self::weeks($month,$year) +1,
			'month'=>date::_custom($year."-".$month."-01",'F'),
			'year'=>$year,
			'cmonth'=>$month,
			'pyear'=>$year - 1,
			'nyear'=>$year + 1,
			'pmonth'=>($month  -1 < 1?12:$month - 1),
			'nmonth'=>($month +1 > 12?1:$month + 1),
			'pmonth_year'=>($month  -1 < 1?$year - 1:$year),
			'nmonth_year'=>($month +1 > 12?$year + 1:$year)
		));
		
		return $layout->show();
	}

/*
	* Print Calendar
	* @Since 4.0.0
	* @Param (Array Events,Int Year,Int Month)
*/		
	public static function view(array $events,int $year,int $month):array{
		
		$calendar ='';
		$event_list = array();
		$first_of_month = $month .'-01-'.$year;
		$first_day = 0;

		list($month, $year, $weekday) = explode(',',date::_custom($first_of_month,'m,Y,w'));
	
		$weekday = ($weekday + 7 - $first_day) % 7;
		

		for($day = 1,$days_in_month=date::_custom($first_of_month,'t');$day<=$days_in_month; $day++,$weekday++){
			
			if($weekday == 7){
				$weekday   = 0;
				$calendar .= "</tr><tr>";	
			}
			  
			if($day == 1 && $weekday != 0)
				$calendar.="<td colspan=\"".$weekday."\"></td>";
			   
		    $check = false;
			if(($day == date::_custom(NULL,'d'))  && (date::_custom(NULL,'n') == $month) && (date::_custom(NULL,'Y') ==$year)){
				$calendar .= '<td><div class="calendar_current">'. $day ."</div></td>";
				for($info_index = 0;$info_index < count($events);$info_index++){
					$get_Day = explode('-',$events[$info_index]['date']);
					if(($day == $get_Day[1])  && ($get_Day[0] == $month) && ($get_Day[2] ==$year)){
						$event_list[] = $events[$info_index]['name'] . ' ('. self::addOrdinalNumberSuffix($day) .')';
						break;
					}
				}
				$check = true;
			}
			else{
				for($info_index = 0;$info_index < count($events);$info_index++){
					$get_Day = explode('-',$events[$info_index]['date']);
					if(($day == $get_Day[1])  && ($get_Day[0] == $month) && ($get_Day[2] ==$year)){	
						$event_list[] = $events[$info_index]['name'] . ' ('. self::addOrdinalNumberSuffix($day) .')';
						$calendar .= '<td><div class="calendar_item">'. $day ."</div></td>";
						$check = true;
						break;	
					}
				}
			}
			
			if($check == false)
				$calendar .= '<td>'. $day  ."</td>";
		}
		if($day == $days_in_month)
			$calendar .= '<td colspan="'.(7-$weekday).'">&nbsp;</td>';
		
		$calendar .= "</tr></table>";
		
		return array($calendar,$event_list);
	}	

/*
	* Add Number Suffix
	* @Since 4.0.0
	* @Param (Int Day)
*/		
	public static function addOrdinalNumberSuffix(int $num):string {
		if (!in_array(($num % 100),array(11,12,13))){
		  switch ($num % 10) {
			case 1:  return $num.'st';
			case 2:  return $num.'nd';
			case 3:  return $num.'rd';
		  }
		}
		return $num.'th';
	}
	
/*
	* Get Number of Weeks in Month
	* @Since 4.0.0
	* @Param (Int Month,Int Year)
*/
	public static function weeks(int $month,int $year): float{
		$days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
		$week_day = date::_custom($month . '-01-'. $year,"N");
		$weeks = ceil(($days + $week_day) / 7);
		return $weeks + 1;
	}

}
