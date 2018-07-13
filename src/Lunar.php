<?php
/**
* Lunar 农历
* @author zhusaidong [zhusaidong@gmail.com]
*/
namespace ChineseLunar;

use DateTime;
use InvalidArgumentException;
use Exception;
use ZipArchive;

class Lunar
{
	/**
	* @const 最小年份
	*/
	const MIN_YEAR = 1901;
	/**
	* @const 最大年份
	*/
	const MAX_YEAR = 2100;
	
	/**
	* @var string 数据包路径
	*/
	private $zipPath = 'LunarData.zip';
	/**
	* @var array 农历月份
	*/
	private $lunarMonth = [1=>'正月','二月','三月','四月','五月','六月','七月','八月','九月','十月','冬月','腊月'];
	/**
	* @var array 农历日个位名称
	*/
	private $lunarDay = [1=>'一','二','三','四','五','六','七','八','九','十'];
	/**
	* @var array 农历日十位名称
	*/
	private $lunarDayTen = [0=>'初',1=>'十',2=>'廿',3=>'三',4=>'二'];
	/**
	* @var array 天干
	*/
	private	$sky = ['甲','乙','丙','丁','戊','己','庚','辛','壬','癸'];
	/**
	* @var array 地支
	*/
	private	$earth = ['子','丑','寅','卯','辰','巳','午','未','申','酉','戌','亥'];
	/**
	* @var array 生肖
	*/
	private	$chineseZodiac = ['猴','鸡','狗','猪','鼠','牛','虎','兔','龙','蛇','马','羊'];
	/**
	* @var array 阳历月份天数
	*/
	private	$solarMonthDays = [31,28,31,30,31,30,31,31,30,31,30,31];
	/**
	* @var array 时辰
	*/
	private $ancientTime = [
		'00-01'=>'子时',
		'01-03'=>'丑时',
		'03-05'=>'寅时',
		'05-07'=>'卯时',
		'07-09'=>'辰时',
		'09-11'=>'巳时',
		'11-13'=>'午时',
		'13-15'=>'未时',
		'15-17'=>'申时',
		'17-19'=>'酉时',
		'19-21'=>'戌时',
		'21-23'=>'亥时',
		'23-24'=>'子时',
	];

	/**
	 * 读取某年农历信息
	 *
	 * @param int $year 年
	 *
	 * @return array 农历信息
	 * @throws Exception
	 */
	private function getLunarInfo($year)
	{
		if (!is_numeric($year) or $year < self::MIN_YEAR or $year > self::MAX_YEAR)
		{
            throw new InvalidArgumentException('error year '.$year);
        }
        return unserialize($this->unExtractZipReadFile('LunarData/lunarInfo_' . $year . '.json'));
	}

	/**
	 * 不解压,读取zip里的某文件内容
	 *
	 * @param string $file zip里的某文件路径
	 *
	 * @return string 文件内容
	 * @throws Exception
	 */
	private function unExtractZipReadFile($file)
	{
		$zipPath = __DIR__.'/'.$this->zipPath;
        if(!is_file($zipPath))
        {
            throw new Exception('the lunar data file not exist');
		}
		if(!class_exists('ZipArchive'))
		{
            throw new Exception('Your PHP version is not compiled with zip support');
        }
        
		$content = '';
		$zip  = new ZipArchive();
		if($zip->open($zipPath) === TRUE)
		{
			if(($content = $zip->getFromName($file)) === FALSE)
			{
	            throw new Exception($zip->getStatusString());
	        }
			$zip->close();
		}
		
		if(empty($content))
		{
			throw new Exception('can not extract the zip');
		}
		return $content;
	}
	/**
	* 日期差
	* 
	* @param string|DateTime $dateTime1 日期1
	* @param string|DateTime $dateTime2 日期2
	* 
	* @return int 差
	*/
	private function dateDiff($dateTime1,$dateTime2)
	{
		if(!($dateTime1 instanceof DateTime))
		{
			$dateTime1 = new DateTime($dateTime1);
		}
		if(!($dateTime2 instanceof DateTime))
		{
			$dateTime2 = new DateTime($dateTime2);
		}
		return intval($dateTime1->diff($dateTime2)->format('%a'));
	}
	/**
	* 天干地支算法
	* 
	* @param int $num
	* 
	* @return string
	*/
	private function getSkyEarth($num)
	{
		return $this->sky[$num % 10].$this->earth[$num % 12];
	}
	
