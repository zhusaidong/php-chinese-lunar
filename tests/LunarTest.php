<?php
use ChineseLunar\Lunar;
use PHPUnit\Framework\TestCase;

class LunarTest extends TestCase
{
	public function __construct()
	{
		$this->lunar = new Lunar;
		parent::__construct();
	}
	//测试农历日
	public function testLunarDay()
	{
		$lunarDays = [1=>'初一','初二','初三','初四','初五','初六','初七','初八','初九','初十','十一','十二','十三','十四','十五','十六','十七','十八','十九','二十','廿一','廿二','廿三','廿四','廿五','廿六','廿七','廿八','廿九','三十'];
		foreach($lunarDays as $solarDay => $lunarDay)
		{
			$this->assertEquals($this->lunar->toSolarDay($lunarDay),$solarDay);
			$this->assertEquals($this->lunar->toLunarDay($solarDay),$lunarDay);
		}
	}
	//测试节气
	public function testSolarTerms()
	{
		$this->assertEquals($this->lunar->getSolarTerms(2018,'清明'),'2018-4-5');
		$this->assertEquals($this->lunar->getSolarTerms(2017,'清明'),'2017-4-4');
	}
	//测试时辰
	public function testAncientTime()
	{
		$ancientTime = ['子时','丑时','丑时','寅时','寅时','卯时','卯时','辰时','辰时','巳时','巳时','午时','午时','未时','未时','申时','申时','酉时','酉时','戌时','戌时','亥时','亥时','子时'];
		foreach(range(0,23) as $hour)
		{
			$this->assertEquals($this->lunar->getAncientTime($hour),$ancientTime[$hour]);
		}
	}
	//测试农历
	public function testLunarDate()
	{
		$dates = [
			'2018-02-15',
			'2018-02-16',
			date('Y-m-d',time()),
		];
		foreach($dates as $key => $date)
		{
			$dateArr = explode('-',$date);
			$t1      = mktime(0,0,0,$dateArr[1],$dateArr[2],$dateArr[0]);

			$lunarArr= $this->lunar->toLunarDate($dateArr[0],$dateArr[1],$dateArr[2]);
			
			$solarArr= $this->lunar->toSolarDate($lunarArr[0],$lunarArr[1],$lunarArr[2]);

			$t2      = mktime(0,0,0,$solarArr[1],$solarArr[2],$solarArr[0]);
			
			$this->assertEquals($t2,$t1);
		}
	}
}