	/**
	* 阳历月转农历月
	* 
	* @param int $solarMonth 阳历月
	* 
	* @return string 农历月
	*/
	public function toLunarMonth($solarMonth)
	{
		$lunarMonthArr = $this->lunarMonth;
		return isset($lunarMonthArr[$solarMonth]) ? $lunarMonthArr[$solarMonth] : NULL;
	}
	/**
	* 农历月转阳历月
	* 
	* @param string $lunarMonth 农历月
	* 
	* @return int 阳历月
	*/
	public function toSolarMonth($lunarMonth)
	{
		//别称
		switch($lunarMonth)
		{
			case '十一月':
				$lunarMonth = '冬月';
				break;
			case '十二月':
				$lunarMonth = '腊月';
				break;
		}
		$lunarMonthArr = array_flip($this->lunarMonth);
		return isset($lunarMonthArr[$lunarMonth]) ? $lunarMonthArr[$lunarMonth] : NULL;
	}
	/**
	* 阳历日转农历日
	* 
	* @param int $solarDay 阳历日
	* 
	* @return string 农历日
	*/
	public function toLunarDay($solarDay)
	{
		$lunarDayArr = $this->lunarDay;
		$lunarDayTenArr = $this->lunarDayTen;

		$lunarDayTen = 0;
		$lunarDay = 0;

		switch(TRUE)
		{
			case $solarDay <= 10:
				$lunarDayTen = 0;
				$lunarDay = $solarDay;
				break;
			case $solarDay > 10 and $solarDay <= 19:
				$lunarDayTen = 1;
				$lunarDay = $solarDay - 10;
				break;
			case $solarDay == 20:
				$lunarDayTen = 4;
				$lunarDay = 10;
				break;
			case $solarDay > 20 and $solarDay <= 29:
				$lunarDayTen = 2;
				$lunarDay = $solarDay - 20;
				break;
			case $solarDay >= 30:
				$lunarDayTen = 3;
				$lunarDay = 10;
				break;
		}

		return $lunarDayTenArr[$lunarDayTen].$lunarDayArr[$lunarDay];
	}
	/**
	* 农历日转阳历日
	* 
	* @param string $lunarDay 农历日
	* 
	* @return int 阳历日
	*/
	public function toSolarDay($lunarDay)
	{
		$lunarDayArr = array_flip($this->lunarDay);
		$lunarDayTenArr = array_flip($this->lunarDayTen);
		
		//特殊写法，比如二十二，替换成廿二
		if(mb_strlen($lunarDay) == 3 and mb_substr($lunarDay,0,2) == '二十')
		{
			$lunarDay = '廿'.mb_substr($lunarDay,2,1);
		}
		
		switch(TRUE)
		{
			case $lunarDay == '二十':
				return 20;
				break;
			case $lunarDay == '三十':
				return 30;
				break;
			default:
				$day1 = mb_substr($lunarDay,0,1);
				$day2 = mb_substr($lunarDay,1,1);
				
				return $lunarDayTenArr[$day1] * 10 + $lunarDayArr[$day2];
				break;
		}
		
	}

	/**
	 * 阳历日期转农历日期
	 *
	 * @param int     $year     年
	 * @param int     $month    阳历月
	 * @param int     $day      阳历日
	 * @param boolean $isNumber 返回数字农历日期
	 *
	 * @return array 农历日期
	 * @throws Exception
	 */
	public function toLunarDate($year,$month,$day,$isNumber = FALSE)
	{
		$year = intval($year);
		$month = intval($month);
		$day = intval($day);
		$solarDate = $year.'-'.$month.'-'.$day;
		
		$lunarInfo = $this->getLunarInfo($year);
		$lunarDate = $year.'-'.$lunarInfo['first_lunar_month'].'-'.$lunarInfo['first_lunar_day'];
		
		//阳历日期未到正月初一，故获取前一年数据
		if(strtotime($solarDate) - strtotime($lunarDate) < 0)
		{
			$lunarInfo = $this->getLunarInfo(--$year);
			$lunarDate = $year.'-'.$lunarInfo['first_lunar_month'].'-'.$lunarInfo['first_lunar_day'];
		}
		
		$lunarDays = $lunarInfo['lunar_month'];
		$leapMonth = $lunarInfo['leap_month'];
		
		$diff_days = $this->dateDiff($solarDate,$lunarDate);
		
		$month = $index = $isLeapMonth = 0;
		while($diff_days >= $lunarDays[$index])
		{
			$diff_days -= $lunarDays[$index];
			$index++;
			//如果是闰月，实际月份不变
			if($leapMonth > 0 and $leapMonth == $index)
			{
				$isLeapMonth = 1;
			}
			else
			{
				$isLeapMonth = 0;
				$month++;
			}
		}
		$day = $diff_days + 1;
		
		if($isNumber)
		{
			return [$year,$month + 1,$day];
		}
		return [$year,($isLeapMonth ? '闰' : '').$this->toLunarMonth($month + 1),$this->toLunarDay($day)];
	}
	/**
	* 农历日期转阳历日期
	* 
	* @param int $year 年
	* @param string|int $month 农历月
	* @param string|int $day 农历日
	* 
	* @return array 阳历日期
	* @throws Exception
	*/
	public function toSolarDate($year,$month,$day)
	{
		$isLeapMonth = strpos($month,'闰') !== FALSE;
		is_string($month) and $month = $this->toSolarMonth(str_replace('闰','',$month));
		is_string($day) and $day = $this->toSolarDay($day);
		
		$lunarInfo = $this->getLunarInfo($year);
		
		$lunarDate = $year.'-'.$lunarInfo['first_lunar_month'].'-'.$lunarInfo['first_lunar_day'];
		
		$lunarDays = $lunarInfo['lunar_month'];
		$leapMonth = $lunarInfo['leap_month'];
		
		//比如2017年是闰六月，传的数据却是2017年闰九月，说明传的农历月份不正确
		if($leapMonth != $month and $isLeapMonth)
		{
			throw new InvalidArgumentException('农历月份不正确');
		}
		
		if(
			$leapMonth == 0 or 
			($leapMonth > 0 and $leapMonth == $month and !$isLeapMonth) or
			($leapMonth > 0 and $leapMonth > $month)
		)
		{
			$month--;
		}
		$_days = array_sum(array_slice($lunarDays,0,$month)) + $day - 1;
		
		$time = strtotime($_days.' days',strtotime($lunarDate));
		return [intval(date('Y',$time)),intval(date('m',$time)),intval(date('d',$time))];
	}
	/**
	* 判断是否是闰年
	* 
	* @param int $year 年
	* 
	* @return boolean 是否是闰年
	*/
	public function isLeapYear($year)
	{
		return (($year % 4 == 0 && $year % 100 != 0) || ($year % 400 == 0));
	}
	/**
	* 获取天干地支年月日
	* 
	* @param int $year
	* @param int $month
	* @param int $day
	* 
	* @return array 天干地支年月日
	* @throws Exception
	*/
	public function getChineseEra($year,$month,$day)
	{
		if(strtotime($year.'-'.$month.'-'.$day) === FALSE)
		{
            throw new InvalidArgumentException('error date');
        }
		
		$chineseMonth = ($year - 1900) * 12 + $month + 11;
		
		$info = $this->getLunarInfo($year);
		foreach($info['solar_terms'] as $solar)
		{
			$time = strtotime($solar);
			if(date('Y',$time) == $year and date('m',$time) == $month)
			{
				if($day >= date('d',$time))
				{
					$chineseMonth++;
				}
				break;
			}
		}
		
		return [
			'year'	=>$this->getSkyEarth($year - 4),
			'month'	=>$this->getSkyEarth($chineseMonth),
			'day'	=>$this->getSkyEarth(mktime(0,0,0,$month,1,$year) / 86400 + 25567 + 10 + $day - 1),
		];
	}
	/**
	* 根据阴历年获取生肖
	* 
	* @param int year 阴历年
	* 
	* @return string 生肖
	*/
	public function getChineseZodiac($year)
	{
		return $this->chineseZodiac[$year % 12];
	}
	/**
	* 获取阳历月份的天数
	* 
	* @param int year 阳历年
	* @param int month 阳历月
	* 
	* @return int 天数
	*/
	public function getSolarMonthDays($year,$month)
	{
		$monthDays = $this->solarMonthDays;
		$this->isLeapYear($year) and $monthDays[1]++;
		return $monthDays[$month - 1];
	}

	/**
	 * 获取节气
	 *
	 * @param int         $year       阳历年
	 * @param string|null $solarTerms 节气名称
	 *
	 * @return array|string 节气时间
	 * @throws Exception
	 */
	public function getSolarTerms($year,$solarTerms = NULL)
	{
		$lunarInfo = $this->getLunarInfo($year);
		$solarTermsInfo = $lunarInfo['solar_terms'];
		return $solarTerms == NULL ? $solarTermsInfo : (isset($solarTermsInfo[$solarTerms]) ? $solarTermsInfo[$solarTerms] : NULL);
	}
	/**
	* 获取时辰
	* 
	* @param int $hour 现代小时
	* 
	* @return string 时辰
	*/
	public function getAncientTime($hour)
	{
		$ancientTime = $this->ancientTime;
		foreach($ancientTime as $timeslot => $ancient)
		{
			$timeslots = explode('-',$timeslot);
			if($hour >= $timeslots[0] and $hour < $timeslots[1])
			{
				return $ancient;
			}
		}
		return NULL;
	}
	/**
	* 获取当前时间的天数和周数
	* 
	* @param int $timestamp 时间戳
	* 
	* @return array 天数和周数
	*/
	public function getDateInfo($timestamp)
	{
		$date = new DateTime;
		$date->setTimestamp($timestamp);
		
		$date1= new DateTime;
		$date1->setTimestamp(mktime(0,0,0,1,1,date('Y',$timestamp)));
		
		$days = $date->diff($date1)->format('%a') + 1;
		return [
			'days'=>$days,
			'weeks'=>ceil($days / 7),
		];
	}

	/**
	 * 获取年的信息
	 *
	 * @param int $year 年
	 *
	 * @return array 信息
	 * @throws Exception
	 */
	public function getYearInfo($year)
	{
		return [
			'isLeapYear'	=>$this->isLeapYear($year),
			'chineseZodiac'	=>$this->getChineseZodiac($year),
			'solarTerms'	=>$this->getSolarTerms($year),
		];
	}
}
